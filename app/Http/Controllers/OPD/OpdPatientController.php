<?php
namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpdPatientRequest;
use App\Models\Appointment;
use App\Models\ConsultationNote;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorFee;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\OpdPatientDocument;
use App\Models\PatientCharge;
use App\Models\PatientHistory;
use App\Models\Prescription;
use App\Models\Transaction;
use App\Services\FontDesk\CaseReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdPatientController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager-load enough to compute the auto-posted charges total per visit
        // without N+1 queries. Postings are linked to the encounter; the bill
        // (if assembled) is also resolved by encounter.
        $with = [
            'patient',
            'doctor',
        ];

        $today_opd_patients    = OpdPatient::with($with)->whereDate('date', today())->latest()->paginate(25, ['*'], 'today_page')->withQueryString();
        $upcoming_opd_patients = OpdPatient::with($with)->whereDate('date', '>', today())->orderBy('date')->paginate(25, ['*'], 'upcoming_page')->withQueryString();
        $old_opd_patients      = OpdPatient::with($with)->whereDate('date', '<', today())->latest('date')->paginate(25, ['*'], 'old_page')->withQueryString();
        $patient_view_list     = OpdPatient::with($with)
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('opd_patients')->groupBy('patient_id');
            })
            ->latest('date')->paginate(25, ['*'], 'all_page')->withQueryString();

        // Resolve charges + bill totals keyed by OPD id for the views to consume.
        $allOpds = $today_opd_patients
            ->concat($upcoming_opd_patients)
            ->concat($old_opd_patients)
            ->concat($patient_view_list)
            ->unique('id');

        $encounterIds = $allOpds->pluck('encounter_id')->filter()->unique()->values();

        $chargesByEncounter = $encounterIds->isEmpty() ? collect() :
            \App\Models\ServiceCharge\ServiceChargePosting::query()
                ->whereIn('encounter_id', $encounterIds)
                ->where('status', 'posted')
                ->selectRaw('encounter_id, COUNT(*) as posts, COALESCE(SUM(net_amount), 0) as charge_total')
                ->groupBy('encounter_id')
                ->get()
                ->keyBy('encounter_id');

        $billsByEncounter = $encounterIds->isEmpty() ? collect() :
            \App\Models\Billing\Bill::query()
                ->whereIn('encounter_id', $encounterIds)
                ->orderByDesc('id')
                ->get()
                ->keyBy('encounter_id');

        return view('opd_patients.index', compact(
            'today_opd_patients',
            'upcoming_opd_patients',
            'old_opd_patients',
            'patient_view_list',
            'chargesByEncounter',
            'billsByEncounter'
        ));
    }

    public function create()
    {
        $yesno_condition = [
            'yes' => 'Yes',
            'no'  => 'No',
        ];

        $patients = Patient::orderBy('patient_name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('opd_patients.create', compact('patients', 'yesno_condition', 'doctors', 'departments'));
    }

    public function getDoctorOpdFee(Request $request)
    {
        $request->validate(['doctor_id' => 'required|exists:doctors,id']);

        $fee = DoctorFee::where('doctor_id', $request->doctor_id)->first();

        return response()->json([
            'opd_visit_fee'      => $fee?->opd_visit_fee    ?? null,
            'first_visit_fee'    => $fee?->first_visit_fee    ?? null,
            'follow_up_fee'      => $fee?->follow_up_fee      ?? null,
            'follow_up_window'   => $fee?->follow_up_window ?? null,
        ]);
    }

    public function getPatientVisitHistory(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
        ]);

        $fee = DoctorFee::where('doctor_id', $request->doctor_id)->first();
        $window = $fee?->follow_up_window ?? null;

        $lastVisit = OpdPatient::where('patient_id', $request->patient_id)
            ->where('doctor_id', $request->doctor_id)
            ->latest('date')
            ->first();

        if (! $lastVisit) {
            return response()->json([
                'has_visited'      => false,
                'total_visits'     => 0,
                'follow_up_window' => $window,
            ]);
        }

        $lastDate   = \Carbon\Carbon::parse($lastVisit->date)->startOfDay();
        $daysSince  = (int) now()->startOfDay()->diffInDays($lastDate);
        $withinWindow = $window !== null && $daysSince <= (int) $window;

        $totalVisits = OpdPatient::where('patient_id', $request->patient_id)
            ->where('doctor_id', $request->doctor_id)
            ->count();

        return response()->json([
            'has_visited'             => true,
            'last_visit_date'         => $lastVisit->date->format('Y-m-d'),
            'last_visit_date_fmt'     => $lastDate->format('d M Y'),
            'last_visit_type'         => $lastVisit->visit_type,
            'last_visit_type_label'   => $lastVisit->visit_type_label,
            'days_since'              => $daysSince,
            'total_visits'            => $totalVisits,
            'follow_up_window'        => $window,
            'within_follow_up_window' => $withinWindow,
            'suggested_visit_type'    => $withinWindow ? 'follow_up' : 'new',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OpdPatientRequest $request, CaseReferenceService $caseService)
    {
        $validated = $request->validated();

        try {
        return DB::transaction(function () use ($request, $validated, $caseService) {

            // 1. Resolve patient
            if ($validated['patient_mode'] === 'new') {
                $patient = Patient::create([
                    'patient_name'          => $validated['patient_name'],
                    'mobileno'              => $validated['mobileno'],
                    'gender'                => $validated['gender'] ?? null,
                    'dob'                   => $validated['dob'] ?? null,
                    'blood_group'           => $validated['blood_group'] ?? null,
                    'discount_type'         => $validated['discount_type'] ?? null,
                    'organization_name'     => $validated['organization_name'] ?? null,
                    'organization_id'       => $validated['organization_id'] ?? null,
                    'organization_api_link' => $validated['organization_api_link'] ?? null,
                    'known_allergies'       => $validated['known_allergies'] ?? null,
                    'note'                  => $validated['note'] ?? null,
                    'created_by'            => auth()->id(),
                ]);
                $patientId = $patient->id;
            } else {
                $patientId = $validated['patient_id'];
                $patient   = Patient::findOrFail($patientId);

                // Update note/allergies on existing patient if provided
                $patient->update(array_filter([
                    'known_allergies' => $validated['known_allergies'] ?? null,
                    'note'            => $validated['note'] ?? null,
                ], fn($v) => $v !== null));
            }

            // 2. Upload supporting doc
            if ($request->hasFile('supporting_doc')) {
                $file     = $request->file('supporting_doc');
                $filename = 'supporting_doc_' . $patientId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('patients/supporting_docs', $filename, 'public');
                $patient->update(['supporting_doc' => $filename]);
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('patients', 'public');
                $patient->update(['image' => $imagePath]);
            }

            // 3. Create case reference
            $caseId = $caseService->createCase($patientId, 'OPD');

            // 4. Create OPD record
            $visitType = $validated['visit_type'] ?? 'new';
            [$slotFrom, $slotTo] = $this->splitSlot($validated['slot'] ?? null);

            $visitDate = $validated['appointment_date'];
            $deptId    = $validated['department_id'];
            $dept      = Department::find($deptId);
            $deptCode  = $dept?->code
                ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dept?->name ?? 'GEN'), 0, 3));
            $deptCode  = str_pad($deptCode ?: 'GEN', 3, 'X');
            $seq       = OpdPatient::whereDate('date', $visitDate)->where('department_id', $deptId)->count() + 1;
            $tokenNo   = \Carbon\Carbon::parse($visitDate)->format('Ymd') . '-' . $deptCode . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);

            $opd = OpdPatient::create([
                'case_id'         => $caseId,
                'patient_id'      => $patientId,
                'doctor_id'       => $validated['consultant_doctor'],
                'department_id'   => $deptId,
                'date'            => $visitDate,
                'visit_type'      => $visitType,
                'shift_id'        => $validated['shift_id'] ?? null,
                'slot_time_from'  => $slotFrom,
                'slot_time_to'    => $slotTo,
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'referral_source' => in_array($visitType, ['referred', 'emergency']) ? ($validated['referral_source'] ?? null) : null,
                'remarks'         => $validated['reference'] ?? null,
                'priority'        => $validated['priority'] ?? 'Normal',
                'token_no'        => $tokenNo,
                'status'          => 'Registered',
            ]);

            $opd->update(['root_visit_id' => $opd->id]);

            // 5. Create patient charge
            $appliedCharge = $validated['applied_charge'] ?? 0;
            $discount      = $validated['discount'] ?? 0;
            $tax           = $validated['tax'] ?? 0;
            $netAmount     = $validated['amount'] ?? 0;

            PatientCharge::create([
                'case_id'       => $caseId,
                'opd_id'        => $opd->id,
                'charge_module' => 'opd',
                'doctor_id'     => $validated['consultant_doctor'],
                'department_id' => $validated['department_id'],
                'charge_item'   => 'Consultant Doctor Fee',
                'charge_id'     => null,
                'unit_price'    => $validated['standard_charge'] ?? 0,
                'quantity'      => 1,
                'amount'        => $appliedCharge,
                'tax'           => $tax,
                'net_amount'    => $netAmount,
                'date'          => $validated['appointment_date'],
                'status'        => 'pending',
                'is_paid'       => ($validated['paid_amount'] ?? 0) >= $netAmount,
                'created_by'    => auth()->id(),
            ]);

            // 6. Create appointment record
            $department = $dept;

            Appointment::create([
                'patient_id'         => $patientId,
                'case_reference_id'  => $caseId,
                'visit_details_id'   => $opd->id,
                'date'               => $validated['appointment_date'],
                'time'               => $slotFrom,
                'priority'           => $validated['priority'] ?? 'Normal',
                'specialist'         => $department?->name ?? '',
                'doctor'             => $validated['consultant_doctor'],
                'amount'             => $netAmount,
                'message'            => $validated['chief_complaint'] ?? null,
                'appointment_status' => 'Approved',
                'visit_status'       => 'checked_in',
                'source'             => 'Walk-in',
                'is_opd'             => 'Yes',
                'is_ipd'             => 'No',
                'shift_id'           => $validated['shift_id'] ?? null,
                'slot_time_from'     => $slotFrom,
                'slot_time_to'       => $slotTo,
                'is_queue'           => null,
                'live_consult'       => 'None',
            ]);

            // 7. Create transaction
            if (($validated['paid_amount'] ?? 0) > 0) {
                Transaction::create([
                    'patient_id'         => $patientId,
                    'case_id'            => $caseId,
                    'opd_patient_id'     => $opd->id,
                    'type'               => 'income',
                    'section'            => 'opd',
                    'amount'             => $appliedCharge,
                    'tax'                => $tax,
                    'discount'           => $discount,
                    'net_amount'         => $validated['paid_amount'],
                    'payment_via'        => $validated['payment_mode'],
                    'payment_date'       => now(),
                    'cheque_no'          => $validated['cheque_no'] ?? null,
                    'cheque_date'        => $validated['cheque_date'] ?? null,
                    'cheque_name'        => $validated['bank_name'] ?? null,
                    'mfs_transaction_id' => $validated['transaction_id'] ?? null,
                    'mfs_no'             => $validated['upi_id'] ?? null,
                    'notes'              => $validated['other_payment_details'] ?? null,
                    'received_by'        => auth()->id(),
                    'status'             => 'paid',
                ]);
            }

            // 8. Save patient documents
            if ($request->has('documents')) {
                foreach ($request->input('documents', []) as $idx => $doc) {
                    $uploaded = $request->file("documents.$idx.file");
                    if (! $uploaded || ! $uploaded->isValid()) {
                        continue;
                    }
                    $stored = $uploaded->store('opd_patient_documents', 'public');
                    OpdPatientDocument::create([
                        'opd_patient_id' => $opd->id,
                        'title'          => $doc['title'] ?? null,
                        'file'           => $stored,
                        'remarks'        => $doc['remarks'] ?? null,
                    ]);
                }
            }

            // 9. Optional package enrolment — auto-post bundle services
            if ($request->filled('enroll_package_id')) {
                try {
                    $pkg = \App\Models\Package::find($request->input('enroll_package_id'));
                    if ($pkg && $pkg->is_active && $opd->encounter_id) {
                        \App\Models\Package\PackageEnrollment::create([
                            'package_id'    => $pkg->id,
                            'encounter_id'  => $opd->encounter_id,
                            'patient_id'    => $patientId,
                            'enrollment_no' => 'PE-' . now()->format('YmdHis') . '-' . $patientId,
                            'start_date'    => now(),
                            'end_date'      => now()->addDays($pkg->validity_days ?: 30),
                            'agreed_price'  => $pkg->total_amount,
                            'paid_amount'   => 0,
                            'status'        => 'active',
                            'notes'         => 'Auto-enrolled at OPD registration',
                            'created_by'    => auth()->id(),
                        ]);
                    }
                } catch (\Throwable $pe) {
                    \Log::warning('OPD package auto-enroll failed', ['err' => $pe->getMessage()]);
                }
            }

            return redirect()->route('opd-patients.index')
                ->with('success', 'OPD patient registered successfully.');
        });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withInput()->withErrors([
                    'mobileno' => 'This phone number is already registered. Please select the existing patient instead.',
                ]);
            }
            throw $e;
        }
    }

    public function recheckup(OpdPatient $opdPatient, CaseReferenceService $caseService)
    {
        return DB::transaction(function () use ($opdPatient, $caseService) {
            $caseId = $caseService->createCase($opdPatient->patient_id, 'OPD');

            $recheckup = OpdPatient::create([
                'case_id'         => $caseId,
                'patient_id'      => $opdPatient->patient_id,
                'doctor_id'       => $opdPatient->doctor_id,
                'department_id'   => $opdPatient->department_id,
                'date'            => now(),
                'remarks'         => $opdPatient->remarks,
                'status'          => 'Registered',
                'visit_type'      => 'recheckup',
                'parent_visit_id' => $opdPatient->id,
                'root_visit_id'   => $opdPatient->root_visit_id ?? $opdPatient->id,
            ]);

            return redirect()->route('opd-patients.show', $recheckup->id)
                ->with('success', 'Recheckup visit created successfully.');
        });
    }

    public function moveToIpd(Request $request, $id)
    {
        $opdPatient = OpdPatient::findOrFail($id);

        $validated = $request->validate([
            'admission_date'          => 'required|date',
            'possible_discharge_date' => 'nullable|date|after_or_equal:admission_date',
            'admission_type'          => 'nullable|string|max:50',
            'doctor_id'               => 'nullable|exists:doctors,id',
            'department_id'           => 'nullable|exists:departments,id',
            'patient_history'         => 'nullable|string',
            'remarks'                 => 'nullable|string',
        ]);

        $ipdPatient = \App\Models\IpdPatient::create([
            'case_id'                 => $opdPatient->case_id,
            'patient_id'              => $opdPatient->patient_id,
            'doctor_id'               => $validated['doctor_id'] ?? $opdPatient->doctor_id,
            'department_id'           => $validated['department_id'] ?? $opdPatient->department_id,
            'admission_date'          => $validated['admission_date'],
            'possible_discharge_date' => $validated['possible_discharge_date'] ?? null,
            'admission_type'          => $validated['admission_type'] ?? null,
            'patient_history'         => $validated['patient_history'] ?? null,
            'remarks'                 => $validated['remarks'] ?? $opdPatient->remarks,
            'status'                  => 'admitted',
        ]);

        $opdPatient->update(['status' => 'Moved to Ipd']);

        return redirect()
            ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
            ->with('success', 'Patient successfully moved to Ipd.');
    }

    public function moveToIpdForm($id)
    {
        $opdPatient  = OpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($id);
        $doctors     = Doctor::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('opd_patients.move_to_ipd', compact('opdPatient', 'doctors', 'departments'));
    }

    public function print($id)
    {
        $opdPatient = OpdPatient::with([
            'patient',
            'doctor.department',
            'doctor.designation',
            'department',
            'vitalChecks',
            'medications.medicine.unit',
            'charges',
            'transactions',
            'prescriptions.doctor',
            'prescriptions.symptoms.symptom',
            'prescriptions.medicines.medicine',
            'prescriptions.labInvestigations.labInvestigation',
        ])->findOrFail($id);

        return view('opd_patients.print', compact('opdPatient'));
    }

    public function bill($id)
    {
        $opdPatient = OpdPatient::with([
            'patient',
            'doctor.department',
            'doctor.designation',
            'department',
            'vitalChecks',
            'charges',
            'transactions',
            'prescriptions.doctor',
            'prescriptions.symptoms.symptom',
            'prescriptions.medicines.medicine',
            'prescriptions.labInvestigations.labInvestigation',
            'medications.medicine.unit',
            'documents',
        ])->findOrFail($id);

        // Pull encounter-layer data so the bill shows auto-posted charges +
        // assembled bills + collected payments alongside the legacy rows.
        $encounterCharges = collect();
        $bills = collect();
        $packageEnrollments = collect();
        if ($opdPatient->encounter_id) {
            $encounterCharges = \App\Models\ServiceCharge\ServiceChargePosting::with('catalog')
                ->where('encounter_id', $opdPatient->encounter_id)
                ->where('status', 'posted')
                ->latest('id')->get();
            $bills = \App\Models\Billing\Bill::with(['items.service', 'payments', 'discounts', 'refunds'])
                ->where('encounter_id', $opdPatient->encounter_id)
                ->latest('id')->get();
            $packageEnrollments = \App\Models\Package\PackageEnrollment::with('package.services.service', 'entries')
                ->where(function ($q) use ($opdPatient) {
                    $q->where('encounter_id', $opdPatient->encounter_id)
                        ->orWhere('patient_id', $opdPatient->patient_id);
                })
                ->where('status', 'active')
                ->get();
        }

        $primaryBill = $bills->first();
        $totals = [
            'legacy_charge_total' => (float) $opdPatient->charges->sum('net_amount'),
            'legacy_paid_total'   => (float) $opdPatient->transactions->sum('net_amount'),
            'encounter_charge_total' => (float) $encounterCharges->sum('net_amount'),
            'bill_grand_total'    => (float) $bills->sum('grand_total'),
            'bill_paid_total'     => (float) $bills->sum('paid_total'),
            'bill_balance_due'    => (float) $bills->sum('balance_due'),
        ];
        $totals['combined_charge'] = $totals['legacy_charge_total'] + $totals['encounter_charge_total'];
        $totals['combined_paid']   = $totals['legacy_paid_total'] + $totals['bill_paid_total'];

        return view('opd_patients.bill', compact(
            'opdPatient', 'encounterCharges', 'bills', 'primaryBill',
            'packageEnrollments', 'totals'
        ));
    }

    public function createPrescription($id)
    {
        $patient = OPDPatient::with(['patient', 'doctor', 'department'])->findOrFail($id);
        $doctors = Doctor::all();

        return view('opd_patients.prescriptions', compact('patient', 'doctors'));
    }

    public function detailsModal($id)
    {
        $patient = OPDPatient::with([
            'patient',
            'doctor',
            'department',
        ])->findOrFail($id);

        return view('opd_patients.visit_details_modal_body', compact('patient'));
    }

    public function manualPrescriptionModal($id)
    {
        $patient = OPDPatient::with([
            'patient',
            'doctor',
            'department',
        ])->findOrFail($id);

        return view('opd_patients.manual_prescription_modal_body', compact('patient'));
    }

    public function manualPrescriptionStore(Request $request, $id)
    {
        $patient = OPDPatient::findOrFail($id);

        $request->validate([
            'finding'    => 'nullable|string',
            'symptoms'   => 'nullable|string',
            'medicine'   => 'nullable|string',
            'test'       => 'nullable|string',
            'advice'     => 'nullable|string',
            'next_visit' => 'nullable|date',
        ]);

        Prescription::create([
            'opd_patient_id' => $patient->id,
            'patient_id'     => $patient->patient_id,
            'doctor_id'      => $patient->consultant_doctor,
            'finding'        => $request->finding,
            'symptoms'       => $request->symptoms,
            'medicine'       => $request->medicine,
            'test'           => $request->test,
            'advice'         => $request->advice,
            'next_visit'     => $request->next_visit,
            'generated_by'   => auth()->id(),
            'type'           => 'manual',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Manual prescription saved successfully.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(OpdPatient $opdPatient)
    {
        $opdPatient->load([
            'patient',
            'doctor',
            'department',
            'vitalChecks',
            'medications.medicine.unit',
            'charges',
            'transactions',
            'prescriptions.doctor',
            'prescriptions.symptoms.symptom',
            'prescriptions.medicines.medicine',
            'prescriptions.labInvestigations.labInvestigation',
            'consultationNote.createdBy',
            'recheckups.doctor',
            'documents',
        ]);

        $patientHistories = PatientHistory::where('patient_id', $opdPatient->patient_id)
            ->with('recordedBy')
            ->latest()
            ->get();

        $doctors = Doctor::select('id', 'name')->orderBy('name')->get();

        $radiologyType  = LabInvestigationType::where('name', 'Radiology')->select('id', 'name')->first();
        $radCategories  = $radiologyType
            ? LabInvestigationCategory::where('type_id', $radiologyType->id)->select('id', 'type_id', 'name')->orderBy('name')->get()
            : collect();
        $radInvestigations = $radCategories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $radCategories->pluck('id'))->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        $pathologyType  = LabInvestigationType::where('name', 'Pathology')->select('id', 'name')->first();
        $pathCategories = $pathologyType
            ? LabInvestigationCategory::where('type_id', $pathologyType->id)->select('id', 'type_id', 'name')->orderBy('name')->get()
            : collect();
        $pathInvestigations = $pathCategories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $pathCategories->pluck('id'))->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        $labOrders = LabInvestigationOrder::with([
                'doctor',
                'requests.labInvestigation',
                'requests.labInvestigationCategory',
            ])
            ->whereIn('type', ['radiology', 'pathology'])
            ->where('patient_id', $opdPatient->patient_id)
            ->latest('id')
            ->get();

        return view('opd_patients.show', compact(
            'opdPatient', 'patientHistories',
            'doctors',
            'radiologyType', 'radCategories', 'radInvestigations',
            'pathologyType', 'pathCategories', 'pathInvestigations',
            'labOrders'
        ));
    }

    public function edit(OpdPatient $opdPatient)
    {
        $opdPatient->load(['patient', 'doctor', 'department', 'documents']);
        $doctors     = Doctor::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('opd_patients.edit', compact('opdPatient', 'doctors', 'departments'));
    }

    public function update(Request $request, OpdPatient $opdPatient)
    {
        $validated = $request->validate([
            'date'             => 'required|date',
            'doctor_id'        => 'required|exists:doctors,id',
            'department_id'    => 'required|exists:departments,id',
            'visit_type'       => 'required|in:new,follow_up,recheckup,referred,emergency',
            'chief_complaint'  => 'nullable|string|max:1000',
            'referral_source'  => 'nullable|string|max:255',
            'remarks'          => 'nullable|string|max:255',
            'status'           => 'nullable|string|max:50',
            'shift_id'         => 'nullable|exists:shifts,id',
            'slot'             => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],
            'documents.*.file' => 'nullable|file|max:5120|mimes:pdf,docx,png,jpg,jpeg',
        ]);

        [$slotFrom, $slotTo] = $this->splitSlot($validated['slot'] ?? null);
        unset($validated['slot']);

        if ($slotFrom !== null) {
            $validated['slot_time_from'] = $slotFrom;
            $validated['slot_time_to']   = $slotTo;
        }

        $opdPatient->update($validated);

        // Save newly uploaded documents
        if ($request->has('documents')) {
            foreach ($request->input('documents', []) as $idx => $doc) {
                $uploaded = $request->file("documents.$idx.file");
                if (! $uploaded || ! $uploaded->isValid()) {
                    continue;
                }
                $stored = $uploaded->store('opd_patient_documents', 'public');
                OpdPatientDocument::create([
                    'opd_patient_id' => $opdPatient->id,
                    'title'          => $doc['title'] ?? null,
                    'file'           => $stored,
                    'remarks'        => $doc['remarks'] ?? null,
                ]);
            }
        }

        return redirect()->route('opd-patients.show', $opdPatient->id)
            ->with('success', 'OPD patient updated successfully.');
    }

    public function storeDocument(Request $request, OpdPatient $opdPatient)
    {
        $request->validate([
            'documents'          => 'required|array|min:1',
            'documents.*.file'   => 'required|file|max:5120|mimes:pdf,docx,png,jpg,jpeg',
            'documents.*.title'  => 'nullable|string|max:255',
            'documents.*.remarks'=> 'nullable|string|max:500',
        ]);

        foreach ($request->input('documents', []) as $idx => $doc) {
            $uploaded = $request->file("documents.$idx.file");
            if (! $uploaded || ! $uploaded->isValid()) {
                continue;
            }
            OpdPatientDocument::create([
                'opd_patient_id' => $opdPatient->id,
                'title'          => $doc['title'] ?? null,
                'file'           => $uploaded->store('opd_patient_documents', 'public'),
                'remarks'        => $doc['remarks'] ?? null,
            ]);
        }

        return back()->with('success', 'Document(s) uploaded successfully.');
    }

    public function destroyDocument(OpdPatient $opdPatient, OpdPatientDocument $document)
    {
        abort_if($document->opd_patient_id !== $opdPatient->id, 403);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OpdPatient $opdPatient)
    {
        //
    }

    private function splitSlot(?string $slot): array
    {
        if (!$slot || !str_contains($slot, '|')) {
            return [null, null];
        }
        [$from, $to] = explode('|', $slot, 2);
        return [$from, $to];
    }
}
