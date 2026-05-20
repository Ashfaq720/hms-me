<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuNursingNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuNursingNoteController extends Controller
{
    public function index($admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $notes = IcuNursingNote::where('icu_admission_id', $admission->id)
            ->orderByDesc('observation_time')
            ->get();

        return view('icu.nursing-notes.index', compact('admission', 'notes'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'shift'                => ['nullable', Rule::in(['Morning', 'Evening', 'Night'])],
            'observation_time'     => ['required', 'date'],
            'consciousness_level'  => ['nullable', 'string', 'max:50'],
            'pain_score'           => ['nullable', 'integer', 'between:0,10'],
            'respiratory_support'  => ['nullable', 'string', 'max:100'],
            'oxygen_flow'          => ['nullable', 'string', 'max:50'],
            'position'             => ['nullable', 'string', 'max:50'],
            'skin_condition'       => ['nullable', 'string', 'max:100'],
            'general_condition'    => ['nullable', 'string', 'max:100'],
            'remarks'              => ['nullable', 'string', 'max:1000'],
        ]);

        $admission = IcuAdmission::findOrFail($admissionId);

        IcuNursingNote::create(array_merge($request->only([
            'shift',
            'observation_time',
            'consciousness_level',
            'pain_score',
            'respiratory_support',
            'oxygen_flow',
            'position',
            'skin_condition',
            'general_condition',
            'remarks',
        ]), [
            'icu_admission_id' => $admission->id,
            'icu_case_id'      => $admission->icu_case_id,
            'patient_id'       => $admission->patient_id,
            'entered_by'       => auth()->id(),
        ]));

        return back()->with('success', 'Nursing note saved.');
    }
}
