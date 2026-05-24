<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\ErPatient;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\PatientVisitor;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Services\FontDesk\CaseReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontDeskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = today();
        $ercriticalCount = ErPatient::whereDate('arrival_time', $today)->where('priority', 'CRITICAL')->count();

        $opdPatients = OpdPatient::with(['patient', 'doctor', 'department'])
            ->whereDate('date', $today)
            ->latest()
            ->get();

        $ipdPatients = IpdPatient::with(['patient', 'doctor', 'department'])
            ->whereDate('admission_date', $today)
            ->latest()
            ->get();

        $erPatients = ErPatient::with('patient')
            ->whereDate('arrival_time', $today)
            ->latest()
            ->get();

        $ambulanceRequests = AmbulanceRequest::with('patient')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $appointments = Appointment::with(['patient', 'doctorRelation'])
            ->whereDate('date', $today)
            ->where('visit_status', '!=', 'no_show')
            ->orderBy('slot_time_from')
            ->get();

        $noShowAppointments = Appointment::with(['patient', 'doctorRelation'])
            ->where('visit_status', 'no_show')
            ->whereDate('date', '>=', $today->copy()->subDays(7))
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        $todayVisitorCount = PatientVisitor::whereDate('visit_date', $today)->count();

        $inConsultationCount = $appointments
            ->where('visit_status', 'in_consultation')
            ->count();

        return view('front-desk.front-desk.index', compact(
            'ercriticalCount', 'todayVisitorCount', 'inConsultationCount',
            'opdPatients', 'ipdPatients', 'erPatients',
            'ambulanceRequests', 'appointments', 'noShowAppointments'
        ));
    }

    public function er_registration()
    {
        $patients = Patient::get();
        return view('front-desk.er-registration.create', compact('patients'));
    }
    public function er_registration_store(Request $request, CaseReferenceService $caseService)
    {
        $data = $request->validate([
            'patient_id'          => 'nullable|integer|exists:patients,id',
            'name'                => 'required|string|max:255',
            'contact_no'          => 'required|string|max:20',
            'age'                 => 'nullable|integer|min:0|max:150',
            'gender'              => 'nullable|in:Male,Female,Other',
            'blood_group'         => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'arrival_time'        => 'required',
            'priority'            => 'nullable|in:NORMAL,HIGH,CRITICAL',
            'discount_type'       => 'nullable|in:CORPORATE,INSURANCE,STUFF,SELF',
            'third_party_name'    => 'nullable|string|max:255',
            'third_party_contact' => 'nullable|string|max:20',
            'description'         => 'nullable|string|max:1000',
        ]);
        $data['status'] = 'PENDING';

        DB::beginTransaction();

        try {

            $patientId = $data['patient_id'] ?? null;

            if (!$patientId) {
                $p = new Patient();
                $p->patient_name          = $data['name'] ?: 'Unknown';
                $p->mobileno              = $data['contact_no'] ?? null;
                $p->discount_type         = $data['discount_type'] ?? null;
                $p->organization_name     = $data['organization_name'] ?? null;
                $p->organization_api_link = $data['organization_api_link'] ?? null;
                $p->save();

                $patientId = $p->id;
            }

            // ✅ get
            //  row (new or existing)
            $p = Patient::findOrFail($patientId);

            // Case create + Type-wise insert
            $caseId = $caseService->createCase($patientId, "ER");

            ErPatient::create([
                'case_id'             => $caseId,
                'patient_id'          => $patientId,
                'age'                 => $data['age'] ?? null,
                'gender'              => $data['gender'] ?? null,
                'blood_group'         => $data['blood_group'] ?? null,
                'arrival_time'        => $data['arrival_time'] ?? null,
                'priority'            => $data['priority'] ?? 'NORMAL',
                'discount_type'       => $data['discount_type'] ?? null,
                'third_party_name'    => $data['third_party_name'] ?? null,
                'third_party_contact' => $data['third_party_contact'] ?? null,
                'description'         => $data['description'] ?? null,
                'status'              => 'PENDING',
            ]);

            DB::commit();

            return back()->with('success', 'ER Registration created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('ER Registration Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * /front_desk/new-patient — alias to the patient registration form.
     */
    public function new_patient()
    {
        return redirect()->route('front_desk.registration.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function er_registration_edit(ErPatient $erPatient)
    {
        $erPatient->load('patient');
        return view('front-desk.er-registration.edit', compact('erPatient'));
    }

    public function er_registration_update(Request $request, ErPatient $erPatient)
    {
        $data = $request->validate([
            'age'                   => 'nullable|integer|min:0|max:150',
            'gender'                => 'nullable|in:Male,Female,Other',
            'blood_group'           => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'arrival_time'          => 'required',
            'priority'              => 'nullable|in:CRITICAL,HIGH,NORMAL',
            'discount_type'         => 'nullable|in:CORPORATE,INSURANCE,STUFF,SELF',
            'third_party_name'      => 'nullable|string|max:255',
            'third_party_contact'   => 'nullable|string|max:20',
            'relation'              => 'nullable|string|max:100',
            'description'           => 'nullable|string|max:1000',
            'status'                => 'required|in:PENDING,ACTIVE,ADMITTED,DISCHARGED,CANCELLED,Registered',
            'remarks'               => 'nullable|string|max:1000',
        ]);

        $erPatient->update($data);

        return redirect()->route('front_desk.index')
            ->with('success', 'ER patient updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function todayRegistrationCount(Request $request)
    {
        $type  = strtolower($request->type);
        $today = today();

        $count = match ($type) {
            'opd'   => OpdPatient::whereDate('date', $today)->count(),

            'ipd'   => IpdPatient::whereDate('admission_date', $today)->count(),

            'er'    => ErPatient::whereDate('arrival_time', $today)->count(),

            default => 0,
        };

        return response()->json([
            'status' => true,
            'type'   => strtoupper($type),
            'count'  => $count,
        ]);
    }
}
