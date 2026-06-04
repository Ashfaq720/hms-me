<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\CaseReference;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\OpdPatient;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $doctors     = Doctor::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'department_id']);
        return view('front-desk.walkin-token.create', compact('departments', 'doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isNew = $request->input('patient_mode') !== 'existing';

        $data = $request->validate([
            'patient_id'    => 'nullable|integer|exists:patients,id',
            'patient_mode'  => 'nullable|string',
            'mobileno'      => ['required', 'string', 'max:20',
                                $isNew ? \Illuminate\Validation\Rule::unique('patients', 'mobileno') : 'sometimes'],
            'patient_name'  => $isNew ? 'required|string|max:255' : 'nullable|string|max:255',
            'gender'        => $isNew ? 'required|in:Male,Female,Other' : 'nullable|in:Male,Female,Other',
            'dob'           => 'nullable|date',
            'blood_group'   => 'nullable|string|max:10',
            'department_id' => 'required|integer|exists:departments,id',
            'doctor_id'     => 'required|integer|exists:doctors,id',
            'date'          => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $patient = null;

            if (! $isNew && ! empty($data['patient_id'])) {
                $patient = Patient::findOrFail($data['patient_id']);
            } else {
                $patient                = new Patient();
                $patient->patient_name  = $data['patient_name'] ?? null;
                $patient->mobileno      = $data['mobileno'];
                $patient->dob           = $data['dob'] ?? null;
                $patient->gender        = $data['gender'] ?? null;
                $patient->blood_group   = $data['blood_group'] ?? null;
                $patient->created_by    = auth()->id();
                $patient->save();
            }

            // token number generate — format: YYYYMMDD-DEPTCODE-NNN
            $today        = date('Y-m-d');
            $departmentId = $request->department_id;
            $dept         = Department::find($departmentId);
            $deptCode     = $dept?->code
                ?? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $dept?->name ?? 'GEN'), 0, 3));
            $deptCode     = str_pad($deptCode ?: 'GEN', 3, 'X');

            $deptCount = OpdPatient::whereDate('date', $today)
                ->where('department_id', $departmentId)
                ->count() + 1;

            $tokenNo = date('Ymd') . '-' . $deptCode . '-' . str_pad($deptCount, 3, '0', STR_PAD_LEFT);

            $caseReference = CaseReference::create();

            // opd patient entry
            $opdPatient                = new OpdPatient();
            $opdPatient->case_id       = $caseReference->id;
            $opdPatient->patient_id    = $patient->id;
            $opdPatient->doctor_id     = $data['doctor_id'];
            $opdPatient->department_id = $data['department_id'];
            $opdPatient->date          = $data['date'];
            $opdPatient->token_no      = $tokenNo ?? '';
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
