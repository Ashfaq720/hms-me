<?php

namespace App\Services\Icu;

use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuVitalLog;
use App\Models\Icu\IcuVitalThreshold;

class VitalClassifier
{
    /**
     * Default ranges, used when no patient-specific threshold row exists.
     * BRD §3 normal ranges; warning/critical zones widened above/below.
     */
    public const DEFAULTS = [
        'HeartRate'       => ['n_min' => 60,  'n_max' => 100, 'w_min' => 50,  'w_max' => 120, 'c_min' => 40,  'c_max' => 140],
        'SystolicBP'      => ['n_min' => 90,  'n_max' => 140, 'w_min' => 80,  'w_max' => 160, 'c_min' => 70,  'c_max' => 180],
        'DiastolicBP'     => ['n_min' => 60,  'n_max' => 90,  'w_min' => 50,  'w_max' => 100, 'c_min' => 40,  'c_max' => 110],
        'SpO2'            => ['n_min' => 95,  'n_max' => 100, 'w_min' => 90,  'w_max' => 100, 'c_min' => 0,   'c_max' => 100],
        'RespiratoryRate' => ['n_min' => 12,  'n_max' => 20,  'w_min' => 10,  'w_max' => 25,  'c_min' => 8,   'c_max' => 30],
        'Temperature'     => ['n_min' => 36.5,'n_max' => 37.5,'w_min' => 36,  'w_max' => 38.5,'c_min' => 35,  'c_max' => 39.5],
    ];

    /**
     * Resolve effective threshold for a given vital, preferring patient/admission rows.
     */
    public function thresholds(IcuAdmission $admission, string $vitalType): array
    {
        $row = IcuVitalThreshold::where('vital_type', $vitalType)
            ->where(function ($q) use ($admission) {
                $q->where('icu_admission_id', $admission->id)
                    ->orWhere(function ($q2) use ($admission) {
                        $q2->whereNull('icu_admission_id')
                            ->where('patient_id', $admission->patient_id);
                    });
            })
            ->orderByRaw('icu_admission_id IS NULL')   // admission-specific first
            ->first();

        if ($row) {
            return [
                'n_min' => $row->normal_min,
                'n_max' => $row->normal_max,
                'w_min' => $row->warning_min,
                'w_max' => $row->warning_max,
                'c_min' => $row->critical_min,
                'c_max' => $row->critical_max,
            ];
        }

        return self::DEFAULTS[$vitalType] ?? self::DEFAULTS['HeartRate'];
    }

    /**
     * Classify a single value into Normal | Warning | Critical.
     */
    public function classifyValue(IcuAdmission $admission, string $vitalType, float $value): string
    {
        $t = $this->thresholds($admission, $vitalType);

        // Critical: outside critical band
        if (($t['c_min'] !== null && $value < $t['c_min']) || ($t['c_max'] !== null && $value > $t['c_max'])) {
            return 'Critical';
        }
        // Warning: outside normal but within critical
        if (($t['n_min'] !== null && $value < $t['n_min']) || ($t['n_max'] !== null && $value > $t['n_max'])) {
            // If warning band is configured, only flag warning when within it
            if (
                ($t['w_min'] !== null && $value < $t['w_min']) ||
                ($t['w_max'] !== null && $value > $t['w_max'])
            ) {
                return 'Critical';
            }
            return 'Warning';
        }
        return 'Normal';
    }

    /**
     * Classify a vital log row across all populated vitals, returns the worst severity
     * and the per-vital map. Does not persist anything.
     */
    public function classifyLog(IcuAdmission $admission, IcuVitalLog $log): array
    {
        $map = [];
        $worst = 'Normal';

        $checks = [
            'HeartRate'       => $log->heart_rate,
            'SystolicBP'      => $log->systolic_bp,
            'DiastolicBP'     => $log->diastolic_bp,
            'SpO2'            => $log->spo2,
            'RespiratoryRate' => $log->respiratory_rate,
            'Temperature'     => $log->temperature,
        ];

        foreach ($checks as $type => $val) {
            if ($val === null || $val === '') {
                continue;
            }
            $sev = $this->classifyValue($admission, $type, (float) $val);
            $map[$type] = $sev;
            if ($sev === 'Critical' || ($sev === 'Warning' && $worst === 'Normal')) {
                $worst = $sev;
            }
        }

        return ['worst' => $worst, 'per_vital' => $map];
    }

    /**
     * Persist alerts for any per-vital reading that crosses Warning/Critical.
     * Returns the alerts created.
     */
    public function generateAlerts(IcuAdmission $admission, IcuVitalLog $log, array $perVital): array
    {
        $created = [];

        foreach ($perVital as $type => $sev) {
            if ($sev === 'Normal') {
                continue;
            }

            $value = match ($type) {
                'HeartRate'       => $log->heart_rate,
                'SystolicBP'      => $log->systolic_bp,
                'DiastolicBP'     => $log->diastolic_bp,
                'SpO2'            => $log->spo2,
                'RespiratoryRate' => $log->respiratory_rate,
                'Temperature'     => $log->temperature,
                default           => null,
            };

            $created[] = IcuAlert::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'alert_type'       => 'VitalAbnormal',
                'vital_type'       => $type,
                'observed_value'   => (string) $value,
                'severity'         => $sev,
                'message'          => sprintf('%s = %s — %s', $type, $value, $sev),
                'source_module'    => 'icu_vital_logs',
                'source_id'        => $log->id,
                'status'           => 'Active',
            ]);
        }

        return $created;
    }
}
