<?php

namespace Database\Seeders;

use App\Models\Charges\Charge;
use App\Models\Package;
use App\Models\PackageService;
use Illuminate\Database\Seeder;

class AttachPackageServicesSeeder extends Seeder
{
    public function run(): void
    {
        // Charge lookup by name (case-insensitive, "contains")
        $charge = function (string $like): ?Charge {
            return Charge::whereRaw('LOWER(charge_name) LIKE ?', ['%' . strtolower($like) . '%'])
                ->orderBy('id')
                ->first();
        };

        $map = [
            'OPD-HC-BASIC' => [
                ['General Consultation', 1], ['CBC', 1], ['Urine R/E', 1], ['ECG', 1],
            ],
            'OPD-HC-STD' => [
                ['Specialist Consultation', 1], ['CBC', 1], ['Lipid Profile', 1], ['LFT', 1],
                ['KFT', 1], ['HbA1c', 1], ['Urine R/E', 1], ['Chest X-Ray', 1], ['Abdominal USG', 1], ['ECG', 1],
            ],
            'OPD-HC-PREM' => [
                ['Specialist Consultation', 2], ['CBC', 1], ['Lipid Profile', 1], ['LFT', 1],
                ['KFT', 1], ['HbA1c', 1], ['Urine R/E', 1], ['Chest X-Ray', 1], ['Abdominal USG', 1],
                ['CT Brain', 1], ['ECG', 1], ['Blood Culture', 1],
            ],
            'OPD-DIAB' => [
                ['Specialist Consultation', 2], ['HbA1c', 1], ['Lipid Profile', 1],
                ['KFT', 1], ['Urine R/E', 1], ['Blood Glucose (Fasting)', 1],
            ],
            'OPD-CARD' => [
                ['Cardiologist Consultation', 2], ['ECG', 2], ['Lipid Profile', 1],
                ['Chest X-Ray', 1],
            ],
            'IPD-STD-3D' => [
                ['General Bed (per day)', 3], ['Routine Nursing Care', 3], ['General Consultation', 2],
                ['IV Cannulation', 1], ['IPD Admission Charge', 1], ['Discharge Documentation', 1],
            ],
            'IPD-PREM-5D' => [
                ['Cabin (per day)', 5], ['Routine Nursing Care', 5], ['Specialist Consultation', 3],
                ['CBC', 1], ['Lipid Profile', 1], ['IPD Admission Charge', 1], ['Discharge Documentation', 1],
            ],
            'OT-APPENDX' => [
                ['Appendectomy', 1], ['Anesthesia — General', 1], ['OT Charge — Major', 2],
                ['General Bed (per day)', 3], ['Routine Nursing Care', 3],
            ],
            'OT-LAPCHOLE' => [
                ['Cholecystectomy', 1], ['Anesthesia — General', 1], ['OT Charge — Major', 3],
                ['Cabin (per day)', 3], ['Routine Nursing Care', 3],
            ],
            'OT-HERNIA' => [
                ['Hernia Repair', 1], ['Anesthesia — General', 1], ['OT Charge — Major', 2],
                ['General Bed (per day)', 2], ['Routine Nursing Care', 2],
            ],
            'OT-CSEC' => [
                ['Caesarean Section', 1], ['Anesthesia — Spinal', 1], ['OT Charge — Major', 2],
                ['Cabin (per day)', 4], ['Routine Nursing Care', 4],
            ],
            'ICU-STD-1D'  => [['ICU Bed (per day)', 1], ['ICU Nursing Care', 1], ['Cardiac Monitor', 1]],
            'ICU-VENT-1D' => [['ICU Bed (per day)', 1], ['ICU Nursing Care', 1], ['Ventilator (per day)', 1], ['Cardiac Monitor', 1]],
            'CCU-STD-1D'  => [['CCU Bed (per day)', 1], ['ICU Nursing Care', 1], ['Cardiac Monitor', 1]],
            'NICU-STD-1D' => [['NICU Bed (per day)', 1], ['ICU Nursing Care', 1], ['Pulse Oximeter', 1]],
            'NICU-PREM'   => [['NICU Bed (per day)', 1], ['ICU Nursing Care', 1], ['Ventilator (per day)', 1], ['Pulse Oximeter', 1]],
            'MAT-NVD'     => [['Normal Vaginal Delivery', 1], ['General Bed (per day)', 2], ['Routine Nursing Care', 2], ['IPD Admission Charge', 1]],
            'MAT-CSEC'    => [['Caesarean Section', 1], ['Anesthesia — Spinal', 1], ['OT Charge — Major', 2], ['Cabin (per day)', 4], ['Routine Nursing Care', 4], ['IPD Admission Charge', 1]],
            'PATH-BASIC'  => [['CBC', 1], ['Blood Glucose (Fasting)', 1], ['Urine R/E', 1], ['Lipid Profile', 1]],
            'PATH-FULL'   => [['CBC', 1], ['LFT', 1], ['KFT', 1], ['Lipid Profile', 1], ['HbA1c', 1], ['Urine R/E', 1], ['Blood Culture', 1]],
            'PATH-PREOP'  => [['CBC', 1], ['Blood Glucose (Fasting)', 1], ['LFT', 1], ['KFT', 1]],
            'RAD-BASIC'   => [['Chest X-Ray', 1], ['Abdominal USG', 1]],
            'RAD-ADV'     => [['CT Brain', 1], ['Chest X-Ray', 1], ['Abdominal USG', 1]],
            'DIAG-DM'     => [['HbA1c', 1], ['Blood Glucose (Fasting)', 1], ['Lipid Profile', 1], ['KFT', 1], ['Urine R/E', 1]],
            'DIAG-CARDIO' => [['ECG', 1], ['Lipid Profile', 1], ['CBC', 1], ['Cardiologist Consultation', 1]],
            'DIAG-GEN'    => [['Lipid Profile', 1], ['LFT', 1], ['KFT', 1], ['Abdominal USG', 1], ['Chest X-Ray', 1], ['ECG', 1]],
            'PHYS-10'     => [['Physiotherapy Session', 10]],
            'WELL-YD'     => [['General Consultation', 1], ['Physiotherapy Session', 5]],
            'DENT-CLEAN'  => [['Specialist Consultation', 1]],
            'CORP-ANNUAL' => [['Specialist Consultation', 1], ['CBC', 1], ['Lipid Profile', 1], ['Chest X-Ray', 1], ['ECG', 1]],
        ];

        foreach ($map as $code => $services) {
            $pkg = Package::where('code', $code)->first();
            if (! $pkg) continue;

            PackageService::where('package_id', $pkg->id)->delete();

            $subtotal = 0;
            foreach ($services as $row) {
                [$nameLike, $qty] = $row;

                $c = $charge($nameLike);
                if (! $c) continue;

                $rate = (float) $c->standard_charge;
                $amount = $qty * $rate;
                $subtotal += $amount;

                PackageService::create([
                    'package_id'  => $pkg->id,
                    'charge_id'   => $c->id,
                    'service_id'  => null,
                    'service_catalog_id' => null,
                    'is_included' => true,
                    'quantity'    => $qty,
                    'rate'        => $rate,
                    'amount'      => $amount,
                ]);
            }
            $pkg->update(['total_amount' => $subtotal]);
        }
    }
}
