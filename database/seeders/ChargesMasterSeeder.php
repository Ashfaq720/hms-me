<?php

namespace Database\Seeders;

use App\Models\Charges\Charge;
use App\Models\Charges\ChargeCategory;
use App\Models\Charges\ChargeType;
use App\Models\Charges\TaxCategory;
use App\Models\Charges\UniteType;
use Illuminate\Database\Seeder;

/**
 * Charges Setup master data — the foundation of billable codes that
 * Service Packages and Patient Charges link to. Idempotent.
 *
 * Run: php artisan db:seed --class=ChargesMasterSeeder
 */
class ChargesMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding Tax Categories…');
        $tax = $this->seedTaxCategories();

        $this->command->info('▶ Seeding Unit Types…');
        $unit = $this->seedUnitTypes();

        $this->command->info('▶ Seeding Charge Types…');
        $type = $this->seedChargeTypes();

        $this->command->info('▶ Seeding Charge Categories…');
        $cat = $this->seedChargeCategories($type);

        $this->command->info('▶ Seeding Charges…');
        $count = $this->seedCharges($cat, $unit, $tax);

        $this->command->info("✓ Charges master seeded: {$count} charges across "
            . $cat->count() . ' categories, '
            . $type->count() . ' types, '
            . $unit->count() . ' units, '
            . $tax->count() . ' tax categories.');
    }

    protected function seedTaxCategories()
    {
        $defs = [
            ['Standard VAT', '15'],
            ['Service VAT',  '10'],
            ['Zero-Rated',   '0'],
            ['Exempt',       '0'],
        ];
        $out = collect();
        foreach ($defs as [$name, $pct]) {
            $out->put($name, TaxCategory::firstOrCreate(
                ['name' => $name],
                ['percentage' => $pct, 'status' => 1]
            ));
        }
        return $out;
    }

    protected function seedUnitTypes()
    {
        $names = ['Per Visit', 'Per Day', 'Per Hour', 'Per Session', 'Per Procedure',
                  'Per Test', 'Per Item', 'Per Unit', 'Per Bag', 'Per Bottle'];
        $out = collect();
        foreach ($names as $n) {
            $out->put($n, UniteType::firstOrCreate(['name' => $n]));
        }
        return $out;
    }

    protected function seedChargeTypes()
    {
        $names = [
            'Doctor Charges',
            'Nursing Charges',
            'Bed Charges',
            'Operation Theatre',
            'Investigation',
            'Procedure',
            'Medicine',
            'Consumable',
            'Equipment',
            'Other Services',
        ];
        $out = collect();
        foreach ($names as $n) {
            $out->put($n, ChargeType::firstOrCreate(['name' => $n], ['status' => 1]));
        }
        return $out;
    }

    protected function seedChargeCategories($types)
    {
        $defs = [
            // category name => parent charge type name
            'Consultant Visit'         => 'Doctor Charges',
            'Specialist Consultation'  => 'Doctor Charges',
            'Surgeon Fee'              => 'Doctor Charges',
            'Anesthesiologist Fee'     => 'Doctor Charges',
            'Round Visit'              => 'Doctor Charges',

            'Standard Nursing'         => 'Nursing Charges',
            'ICU Nursing 1:1'          => 'Nursing Charges',
            'Special Care Nursing'     => 'Nursing Charges',

            'General Bed Day'          => 'Bed Charges',
            'Cabin Day'                => 'Bed Charges',
            'ICU / CCU Day'            => 'Bed Charges',
            'NICU Day'                 => 'Bed Charges',
            'OT Recovery Hour'         => 'Bed Charges',

            'Major OT'                 => 'Operation Theatre',
            'Minor OT'                 => 'Operation Theatre',
            'Emergency OT'             => 'Operation Theatre',

            'Pathology'                => 'Investigation',
            'Radiology'                => 'Investigation',
            'Cardiology Test'          => 'Investigation',
            'Endoscopy'                => 'Investigation',

            'Dialysis'                 => 'Procedure',
            'Phototherapy'             => 'Procedure',
            'Surfactant Administration'=> 'Procedure',
            'Intubation'               => 'Procedure',

            'IV Antibiotic'            => 'Medicine',
            'Oral Medication'          => 'Medicine',

            'Surgical Consumable'      => 'Consumable',
            'Suture Kit'               => 'Consumable',
            'Catheter / Tube'          => 'Consumable',

            'Monitor Use'              => 'Equipment',
            'Ventilator Use'           => 'Equipment',
            'Phototherapy Unit'        => 'Equipment',

            'Ambulance'                => 'Other Services',
            'Documentation Fee'        => 'Other Services',
            'Discharge Summary'        => 'Other Services',
        ];

        $out = collect();
        foreach ($defs as $cat => $typeName) {
            $typeId = $types[$typeName]?->id ?? $types->first()->id;
            $out->put($cat, ChargeCategory::firstOrCreate(
                ['name' => $cat],
                ['charge_type_id' => $typeId, 'status' => 1]
            ));
        }
        return $out;
    }

    protected function seedCharges($categories, $units, $tax): int
    {
        // [name, category, unit_type, std_charge, tax_category, tax_amount]
        $defs = [
            ['General Consultation',           'Consultant Visit',         'Per Visit',     500,  'Standard VAT', 75],
            ['Specialist Consultation',        'Specialist Consultation',  'Per Visit',     800,  'Standard VAT', 120],
            ['Cardiologist Consultation',      'Specialist Consultation',  'Per Visit',     1200, 'Standard VAT', 180],
            ['Neonatologist Consultation',     'Specialist Consultation',  'Per Visit',     1200, 'Standard VAT', 180],
            ['Surgeon Fee — Major',            'Surgeon Fee',              'Per Procedure', 25000,'Service VAT',  2500],
            ['Surgeon Fee — Minor',            'Surgeon Fee',              'Per Procedure', 10000,'Service VAT',  1000],
            ['Anesthesia (GA)',                'Anesthesiologist Fee',     'Per Procedure', 8000, 'Service VAT',  800],
            ['Anesthesia (Spinal)',            'Anesthesiologist Fee',     'Per Procedure', 5000, 'Service VAT',  500],
            ['Round Visit',                    'Round Visit',              'Per Visit',     300,  'Standard VAT', 45],

            ['Nursing — General Ward',         'Standard Nursing',         'Per Day',       400,  'Exempt',       0],
            ['Nursing — Cabin',                'Standard Nursing',         'Per Day',       600,  'Exempt',       0],
            ['Nursing — ICU 1:1',              'ICU Nursing 1:1',          'Per Day',       2000, 'Exempt',       0],

            ['General Bed Day',                'General Bed Day',          'Per Day',       1000, 'Exempt',       0],
            ['Pediatric Bed Day',              'General Bed Day',          'Per Day',       1200, 'Exempt',       0],
            ['Postnatal Bed Day',              'General Bed Day',          'Per Day',       1500, 'Exempt',       0],
            ['Cabin Day',                      'Cabin Day',                'Per Day',       3500, 'Exempt',       0],
            ['Deluxe Cabin Day',               'Cabin Day',                'Per Day',       5500, 'Exempt',       0],
            ['VIP Cabin Day',                  'Cabin Day',                'Per Day',       12000,'Exempt',       0],
            ['ICU Bed Day',                    'ICU / CCU Day',            'Per Day',       8000, 'Exempt',       0],
            ['CCU Bed Day',                    'ICU / CCU Day',            'Per Day',       7500, 'Exempt',       0],
            ['HDU Bed Day',                    'ICU / CCU Day',            'Per Day',       5000, 'Exempt',       0],
            ['NICU Bed Day',                   'NICU Day',                 'Per Day',       7000, 'Exempt',       0],
            ['Incubator Day',                  'NICU Day',                 'Per Day',       9500, 'Exempt',       0],
            ['Warmer Day',                     'NICU Day',                 'Per Day',       6500, 'Exempt',       0],
            ['PICU Bed Day',                   'NICU Day',                 'Per Day',       7500, 'Exempt',       0],

            ['Major OT Theatre Charge',        'Major OT',                 'Per Session',   15000,'Service VAT',  1500],
            ['Minor OT Theatre Charge',        'Minor OT',                 'Per Session',   6000, 'Service VAT',  600],
            ['Emergency OT Theatre',           'Emergency OT',             'Per Session',   18000,'Service VAT',  1800],

            ['CBC (Complete Blood Count)',     'Pathology',                'Per Test',      350,  'Standard VAT', 52],
            ['Urine RE',                       'Pathology',                'Per Test',      250,  'Standard VAT', 38],
            ['Lipid Profile',                  'Pathology',                'Per Test',      1200, 'Standard VAT', 180],
            ['Blood Culture',                  'Pathology',                'Per Test',      1800, 'Standard VAT', 270],
            ['LFT',                            'Pathology',                'Per Test',      1500, 'Standard VAT', 225],
            ['RFT',                            'Pathology',                'Per Test',      1500, 'Standard VAT', 225],
            ['HbA1c',                          'Pathology',                'Per Test',      900,  'Standard VAT', 135],
            ['CRP',                            'Pathology',                'Per Test',      600,  'Standard VAT', 90],
            ['Bilirubin (Neonatal)',           'Pathology',                'Per Test',      450,  'Standard VAT', 68],

            ['Chest X-Ray',                    'Radiology',                'Per Test',      800,  'Standard VAT', 120],
            ['USG Abdomen',                    'Radiology',                'Per Test',      2000, 'Standard VAT', 300],
            ['CT Brain',                       'Radiology',                'Per Test',      5500, 'Standard VAT', 825],
            ['MRI Spine',                      'Radiology',                'Per Test',      9500, 'Standard VAT', 1425],

            ['ECG',                            'Cardiology Test',          'Per Test',      500,  'Standard VAT', 75],
            ['Echocardiogram',                 'Cardiology Test',          'Per Test',      2500, 'Standard VAT', 375],
            ['Treadmill Test (TMT)',           'Cardiology Test',          'Per Test',      3000, 'Standard VAT', 450],

            ['Hemodialysis Session',           'Dialysis',                 'Per Session',   3500, 'Service VAT',  350],
            ['Phototherapy (NICU)',            'Phototherapy',             'Per Day',       1200, 'Exempt',       0],
            ['Surfactant',                     'Surfactant Administration','Per Procedure', 18000,'Service VAT',  1800],
            ['Endotracheal Intubation',        'Intubation',               'Per Procedure', 3500, 'Service VAT',  350],

            ['IV Antibiotic — Generic',        'IV Antibiotic',            'Per Day',       400,  'Standard VAT', 60],
            ['IV Antibiotic — Premium',        'IV Antibiotic',            'Per Day',       1200, 'Standard VAT', 180],
            ['Oral Antibiotic Course',         'Oral Medication',          'Per Day',       150,  'Standard VAT', 23],

            ['Hernia Mesh',                    'Surgical Consumable',      'Per Item',      2500, 'Standard VAT', 375],
            ['Suture Kit (Major)',             'Suture Kit',               'Per Item',      800,  'Standard VAT', 120],
            ['Foley Catheter',                 'Catheter / Tube',          'Per Item',      500,  'Standard VAT', 75],
            ['NG Tube',                        'Catheter / Tube',          'Per Item',      350,  'Standard VAT', 53],

            ['Cardiac Monitor Day',            'Monitor Use',              'Per Day',       1200, 'Exempt',       0],
            ['Ventilator Day',                 'Ventilator Use',           'Per Day',       4500, 'Exempt',       0],
            ['Phototherapy Unit Day',          'Phototherapy Unit',        'Per Day',       800,  'Exempt',       0],

            ['Ambulance — Local',              'Ambulance',                'Per Procedure', 1500, 'Service VAT',  150],
            ['Ambulance — Outstation',         'Ambulance',                'Per Procedure', 5000, 'Service VAT',  500],
            ['Discharge Summary Fee',          'Discharge Summary',        'Per Item',      500,  'Service VAT',  50],
        ];

        $total = 0;
        foreach ($defs as [$name, $catName, $unitName, $std, $taxName, $taxAmt]) {
            $cat  = $categories[$catName] ?? null;
            $unit = $units[$unitName]     ?? null;
            $t    = $tax[$taxName]        ?? $tax->first();
            if (! $cat || ! $unit || ! $t) continue;

            Charge::firstOrCreate(
                ['charge_name' => $name],
                [
                    'charge_type_id'     => $cat->charge_type_id,
                    'charge_category_id' => $cat->id,
                    'unite_type_id'      => $unit->id,
                    'tax_category_id'    => $t->id,
                    'standard_charge'    => $std,
                    'tax'                => $taxAmt,
                    'description'        => null,
                ]
            );
            $total++;
        }

        return $total;
    }
}
