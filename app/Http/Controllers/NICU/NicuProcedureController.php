<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Nicu\NicuProcedure;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuProcedureController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuProcedure::with([
            'admission.baby:id,patient_name',
            'performer:id,name',
            'recordedBy:id,name',
        ]);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $procedures = $q->latest('performed_at')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();
        $doctors = Doctor::orderBy('name')->get(['id', 'name']);

        return view('nicu.procedures.index', compact('procedures', 'activeAdmissions', 'doctors'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'performed_at'      => ['required', 'date'],
            'procedure_name'    => ['required', 'string', 'max:200'],
            'outcome'           => ['nullable', 'in:' . implode(',', NicuProcedure::OUTCOMES)],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'performed_by'      => ['nullable', 'exists:doctors,id'],
        ]);

        $data['recorded_by'] = auth()->id();
        NicuProcedure::create($data);

        return back()->with('success', 'Procedure logged.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
