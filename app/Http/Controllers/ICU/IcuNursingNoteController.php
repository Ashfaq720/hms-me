<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuNursingNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuNursingNoteController extends Controller
{
    /**
     * Cross-admission Nursing Notes list.
     * Lists individual rows from icu_nursing_notes with filters; drill-in goes
     * to the per-admission notes page.
     */
    public function manage(Request $request)
    {
        $startDate = $request->input('start_date');     // YYYY-MM-DD | null
        $endDate   = $request->input('end_date');       // YYYY-MM-DD | null
        $shift     = $request->input('shift');          // Morning|Evening|Night|null
        $patientId = $request->input('patient_id');
        $bedId     = $request->input('bed_id');
        $search    = trim((string) $request->input('q', ''));
        $icuType   = $request->input('icu_type');       // ICU|CCU|null

        $notes = IcuNursingNote::query()
            ->with([
                'admission:id,icu_case_id,icu_type,bed_id,patient_id,ventilator_required',
                'admission.patient:id,patient_name,gender,dob',
                'admission.bed:id,name',
                'enteredBy:id,name',
            ])
            ->when($startDate, fn($q) => $q->whereDate('observation_time', '>=', $startDate))
            ->when($endDate,   fn($q) => $q->whereDate('observation_time', '<=', $endDate))
            ->when($shift,     fn($q) => $q->where('shift', $shift))
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->when($bedId,     fn($q) => $q->whereHas('admission', fn($a) => $a->where('bed_id', $bedId)))
            ->when($icuType,   fn($q) => $q->whereHas('admission', fn($a) => $a->where('icu_type', $icuType)))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('icu_case_id', 'like', "%{$search}%")
                       ->orWhereHas('admission.patient', fn($p) => $p->where('patient_name', 'like', "%{$search}%"))
                       ->orWhereRaw("CONCAT('PID', LPAD(patient_id, 6, '0')) like ?", ["%{$search}%"]);
                });
            })
            ->orderByDesc('observation_time')
            ->orderByDesc('id')
            ->get();

        $painNotes = $notes->whereNotNull('pain_score');

        $totals = [
            'notes'    => $notes->count(),
            'patients' => $notes->pluck('icu_admission_id')->unique()->count(),
            'avg_pain' => $painNotes->count() ? round((float) $painNotes->avg('pain_score'), 1) : null,
        ];

        $filterScope = IcuAdmission::query()
            ->with(['patient:id,patient_name', 'bed:id,name'])
            ->active()
            ->when($icuType, fn($q) => $q->where('icu_type', $icuType))
            ->get();

        $patients = $filterScope->pluck('patient')->filter()->unique('id')->sortBy('patient_name')->values();
        $beds     = $filterScope->pluck('bed')->filter()->unique('id')->sortBy('name')->values();

        return view('icu.nursing-notes.manage', compact(
            'notes', 'totals', 'patients', 'beds',
            'startDate', 'endDate', 'shift', 'patientId', 'bedId', 'search', 'icuType'
        ));
    }

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
