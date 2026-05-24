<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuInfection;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuInfectionController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuInfection::with(['admission.baby:id,patient_name', 'reporter:id,name']);
        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }
        $infections = $q->latest('identified_on')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();

        return view('nicu.infections.index', compact('infections', 'activeAdmissions'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'infection_type'    => ['required', 'in:' . implode(',', NicuInfection::TYPES)],
            'organism'          => ['nullable', 'string', 'max:200'],
            'source'            => ['nullable', 'in:' . implode(',', NicuInfection::SOURCES)],
            'isolation_status'  => ['nullable', 'in:' . implode(',', NicuInfection::ISOLATIONS)],
            'identified_on'     => ['required', 'date'],
            'resolved_on'       => ['nullable', 'date', 'after_or_equal:identified_on'],
            'treatment_summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['reported_by'] = auth()->id();
        NicuInfection::create($data);

        return back()->with('success', 'Infection record saved.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
