<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillRemainingGapsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $userId = DB::table('users')->value('id');

        // ── 1. Seed ICU vital logs for existing ICU admissions ──
        $icuAdmissions = DB::table('icu_admissions')->limit(7)->get();
        foreach ($icuAdmissions as $a) {
            // Skip if already has vitals
            if (DB::table('icu_vital_logs')->where('icu_admission_id', $a->id)->exists()) continue;

            // Insert 8 hourly vital readings over the last 24h
            for ($h = 8; $h >= 0; $h--) {
                $hr = 75 + rand(-15, 25);
                $sys = 120 + rand(-30, 40);
                $dia = 80 + rand(-15, 20);
                $spo2 = 96 - rand(0, 6);
                $rr = 16 + rand(-4, 10);
                $temp = 98.6 + (rand(-15, 25) / 10);

                // Severity based on deviation (enum: Normal/Warning/Critical)
                $severity = 'Normal';
                if ($hr > 110 || $hr < 50 || $sys > 160 || $sys < 90 || $spo2 < 92) $severity = 'Critical';
                elseif ($hr > 100 || $spo2 < 95) $severity = 'Warning';

                DB::table('icu_vital_logs')->insert([
                    'icu_admission_id'  => $a->id,
                    'icu_case_id'       => $a->icu_case_id,
                    'patient_id'        => $a->patient_id,
                    'bed_id'            => $a->bed_id,
                    'heart_rate'        => $hr,
                    'systolic_bp'       => $sys,
                    'diastolic_bp'      => $dia,
                    'spo2'              => $spo2,
                    'respiratory_rate'  => $rr,
                    'temperature'       => round($temp, 1),
                    'source_type'       => $h % 2 === 0 ? 'manual' : 'device',
                    'severity'          => $severity,
                    'recorded_at'       => $now->copy()->subHours($h),
                    'entered_by'        => $userId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }

        // ── 2. Mark some lab order requests as 'completed' ──
        DB::table('lab_investigation_order_request')
            ->where('status', '!=', 'completed')
            ->whereNull('file')
            ->limit(50)
            ->update(['status' => 'completed', 'updated_at' => $now]);

        // ── 3. Seed per-admission ICU vital thresholds ──
        foreach (DB::table('icu_admissions')->get() as $a) {
            if (DB::table('icu_vital_thresholds')->where('icu_admission_id', $a->id)->exists()) continue;
            $thresholds = [
                ['HeartRate',       60, 100, 50, 110, 40, 130],
                ['SystolicBP',     100, 140, 90, 160, 80, 180],
                ['DiastolicBP',     60,  90, 55,  95, 50, 110],
                ['SpO2',             95, 100, 92, 100, 88, 100],
                ['RespiratoryRate', 12,  20, 10,  24,  8,  30],
                ['Temperature',      97, 99,  96.5, 100.5, 95, 103],
            ];
            foreach ($thresholds as [$vital, $nmin, $nmax, $wmin, $wmax, $cmin, $cmax]) {
                DB::table('icu_vital_thresholds')->insert([
                    'icu_admission_id' => $a->id,
                    'patient_id'       => $a->patient_id,
                    'vital_type'       => $vital,
                    'normal_min'       => $nmin,
                    'normal_max'       => $nmax,
                    'warning_min'      => $wmin,
                    'warning_max'      => $wmax,
                    'critical_min'     => $cmin,
                    'critical_max'     => $cmax,
                    'configured_by'    => $userId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }
    }
}
