<?php

namespace Database\Seeders;

use App\Models\BedType;
use App\Models\Department;
use App\Models\ServicePackage;
use App\Models\ServicePackageBedPrice;
use App\Models\ServicePackageItem;
use Illuminate\Database\Seeder;

/**
 * Realistic hospital service package master.
 *
 * Each package targets a primary bed type and lists an item-level
 * breakdown (doctor visits, nursing days, OT, investigations, medicines,
 * etc.). Bed-type variants drive automatic upgraded pricing when the
 * patient is admitted to a Cabin / Deluxe / ICU instead of the default.
 *
 * Idempotent — re-running won't duplicate (matches on code).
 *
 * Run:  php artisan db:seed --class=PackageMasterSeeder
 */
class PackageMasterSeeder extends Seeder
{
    public function run(): void
    {
        $bedTypes = BedType::pluck('id', 'name');
        $deptIds  = Department::pluck('id', 'name');

        $packages = $this->packageBlueprints($bedTypes, $deptIds);

        $created = 0;
        foreach ($packages as $blueprint) {
            $pkg = ServicePackage::firstOrCreate(
                ['code' => $blueprint['code']],
                array_merge([
                    'status'             => ServicePackage::STATUS_ACTIVE,
                    'patient_category'   => 'General',
                    'requires_approval'  => false,
                    'remarks'            => $blueprint['remarks'] ?? null,
                ], $blueprint['attrs'])
            );

            // Seed items (idempotent via deleteThenInsert)
            if (! empty($blueprint['items'])) {
                ServicePackageItem::where('service_package_id', $pkg->id)->delete();
                $order = 0;
                foreach ($blueprint['items'] as $it) {
                    ServicePackageItem::create([
                        'service_package_id' => $pkg->id,
                        'item_category'      => $it['cat'],
                        'master_type'        => $it['master_type'] ?? null,
                        'master_id'          => $it['master_id']   ?? null,
                        'item_name'          => $it['name'],
                        'included_qty'       => $it['qty']  ?? 1,
                        'unit'               => $it['unit'] ?? null,
                        'sort_order'         => $order++,
                        'notes'              => $it['notes'] ?? null,
                    ]);
                }
            }

            // Seed bed-type variants
            if (! empty($blueprint['bed_variants'])) {
                ServicePackageBedPrice::where('service_package_id', $pkg->id)->delete();
                foreach ($blueprint['bed_variants'] as $bedTypeName => $price) {
                    $btId = $bedTypes[$bedTypeName] ?? null;
                    if (! $btId) continue;
                    ServicePackageBedPrice::create([
                        'service_package_id' => $pkg->id,
                        'bed_type_id'        => $btId,
                        'price'              => $price,
                    ]);
                }
            }

            $created++;
        }

        $this->command->info("✓ Package master seeded: {$created} packages with items + bed-type variants.");
    }

