<?php

namespace Database\Seeders;

use App\Models\Charges\Charge;
use App\Models\Charges\ChargeCategory;
use App\Models\Charges\ChargeType;
use App\Models\Charges\TaxCategory;
use App\Models\Charges\UniteType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealChargesMasterSeeder extends Seeder
{
    public function run(): void
    {
        // ───── 1. Charge Types ─────
        $types = [
            'Consultation', 'Procedure', 'Investigation', 'Pharmacy',
            'Bed Charge', 'Nursing', 'OT', 'ICU', 'Equipment Usage',
            'Ambulance', 'Administrative', 'Therapy',
        ];
        $typeIds = [];
        foreach ($types as $t) {
            $typeIds[$t] = ChargeType::firstOrCreate(['name' => $t], ['status' => 1])->id;
        }

        // ───── 2. Charge Categories ─────
        $categories = [
            ['General Consultation',     'Consultation'],
            ['Specialist Consultation',  'Consultation'],
            ['Emergency Consultation',   'Consultation'],
            ['Minor Procedure',          'Procedure'],
            ['Major Procedure',          'Procedure'],
            ['Pathology Test',           'Investigation'],
            ['Radiology / Imaging',      'Investigation'],
            ['Pharmacy / Medication',    'Pharmacy'],
            ['General Bed',              'Bed Charge'],
            ['ICU Bed',                  'Bed Charge'],
            ['Cabin / Private Room',     'Bed Charge'],
            ['Nursing Service',          'Nursing'],
            ['OT Service',               'OT'],
            ['Anesthesia',               'OT'],
            ['Equipment Charge',         'Equipment Usage'],
            ['Ventilator',               'ICU'],
            ['Monitor / Telemetry',      'ICU'],
            ['Dialysis',                 'Therapy'],
            ['Physiotherapy',            'Therapy'],
            ['Ambulance Service',        'Ambulance'],
            ['Registration / Admission', 'Administrative'],
            ['Discharge / Documentation','Administrative'],
        ];
        $catIds = [];
        foreach ($categories as [$name, $type]) {
            $catIds[$name] = ChargeCategory::firstOrCreate(
                ['name' => $name],
                ['charge_type_id' => $typeIds[$type] ?? null, 'description' => $name, 'status' => 1]
            )->id;
        }

        // ───── 3. Unit Types ─────
        $units = ['Visit', 'Day', 'Hour', 'Session', 'Test', 'Item', 'Procedure', 'Km'];
        $unitIds = [];
        foreach ($units as $u) {
            $unitIds[$u] = UniteType::firstOrCreate(['name' => $u])->id;
        }

        // ───── 4. Tax Categories ─────
        $taxes = [['No Tax', 0], ['VAT 5%', 5], ['VAT 7.5%', 7.5], ['VAT 15%', 15], ['Service Tax 10%', 10]];
        $taxIds = [];
        foreach ($taxes as [$name, $pct]) {
            $taxIds[$name] = TaxCategory::firstOrCreate(['name' => $name], ['percentage' => $pct, 'status' => 1])->id;
        }

        // ───── 5. Real Charges (60+ entries) ─────
        $rows = [
            // Consultations
            ['General Consultation',        'General Consultation',        'Visit',    'No Tax',    500,    'Standard OPD visit'],
            ['Specialist Consultation',     'Specialist Consultation',     'Visit',    'No Tax',    1200,   'Specialist OPD visit'],
            ['Cardiologist Consultation',   'Specialist Consultation',     'Visit',    'No Tax',    1500,   'Cardio specialist'],
            ['Neurologist Consultation',    'Specialist Consultation',     'Visit',    'No Tax',    1500,   'Neuro specialist'],
            ['Orthopaedic Consultation',    'Specialist Consultation',     'Visit',    'No Tax',    1200,   'Ortho specialist'],
            ['Paediatric Consultation',     'Specialist Consultation',     'Visit',    'No Tax',    1000,   'Paediatric specialist'],
            ['Gynaecology Consultation',    'Specialist Consultation',     'Visit',    'No Tax',    1200,   'Gynae specialist'],
            ['Emergency Consultation',      'Emergency Consultation',      'Visit',    'No Tax',    800,    'ER doctor'],

            // Beds
            ['General Bed (per day)',       'General Bed',                 'Day',      'No Tax',    1500,   'General ward bed'],
            ['Cabin (per day)',             'Cabin / Private Room',        'Day',      'No Tax',    4000,   'Private cabin'],
            ['Deluxe Cabin (per day)',      'Cabin / Private Room',        'Day',      'No Tax',    7000,   'Deluxe with sofa-bed'],
            ['VIP Suite (per day)',         'Cabin / Private Room',        'Day',      'No Tax',    15000,  'VVIP suite'],
            ['ICU Bed (per day)',           'ICU Bed',                     'Day',      'No Tax',    8000,   'ICU bed'],
            ['CCU Bed (per day)',           'ICU Bed',                     'Day',      'No Tax',    7500,   'CCU bed'],
            ['NICU Bed (per day)',          'ICU Bed',                     'Day',      'No Tax',    6500,   'NICU incubator'],
            ['HDU Bed (per day)',           'General Bed',                 'Day',      'No Tax',    5500,   'High dependency'],
            ['Isolation Room (per day)',    'General Bed',                 'Day',      'No Tax',    5000,   'Negative-pressure'],

            // Nursing
            ['Routine Nursing Care',        'Nursing Service',             'Day',      'No Tax',    200,    'Standard nursing'],
            ['ICU Nursing Care',            'Nursing Service',             'Day',      'No Tax',    1000,   'ICU 1:1 nursing'],
            ['Special Nursing',             'Nursing Service',             'Day',      'No Tax',    500,    'High dep nursing'],

            // Procedures (Minor)
            ['ECG',                         'Minor Procedure',             'Procedure','No Tax',    300,    '12-lead ECG'],
            ['Dressing Change',             'Minor Procedure',             'Procedure','No Tax',    150,    'Wound dressing'],
            ['IV Cannulation',              'Minor Procedure',             'Procedure','No Tax',    100,    'IV insertion'],
            ['Urinary Catheterisation',     'Minor Procedure',             'Procedure','No Tax',    500,    'Foley catheter'],
            ['NG Tube Insertion',           'Minor Procedure',             'Procedure','No Tax',    400,    'Nasogastric tube'],
            ['Wound Suturing',              'Minor Procedure',             'Procedure','No Tax',    800,    'Wound suturing'],
            ['Nebulisation',                'Minor Procedure',             'Session',  'No Tax',    150,    'Per session'],

            // Procedures (Major)
            ['Appendectomy',                'Major Procedure',             'Procedure','No Tax',    25000,  'Open appendectomy'],
            ['Cholecystectomy',             'Major Procedure',             'Procedure','No Tax',    45000,  'Lap chole'],
            ['Hernia Repair',               'Major Procedure',             'Procedure','No Tax',    30000,  'Open hernia'],
            ['Caesarean Section (C-Sec)',   'Major Procedure',             'Procedure','No Tax',    35000,  'LSCS'],
            ['Normal Vaginal Delivery',     'Major Procedure',             'Procedure','No Tax',    15000,  'NVD'],
            ['Hysterectomy',                'Major Procedure',             'Procedure','No Tax',    55000,  'Abdominal hysterectomy'],

            // OT
            ['OT Charge — Minor',           'OT Service',                  'Hour',     'No Tax',    3000,   'Minor OT room rent/hr'],
            ['OT Charge — Major',           'OT Service',                  'Hour',     'No Tax',    8000,   'Major OT room rent/hr'],
            ['Anesthesia — General',        'Anesthesia',                  'Procedure','No Tax',    8000,   'GA charge'],
            ['Anesthesia — Spinal',         'Anesthesia',                  'Procedure','No Tax',    5000,   'Spinal charge'],
            ['Anesthesia — Local',          'Anesthesia',                  'Procedure','No Tax',    1500,   'Local block'],

            // Equipment
            ['Ventilator (per day)',        'Ventilator',                  'Day',      'No Tax',    3000,   'Mechanical ventilation'],
            ['Cardiac Monitor (per day)',   'Monitor / Telemetry',         'Day',      'No Tax',    800,    '5-para monitor'],
            ['Pulse Oximeter (per day)',    'Equipment Charge',            'Day',      'No Tax',    200,    'SpO2 monitoring'],
            ['Defibrillator Use',           'Equipment Charge',            'Procedure','No Tax',    1500,   'Per shock'],
            ['Syringe Pump (per day)',      'Equipment Charge',            'Day',      'No Tax',    500,    'IV infusion'],

            // Pathology (master entries)
            ['CBC',                         'Pathology Test',              'Test',     'No Tax',    400,    'Complete Blood Count'],
            ['Blood Glucose (Fasting)',     'Pathology Test',              'Test',     'No Tax',    150,    'FBS'],
            ['Lipid Profile',               'Pathology Test',              'Test',     'No Tax',    1200,   'Cholesterol panel'],
            ['LFT — Liver Function Test',   'Pathology Test',              'Test',     'No Tax',    900,    'LFT'],
            ['KFT — Renal Function Test',   'Pathology Test',              'Test',     'No Tax',    800,    'KFT'],
            ['HbA1c',                       'Pathology Test',              'Test',     'No Tax',    900,    'Glycated Hb'],
            ['Urine R/E',                   'Pathology Test',              'Test',     'No Tax',    250,    'Urinalysis'],
            ['Blood Culture',               'Pathology Test',              'Test',     'No Tax',    1500,   'Aerobic + anaerobic'],

            // Radiology
            ['Chest X-Ray PA',              'Radiology / Imaging',         'Test',     'No Tax',    500,    'CXR PA view'],
            ['Abdominal USG',               'Radiology / Imaging',         'Test',     'No Tax',    1200,   'Abdomen ultrasound'],
            ['CT Brain (Plain)',            'Radiology / Imaging',         'Test',     'No Tax',    4500,   'NCCT brain'],
            ['MRI Brain',                   'Radiology / Imaging',         'Test',     'No Tax',    8500,   'MRI brain plain'],
            ['ECG (Diagnostic)',            'Radiology / Imaging',         'Test',     'No Tax',    300,    'Diagnostic ECG'],

            // Therapy
            ['Hemodialysis',                'Dialysis',                    'Session',  'No Tax',    4000,   'Per HD session'],
            ['Physiotherapy Session',       'Physiotherapy',               'Session',  'No Tax',    600,    'Per session'],

            // Ambulance
            ['Ambulance — Basic',           'Ambulance Service',           'Km',       'No Tax',    50,     'Per km'],
            ['Ambulance — ICU Equipped',    'Ambulance Service',           'Km',       'No Tax',    100,    'Per km ICU amb'],

            // Administrative
            ['IPD Admission Charge',        'Registration / Admission',    'Visit',    'No Tax',    500,    'One-time admission'],
            ['Discharge Documentation',     'Discharge / Documentation',   'Visit',    'No Tax',    300,    'Discharge summary'],
            ['Medical Record Copy',         'Discharge / Documentation',   'Item',     'No Tax',    200,    'Per copy'],
        ];

        $now = now();
        foreach ($rows as [$name, $cat, $unit, $tax, $price, $desc]) {
            Charge::firstOrCreate(
                ['charge_name' => $name],
                [
                    'charge_type_id'     => ChargeCategory::find($catIds[$cat])->charge_type_id ?? $typeIds['Consultation'],
                    'charge_category_id' => $catIds[$cat]    ?? null,
                    'unite_type_id'      => $unitIds[$unit]  ?? null,
                    'tax_category_id'    => $taxIds[$tax]    ?? null,
                    'tax'                => collect($taxes)->firstWhere(0, $tax)[1] ?? 0,
                    'standard_charge'    => $price,
                    'description'        => $desc,
                ]
            );
        }

        // ───── 6. Sync charges → service_catalogs so packages can use them ─────
        $typeMap = [
            'Consultation'      => 'consultation',
            'Procedure'         => 'procedure',
            'Investigation'     => 'lab_test',
            'Pharmacy'          => 'pharmacy',
            'Bed Charge'        => 'bed',
            'Nursing'           => 'nursing',
            'OT'                => 'ot_room',
            'ICU'               => 'icu_bed',
            'Equipment Usage'   => 'equipment',
            'Ambulance'         => 'ambulance',
            'Administrative'    => 'administrative',
            'Therapy'           => 'procedure',
        ];

        $unitMap = [
            'visit'     => 'per_use',
            'day'       => 'per_day',
            'hour'      => 'per_hour',
            'session'   => 'per_session',
            'test'      => 'per_test',
            'item'      => 'per_unit',
            'procedure' => 'per_use',
            'km'        => 'per_km',
        ];

        foreach (Charge::with('chargeType', 'uniteType')->get() as $c) {
            $svcType = $typeMap[optional($c->chargeType)->name ?? ''] ?? 'other';
            $code = 'CHG-' . str_pad($c->id, 4, '0', STR_PAD_LEFT);
            $rawUnit = strtolower(optional($c->uniteType)->name ?? 'unit');
            $unitLabel = $unitMap[$rawUnit] ?? 'per_unit';

            DB::table('service_catalogs')->updateOrInsert(
                ['code' => $code],
                [
                    'name'             => $c->charge_name,
                    'service_type'     => $svcType,
                    'charge_unit'      => $unitLabel,
                    'base_price'       => $c->standard_charge,
                    'tax_percent'      => $c->tax,
                    'patient_type'     => 'ALL',
                    'description'      => $c->description,
                    'is_active'        => 1,
                    'package_eligible' => 1,
                    'updated_at'       => $now,
                    'created_at'       => $now,
                ]
            );
        }
    }
}
