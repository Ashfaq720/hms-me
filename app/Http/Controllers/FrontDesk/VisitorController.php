<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FrontDesk\ErPatient;
use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\PatientVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        return view('front-desk.visitor.create', compact('departments'));
    }

    public function patientsByType(Request $request)
    {
        $type = $request->get('type');

        if ($type === 'OPD') {
            $rows = OpdPatient::with('patient')
                ->whereDate('date', today())
                ->orderByDesc('date')
                ->get()
                ->filter(fn ($o) => $o->patient)
                ->map(fn ($o) => [
                    'id'   => $o->patient_id,
                    'text' => $o->patient->patient_name
                        . ' | ' . ($o->patient->mrn ?? 'No MRN')
                        . ' | OPD ' . $o->date?->format('d M'),
                ])
                ->unique('id')
                ->values();
        } elseif ($type === 'Ipd') {
            $rows = IpdPatient::with('patient')
                ->whereIn('status', ['Admitted', 'admitted'])
                ->orderByDesc('admission_date')
                ->get()
                ->filter(fn ($i) => $i->patient)
                ->map(fn ($i) => [
                    'id'   => $i->patient_id,
                    'text' => $i->patient->patient_name
                        . ' | ' . ($i->patient->mrn ?? 'No MRN')
                        . ' | IPD #' . $i->ipd_no,
                ])
                ->unique('id')
                ->values();
        } elseif ($type === 'ER') {
            $rows = ErPatient::with('patient')
                ->whereNotIn('status', ['DISCHARGED', 'CANCELLED'])
                ->orderByDesc('arrival_time')
                ->get()
                ->filter(fn ($e) => $e->patient)
                ->map(fn ($e) => [
                    'id'   => $e->patient_id,
                    'text' => $e->patient->patient_name
                        . ' | ' . ($e->patient->mrn ?? 'No MRN')
                        . ' | ER ' . \Carbon\Carbon::parse($e->arrival_time)->format('d M H:i'),
                ])
                ->unique('id')
                ->values();
        } else {
            $rows = collect();
        }

        return response()->json($rows);
    }

    public function slip(PatientVisitor $visitor)
    {
        $visitor->load(['department', 'patient']);
        return view('front-desk.visitor.slip', compact('visitor'));
    }

    public function todayVisitors()
    {
        $visitors = PatientVisitor::with('department')
            ->whereDate('visit_date', today())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($v) => [
                'id'           => $v->id,
                'visit_code'   => $v->visit_code,
                'visit_date'   => $v->visit_date,
                'visit_time'   => $v->visit_time ? \Carbon\Carbon::parse($v->visit_time)->format('h:i A') : null,
                'patient_name' => $v->patient_name,
                'visitor_name' => $v->visitor_name,
                'contact_no'   => $v->contact_no,
                'department'   => $v->department?->name ?? '—',
                'patient_type' => $v->patient_type,
                'visitor_qty'  => $v->visitor_qty,
            ]);

        return response()->json($visitors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitor_name'  => ['required', 'string', 'max:255'],
            'contact_no'    => ['required', 'string', 'max:20'],
            'patient_type'  => ['required', 'in:OPD,Ipd,ER'],
            'visit_date'    => ['required', 'date'],
            'visit_time'    => ['nullable', 'date_format:H:i'],
            'visitor_qty'   => ['required', 'integer', 'min:1', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'patient_id'    => ['nullable', 'exists:patients,id'],
            'patient_name'  => ['nullable', 'string', 'max:255'],
            'remarks'       => ['nullable', 'string', 'max:2000'],
        ], [
            'department_id.exists' => 'Selected department is invalid.',
        ]);

        if (empty($request->patient_id) && empty(trim((string) $request->patient_name))) {
            return back()
                ->withErrors(['patient_name' => 'Please select a patient or enter the patient name manually.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $patientName = null;
            if (!empty($request->patient_id)) {
                $patient     = Patient::findOrFail($request->patient_id);
                $patientName = $patient->patient_name;
            }

            PatientVisitor::create([
                'visitor_name'  => $request->visitor_name,
                'contact_no'    => $request->contact_no,
                'patient_type'  => $request->patient_type,
                'visit_date'    => $request->visit_date,
                'visit_time'    => $request->visit_time ?: null,
                'visitor_qty'   => $request->visitor_qty,
                'patient_id'    => $request->patient_id ?? null,
                'patient_name'  => $patientName ?: $request->patient_name,
                'department_id' => $request->department_id,
                'remarks'       => $request->remarks,
                'created_by'    => auth()->id() ?? null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Visitor entry saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong. Please try again.')->withInput();
        }
    }
}
