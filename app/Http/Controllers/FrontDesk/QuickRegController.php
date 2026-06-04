<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\FrontDesk\ErPatient;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Services\FontDesk\CaseReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickRegController extends Controller
{
    public function index() {}

    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $doctors     = Doctor::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'department_id']);
        return view('front-desk.quick-reg.create', compact('departments', 'doctors'));
    }

    public function store(Request $request, CaseReferenceService $caseService)
    {
        $rules = [
            'patient_name'  => 'required|string|max:255',
            'mobileno'      => 'required|string|max:20|unique:patients,mobileno',
            'dob'           => 'nullable|date',
            'gender'        => 'nullable|in:Male,Female,Other',
            'blood_group'   => 'nullable|string|max:10',
            'patient_type'  => 'required|in:OPD,ER,LAB',
            'visit_type'    => 'nullable|in:new,follow_up,recheckup,referred,emergency',
            'date'          => 'required|date',
            'department_id' => 'required|integer|exists:departments,id',
            'doctor_id'     => 'required|integer|exists:doctors,id',
            'er_priority'   => 'nullable|in:CRITICAL,HIGH,NORMAL',
        ];

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Always creates a new patient
            $patient                = new Patient();
            $patient->patient_name  = $data['patient_name'];
            $patient->mobileno      = $data['mobileno'];
            $patient->dob           = $data['dob'] ?? null;
            $patient->gender        = $data['gender'] ?? null;
            $patient->blood_group   = $data['blood_group'] ?? null;
            $patient->created_by    = auth()->id();
            $patient->save();

            $caseId = $caseService->createCase($patient->id, $data['patient_type']);

            if ($data['patient_type'] === 'OPD') {
                $dept = Department::find($data['department_id']);
                $deptCode = $dept?->code
                    ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dept?->name ?? 'GEN'), 0, 3));
                $deptCode = str_pad($deptCode ?: 'GEN', 3, 'X');
                $seq      = OpdPatient::whereDate('date', $data['date'])
                    ->where('department_id', $data['department_id'])->count() + 1;
                $tokenNo  = \Carbon\Carbon::parse($data['date'])->format('Ymd')
                    . '-' . $deptCode . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);

                $opd = OpdPatient::create([
                    'patient_id'    => $patient->id,
                    'case_id'       => $caseId,
                    'doctor_id'     => $data['doctor_id'],
                    'department_id' => $data['department_id'],
                    'date'          => $data['date'],
                    'visit_type'    => $data['visit_type'] ?? 'new',
                    'token_no'      => $tokenNo,
                    'status'        => 'Registered',
                ]);


            } elseif ($data['patient_type'] === 'ER') {
                ErPatient::create([
                    'patient_id'    => $patient->id,
                    'case_id'       => $caseId,
                    'doctor_id'     => $data['doctor_id'],
                    'department_id' => $data['department_id'],
                    'arrival_time'  => now(),
                    'priority'      => $data['er_priority'] ?? 'NORMAL',
                    'status'        => 'Registered',
                ]);

            } elseif ($data['patient_type'] === 'LAB') {
                DB::commit();
                return redirect()
                    ->route('front_desk.lab_order.create', ['patient_id' => $patient->id, 'case_id' => $caseId])
                    ->with('success', 'Patient registered. Please create lab orders below.');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Quick registration saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $_id) {}
    public function edit(string $_id) {}
    public function update(Request $_request, string $_id) {}
    public function destroy(string $_id) {}
}
