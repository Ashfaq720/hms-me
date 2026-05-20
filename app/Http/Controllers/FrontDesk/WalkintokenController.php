<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\OpdPatient;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WalkintokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $doctors     = Doctor::orderBy('name')->get(['id', 'name']);
        $patients    = Patient::orderBy('patient_name')->get(['id', 'patient_name', 'mrn']);
        return view('front-desk.walkin-token.create', compact('departments', 'patients', 'doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([

            // existing patient
            'patient_id'            => 'nullable|integer|exists:patients,id',

            // new patient
            'patient_name'          => 'required_without:patient_id|string|max:255',
            'mobileno'              => 'required_without:patient_id|string|max:20|unique:patients,mobileno',
            'dob'                   => 'nullable|date',
            'gender'                => 'required_without:patient_id|in:Male,Female,Other',
            'blood_group'           => 'nullable|string|max:10',
            'discount_type'         => 'nullable|in:CORPORATE,INSURANCE,STUFF,SELF',
            'organization_name'     => 'nullable|string|max:100',
            'organization_id'       => 'nullable|string|max:100',
            'organization_api_link' => 'nullable|string|max:255',

            // opd
            'department_id'         => 'required|integer|exists:departments,id',
            'doctor_id'             => 'required|integer|exists:doctors,id',
            'date'                  => 'required|date',
            'remarks'               => 'nullable|string|max:255',
            'supporting_doc'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

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

            // token number generate
            $today        = date('Y-m-d');
            $departmentId = $request->department_id;

            $deptCount = OpdPatient::whereDate('date', $today)
                ->where('department_id', $departmentId)
                ->count() + 1;

            $tokenNo = date('Ymd') . '-' . $departmentId . '-' . str_pad($deptCount, 3, '0', STR_PAD_LEFT);

            $caseReference = CaseReference::create();

            // opd patient entry
            $opdPatient                = new OpdPatient();
            $opdPatient->case_id       = $caseReference->id;
            $opdPatient->patient_id    = $patient->id;
            $opdPatient->doctor_id     = $data['doctor_id'];
            $opdPatient->department_id = $data['department_id'];
            $opdPatient->date          = $data['date'];
            $opdPatient->token_no      = $tokenNo ?? '';
            $opdPatient->remarks       = $data['remarks'] ?? null;
            $opdPatient->status        = 'Pending';
            $opdPatient->save();

            DB::commit();

            $opd = OpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($opdPatient->id);

            $html = view('front-desk.walkin-token.pdf', compact('opd'))->render();

            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => [58, 100], // width, height in mm
                'margin_top'    => 3,
                'margin_bottom' => 3,
                'margin_left'   => 3,
                'margin_right'  => 3,
            ]);
            $mpdf->WriteHTML($html);

            $pdfFileName = 'WT-' . $opd->token_no . '.pdf';
            $filePath    = public_path('pdf/tmp/' . $pdfFileName);

            // Ensure folder exists
            if (! file_exists(public_path('pdf/tmp'))) {
                mkdir(public_path('pdf/tmp'), 0777, true);
            }

            $mpdf->Output($filePath, \Mpdf\Output\Destination::FILE);

            if (file_exists($filePath)) {
                session()->flash('view_token', asset('pdf/tmp/' . $pdfFileName));
            }

            return redirect()->back()->with('success', 'Walk In Token created successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred while creating Walk In Token: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    public function getDoctorsByDepartment($department_id)
    {
        $doctors = Doctor::where('department_id', $department_id)
            ->where('is_active', 1) // optional
            ->select('id', 'name')
            ->get();

        return response()->json($doctors);
    }

    public function pdf($id)
    {
        $opd = OpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($id);

        $html = view('front-desk.walkin-token.pdf', compact('opd'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'format' => [80, 100],
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('token.pdf', 'I');
    }

}
