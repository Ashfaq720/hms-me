<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuVitalThreshold;
use App\Services\Icu\VitalClassifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuVitalThresholdController extends Controller
{
    private const VITAL_TYPES = [
        'HeartRate', 'SystolicBP', 'DiastolicBP', 'SpO2', 'RespiratoryRate', 'Temperature',
    ];

    public function index($admissionId)
    {
        $admission  = IcuAdmission::findOrFail($admissionId);
        $configured = IcuVitalThreshold::where('icu_admission_id', $admission->id)
            ->get()->keyBy('vital_type');

        $rows = [];
        foreach (self::VITAL_TYPES as $type) {
            $defaults = VitalClassifier::DEFAULTS[$type];
            $rows[$type] = $configured->get($type) ?? (object) [
                'normal_min'   => $defaults['n_min'],
                'normal_max'   => $defaults['n_max'],
                'warning_min'  => $defaults['w_min'],
                'warning_max'  => $defaults['w_max'],
                'critical_min' => $defaults['c_min'],
                'critical_max' => $defaults['c_max'],
            ];
        }

        return view('icu.thresholds.index', compact('admission', 'rows'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'vital_type'   => ['required', Rule::in(self::VITAL_TYPES)],
            'normal_min'   => ['nullable', 'numeric'],
            'normal_max'   => ['nullable', 'numeric'],
            'warning_min'  => ['nullable', 'numeric'],
            'warning_max'  => ['nullable', 'numeric'],
            'critical_min' => ['nullable', 'numeric'],
            'critical_max' => ['nullable', 'numeric'],
        ]);

        $admission = IcuAdmission::findOrFail($admissionId);

        IcuVitalThreshold::updateOrCreate(
            [
                'icu_admission_id' => $admission->id,
                'vital_type'       => $request->vital_type,
            ],
            [
                'patient_id'    => $admission->patient_id,
                'normal_min'    => $request->normal_min,
                'normal_max'    => $request->normal_max,
                'warning_min'   => $request->warning_min,
                'warning_max'   => $request->warning_max,
                'critical_min'  => $request->critical_min,
                'critical_max'  => $request->critical_max,
                'configured_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Threshold for {$request->vital_type} saved.");
    }

    public function destroy($admissionId, $vitalType)
    {
        IcuVitalThreshold::where('icu_admission_id', $admissionId)
            ->where('vital_type', $vitalType)
            ->delete();

        return back()->with('success', "Reverted {$vitalType} to default thresholds.");
    }
}
