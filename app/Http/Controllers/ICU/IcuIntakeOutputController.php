<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuIntakeOutputChart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuIntakeOutputController extends Controller
{
    private const INTAKE_CATEGORIES = [
        'IVFluid', 'OralFluid', 'Blood', 'MedFluid', 'TubeFeeding',
    ];

    private const OUTPUT_CATEGORIES = [
        'Urine', 'Drain', 'Vomiting', 'Stool', 'BloodLoss', 'Other',
    ];

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
