<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Http\Requests\FontDesk\StoreFrontDeskRegistrationRequest;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\DoctorSlotTime;
use App\Models\Doctor;
use App\Models\FrontDesk\ErPatient;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\PatientCharge;
use App\Services\FontDesk\CaseReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontDeskRegistrationController extends Controller
{
    public function create()
    {
        $doctors     = Doctor::where('is_active', 1)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // organization list demo (আপনি corporate agreements table থেকে আনবেন)
        $organizations = collect([
            (object) ['id' => 'CORP-001', 'name' => 'Corporate'],
            (object) ['id' => 'ORG-002', 'name' => 'Organization'],
        ]);

        $patients = Patient::select('id', 'patient_name', 'mobileno', 'mrn')
            ->orderBy('patient_name')
            ->get();

        $beds = Bed::select('id', 'name')->where('is_reserved', 0)->get();

        return view('front-desk.registration.create', compact('doctors', 'departments', 'organizations', 'patients', 'beds'));
    }

    public function store(StoreFrontDeskRegistrationRequest $request, CaseReferenceService $caseService)
    {
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $caseService) {

            if ($data['registration_type'] === 'EXISTING_PATIENT' && empty($data['patient_id'])) {

                return back()->with('error', 'Patient ID required for Existing Patient');
            }

            $patientId = $data['patient_id'] ?? null;

            // create new patient if needed
            if (! $patientId && in_array($data['registration_type'], ['NEW_PATIENT', 'UNKNOWN'])) {
                $p                        = new Patient();
                $p->patient_name          = $data['name'] ?: 'Unknown';
                $p->mobileno              = $data['contact_no'] ?? null;
                $p->gender                = $data['gender'] ?? null;
                $p->dob                   = $data['dob'] ?? null;
                $p->blood_group           = $data['blood_group'] ?? null;
                $p->identification_number = $data['nid_passport'] ?? null;
                $p->address               = $data['address'] ?? null;
                $p->guardian_name         = $data['guardian_name'] ?? null;
                $p->emergency_contact     = $data['emergency_contact'] ?? null;
                $p->discount_type         = $data['discount_type'] ?? null;
                $p->organization_name     = $data['organization_name'] ?? null;
                $p->organization_id       = $data['organization_id'] ?? null;
                $p->organization_api_link = $data['organization_api_link'] ?? null;
                $p->created_by            = auth()->id();
                $p->save();

                $patientId = $p->id;
            }

            // ✅ get patient row (new or existing)
            $p = Patient::findOrFail($patientId);

            // ✅ upload + replace old file if new file given
            if ($request->hasFile('supporting_doc')) {

                // 2) store new file
                $file     = $request->file('supporting_doc');
                $filename = 'supporting_doc_' . $patientId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('patients/supporting_docs', $filename, 'public');

                // 3) update DB
                $p->supporting_doc = $filename;
                $p->save();
            }

            // Case create + Type-wise insert
            $caseId = $caseService->createCase($patientId, $data['patient_type']);

            if ($data['patient_type'] === 'OPD') {
                $deptId   = $data['department_id'];
                $dept     = Department::find($deptId);
                $deptCode = $dept?->code
                    ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dept?->name ?? 'GEN'), 0, 3));
                $deptCode = str_pad($deptCode ?: 'GEN', 3, 'X');

                $seq     = OpdPatient::whereDate('date', now()->format('Y-m-d'))
                    ->where('department_id', $deptId)
                    ->count() + 1;
                $tokenNo = now()->format('Ymd') . '-' . $deptCode . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);

                $slotFrom = $slotTo = null;
                if (! empty($data['slot_time'])) {
                    [$slotFrom, $slotTo] = array_pad(explode('|', $data['slot_time'], 2), 2, null);
                }

                $visitDate = $data['appointment_date'] ?? now()->toDateString();

                $priority = $data['priority'] ?? 'Normal';

                $opd = OpdPatient::create([
                    'case_id'         => $caseId,
                    'patient_id'      => $patientId,
                    'doctor_id'       => $data['doctor_id'],
                    'shift_id'        => $data['shift_id'] ?? null,
                    'slot_time_from'  => $slotFrom,
                    'slot_time_to'    => $slotTo,
                    'department_id'   => $deptId,
                    'date'            => $visitDate,
                    'visit_type'      => $data['visit_type'] ?? 'new',
                    'referral_source' => $data['referral_source'] ?? null,
                    'token_no'        => $tokenNo,
                    'remarks'         => $data['description'] ?? null,
                    'status'          => 'Registered',
                    'priority'        => $priority,
                ]);

                // Billing
                $appliedCharge = (float) ($data['applied_charge'] ?? 0);
                $tax           = (float) ($data['tax']            ?? 0);
                $netAmount     = (float) ($data['amount']         ?? 0);

                PatientCharge::create([
                    'case_id'       => $caseId,
                    'opd_id'        => $opd->id,
                    'charge_module' => 'opd',
                    'doctor_id'     => $data['doctor_id'],
                    'department_id' => $deptId,
                    'charge_item'   => 'Consultant Doctor Fee',
                    'charge_id'     => null,
                    'unit_price'    => $data['standard_charge'] ?? 0,
                    'quantity'      => 1,
                    'amount'        => $appliedCharge,
                    'tax'           => $tax,
                    'net_amount'    => $netAmount,
                    'date'          => $visitDate,
                    'status'        => 'pending',
                    'is_paid'       => false,
                    'created_by'    => auth()->id(),
                ]);

                if (($data['booking_status'] ?? 'WALK_IN') === 'PRE_BOOK') {
                $department = Department::find($deptId);

                Appointment::create([
                    'patient_id'         => $patientId,
                    'case_reference_id'  => $caseId,
                    'visit_details_id'   => $opd->id,
                    'date'               => $visitDate,
                    'time'               => $slotFrom,
                    'priority'           => $priority,
                    'specialist'         => $department?->name ?? '',
                    'doctor'             => $data['doctor_id'],
                    'amount'             => $netAmount,
                    'message'            => $data['description'] ?? null,
                    'appointment_status' => $data['booking_status'] === 'PRE_BOOK' ? 'Pending' : 'Approved',
                    'visit_status'       => $data['booking_status'] === 'PRE_BOOK' ? 'booked' : 'checked_in',
                    'source'             => match ($data['booking_status'] ?? 'WALK_IN') {
                        'PRE_BOOK' => 'Appointment',
                        'REFERRAL' => 'Referral',
                        default    => 'Walk-in',
                    },
                    'is_opd'             => 'Yes',
                    'is_ipd'             => 'No',
                    'shift_id'           => $data['shift_id'] ?? null,
                    'slot_time_from'     => $slotFrom,
                    'slot_time_to'       => $slotTo,
                    'is_queue'           => null,
                    'live_consult'       => 'None',
                ]);
                } // end if PRE_BOOK
            }

            if ($data['patient_type'] === 'Ipd') {
                $ipd_patient = IpdPatient::create([
                    'case_id'                 => $caseId,
                    'patient_id'              => $patientId,
                    'doctor_id'               => $data['doctor_id'],
                    'department_id'           => $data['department_id'],
                    'admission_date'          => now(),
                    'possible_discharge_date' => null,
                    'patient_history'         => null,
                    'remarks'                 => $data['description'] ?? null,
                    'status'                  => 'Admitted',
                ]);

                // B) Bed Allocation
                IpdPatientBed::create([
                    'case_id'        => $caseId,
                    'ipd_patient_id' => $ipd_patient->id,
                    'bed_id'         => $request->bed_id,
                    'from'           => $request->from,
                    'to'             => $request->to,
                    'remarks'        => $request->bed_remarks,
                ]);
                
                Bed::where('id', $request->bed_id)->update(['is_reserved' => 1]);
            }

            if ($data['patient_type'] === 'ER') {
                ErPatient::create([
                    'case_id'       => $caseId,
                    'patient_id'    => $patientId,
                    'doctor_id'     => $data['doctor_id'] ?? null,
                    'department_id' => $data['department_id'] ?? null,
                    'arrival_time'  => now(),
                    'priority'      => $data['er_priority'] ?? 'NORMAL',
                    'remarks'       => $data['description'] ?? null,
                    'status'        => 'Registered',
                ]);
            }

            if ($data['patient_type'] === 'LAB') {
                return redirect()
                    ->route('front_desk.lab_order.create', ['case_id' => $caseId, 'patient_id' => $patientId])
                    ->with('success', 'Patient registered. Please create lab orders below.');
            }

            return back()->with('success', 'Registration saved successfully.');
        });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withInput()->withErrors([
                    'contact_no' => 'This phone number is already registered. Please search for the existing patient or use a different number.',
                ]);
            }
            throw $e;
        }
    }

    public function checkActiveToken(Request $request)
    {
        $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'department_id' => 'required|exists:departments,id',
            'date'          => 'required|date',
        ]);

        $active = OpdPatient::with('doctor:id,name')
            ->where('patient_id', $request->patient_id)
            ->where('department_id', $request->department_id)
            ->whereDate('date', $request->date)
            ->whereNotIn('status', ['Completed', 'Closed', 'Cancelled', 'No Show'])
            ->first();

        return response()->json([
            'has_active' => $active !== null,
            'token_no'   => $active?->token_no,
            'status'     => $active?->status,
            'doctor'     => $active?->doctor?->name,
        ]);
    }

    public function getOpdSlots(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'shift_id'  => 'required|exists:shifts,id',
            'date'      => 'required|date',
        ]);

        $day = \Carbon\Carbon::parse($data['date'])->format('l');

        $slots = DoctorSlotTime::where('doctor_id', $data['doctor_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('day', $day)
            ->select('time_from', 'time_to')
            ->distinct()
            ->orderBy('time_from')
            ->get()
            ->map(fn ($s) => [
                'time_from' => substr($s->time_from, 0, 5),
                'time_to'   => substr($s->time_to, 0, 5),
            ])
            ->unique(fn ($s) => $s['time_from'] . '|' . $s['time_to'])
            ->values()
            ->map(function ($s) use ($data) {
                $booked = OpdPatient::where('doctor_id', $data['doctor_id'])
                    ->whereDate('date', $data['date'])
                    ->where('slot_time_from', 'like', $s['time_from'] . '%')
                    ->whereNotIn('status', ['Cancelled', 'No Show'])
                    ->count();

                return array_merge($s, ['booked_count' => $booked]);
            });

        return response()->json($slots);
    }

    public function checkPhone(Request $request)
    {
        $phone = trim($request->get('phone', ''));

        if (! $phone || \strlen(preg_replace('/[\s\-\(\)\+]/', '', $phone)) < 7) {
            return response()->json(['exists' => false]);
        }

        $patient = Patient::where('mobileno', $phone)
            ->select('id', 'patient_name', 'mrn', 'mobileno')
            ->first();

        return response()->json([
            'exists'       => $patient !== null,
            'patient_id'   => $patient?->id,
            'patient_name' => $patient?->patient_name,
            'mrn'          => $patient?->mrn,
        ]);
    }

    public function search(Request $request)
    {
        // Select2 AJAX search: ?q=term  → returns {results:[{id,text}]}
        // Called with empty q when dropdown opens (minimumInputLength=0) → return recent 30
        $q = trim($request->get('q', ''));

        $query = Patient::select('id', 'patient_name', 'mobileno', 'mrn', 'health_card_no');

        if ($q !== '') {
            $term = '%' . $q . '%';
            $query->where(function ($w) use ($term) {
                $w->where('patient_name', 'like', $term)
                  ->orWhere('mobileno', 'like', $term)
                  ->orWhere('mrn', 'like', $term)
                  ->orWhere('health_card_no', 'like', $term)
                  ->orWhere('identification_number', 'like', $term);
            })->orderBy('patient_name');
        } else {
            $query->orderByDesc('id'); // most recently registered first
        }

        if ($request->has('q')) {
            $patients = $query->get()
                ->map(fn($p) => [
                    'id'   => $p->id,
                    'text' => $p->patient_name . ' | ' . $p->mobileno . ' | ' . $p->mrn,
                ]);

            return response()->json(['results' => $patients]);
        }

        // Auto-fill: ?id=X  → returns single patient detail object
        $p = Patient::select(
                'id', 'dob', 'gender', 'blood_group', 'patient_name', 'mobileno',
                'organization_name', 'organization_id', 'organization_api_link',
                'discount_type', 'health_card_no', 'address', 'guardian_name',
                'emergency_contact', 'identification_number'
            )
            ->with(['opdPatients' => fn($q) => $q->with('department')->latest('date')->limit(1)])
            ->findOrFail($request->get('id'));

        $lastVisit = $p->opdPatients->first();

        return response()->json([
            'patient_name'          => $p->patient_name,
            'mobileno'              => $p->mobileno,
            'organization_name'     => $p->organization_name,
            'organization_id'       => $p->organization_id,
            'organization_api_link' => $p->organization_api_link,
            'discount_type'         => $p->discount_type,
            'gender'                => $p->gender,
            'blood_group'           => $p->blood_group,
            'dob'                   => $p->dob ? \Carbon\Carbon::parse($p->dob)->format('Y-m-d') : '',
            'health_card_no'        => $p->health_card_no,
            'address'               => $p->address,
            'guardian_name'         => $p->guardian_name,
            'emergency_contact'     => $p->emergency_contact,
            'identification_number' => $p->identification_number,
            'last_visit_date'       => $lastVisit?->date?->format('d M Y'),
            'last_dept'             => $lastVisit?->department?->name,
        ]);
    }
}
