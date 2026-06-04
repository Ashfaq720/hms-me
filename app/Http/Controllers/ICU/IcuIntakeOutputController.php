<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuIntakeOutputChart;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class IcuIntakeOutputController extends Controller
{
    private const INTAKE_CATEGORIES = [
        'IVFluid', 'OralFluid', 'Blood', 'MedFluid', 'TubeFeeding',
    ];

    private const OUTPUT_CATEGORIES = [
        'Urine', 'Drain', 'Vomiting', 'Stool', 'BloodLoss', 'Other',
    ];

    /**
     * Cross-admission Intake / Output dashboard.
     * Lists every active ICU/CCU admission and aggregates intake/output for the
     * selected date (+ optional shift). Drill-in goes to the per-admission chart.
     */
    public function manage(Request $request)
    {
        $date      = $request->input('date', now()->toDateString());
        $shift     = $request->input('shift');         // Morning|Evening|Night|null
        $patientId = $request->input('patient_id');
        $bedId     = $request->input('bed_id');
        $search    = trim((string) $request->input('q', ''));
        $icuType   = $request->input('icu_type');      // ICU|CCU|null

        $admissions = IcuAdmission::query()
            ->with(['patient:id,patient_name,gender,dob', 'bed:id,name'])
            ->active()
            ->when($icuType,   fn($q) => $q->where('icu_type', $icuType))
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->when($bedId,     fn($q) => $q->where('bed_id', $bedId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('icu_case_id', 'like', "%{$search}%")
                       ->orWhereHas('patient', fn($p) => $p->where('patient_name', 'like', "%{$search}%"))
                       ->orWhereRaw("CONCAT('PID', LPAD(patient_id, 6, '0')) like ?", ["%{$search}%"]);
                });
            })
            ->orderBy('bed_id')
            ->get();

        $agg = IcuIntakeOutputChart::query()
            ->whereIn('icu_admission_id', $admissions->pluck('id'))
            ->whereDate('entry_time', $date)
            ->when($shift, function ($q) use ($shift) {
                if ($shift === 'Morning') {
                    $q->whereRaw('HOUR(entry_time) >= 6 AND HOUR(entry_time) < 14');
                } elseif ($shift === 'Evening') {
                    $q->whereRaw('HOUR(entry_time) >= 14 AND HOUR(entry_time) < 22');
                } elseif ($shift === 'Night') {
                    $q->whereRaw('HOUR(entry_time) >= 22 OR HOUR(entry_time) < 6');
                }
            })
            ->selectRaw('icu_admission_id,
                SUM(CASE WHEN entry_type = "Intake" THEN quantity_ml ELSE 0 END) AS total_intake,
                SUM(CASE WHEN entry_type = "Output" THEN quantity_ml ELSE 0 END) AS total_output,
                MAX(entry_time) AS last_entry_time')
            ->groupBy('icu_admission_id')
            ->get()
            ->keyBy('icu_admission_id');

        $rows = $admissions->map(function ($a) use ($agg) {
            $stats  = $agg->get($a->id);
            $intake = (int) ($stats->total_intake ?? 0);
            $output = (int) ($stats->total_output ?? 0);
            $last   = $stats && $stats->last_entry_time ? Carbon::parse($stats->last_entry_time) : null;

            return (object) [
                'admission'       => $a,
                'intake'          => $intake,
                'output'          => $output,
                'balance'         => $intake - $output,
                'last_entry_time' => $last,
                'shift_label'     => $last ? $this->shiftFor((int) $last->hour) : null,
            ];
        });

        $totals = [
            'patients' => $rows->count(),
            'intake'   => (int) $rows->sum('intake'),
            'output'   => (int) $rows->sum('output'),
            'balance'  => (int) ($rows->sum('intake') - $rows->sum('output')),
        ];

        // Filter dropdown sources — independent of patient/bed filters so they don't self-shrink
        $filterScope = IcuAdmission::query()
            ->with(['patient:id,patient_name', 'bed:id,name'])
            ->active()
            ->when($icuType, fn($q) => $q->where('icu_type', $icuType))
            ->get();

        $patients = $filterScope->pluck('patient')->filter()->unique('id')->sortBy('patient_name')->values();
        $beds     = $filterScope->pluck('bed')->filter()->unique('id')->sortBy('name')->values();

        return view('icu.intake-output.manage', compact(
            'rows', 'totals', 'patients', 'beds',
            'date', 'shift', 'patientId', 'bedId', 'search', 'icuType'
        ));
    }

    protected function shiftFor(int $hour): string
    {
        if ($hour >= 6 && $hour < 14)  return 'Morning';
        if ($hour >= 14 && $hour < 22) return 'Evening';
        return 'Night';
    }

    public function index(Request $request, $admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $date = $request->input('date', now()->toDateString());

        $entries = IcuIntakeOutputChart::where('icu_admission_id', $admission->id)
            ->whereDate('entry_time', $date)
            ->orderBy('entry_time')
            ->get();

        $totalIntake  = (int) $entries->where('entry_type', 'Intake')->sum('quantity_ml');
        $totalOutput  = (int) $entries->where('entry_type', 'Output')->sum('quantity_ml');
        $balance      = $totalIntake - $totalOutput;

        return view('icu.intake-output.index', compact(
            'admission', 'entries', 'date', 'totalIntake', 'totalOutput', 'balance'
        ));
    }

    public function store(Request $request, $admissionId)
    {
        $type = $request->input('entry_type');
        $allowedCats = $type === 'Intake' ? self::INTAKE_CATEGORIES : self::OUTPUT_CATEGORIES;

        $request->validate([
            'entry_time'  => ['required', 'date'],
            'entry_type'  => ['required', Rule::in(['Intake', 'Output'])],
            'category'    => ['required', Rule::in($allowedCats)],
            'quantity_ml' => ['required', 'integer', 'min:1', 'max:100000'],
            'remarks'     => ['nullable', 'string', 'max:500'],
        ]);

        $admission = IcuAdmission::findOrFail($admissionId);

        IcuIntakeOutputChart::create([
            'icu_admission_id' => $admission->id,
            'icu_case_id'      => $admission->icu_case_id,
            'patient_id'       => $admission->patient_id,
            'entry_time'       => $request->entry_time,
            'entry_type'       => $request->entry_type,
            'category'         => $request->category,
            'quantity_ml'      => $request->quantity_ml,
            'remarks'          => $request->remarks,
            'entered_by'       => auth()->id(),
        ]);

        return back()->with('success', "{$type} entry saved.");
    }
}
