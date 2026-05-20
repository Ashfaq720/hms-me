<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\FrontDesk\ErPatient;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
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
        $patients    = Patient::orderBy('patient_name')->get(['id', 'patient_name', 'mrn', 'mobileno']);
        $doctors     = Doctor::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'department_id']);
        $beds        = Bed::where('is_reserved', 0)->with('bedGroup')->orderBy('name')->get();
        return view('front-desk.quick-reg.create', compact('departments', 'patients', 'doctors', 'beds'));
    }

    public function store(Request $request, CaseReferenceService $caseService)
    {
        $isNew = $request->input('patient_mode') !== 'existing';
        $type  = $request->input('patient_type');

        $rules = [
            'patient_mode'          => 'required|in:new,existing',
            'patient_id'            => 'required_if:patient_mode,existing|nullable|exists:patients,id',
            'patient_name'          => ($isNew ? 'required' : 'nullable') . '|string|max:255',
            'mobileno'              => ($isNew ? 'required' : 'nullable') . '|string|max:20' . ($isNew ? '|unique:patients,mobileno' : ''),
            'dob'                   => 'nullable|date',
            'gender'                => 'nullable|in:Male,Female,Other',
            'blood_group'           => 'nullable|string|max:10',
            'date'                  => 'required|date',
            'department_id'         => 'required|integer|exists:departments,id',
            'doctor_id'             => 'required|integer|exists:doctors,id',
            'patient_type'          => 'required|in:OPD,Ipd,ER',
            'visit_type'            => 'required_if:patient_type,OPD|nullable|in:new,follow_up,recheckup,referred,emergency',
            'chief_complaint'       => 'nullable|string|max:1000',
            'shift_id'              => 'nullable|exists:shifts,id',
            'slot'                  => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],
            'er_priority'           => 'nullable|in:CRITICAL,HIGH,NORMAL',
            'bed_id'                => ($type === 'Ipd' ? 'required' : 'nullable') . '|exists:beds,id',
            'bed_from'              => 'nullable|date',
            'bed_to'                => 'nullable|date|after_or_equal:bed_from',
            'bed_remarks'           => 'nullable|string|max:500',
            'discount_type'         => 'nullable|in:CORPORATE,INSURANCE,STAFF,SELF',
            'organization_name'     => 'nullable|string|max:100',
            'organization_id'       => 'nullable|string|max:100',
            'organization_api_link' => 'nullable|string|max:255',
            'remarks'               => 'nullable|string|max:2000',
            'supporting_doc'        => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:5120',
        ];

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            if ($isNew) {
                $patient                        = new Patient();
                $patient->patient_name          = $data['patient_name'];
                $patient->mobileno              = $data['mobileno'];
                $patient->dob                   = $data['dob'] ?? null;
                $patient->gender                = $data['gender'] ?? null;
                $patient->blood_group           = $data['blood_group'] ?? null;
                $patient->discount_type         = $data['discount_type'] ?? null;
                $patient->organization_name     = $data['organization_name'] ?? null;
                $patient->organization_id       = $data['organization_id'] ?? null;
                $patient->organization_api_link = $data['organization_api_link'] ?? null;
                $patient->save();

                if ($request->hasFile('supporting_doc')) {
                    $file     = $request->file('supporting_doc');
                    $filename = 'supporting_doc_' . $patient->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('patients/supporting_docs', $filename, 'public');
                    $patient->supporting_doc = $filename;
                    $patient->save();
                }
            } else {
                $patient = Patient::findOrFail($data['patient_id']);
            }

            $caseId = $caseService->createCase($patient->id, $data['patient_type']);

            if ($data['patient_type'] === 'OPD') {
                [$slotFrom, $slotTo] = $this->splitSlot($data['slot'] ?? null);

                OpdPatient::create([
                    'patient_id'      => $patient->id,
                    'case_id'         => $caseId,
                    'doctor_id'       => $data['doctor_id'],
                    'department_id'   => $data['department_id'],
                    'date'            => $data['date'],
                    'visit_type'      => $data['visit_type'] ?? 'new',
                    'shift_id'        => $data['shift_id'] ?? null,
                    'slot_time_from'  => $slotFrom,
                    'slot_time_to'    => $slotTo,
                    'chief_complaint' => $data['chief_complaint'] ?? null,
                    'remarks'         => $data['remarks'] ?? null,
                    'status'          => 'Registered',
                ]);
            } elseif ($data['patient_type'] === 'Ipd') {
                $ipd = IpdPatient::create([
                    'patient_id'     => $patient->id,
                    'case_id'        => $caseId,
                    'doctor_id'      => $data['doctor_id'],
                    'department_id'  => $data['department_id'],
                    'admission_date' => $data['date'],
                    'remarks'        => $data['remarks'] ?? null,
                    'status'         => 'Admitted',
                ]);

                IpdPatientBed::create([
                    'case_id'        => $caseId,
                    'ipd_patient_id' => $ipd->id,
                    'bed_id'         => $data['bed_id'],
                    'from'           => $data['bed_from'] ?? now(),
                    'to'             => $data['bed_to'] ?? null,
                    'remarks'        => $data['bed_remarks'] ?? null,
                ]);

                Bed::where('id', $data['bed_id'])->update(['is_reserved' => 1]);
            } elseif ($data['patient_type'] === 'ER') {
                ErPatient::create([
                    'patient_id'    => $patient->id,
                    'case_id'       => $caseId,
                    'doctor_id'     => $data['doctor_id'],
                    'department_id' => $data['department_id'],
                    'arrival_time'  => $data['date'],
                    'priority'      => $data['er_priority'] ?? 'NORMAL',
                    'remarks'       => $data['remarks'] ?? null,
                    'status'        => 'Registered',
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Quick registration saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage())->withInput();
        }
    }

    private function splitSlot(?string $slot): array
    {
        if (!$slot || !str_contains($slot, '|')) return [null, null];
        [$from, $to] = explode('|', $slot, 2);
        return [$from, $to];
    }

    public function show(string $_id) {}
    public function edit(string $_id) {}
    public function update(Request $_request, string $_id) {}
    public function destroy(string $_id) {}
}
