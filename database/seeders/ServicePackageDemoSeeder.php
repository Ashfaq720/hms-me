<?php

namespace Database\Seeders;

use App\Models\BedType;
use App\Models\Department;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryType;
use App\Models\ServicePackage;
use App\Models\ServicePackageBedPrice;
use App\Models\ServicePackageItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Realistic Service Package master data — mirrors the spec's example
 * table (C-Section / Cardiac Care / General Medicine / ICU / CCU /
 * Diabetes Follow-up / Health Checkup / Endoscopy / General Admission).
 *
 * Safe to re-run — keyed by `code`. Existing rows with the same code
 * are wiped and re-created so prices stay aligned with the seeder.
 */
class ServicePackageDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedAll();
        });
    }

    private function seedAll(): void
    {
        // Reference data — fail-soft if missing.
        $dept    = fn ($name) => optional(Department::where('name', 'like', "%{$name}%")->first())->id;
        $bed     = fn ($name) => optional(BedType::where('name', $name)->first())->id;
        $surgery = fn ($name) => optional(OtSurgeryType::where('name', 'like', "%{$name}%")->first())->id;
        $cat     = fn ($name) => optional(OtSurgeryCategory::where('name', 'like', "%{$name}%")->first())->id;

        $bedWard = $bed('Normal');   // proxy for General Ward
        $bedIcu  = $bed('ICU');
        $bedCcu  = $bed('CCU');
        $bedMale = $bed('Male');

        $consumableSyringe = optional(OtConsumable::where('name', 'like', '%Syringe%')->orWhere('name', 'like', '%Gloves%')->first())->id
                          ?? optional(OtConsumable::first())->id;

        $packages = [
            // ─────────── OT package ───────────
            [
                'code'         => 'PKG-OT-CSEC',
                'name'         => 'C-Section Package',
                'package_type' => 'OT',
                'department_id'        => $dept('MEDICINE') ?? $dept('PEDIATRIC'),
                'admission_type'       => 'Planned',
                'bed_type_id'          => $bedWard,
                'surgery_type_id'      => $surgery('C-Section'),
                'surgery_category_id'  => $cat('Major'),
                'duration_days'        => 4,
                'base_price'           => 65000,
                'patient_category'     => 'General',
                'requires_approval'    => false,
                'included_services_text' => "Bed 4 days\nOT room + surgeon + anesthesia\nNursing care\nBasic consumables\nPost-operative care",
                'excluded_services_text' => "Extra bed days\nHigh-value medicines\nMRI / CT\nBlood transfusion\nImplants",
                'status'   => 'Active',
                'remarks'  => 'Standard maternity OT package — 4-day stay.',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'General Ward 4 Days',         'qty' => 4, 'unit' => 'days'],
                    ['cat' => 'OT',  'name' => 'OT Room + Surgeon + Anesthesia','qty' => 1, 'unit' => 'set', 'master' => 'surgery_type', 'master_id' => $surgery('C-Section')],
                    ['cat' => 'Doctor Visit',  'name' => 'Consultant Visit',    'qty' => 4, 'unit' => 'visits'],
                    ['cat' => 'Nursing',       'name' => 'Daily Nursing Care',  'qty' => 4, 'unit' => 'days'],
                    ['cat' => 'Investigation', 'name' => 'CBC + RBS + ECG',     'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Consumable',    'name' => 'Basic OT Consumables','qty' => 1, 'unit' => 'set', 'master' => 'consumable', 'master_id' => $consumableSyringe],
                ],
                'bed_prices' => [
                    $bedWard => 55000,
                    $bedMale => 75000,   // proxy for Cabin pricing
                ],
            ],

            // ─────────── IPD cardiac (multi-bed-type) ───────────
            [
                'code'         => 'PKG-IPD-CARD',
                'name'         => 'Cardiac Care Package',
                'package_type' => 'IPD',
                'department_id'        => $dept('MEDICINE'),
                'admission_type'       => 'Planned',
                'bed_type_id'          => $bedWard,
                'duration_days'        => 5,
                'base_price'           => 45000,
                'patient_category'     => 'General',
                'requires_approval'    => true,
                'approval_role'        => 'Billing Manager',
                'included_services_text' => "Bed 5 days\nCardiologist visit (daily)\nECG, Echo, basic labs\nBasic medicines\nNursing care",
                'excluded_services_text' => "Coronary angiogram\nStent / PCI\nDialysis\nHigh-value antibiotics",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'Bed 5 Days', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Cardiologist Visit', 'qty' => 5, 'unit' => 'visits'],
                    ['cat' => 'Investigation', 'name' => 'ECG (Daily)', 'qty' => 5, 'unit' => 'tests'],
                    ['cat' => 'Investigation', 'name' => 'Echo + CBC',  'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Nursing', 'name' => 'Cardiac Nursing Care', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Medicine', 'name' => 'Basic Cardiac Medicines', 'qty' => 1, 'unit' => 'set'],
                ],
                'bed_prices' => [
                    $bedWard => 45000,
                    $bedMale => 70000,
                    $bedIcu  => 120000,
                    $bedCcu  => 130000,
                ],
            ],

            // ─────────── IPD general medicine ───────────
            [
                'code'         => 'PKG-IPD-GMED',
                'name'         => 'General Medicine Package',
                'package_type' => 'IPD',
                'department_id'        => $dept('MEDICINE'),
                'admission_type'       => 'Planned',
                'bed_type_id'          => $bedWard,
                'duration_days'        => 3,
                'base_price'           => 25000,
                'patient_category'     => 'General',
                'requires_approval'    => false,
                'included_services_text' => "Bed 3 days\nDuty doctor visit\nBasic labs (CBC, RBS, Urine R/E)\nNursing\nBasic medicines",
                'excluded_services_text' => "Specialist consultation\nExpensive antibiotics\nImaging (MRI / CT)\nBlood / transfusion",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'Bed 3 Days', 'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Duty Doctor Visit', 'qty' => 3, 'unit' => 'visits'],
                    ['cat' => 'Investigation', 'name' => 'CBC + RBS + Urine R/E', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Nursing', 'name' => 'Standard Nursing Care', 'qty' => 3, 'unit' => 'days'],
                ],
                'bed_prices' => [
                    $bedWard => 25000,
                    $bedMale => 45000,
                    $bedIcu  => 95000,
                ],
            ],

            // ─────────── ICU package ───────────
            [
                'code'         => 'PKG-ICU-CRIT',
                'name'         => 'ICU 3 Days Critical Care Package',
                'package_type' => 'ICU',
                'department_id'        => $dept('MEDICINE'),
                'admission_type'       => 'Emergency',
                'bed_type_id'          => $bedIcu,
                'duration_days'        => 3,
                'base_price'           => 95000,
                'patient_category'     => 'General',
                'requires_approval'    => true,
                'approval_role'        => 'Duty Manager',
                'included_services_text' => "ICU bed 3 days\nIntensivist visit (twice daily)\nVentilator support if needed\nMonitor / infusion pumps\nBasic ICU consumables\nNursing 1:2",
                'excluded_services_text' => "Dialysis / CRRT\nHigh-value antibiotics\nBlood components",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'ICU Bed 3 Days', 'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Intensivist Round (2x/day)', 'qty' => 6, 'unit' => 'visits'],
                    ['cat' => 'Nursing', 'name' => 'ICU Nursing 1:2', 'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Equipment', 'name' => 'Monitor + Infusion Pump', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Consumable', 'name' => 'Basic ICU Consumables', 'qty' => 1, 'unit' => 'set'],
                ],
                'bed_prices' => [
                    $bedIcu => 95000,
                ],
            ],

            // ─────────── CCU package ───────────
            [
                'code'         => 'PKG-CCU-MON',
                'name'         => 'CCU Cardiac Monitoring Package',
                'package_type' => 'CCU',
                'department_id'        => $dept('MEDICINE'),
                'admission_type'       => 'Emergency',
                'bed_type_id'          => $bedCcu,
                'duration_days'        => 2,
                'base_price'           => 80000,
                'patient_category'     => 'General',
                'requires_approval'    => true,
                'approval_role'        => 'Duty Manager',
                'included_services_text' => "CCU bed 2 days\nCardiologist round (daily)\nContinuous ECG monitoring\nBasic cardiac drugs\nNursing 1:2",
                'excluded_services_text' => "PCI / Stent\nThrombolytic agents\nPacemaker",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'CCU Bed 2 Days', 'qty' => 2, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Cardiologist Round', 'qty' => 2, 'unit' => 'visits'],
                    ['cat' => 'Nursing', 'name' => 'CCU Nursing 1:2', 'qty' => 2, 'unit' => 'days'],
                    ['cat' => 'Equipment', 'name' => 'Continuous ECG Monitor', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine', 'name' => 'Basic Cardiac Drugs', 'qty' => 1, 'unit' => 'set'],
                ],
                'bed_prices' => [
                    $bedCcu => 80000,
                ],
            ],

            // ─────────── OPD package ───────────
            [
                'code'         => 'PKG-OPD-DIAB',
                'name'         => 'Diabetes Follow-up Package',
                'package_type' => 'OPD',
                'department_id'        => $dept('MEDICINE'),
                'duration_days'        => 30,
                'base_price'           => 3500,
                'patient_category'     => 'General',
                'requires_approval'    => false,
                'included_services_text' => "3 endocrinologist visits in 30 days\nHbA1c + Fasting + PP glucose\nUrine R/E\nDiet counselling",
                'excluded_services_text' => "Insulin / oral antidiabetics\nDiabetic retinopathy screening\nFoot care procedures",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Doctor Visit', 'name' => 'Endocrinologist Visit', 'qty' => 3, 'unit' => 'visits'],
                    ['cat' => 'Investigation', 'name' => 'HbA1c', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation', 'name' => 'Fasting + PP Glucose', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Other', 'name' => 'Dietitian Counselling', 'qty' => 1, 'unit' => 'session'],
                ],
            ],

            // ─────────── Diagnostic (health checkup) ───────────
            [
                'code'         => 'PKG-DIAG-EXEC',
                'name'         => 'Executive Health Checkup',
                'package_type' => 'Diagnostic',
                'department_id'        => $dept('MEDICINE'),
                'duration_days'        => 1,
                'base_price'           => 8500,
                'patient_category'     => 'Corporate',
                'requires_approval'    => false,
                'included_services_text' => "CBC, ESR, Lipid Profile, FBS, RBS\nLFT, RFT, Urine R/E\nChest X-Ray, ECG\nGeneral physician consultation\nNutritionist briefing",
                'excluded_services_text' => "Endoscopy / Colonoscopy\nMRI / CT\nCardiac stress test",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Investigation', 'name' => 'CBC + ESR', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Investigation', 'name' => 'Lipid Profile', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Investigation', 'name' => 'LFT + RFT + Urine R/E', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Investigation', 'name' => 'Chest X-Ray + ECG', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Doctor Visit', 'name' => 'General Physician', 'qty' => 1, 'unit' => 'visit'],
                ],
            ],

            // ─────────── Procedure package ───────────
            [
                'code'         => 'PKG-PROC-ENDO',
                'name'         => 'Endoscopy Package',
                'package_type' => 'Procedure',
                'department_id'        => $dept('MEDICINE'),
                'duration_days'        => 1,
                'base_price'           => 6500,
                'patient_category'     => 'General',
                'requires_approval'    => false,
                'surgery_category_id'  => $cat('Endoscopy'),
                'surgery_type_id'      => $surgery('Colonoscopy'),
                'included_services_text' => "Procedure room\nGastroenterologist\nSedation\nBasic consumables\nBiopsy if needed (1 sample)",
                'excluded_services_text' => "Histopath beyond 1 sample\nTherapeutic interventions\nHigh-end sedation",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Procedure', 'name' => 'Endoscopy Procedure', 'qty' => 1, 'unit' => 'set', 'master' => 'surgery_type', 'master_id' => $surgery('Colonoscopy')],
                    ['cat' => 'Doctor Visit', 'name' => 'Gastroenterologist', 'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Consumable', 'name' => 'Basic Endoscopy Consumables', 'qty' => 1, 'unit' => 'set'],
                ],
            ],

            // ─────────── IPD general admission ───────────
            [
                'code'         => 'PKG-IPD-GEN',
                'name'         => 'General Admission Package',
                'package_type' => 'IPD',
                'department_id'        => $dept('MEDICINE'),
                'admission_type'       => 'Planned',
                'bed_type_id'          => $bedWard,
                'duration_days'        => 3,
                'base_price'           => 18000,
                'patient_category'     => 'General',
                'requires_approval'    => false,
                'included_services_text' => "Bed 3 days\nDuty doctor visit\nNursing care\nBasic medicines",
                'excluded_services_text' => "Specialist visits\nInvestigations\nProcedures",
                'status'  => 'Active',
                'items' => [
                    ['cat' => 'Bed', 'name' => 'Bed 3 Days', 'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Duty Doctor Visit', 'qty' => 3, 'unit' => 'visits'],
                    ['cat' => 'Nursing', 'name' => 'Standard Nursing', 'qty' => 3, 'unit' => 'days'],
                ],
            ],
        ];

        $created = $updated = 0;
        foreach ($packages as $row) {
            $items     = $row['items']      ?? [];
            $bedPrices = $row['bed_prices'] ?? [];
            unset($row['items'], $row['bed_prices']);

            // Find-or-create by code. If found, wipe child rows and replace
            // so re-seeding gives a clean, expected state.
            $existing = ServicePackage::withTrashed()->where('code', $row['code'])->first();

            if ($existing) {
                $existing->restore();
                $existing->update($row);
                $existing->items()->delete();
                $existing->bedPrices()->delete();
                $package = $existing;
                $updated++;
            } else {
                $package = ServicePackage::create($row);
                $created++;
            }

            $sort = 0;
            foreach ($items as $i) {
                ServicePackageItem::create([
                    'service_package_id' => $package->id,
                    'item_category'      => $i['cat'],
                    'master_type'        => $i['master']    ?? null,
                    'master_id'          => $i['master_id'] ?? null,
                    'item_name'          => $i['name'],
                    'included_qty'       => $i['qty']  ?? 1,
                    'unit'               => $i['unit'] ?? null,
                    'sort_order'         => $sort++,
                ]);
            }

            foreach ($bedPrices as $bedTypeId => $price) {
                if (! $bedTypeId || ! is_numeric($price)) continue;
                ServicePackageBedPrice::create([
                    'service_package_id' => $package->id,
                    'bed_type_id'        => (int) $bedTypeId,
                    'price'              => (float) $price,
                ]);
            }
        }

        $this->command->info("==========================================");
        $this->command->info("  Service Package Demo Seeder Complete");
        $this->command->info("==========================================");
        $this->command->info("  Created : {$created}");
        $this->command->info("  Updated : {$updated}");
        $this->command->info("  Total in master: " . ServicePackage::count());
    }
}
