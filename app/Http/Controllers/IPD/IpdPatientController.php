<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Http\Requests\IPDPatientRequest;
use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\IpdPatientDocument;
use App\Models\Patient;
use App\Models\PatientCharge;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IpdPatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ipdPatients = IpdPatient::with([
            'patient',
            'doctor',
            'department',
            'bedAllocations' => function ($query) {
                $query->latest('id');
            },
            'bedAllocations.bed',
            'prescriptions' => function ($query) {
                $query->latest('id')->limit(1);
            },
        ])
            ->latest()
            ->get();

        return view('ipd_patients.index', compact('ipdPatients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients    = Patient::select('id', 'patient_name', 'mrn')->get();
        $doctors     = Doctor::select('id', 'name', 'department_id')->get();
        $departments = Department::select('id', 'name')->get();

        $allBeds = Bed::with('bedType')
            ->where('is_reserved', false)
            ->get();

        $beds    = $allBeds->filter(fn($b) => ! optional($b->bedType)->is_icu)->values();
        $icuBeds = $allBeds->filter(fn($b) => optional($b->bedType)->is_icu)->values();

        return view('ipd_patients.create', compact('patients', 'doctors', 'departments', 'beds', 'icuBeds'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(IPDPatientRequest $request)
    {

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $patient = null;

            //existing
            if (! empty($data['patient_id'])) {
                $patient = Patient::findOrFail($data['patient_id']);
            } else {
                // new patient
                $patient                        = new Patient();
                $patient->patient_name          = $data['patient_name'] ?? null;
                $patient->mobileno              = $data['mobileno'] ?? null;
                $patient->dob                   = $data['dob'] ?? null;
                $patient->gender                = $data['gender'] ?? null;
                $patient->blood_group           = $data['blood_group'] ?? null;
                $patient->discount_type         = $data['discount_type'] ?? null;
                $patient->organization_name     = $data['organization_name'] ?? null;
                $patient->organization_id       = $data['organization_id'] ?? null;
                $patient->organization_api_link = $data['organization_api_link'] ?? null;

                $patient->save();
            }

            // supporting doc save/update in patients table
            if ($request->hasFile('supporting_doc')) {
                if (! empty($patient->supporting_doc) && Storage::disk('public')->exists('patients/supporting_docs/' . $patient->supporting_doc)) {
                    Storage::disk('public')->delete('patients/supporting_docs/' . $patient->supporting_doc);
                }

                $file     = $request->file('supporting_doc');
                $filename = 'supporting_doc_' . $patient->id . '_' . time() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('patients/supporting_docs', $filename, 'public');

                $patient->supporting_doc = $filename;
                $patient->save();
            }
            // Case ID Create
            $case = CaseReference::create();

            //A) Ipd Patient
            $ipd = IpdPatient::create([
                'case_id'                 => $case->id,
                'patient_id'              => $patient->id,
                'doctor_id'               => $request->doctor_id,
                'department_id'           => $request->department_id,
                'admission_date'          => $request->admission_date,
                'possible_discharge_date' => $request->possible_discharge_date ?? null,
                'admission_type'          => $request->admission_type ?? null,
                'patient_history'         => $request->patient_history ?? null,
                'remarks'                 => $request->remarks ?? null,
                'status'                  => $request->ipd_status,
            ]);

            // B) Bed / ICU Allocation
            // allocation_choice = 'bed' (default) or 'icu'
            $allocationChoice = $request->input('allocation_choice', 'bed');
            $allocatedBedId   = $allocationChoice === 'icu'
                ? $request->input('icu_bed_id')
                : $request->input('bed_id');

            // Validate bed type matches choice (defensive check)
            $allocatedBed = Bed::with('bedType')->findOrFail($allocatedBedId);
            $bedIsIcu     = (bool) optional($allocatedBed->bedType)->is_icu;

            IpdPatientBed::create([
                'case_id'         => $case->id,
                'ipd_patient_id'  => $ipd->id,
                'bed_id'          => $allocatedBedId,
                'allocation_type' => $bedIsIcu ? 'icu' : 'bed',
                'from'            => $request->from,
                'to'              => $request->to,
                'status'          => 'Admitted',
                'remarks'         => $request->bed_remarks,
            ]);

            Bed::where('id', $allocatedBedId)->update(['is_reserved' => true]);

            // If admitting straight into ICU, mirror an icu_admissions row so the
            // dedicated ICU module sees this patient. Resource validation here is
            // intentionally light because Ipd admission is the user's entry point;
            // the ICU module's own admission flow is the validated path for direct
            // ICU admissions.
            if ($bedIsIcu) {
                $icuType = optional($allocatedBed->bedType)->icu_type ?: 'ICU';
                IcuAdmission::create([
                    'icu_case_id'         => IcuAdmission::generateCaseId($icuType, new \DateTimeImmutable($request->from)),
                    'case_id'             => $case->id,
                    'patient_id'          => $patient->id,
                    'source_type'         => 'Ipd',
                    'source_id'           => $ipd->id,
                    'icu_type'            => $icuType,
                    'admission_type'      => $request->admission_type ?? 'Emergency',
                    'admission_diagnosis' => $request->patient_history ?? '(from Ipd admission)',
                    'referring_doctor_id' => $request->doctor_id,
                    'isolation_type'      => 'None',
                    'ventilator_required' => (bool) optional($allocatedBed->bedType)->has_ventilator_support,
                    'monitor_required'    => (bool) optional($allocatedBed->bedType)->has_monitor_support,
                    'bed_id'              => $allocatedBedId,
                    'admission_time'      => $request->from,
                    'status'              => 'Admitted',
                    'remarks'             => $request->bed_remarks,
                    'created_by'          => auth()->id(),
                ]);
            }

            // C) Patient Documents (multiple)
            if ($request->has('documents')) {
                foreach ($request->input('documents', []) as $idx => $doc) {
                    $uploaded = $request->file("documents.$idx.file");
                    if (! $uploaded || ! $uploaded->isValid()) {
                        continue;
                    }
                    $stored = $uploaded->store('ipd_patient_documents', 'public');

                    IpdPatientDocument::create([
                        'ipd_patient_id' => $ipd->id,
                        'title'          => $doc['title'] ?? null,
                        'file'           => $stored,
                        'remarks'        => $doc['remarks'] ?? null,
                    ]);
                }
            }

            // D) Advance Payment

            $storedFiles = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file && $file->isValid()) {
                        $storedFiles[] = $file->store('transactions', 'public');
                    }
                }
            }
            $advanceTransaction = null;
            if (($request->amount ?? 0) > 0) {
                $amount    = (float) ($request->amount ?? 0);
                $vatPct    = (float) ($request->vat ?? 0);
                $taxPct    = (float) ($request->tax ?? 0);
                $discPct   = (float) ($request->discount ?? 0);
                $netAmount = round($amount + ($amount * $vatPct / 100) + ($amount * $taxPct / 100) - ($amount * $discPct / 100), 2);

                $advanceTransaction = Transaction::create([
                    'patient_id'         => $patient->id,
                    'case_id'            => $case->id,
                    'ipd_patient_id'     => $ipd->id,
                    'type'               => 'Advance',
                    'section'            => 'Ipd',
                    'amount'             => $amount,
                    'vat'                => $vatPct,
                    'tax'                => $taxPct,
                    'discount'           => $discPct,
                    'net_amount'         => $netAmount,
                    'payment_via'        => $request->payment_via,
                    'payment_date'       => $request->payment_date,
                    'cheque_name'        => $request->cheque_name ?? null,
                    'cheque_no'          => $request->cheque_no ?? null,
                    'cheque_date'        => $request->cheque_date ?? null,
                    'card_no'            => $request->card_no ?? null,
                    'card_type'          => $request->card_type ?? null,
                    'mfs_type'           => $request->mfs_type ?? null,
                    'mfs_no'             => $request->mfs_no ?? null,
                    'mfs_transaction_id' => $request->mfs_transaction_id ?? null,
                    'notes'              => $request->notes ?? null,
                    'received_by'        => $request->received_by ?? null,
                    'files'              => ! empty($storedFiles) ? json_encode($storedFiles) : null,
                    'status'             => $request->payment_status,
                ]);
            }

            // If admitted from an OPD visit, mark that OPD record as moved
            if ($request->filled('from_opd_id')) {
                \App\Models\OpdPatient::where('id', $request->input('from_opd_id'))
                    ->update(['status' => 'Moved to Ipd']);
            }

            DB::commit();

            $printSlips = [
                'admission' => route('ipd-patients.ipd-patients.admission-slip', $ipd->id),
            ];
            if ($advanceTransaction) {
                $printSlips['payment'] = route('ipd-patients.ipd-patients.payment-slip', [
                    'id'            => $ipd->id,
                    'transactionId' => $advanceTransaction->id,
                ]);
            }

            return redirect()
                ->route('ipd-patients.ipd-patients.show', $ipd->id)
                ->with('success', 'Ipd Admission saved successfully.')
                ->with('print_slips', $printSlips);

        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();

            Log::error('Ipd store failed', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ipd Admission failed! ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(IpdPatient $iPDPatient)
    {
        $iPDPatient->load(['patient', 'doctor', 'department', 'bedAllocations.bed.bedType', 'bedAllocations.bed.bedGroup', 'nurseNotes', 'charges', 'transactions', 'prescriptions.medicines.medicine', 'medicineOrders.medicine', 'medicineOrders.prescribedBy', 'documents']);
        return view('ipd_patients.show', compact('iPDPatient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IpdPatient $iPDPatient, $id)
    {
        // dd($id);
        $ipdPatient = IpdPatient::with([
            'patient',
            'doctor',
            'department',
            'bedAllocations.bed',
            'transactions',
            'documents',
        ])->findOrFail($id);

        $patients    = Patient::select('id', 'patient_name', 'mrn')->get();
        $doctors     = Doctor::select('id', 'name', 'department_id')->get();
        $departments = Department::select('id', 'name')->get();

        // current allocated bed-ও দেখাতে হবে, তাই reserved হলেও current bed include করবো
        $currentBedId = optional($ipdPatient->bedAllocations()->latest('id')->first())->bed_id;

        $beds = Bed::select('id', 'name', 'rent')
            ->where(function ($query) use ($currentBedId) {
                $query->where('is_reserved', false);

                if ($currentBedId) {
                    $query->orWhere('id', $currentBedId);
                }
            })
            ->get();

        $bedAllocation = $ipdPatient->bedAllocations()->latest('id')->first();
        $transaction   = $ipdPatient->transactions->firstWhere('type', 'Advance');

        return view('ipd_patients.edit', compact(
            'ipdPatient',
            'patients',
            'doctors',
            'departments',
            'beds',
            'bedAllocation',
            'transaction'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IPDPatientRequest $request, $id)
    {
        // dd($request->all());
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ipd     = IpdPatient::with(['patient', 'bedAllocations', 'transactions'])->findOrFail($id);
            $patient = $ipd->patient;

            // 1. Patient update
            if (! empty($data['patient_id'])) {
                $patient = Patient::findOrFail($data['patient_id']);
            } else {
                $patient->patient_name          = $data['patient_name'] ?? null;
                $patient->mobileno              = $data['mobileno'] ?? null;
                $patient->dob                   = $data['dob'] ?? null;
                $patient->gender                = $data['gender'] ?? null;
                $patient->blood_group           = $data['blood_group'] ?? null;
                $patient->discount_type         = $data['discount_type'] ?? null;
                $patient->organization_name     = $data['organization_name'] ?? null;
                $patient->organization_id       = $data['organization_id'] ?? null;
                $patient->organization_api_link = $data['organization_api_link'] ?? null;
                $patient->save();
            }

            // supporting doc update
            if ($request->hasFile('supporting_doc')) {
                if (! empty($patient->supporting_doc) && Storage::disk('public')->exists('patients/supporting_docs/' . $patient->supporting_doc)) {
                    Storage::disk('public')->delete('patients/supporting_docs/' . $patient->supporting_doc);
                }

                $file     = $request->file('supporting_doc');
                $filename = 'supporting_doc_' . $patient->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('patients/supporting_docs', $filename, 'public');

                $patient->supporting_doc = $filename;
                $patient->save();
            }

            // 2. Ipd update
            $ipd->update([
                'patient_id'              => $patient->id,
                'doctor_id'               => $request->doctor_id,
                'department_id'           => $request->department_id,
                'admission_date'          => $request->admission_date,
                'possible_discharge_date' => $request->possible_discharge_date ?? null,
                'admission_type'          => $request->admission_type ?? null,
                'patient_history'         => $request->patient_history ?? null,
                'remarks'                 => $request->remarks ?? null,
                'status'                  => $request->ipd_status,
            ]);

            // 3. Bed Allocation update
            $bedAllocation = $ipd->bedAllocations()->latest('id')->first();

            if ($bedAllocation) {
                $oldBedId = $bedAllocation->bed_id;

                // যদি bed change হয়
                if ((int) $oldBedId !== (int) $request->bed_id) {
                    Bed::where('id', $oldBedId)->update(['is_reserved' => false]);
                    Bed::where('id', $request->bed_id)->update(['is_reserved' => true]);
                }

                $bedAllocation->update([
                    'bed_id'  => $request->bed_id,
                    'from'    => $request->from,
                    'to'      => $request->to,
                    'remarks' => $request->bed_remarks,
                ]);
            } else {
                IpdPatientBed::create([
                    'case_id'        => $ipd->case_id,
                    'ipd_patient_id' => $ipd->id,
                    'bed_id'         => $request->bed_id,
                    'from'           => $request->from,
                    'to'             => $request->to,
                    'remarks'        => $request->bed_remarks,
                ]);

                Bed::where('id', $request->bed_id)->update(['is_reserved' => true]);
            }

            // 4a. Patient Documents (append new uploads)
            if ($request->has('documents')) {
                foreach ($request->input('documents', []) as $idx => $doc) {
                    $uploaded = $request->file("documents.$idx.file");
                    if (! $uploaded || ! $uploaded->isValid()) {
                        continue;
                    }
                    $stored = $uploaded->store('ipd_patient_documents', 'public');

                    IpdPatientDocument::create([
                        'ipd_patient_id' => $ipd->id,
                        'title'          => $doc['title'] ?? null,
                        'file'           => $stored,
                        'remarks'        => $doc['remarks'] ?? null,
                    ]);
                }
            }

            // 4. Advance Transaction update/create
            $transaction = $ipd->transactions->firstWhere('type', 'Advance');

            $storedFiles = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file && $file->isValid()) {
                        $storedFiles[] = $file->store('transactions', 'public');
                    }
                }
            }

            if (($request->amount ?? 0) > 0) {
                $paymentVia = strtolower(trim($request->payment_via ?? ''));

                $amount    = (float) ($request->amount ?? 0);
                $vatTotal  = (float) ($request->vat ?? 0) + ($amount * (float) ($request->vat_percent ?? 0) / 100);
                $taxTotal  = (float) ($request->tax ?? 0) + ($amount * (float) ($request->tax_percent ?? 0) / 100);
                $discTotal = (float) ($request->discount ?? 0) + ($amount * (float) ($request->discount_percent ?? 0) / 100);
                $netAmount = $amount + $vatTotal + $taxTotal - $discTotal;

                $transactionData = [
                    'patient_id'         => $patient->id,
                    'case_id'            => $ipd->case_id,
                    'ipd_patient_id'     => $ipd->id,
                    'type'               => 'Advance',
                    'section'            => 'Ipd',
                    'amount'             => $amount,
                    'vat'                => $vatTotal,
                    'tax'                => $taxTotal,
                    'discount'           => $discTotal,
                    'net_amount'         => $netAmount,
                    'payment_via'        => $request->payment_via,
                    'payment_date'       => $request->payment_date,

                    'cheque_name'        => $request->cheque_name ?? null,
                    'cheque_no'          => $request->cheque_no ?? null,
                    'cheque_date'        => $request->cheque_date ?? null,
                    'card_no'            => $request->card_no ?? null,
                    'card_type'          => $request->card_type ?? null,
                    'mfs_type'           => $request->mfs_type ?? null,
                    'mfs_no'             => $request->mfs_no ?? null,
                    'mfs_transaction_id' => $request->mfs_transaction_id ?? null,

                    'notes'              => $request->notes ?? null,
                    'received_by'        => $request->received_by ?? null,
                    'status'             => $request->payment_status ?? '',
                ];

                // Set null based on payment method
                if ($paymentVia === 'cash') {
                    $transactionData['cheque_name']        = null;
                    $transactionData['cheque_no']          = null;
                    $transactionData['cheque_date']        = null;
                    $transactionData['card_no']            = null;
                    $transactionData['card_type']          = null;
                    $transactionData['mfs_type']           = null;
                    $transactionData['mfs_no']             = null;
                    $transactionData['mfs_transaction_id'] = null;
                } elseif ($paymentVia === 'card') {
                    $transactionData['cheque_name']        = null;
                    $transactionData['cheque_no']          = null;
                    $transactionData['cheque_date']        = null;
                    $transactionData['mfs_type']           = null;
                    $transactionData['mfs_no']             = null;
                    $transactionData['mfs_transaction_id'] = null;
                } elseif ($paymentVia === 'cheque') {
                    $transactionData['card_no']            = null;
                    $transactionData['card_type']          = null;
                    $transactionData['mfs_type']           = null;
                    $transactionData['mfs_no']             = null;
                    $transactionData['mfs_transaction_id'] = null;
                } elseif ($paymentVia === 'mfs') {
                    $transactionData['cheque_name'] = null;
                    $transactionData['cheque_no']   = null;
                    $transactionData['cheque_date'] = null;
                    $transactionData['card_no']     = null;
                    $transactionData['card_type']   = null;
                }

                if (! empty($storedFiles)) {
                    $transactionData['files'] = json_encode($storedFiles);
                }

                if ($transaction) {
                    $transaction->update($transactionData);
                } else {
                    Transaction::create($transactionData);
                }
            }
            DB::commit();

            return redirect()
                ->route('ipd-patients.index')
                ->with('success', 'Ipd Admission updated successfully.');

        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();

            Log::error('Ipd update failed', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ipd Admission update failed! ' . $e->getMessage());
        }
    }
    public function convertToOpd(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $ipd = IpdPatient::with(['bedAllocations'])->findOrFail($id);

            // Release allocated bed
            $bedAllocation = $ipd->bedAllocations()->latest('id')->first();
            if ($bedAllocation) {
                Bed::where('id', $bedAllocation->bed_id)->update(['is_reserved' => false]);
                $bedAllocation->update(['to' => now(), 'status' => 'Released']);
            }

            // Cancel the Ipd record
            $ipd->update([
                'status'  => 'Cancelled',
                'remarks' => trim(($ipd->remarks ?? '') . ' [Converted to OPD - wrong registration]'),
            ]);

            // Generate OPD token
            $deptId = $ipd->department_id;
            $seq    = \App\Models\OpdPatient::whereDate('date', now()->format('Y-m-d'))
                ->where('department_id', $deptId)
                ->count() + 1;
            $tokenNo = now()->format('Ymd') . '-' . $deptId . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // Create OPD record reusing the same case
            $opd = \App\Models\OpdPatient::create([
                'case_id'       => $ipd->case_id,
                'patient_id'    => $ipd->patient_id,
                'doctor_id'     => $ipd->doctor_id,
                'department_id' => $deptId,
                'date'          => now(),
                'visit_type'    => 'new',
                'token_no'      => $tokenNo,
                'remarks'       => 'Converted from Ipd #' . $ipd->ipd_no,
                'status'        => 'Registered',
            ]);

            DB::commit();

            return redirect()
                ->route('opd-patients.show', $opd->id)
                ->with('success', 'Ipd record cancelled and patient moved to OPD successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Ipd to OPD conversion failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function dischargeRequest($id)
    {
        $id = (int) $id;

        DB::beginTransaction();

        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            $currentBedAllocation = $ipdPatient->bedAllocations()->latest('id')->first();

            if ($currentBedAllocation) {
                $currentBedAllocation->update([
                    'to'     => now(),
                    'status' => 'Discharged',
                ]);

                $from = Carbon::parse($currentBedAllocation->from);
                $to   = Carbon::parse($currentBedAllocation->to);

                $totalDays = $from->startOfDay()->diffInDays($to->startOfDay());

                if ($to->format('H:i:s') > '12:00:00') {
                    $totalDays++;
                }

                $totalDays = max($totalDays, 1);

                $rent = (float) ($currentBedAllocation->bed?->rent ?? 0);

                PatientCharge::create([
                    'case_id'       => $ipdPatient->case_id,
                    'charge_module' => 'ipd',
                    'doctor_id'     => $ipdPatient->doctor_id,
                    'department_id' => $ipdPatient->department_id,
                    'ipd_id'        => $ipdPatient->id,
                    'charge_item'   => $currentBedAllocation->bed?->name,
                    'unit_price'    => $rent,
                    'quantity'      => $totalDays,
                    'amount'        => $rent * $totalDays,
                    'net_amount'    => $rent * $totalDays,
                    'date'          => now(),
                    'created_by'    => auth()->id(),
                ]);

                if ($currentBedAllocation->bed_id) {
                    Bed::where('id', $currentBedAllocation->bed_id)->update(['is_reserved' => false]);
                }
            }

            $ipdPatient->update(['status' => 'Discharge in Process']);

            DB::commit();

            return redirect()
                ->route('ipd-patients.index')
                ->with('success', 'Discharge request submitted. Bed charge added and bed released.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Ipd discharge request failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Discharge request failed! ' . $e->getMessage());
        }
    }
    public function admissionSlip($id)
    {
        $ipdPatient = IpdPatient::with([
            'patient',
            'doctor.department',
            'doctor.designation',
            'department',
            'bedAllocations.bed.bedType',
            'bedAllocations.bed.bedGroup',
        ])->findOrFail($id);

        $settings = Setting::where('group', 'company')
            ->where('is_active', true)
            ->pluck('value', 'key')
            ->toArray();

        return view('ipd_patients.admission-slip', compact('ipdPatient', 'settings'));
    }

    public function paymentSlip($id, $transactionId)
    {
        $ipdPatient  = IpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($id);
        $transaction = Transaction::where('ipd_patient_id', $ipdPatient->id)
            ->findOrFail($transactionId);

        $settings = Setting::where('group', 'company')
            ->where('is_active', true)
            ->pluck('value', 'key')
            ->toArray();

        return view('ipd_patients.payment-slip', compact('ipdPatient', 'transaction', 'settings'));
    }

    public function bedTransfer($id)
    {
        $id = (int) $id;

        // Only show non-ICU beds in the regular bed-transfer screen.
        $beds = Bed::with('bedType')
            ->where('is_reserved', false)
            ->get()
            ->filter(fn($b) => ! optional($b->bedType)->is_icu)
            ->values();

        return view('ipd_patients.bedtransfer', compact('beds', 'id'));
    }

    /**
     * Show transfer-to-ICU form.
     */
    public function icuTransfer($id)
    {
        $id = (int) $id;

        $icuBeds = Bed::with('bedType')
            ->where('is_reserved', false)
            ->get()
            ->filter(fn($b) => optional($b->bedType)->is_icu)
            ->values();

        return view('ipd_patients.icutransfer', compact('icuBeds', 'id'));
    }

    /**
     * Generic transfer between regular bed and ICU bed.
     * $direction = 'icu' (bed -> ICU) or 'bed' (ICU -> bed)
     */
    protected function performAllocationTransfer(Request $request, $id, string $direction)
    {
        $id = (int) $id;

        $rules = [
            'from'    => 'required|date',
            'remarks' => 'nullable|string',
        ];
        $rules[$direction === 'icu' ? 'icu_bed_id' : 'bed_id'] = 'required|exists:beds,id';
        $request->validate($rules);

        $newBedId = $direction === 'icu' ? $request->input('icu_bed_id') : $request->input('bed_id');

        DB::beginTransaction();
        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            // Defensive: confirm the destination bed type really matches direction.
            $newBed      = Bed::with('bedType')->findOrFail($newBedId);
            $newBedIsIcu = (bool) optional($newBed->bedType)->is_icu;
            if ($direction === 'icu' && ! $newBedIsIcu) {
                throw new \RuntimeException('Selected bed is not an ICU bed.');
            }
            if ($direction === 'bed' && $newBedIsIcu) {
                throw new \RuntimeException('Selected bed is an ICU bed; use ICU transfer instead.');
            }

            $current = $ipdPatient->bedAllocations()->latest('id')->first();

            if ($current) {
                Bed::where('id', $current->bed_id)->update(['is_reserved' => false]);
                $current->update([
                    'to'     => now(),
                    'status' => 'TRANSFERRED',
                ]);

                // Charge for old stay
                $from      = Carbon::parse($current->from);
                $to        = Carbon::parse($current->to);
                $totalDays = $from->startOfDay()->diffInDays($to->startOfDay());
                if ($to->format('H:i:s') > '12:00:00') {
                    $totalDays++;
                }
                $totalDays = max($totalDays, 1);

                $rent = (float) ($current->bed?->rent ?? 0);

                PatientCharge::create([
                    'case_id'       => $ipdPatient->case_id,
                    'charge_module' => 'ipd',
                    'doctor_id'     => $ipdPatient->doctor_id,
                    'department_id' => $ipdPatient->department_id,
                    'ipd_id'        => $ipdPatient->id,
                    'charge_item'   => $current->bed?->name,
                    'unit_price'    => $rent,
                    'quantity'      => $totalDays,
                    'amount'        => $rent * $totalDays,
                    'net_amount'    => $rent * $totalDays,
                    'date'          => now(),
                    'created_by'    => auth()->id(),
                ]);
            }

            IpdPatientBed::create([
                'case_id'         => $ipdPatient->case_id,
                'ipd_patient_id'  => $ipdPatient->id,
                'bed_id'          => $newBedId,
                'allocation_type' => $direction === 'icu' ? 'icu' : 'bed',
                'from'            => $request->input('from'),
                'status'          => 'TRANSFERRED',
                'remarks'         => $request->input('remarks'),
            ]);

            Bed::where('id', $newBedId)->update(['is_reserved' => true]);

            // Mirror the move into icu_admissions
            if ($direction === 'icu') {
                $icuType = optional($newBed->bedType)->icu_type ?: 'ICU';
                IcuAdmission::create([
                    'icu_case_id'         => IcuAdmission::generateCaseId($icuType, new \DateTimeImmutable($request->input('from'))),
                    'case_id'             => $ipdPatient->case_id,
                    'patient_id'          => $ipdPatient->patient_id,
                    'source_type'         => 'Ipd',
                    'source_id'           => $ipdPatient->id,
                    'icu_type'            => $icuType,
                    'admission_type'      => 'Transfer',
                    'admission_diagnosis' => '(transferred from Ipd bed)',
                    'referring_doctor_id' => $ipdPatient->doctor_id,
                    'isolation_type'      => 'None',
                    'ventilator_required' => (bool) optional($newBed->bedType)->has_ventilator_support,
                    'monitor_required'    => (bool) optional($newBed->bedType)->has_monitor_support,
                    'bed_id'              => $newBedId,
                    'admission_time'      => $request->input('from'),
                    'status'              => 'Admitted',
                    'remarks'             => $request->input('remarks'),
                    'created_by'          => auth()->id(),
                ]);
            } else {
                // ICU -> regular bed: close any active ICU admission for this patient,
                // and stop any open equipment usage (which posts the final charges).
                $activeAdmissions = IcuAdmission::where('source_type', 'Ipd')
                    ->where('source_id', $ipdPatient->id)
                    ->whereIn('status', ['Admitted', 'Approved'])
                    ->get();

                if ($activeAdmissions->isNotEmpty()) {
                    $usage = app(\App\Http\Controllers\ICU\IcuEquipmentUsageController::class);
                    $when  = new \DateTimeImmutable($request->input('from'));
                    foreach ($activeAdmissions as $a) {
                        $usage->closeAllForAdmission($a->id, $when, auth()->id(), 'Transferred out of ICU');
                    }

                    IcuAdmission::whereIn('id', $activeAdmissions->pluck('id'))
                        ->update([
                            'status'        => 'Transferred',
                            'transfer_time' => $request->input('from'),
                            'closed_by'     => auth()->id(),
                        ]);
                }
            }

            DB::commit();

            $msg = $direction === 'icu'
                ? 'Patient transferred to ICU successfully.'
                : 'Patient transferred from ICU to bed successfully.';

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Allocation transfer failed', [
                'direction' => $direction,
                'message'   => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Transfer failed! ' . $e->getMessage());
        }
    }

    public function icuTransferStore(Request $request, $id)
    {
        return $this->performAllocationTransfer($request, $id, 'icu');
    }

    public function bedTransferStore(Request $request, $id)
    {
        $id = (int) $id;

        $requestData = request()->validate([
            'bed_id'  => 'required|exists:beds,id',
            'from'    => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            $currentBedAllocation = $ipdPatient->bedAllocations()->latest('id')->first();

            // dd($currentBedAllocation);

            if ($currentBedAllocation) {
                // পুরানো বেড ফাঁকা করা
                Bed::where('id', $currentBedAllocation->bed_id)->update(['is_reserved' => false]);
                $currentBedAllocation->update([
                    'to' => now(), // অথবা আপনি চাইলে এখানে অন্য একটি তারিখ সেট করতে পারেন
                ]);

            }
            // dd($currentBedAllocation);

            $from = Carbon::parse($currentBedAllocation->from);
            $to   = Carbon::parse($currentBedAllocation->to);

            $totalDays = $from->startOfDay()->diffInDays($to->startOfDay());

            if ($to->format('H:i:s') > '12:00:00') {
                $totalDays++;
            }

            $totalDays = max($totalDays, 1); // +1 to include the current day

            //Add patient charge for bed transfer
            PatientCharge::create([
                'case_id'       => $ipdPatient->case_id,
                'charge_module' => 'ipd',
                'doctor_id'     => $ipdPatient->doctor_id,
                'department_id' => $ipdPatient->department_id,
                'ipd_id'        => $ipdPatient->id,
                'charge_item'   => $currentBedAllocation->bed?->name,
                'unit_price'    => $currentBedAllocation->bed?->rent,
                'quantity'      => $totalDays,
                'amount'        => $currentBedAllocation->bed?->rent * $totalDays, // unit_price * quantity
                'net_amount'    => $currentBedAllocation->bed?->rent * $totalDays, // amount + vat + tax (যদি থাকে)
                'date'          => now(),
                'created_by'    => auth()->id(),
            ]);

            // নতুন বেড বরাদ্দ
            $newBed      = Bed::with('bedType')->findOrFail($requestData['bed_id']);
            $newBedIsIcu = (bool) optional($newBed->bedType)->is_icu;

            IpdPatientBed::create([
                'case_id'         => $ipdPatient->case_id,
                'ipd_patient_id'  => $ipdPatient->id,
                'bed_id'          => $requestData['bed_id'],
                'allocation_type' => $newBedIsIcu ? 'icu' : 'bed',
                'from'            => $requestData['from'],
                'status'          => 'TRANSFERRED',
                'remarks'         => $requestData['remarks'] ?? null,
            ]);

            Bed::where('id', $requestData['bed_id'])->update(['is_reserved' => true]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Bed transfer successful.');

        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();

            Log::error('Bed transfer failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Bed transfer failed! ' . $e->getMessage());
        }
    }

}
