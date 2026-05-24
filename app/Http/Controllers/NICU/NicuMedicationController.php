<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Nicu\NicuMedication;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuMedicationController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuMedication::with([
            'admission.baby:id,patient_name',
            'prescriber:id,name',
            'administeredBy:id,name',
        ]);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $meds = $q->latest('administered_at')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();
        $doctors = Doctor::orderBy('name')->get(['id', 'name']);

        return view('nicu.medications.index', compact('meds', 'activeAdmissions', 'doctors'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'administered_at'   => ['required', 'date'],
            'drug_name'         => ['required', 'string', 'max:200'],
            'dose'              => ['required', 'string', 'max:100'],
            'route'             => ['nullable', 'in:' . implode(',', NicuMedication::ROUTES)],
            'frequency'         => ['nullable', 'string', 'max:100'],
            'indication'        => ['nullable', 'string', 'max:1000'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'prescribed_by'     => ['nullable', 'exists:doctors,id'],
        ]);

        $data['administered_by'] = auth()->id();
        NicuMedication::create($data);

        return back()->with('success', 'Medication recorded.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