    /**
     * Each blueprint defines a hospital package with realistic items and
     * bed-type pricing variants. Add or tweak here — re-running re-syncs.
     */
    protected function packageBlueprints($bedTypes, $deptIds): array
    {
        $bedTypeId = fn (string $n) => $bedTypes[$n] ?? null;
        $deptId    = fn (string $n) => $deptIds[$n] ?? null;

        return [
            /* ────────── Maternity ────────── */
            [
                'code'  => 'PKG-IPD-001',
                'attrs' => [
                    'name'                  => 'Normal Delivery — Standard',
                    'package_type'          => 'IPD',
                    'department_id'         => $deptId('Gynecology') ?? $deptId('Obstetrics & Gynecology'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('Postnatal Bed'),
                    'duration_days'         => 3,
                    'base_price'            => 25000,
                    'included_services_text'=> "OBGYN consultation, labor monitoring, vaginal delivery, 3-day postnatal stay, newborn check, lactation counselling.",
                    'excluded_services_text'=> "Epidural anesthesia, NICU charges, extended stay beyond 3 days.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'Postnatal bed',           'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'OB-GYN consultant visit', 'qty' => 4, 'unit' => 'visits'],
                    ['cat' => 'Doctor Visit', 'name' => 'Pediatrician (newborn)',  'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Nursing',      'name' => 'Nursing care',            'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Procedure',    'name' => 'Vaginal delivery',        'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Investigation','name' => 'CBC + Urine RE',          'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'Postnatal medications',   'qty' => 3, 'unit' => 'days'],
                ],
                'bed_variants' => [
                    'Postnatal Bed' => 25000,
                    'Cabin'         => 35000,
                    'Deluxe Cabin'  => 45000,
                ],
            ],

            [
                'code'  => 'PKG-OT-001',
                'attrs' => [
                    'name'                  => 'C-Section Delivery — Comprehensive',
                    'package_type'          => 'OT',
                    'department_id'         => $deptId('Gynecology') ?? $deptId('Obstetrics & Gynecology'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('Cabin'),
                    'duration_days'         => 5,
                    'base_price'            => 55000,
                    'included_services_text'=> "Pre-op workup, spinal anesthesia, LSCS in OT, 5-day stay, newborn check, postnatal care, suture removal.",
                    'excluded_services_text'=> "NICU admission charges (covered by NICU package), blood transfusion, extended stay.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'Cabin (post-op recovery)', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'OT',           'name' => 'Operation theatre charge', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Procedure',    'name' => 'LSCS (lower segment C-section)', 'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Other',        'name' => 'Spinal anesthesia',        'qty' => 1, 'unit' => 'dose'],
                    ['cat' => 'Doctor Visit', 'name' => 'OB-GYN surgeon',           'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Doctor Visit', 'name' => 'Anesthesiologist',         'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Doctor Visit', 'name' => 'Pediatrician (newborn)',   'qty' => 2, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'Post-op nursing',          'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'CBC, Urine RE, Blood group, HIV, HBsAg', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'Antibiotics + analgesics', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Consumable',   'name' => 'Suture kit + dressing',    'qty' => 1, 'unit' => 'kit'],
                ],
                'bed_variants' => [
                    'Postnatal Bed' => 55000,
                    'Cabin'         => 70000,
                    'Deluxe Cabin'  => 90000,
                    'VIP Cabin'     => 125000,
                ],
            ],

            /* ────────── General Surgery ────────── */
            [
                'code'  => 'PKG-OT-002',
                'attrs' => [
                    'name'                  => 'Appendectomy (Open)',
                    'package_type'          => 'OT',
                    'department_id'         => $deptId('Surgery') ?? $deptId('General Surgery'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('General Bed'),
                    'duration_days'         => 3,
                    'base_price'            => 35000,
                    'included_services_text'=> "Pre-op workup, GA, open appendectomy, 3-day stay, suture removal.",
                    'excluded_services_text'=> "Lap appendectomy (separate package), complications, blood transfusion.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'General bed', 'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'OT',           'name' => 'OT charge',   'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Procedure',    'name' => 'Open appendectomy', 'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Other',        'name' => 'General anesthesia', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Doctor Visit', 'name' => 'Surgeon',  'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Nursing',      'name' => 'Nursing',  'qty' => 3, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'CBC + Urine RE + ECG', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'Antibiotics + analgesics', 'qty' => 3, 'unit' => 'days'],
                ],
                'bed_variants' => [
                    'General Bed'  => 35000,
                    'Cabin'        => 50000,
                    'Deluxe Cabin' => 65000,
                ],
            ],

            [
                'code'  => 'PKG-OT-003',
                'attrs' => [
                    'name'                  => 'Laparoscopic Cholecystectomy',
                    'package_type'          => 'OT',
                    'department_id'         => $deptId('Surgery') ?? $deptId('General Surgery'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('Cabin'),
                    'duration_days'         => 2,
                    'base_price'            => 65000,
                    'included_services_text'=> "Pre-op workup, GA, lap chole, 2-day stay, port-site dressing.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'Cabin', 'qty' => 2, 'unit' => 'days'],
                    ['cat' => 'OT',           'name' => 'Laparoscopy theatre charge', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Procedure',    'name' => 'Laparoscopic cholecystectomy', 'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Equipment',    'name' => 'Laparoscope use', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Other',        'name' => 'General anesthesia', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Doctor Visit', 'name' => 'Laparoscopic surgeon', 'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Nursing',      'name' => 'Nursing', 'qty' => 2, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'USG abdomen + LFT', 'qty' => 1, 'unit' => 'set'],
                ],
                'bed_variants' => [
                    'General Bed'  => 65000,
                    'Cabin'        => 75000,
                    'Deluxe Cabin' => 85000,
                ],
            ],

            [
                'code'  => 'PKG-OT-004',
                'attrs' => [
                    'name'                  => 'Hernia Repair (Open / Mesh)',
                    'package_type'          => 'OT',
                    'department_id'         => $deptId('Surgery') ?? $deptId('General Surgery'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('General Bed'),
                    'duration_days'         => 2,
                    'base_price'            => 28000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'General bed', 'qty' => 2, 'unit' => 'days'],
                    ['cat' => 'OT',           'name' => 'OT charge', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Procedure',    'name' => 'Mesh hernioplasty', 'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Consumable',   'name' => 'Hernia mesh', 'qty' => 1, 'unit' => 'pcs'],
                    ['cat' => 'Doctor Visit', 'name' => 'Surgeon', 'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Nursing',      'name' => 'Nursing', 'qty' => 2, 'unit' => 'days'],
                ],
                'bed_variants' => [
                    'General Bed'  => 28000,
                    'Cabin'        => 40000,
                    'Deluxe Cabin' => 55000,
                ],
            ],

            /* ────────── Critical Care ────────── */
            [
                'code'  => 'PKG-ICU-001',
                'attrs' => [
                    'name'                  => 'ICU Daily Care',
                    'package_type'          => 'ICU',
                    'department_id'         => $deptId('Critical Care') ?? $deptId('Medicine'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('ICU Bed'),
                    'duration_days'         => 1,
                    'base_price'            => 9000,
                    'included_services_text'=> "ICU bed/day, monitor, basic nursing, intensivist round.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'ICU bed', 'qty' => 1, 'unit' => 'day'],
                    ['cat' => 'Doctor Visit', 'name' => 'Intensivist round', 'qty' => 2, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => '1:1 ICU nursing', 'qty' => 1, 'unit' => 'day'],
                    ['cat' => 'Equipment',    'name' => 'Multi-parameter monitor', 'qty' => 1, 'unit' => 'day'],
                ],
            ],

            [
                'code'  => 'PKG-ICU-002',
                'attrs' => [
                    'name'                  => 'Sepsis Treatment Package',
                    'package_type'          => 'ICU',
                    'department_id'         => $deptId('Critical Care') ?? $deptId('Medicine'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('ICU Bed'),
                    'duration_days'         => 5,
                    'base_price'            => 45000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'ICU bed', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Intensivist round', 'qty' => 10, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'ICU nursing', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Medicine',     'name' => 'IV antibiotics + supportive meds', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'Blood culture + CBC daily + CRP', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Equipment',    'name' => 'Monitor + IV pump', 'qty' => 5, 'unit' => 'days'],
                ],
            ],

            [
                'code'  => 'PKG-CCU-001',
                'attrs' => [
                    'name'                  => 'Acute MI Management',
                    'package_type'          => 'CCU',
                    'department_id'         => $deptId('Cardiology') ?? $deptId('Medicine'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('CCU Bed'),
                    'duration_days'         => 5,
                    'base_price'            => 55000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'CCU bed', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Cardiologist round', 'qty' => 10, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'CCU nursing', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'ECG + Troponin + Echo + LFT + RFT', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'Anticoagulants + statin + antiplatelets', 'qty' => 5, 'unit' => 'days'],
                ],
            ],

            /* ────────── NICU ────────── */
            [
                'code'  => 'PKG-NICU-001',
                'attrs' => [
                    'name'                  => 'NICU Daily Care',
                    'package_type'          => 'ICU',
                    'department_id'         => $deptId('Pediatrics'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('NICU Bed'),
                    'duration_days'         => 1,
                    'base_price'            => 7500,
                    'included_services_text'=> "NICU bed/day, neonatologist round, monitoring, basic phototherapy.",
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'NICU bed', 'qty' => 1, 'unit' => 'day'],
                    ['cat' => 'Doctor Visit', 'name' => 'Neonatologist round', 'qty' => 2, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'NICU nursing', 'qty' => 1, 'unit' => 'day'],
                    ['cat' => 'Equipment',    'name' => 'Monitor + phototherapy unit', 'qty' => 1, 'unit' => 'day'],
                ],
                'bed_variants' => [
                    'NICU Bed'  => 7500,
                    'Incubator' => 9500,
                    'Warmer'    => 8000,
                ],
            ],

            [
                'code'  => 'PKG-NICU-002',
                'attrs' => [
                    'name'                  => 'Preterm / LBW Care — 7-Day',
                    'package_type'          => 'ICU',
                    'department_id'         => $deptId('Pediatrics'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('Incubator'),
                    'duration_days'         => 7,
                    'base_price'            => 65000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'Incubator', 'qty' => 7, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Neonatologist round', 'qty' => 14, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'NICU nursing', 'qty' => 7, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'CBC daily + Bilirubin + CRP + ABG', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'IV antibiotics + TPN', 'qty' => 7, 'unit' => 'days'],
                ],
                'bed_variants' => [
                    'NICU Bed'  => 55000,
                    'Incubator' => 65000,
                    'Warmer'    => 60000,
                ],
            ],

            /* ────────── Day Care / Diagnostic / Procedure ────────── */
            [
                'code'  => 'PKG-PROC-001',
                'attrs' => [
                    'name'                  => 'Cataract Surgery (Phaco + IOL)',
                    'package_type'          => 'Procedure',
                    'department_id'         => $deptId('Ophthalmology'),
                    'admission_type'        => 'Day Care',
                    'bed_type_id'           => $bedTypeId('Day Care Bed'),
                    'duration_days'         => 1,
                    'base_price'            => 18000,
                ],
                'items' => [
                    ['cat' => 'OT',           'name' => 'Eye OT charge', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Procedure',    'name' => 'Phacoemulsification + IOL', 'qty' => 1, 'unit' => 'procedure'],
                    ['cat' => 'Consumable',   'name' => 'Foldable IOL', 'qty' => 1, 'unit' => 'pcs'],
                    ['cat' => 'Doctor Visit', 'name' => 'Ophthalmologist', 'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Medicine',     'name' => 'Eye drops kit', 'qty' => 1, 'unit' => 'kit'],
                ],
            ],

            [
                'code'  => 'PKG-PROC-002',
                'attrs' => [
                    'name'                  => 'Dialysis — Single Session',
                    'package_type'          => 'Procedure',
                    'department_id'         => $deptId('Nephrology'),
                    'admission_type'        => 'Day Care',
                    'bed_type_id'           => $bedTypeId('Dialysis Bed'),
                    'duration_days'         => 1,
                    'base_price'            => 3500,
                ],
                'items' => [
                    ['cat' => 'Procedure',    'name' => 'Hemodialysis', 'qty' => 1, 'unit' => 'session'],
                    ['cat' => 'Consumable',   'name' => 'Dialyzer + tubing', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Doctor Visit', 'name' => 'Nephrologist', 'qty' => 1, 'unit' => 'visit'],
                    ['cat' => 'Nursing',      'name' => 'Dialysis nursing', 'qty' => 1, 'unit' => 'session'],
                ],
            ],

            [
                'code'  => 'PKG-HC-001',
                'attrs' => [
                    'name'                  => 'Full Body Health Checkup — Basic',
                    'package_type'          => 'Health Checkup',
                    'admission_type'        => 'Day Care',
                    'duration_days'         => 1,
                    'base_price'            => 5000,
                    'included_services_text'=> "CBC, Urine RE, Blood Sugar, Lipid Profile, Liver Function, Kidney Function, Chest X-ray.",
                ],
                'items' => [
                    ['cat' => 'Investigation','name' => 'CBC', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'Urine RE', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'Fasting Blood Sugar', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'Lipid Profile', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'LFT', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'RFT', 'qty' => 1, 'unit' => 'test'],
                    ['cat' => 'Investigation','name' => 'Chest X-Ray', 'qty' => 1, 'unit' => 'film'],
                    ['cat' => 'Doctor Visit', 'name' => 'GP consultation', 'qty' => 1, 'unit' => 'visit'],
                ],
            ],

            [
                'code'  => 'PKG-HC-002',
                'attrs' => [
                    'name'                  => 'Executive Health Checkup',
                    'package_type'          => 'Health Checkup',
                    'admission_type'        => 'Day Care',
                    'duration_days'         => 1,
                    'base_price'            => 15000,
                    'included_services_text'=> "Full Basic panel + USG, ECG, Echo, Stress Test, TSH, HbA1c, PSA (M) / Pap smear (F).",
                ],
                'items' => [
                    ['cat' => 'Investigation','name' => 'CBC + Urine RE + Sugar + Lipid + LFT + RFT', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Investigation','name' => 'USG whole abdomen', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'ECG', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'Echocardiogram', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'Treadmill Stress Test', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'TSH + HbA1c', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Doctor Visit', 'name' => 'GP + Cardiologist + Endocrinologist', 'qty' => 1, 'unit' => 'set'],
                ],
            ],

            [
                'code'  => 'PKG-DX-001',
                'attrs' => [
                    'name'                  => 'Cardiac Workup',
                    'package_type'          => 'Diagnostic',
                    'department_id'         => $deptId('Cardiology'),
                    'admission_type'        => 'Day Care',
                    'duration_days'         => 1,
                    'base_price'            => 12000,
                ],
                'items' => [
                    ['cat' => 'Investigation','name' => 'ECG', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'Echocardiogram', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'Treadmill / Stress Test', 'qty' => 1, 'unit' => 'study'],
                    ['cat' => 'Investigation','name' => 'Lipid Profile + Troponin', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Doctor Visit', 'name' => 'Cardiologist', 'qty' => 1, 'unit' => 'visit'],
                ],
            ],

            /* ────────── IPD Medicine ────────── */
            [
                'code'  => 'PKG-IPD-002',
                'attrs' => [
                    'name'                  => 'Pneumonia — Adult Treatment (5-Day)',
                    'package_type'          => 'IPD',
                    'department_id'         => $deptId('Medicine'),
                    'admission_type'        => 'Planned',
                    'bed_type_id'           => $bedTypeId('General Bed'),
                    'duration_days'         => 5,
                    'base_price'            => 18000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'General bed', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Physician round', 'qty' => 10, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'Nursing', 'qty' => 5, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'Chest X-Ray + CBC + Sputum + CRP', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'IV antibiotics + nebulization', 'qty' => 5, 'unit' => 'days'],
                ],
                'bed_variants' => [
                    'General Bed'  => 18000,
                    'Cabin'        => 28000,
                    'Deluxe Cabin' => 38000,
                ],
            ],

            [
                'code'  => 'PKG-IPD-003',
                'attrs' => [
                    'name'                  => 'Stroke — Acute Management (7-Day)',
                    'package_type'          => 'ICU',
                    'department_id'         => $deptId('Neurology') ?? $deptId('Medicine'),
                    'admission_type'        => 'Emergency',
                    'bed_type_id'           => $bedTypeId('ICU Bed'),
                    'duration_days'         => 7,
                    'base_price'            => 85000,
                ],
                'items' => [
                    ['cat' => 'Bed',          'name' => 'ICU bed', 'qty' => 7, 'unit' => 'days'],
                    ['cat' => 'Doctor Visit', 'name' => 'Neurologist round', 'qty' => 14, 'unit' => 'visits'],
                    ['cat' => 'Nursing',      'name' => 'ICU nursing', 'qty' => 7, 'unit' => 'days'],
                    ['cat' => 'Investigation','name' => 'CT brain + MRI + lipid + ECG + carotid Doppler', 'qty' => 1, 'unit' => 'set'],
                    ['cat' => 'Medicine',     'name' => 'Antiplatelets / anticoagulants + statin', 'qty' => 7, 'unit' => 'days'],
                ],
            ],
        ];
    }
}
