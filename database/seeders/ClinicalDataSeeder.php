<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds the second wave of clinical/operational tables so all major workflows
 * have data to display:
 *   • Medicines master list (~25 rows)
 *   • Blood donors + blood bags
 *   • NICU vitals (against existing nicu_admissions)
 *   • Front-desk vital checks
 *   • Medicine orders (IPD prescription items)
 *   • Pharmacy transactions
 *   • Service charge postings (auto-billed line items)
 *
 * Idempotent — skips tables that already have data.
 * Run: php artisan db:seed --class=ClinicalDataSeeder
 */
class ClinicalDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding clinical workflow data…');

        $this->seedMedicines();
        $this->seedBloodDonors();
        $this->seedBloodBags();
        $this->seedNicuVitals();
        $this->seedFdVitalChecks();
        $this->seedMedicineOrders();
        $this->seedPharmacyTransactions();
        $this->seedServiceChargePostings();

        $this->command->info('✓ Clinical data seeded.');
    }

    /* ───────── Medicines master ───────── */
    protected function seedMedicines(): void
    {
        if (! Schema::hasTable('medicines') || DB::table('medicines')->count() > 0) {
            $this->command->line('  · medicines already has data — skipped'); return;
        }

        // Make sure a category and unit exist
        $catId = DB::table('medicine_categories')->insertGetId([
            'name' => 'General', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $unitId = Schema::hasTable('medicine_units')
            ? DB::table('medicine_units')->insertGetId(['name' => 'Tablet', 'created_at' => now(), 'updated_at' => now()])
            : 1;

        $items = [
            ['Paracetamol 500mg',    'Antipyretic / Analgesic',  'Generic'],
            ['Amoxicillin 500mg',    'Antibiotic',               'Generic'],
            ['Ciprofloxacin 500mg',  'Antibiotic',               'Generic'],
            ['Metronidazole 400mg',  'Antibiotic',               'Generic'],
            ['Omeprazole 20mg',      'PPI (GERD)',               'Generic'],
            ['Pantoprazole 40mg',    'PPI (GERD)',               'Generic'],
            ['Metformin 500mg',      'Antidiabetic',             'Generic'],
            ['Atorvastatin 10mg',    'Lipid-lowering',           'Generic'],
            ['Amlodipine 5mg',       'Antihypertensive (CCB)',   'Generic'],
            ['Losartan 50mg',        'Antihypertensive (ARB)',   'Generic'],
            ['Aspirin 75mg',         'Antiplatelet',             'Generic'],
            ['Salbutamol Inhaler',   'Bronchodilator',           'GSK'],
            ['Cetirizine 10mg',      'Antihistamine',            'Generic'],
            ['Loratadine 10mg',      'Antihistamine',            'Generic'],
            ['Diclofenac 50mg',      'NSAID',                    'Generic'],
            ['Ibuprofen 400mg',      'NSAID',                    'Generic'],
            ['Tramadol 50mg',        'Opioid analgesic',         'Generic'],
            ['Diazepam 5mg',         'Anxiolytic / muscle relax','Generic'],
            ['Ondansetron 4mg',      'Antiemetic',               'Generic'],
            ['Ranitidine 150mg',     'H2 blocker',               'Generic'],
            ['Ferrous Sulfate 200mg','Iron supplement',          'Generic'],
            ['Folic Acid 5mg',       'Vitamin',                  'Generic'],
            ['Vitamin D3 1000IU',    'Vitamin',                  'Generic'],
            ['Insulin Regular 100IU','Antidiabetic (injectable)','Novo'],
            ['Adrenaline 1mg/ml',    'Emergency cardiac',        'Generic'],
        ];

        $rows = [];
        foreach ($items as [$name, $composition, $company]) {
            $rows[] = [
                'medicine_name'        => $name,
                'medicine_composition' => $composition,
                'medicine_category_id' => $catId,
                'medicine_unit_id'     => $unitId,
                'min_level'            => 50,
                'reorder_level'        => 100,
                'available_qty'        => random_int(80, 500),
                'tax'                  => 0,
                'status'               => 1,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }
        DB::table('medicines')->insert($rows);
        $this->command->line('  · '.count($rows).' medicines inserted');
    }

    /* ───────── Blood donors ───────── */
    protected function seedBloodDonors(): void
    {
        if (! Schema::hasTable('blood_donors') || DB::table('blood_donors')->count() > 0) {
            $this->command->line('  · blood_donors already has data — skipped'); return;
        }
        $bg = DB::table('blood_groups')->pluck('id')->all();
        if (empty($bg)) { $this->command->line('  · no blood groups configured — skipped'); return; }

        $names = ['Karim Ahmed','Rina Sultana','Tanvir Hasan','Maliha Begum','Imran Khan','Fariha Akter','Nasrul Islam','Sumaiya Rahman','Rafiq Ullah','Mahmuda Khanam'];
        $rows = [];
        foreach ($names as $i => $name) {
            $rows[] = [
                'donor_code'     => 'BD-' . str_pad((string)($i + 1), 5, '0', STR_PAD_LEFT),
                'name'           => $name,
                'dob'            => now()->subYears(25 + ($i % 20))->toDateString(),
                'blood_group_id' => $bg[$i % count($bg)],
                'gender'         => $i % 2 ? 'FEMALE' : 'MALE',
                'father_name'    => 'Father of ' . explode(' ', $name)[0],
                'contact_no'     => '0170' . str_pad((string)random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
                'address'        => 'Dhaka, Bangladesh',
                'is_active'      => 1,
                'created_at'     => now(), 'updated_at' => now(),
            ];
        }
        DB::table('blood_donors')->insert($rows);
        $this->command->line('  · '.count($rows).' blood donors inserted');
    }

    /* ───────── Blood bags ───────── */
    protected function seedBloodBags(): void
    {
        if (! Schema::hasTable('blood_bags') || DB::table('blood_bags')->count() > 0) {
            $this->command->line('  · blood_bags already has data — skipped'); return;
        }

        $types = ['SINGLE','DOUBLE','TRIPLE'];
        $rows = [];
        for ($i = 1; $i <= 8; $i++) {
            $type = $types[$i % 3];
            $rows[] = [
                'bag_code'  => 'BAG-' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'bag_type'  => $type,
                'volume_ml' => $type === 'TRIPLE' ? 450 : ($type === 'DOUBLE' ? 350 : 250),
                'is_active' => 1,
                'is_locked' => 0,
                'created_at'=> now(), 'updated_at' => now(),
            ];
        }
        DB::table('blood_bags')->insert($rows);
        $this->command->line('  · '.count($rows).' blood bags inserted');
    }

    /* ───────── NICU vitals ───────── */
    protected function seedNicuVitals(): void
    {
        if (! Schema::hasTable('nicu_vitals') || DB::table('nicu_vitals')->count() > 0) {
            $this->command->line('  · nicu_vitals already has data — skipped'); return;
        }
        $admissions = DB::table('nicu_admissions')->pluck('id')->all();
        if (empty($admissions)) { $this->command->line('  · no NICU admissions to attach vitals — skipped'); return; }

        $rows = [];
        foreach ($admissions as $aid) {
            for ($i = 0; $i < 4; $i++) {
                $hr  = random_int(120, 165);
                $spo = random_int(92, 99);
                $rows[] = [
                    'nicu_admission_id'    => $aid,
                    'recorded_at'          => now()->subHours($i * 6),
                    'heart_rate'           => $hr,
                    'respiratory_rate'     => random_int(35, 60),
                    'spo2'                 => $spo,
                    'temperature_c'        => 36.5 + (random_int(0, 15) / 10),
                    'blood_glucose_mgdl'   => random_int(55, 110),
                    'source'               => 'MANUAL',
                    'alert_apnea'          => 0,
                    'alert_hypothermia'    => 0,
                    'alert_spo2_critical'  => $spo < 90 ? 1 : 0,
                    'alert_hr_abnormal'    => ($hr < 100 || $hr > 180) ? 1 : 0,
                    'alert_level'          => 'NORMAL',
                    'notes'                => 'Routine 6-hourly check',
                    'recorded_by'          => 1,
                    'created_at'           => now(), 'updated_at' => now(),
                ];
            }
        }
        DB::table('nicu_vitals')->insert($rows);
        $this->command->line('  · '.count($rows).' NICU vital readings inserted');
    }

    /* ───────── Front-desk vital checks ───────── */
    protected function seedFdVitalChecks(): void
    {
        if (! Schema::hasTable('fd_vital_checks') || DB::table('fd_vital_checks')->count() > 0) {
            $this->command->line('  · fd_vital_checks already has data — skipped'); return;
        }
        $opd = DB::table('opd_patients')->limit(6)->get();
        if ($opd->isEmpty()) { $this->command->line('  · no OPD visits — skipped'); return; }

        $rows = [];
        foreach ($opd as $visit) {
            $patient = DB::table('patients')->where('id', $visit->patient_id)->first();
            if (! $patient) continue;
            $rows[] = [
                'patient_id'      => $visit->patient_id,
                'patient_type'    => 'OPD',
                'opd_patient_id'  => $visit->id,
                'patient_token'   => 'T-' . str_pad((string)$visit->id, 5, '0', STR_PAD_LEFT),
                'patient_name'    => $patient->patient_name ?? 'Patient',
                'gender'          => $patient->gender ?? 'male',
                'age'             => 30,
                'weight'          => random_int(55, 90),
                'height'          => random_int(160, 180),
                'blood_pressure'  => random_int(105, 140) . '/' . random_int(65, 90),
                'temperature'     => 36.5 + (random_int(0, 18) / 10),
                'heart_rate'      => random_int(60, 95),
                'respiratory_rate'=> random_int(14, 20),
                'spo2'            => random_int(95, 99),
                'machine_fetched' => 0,
                'checked_by'      => 1,
                'checked_at'      => now(),
                'created_at'      => now(), 'updated_at' => now(),
            ];
        }
        DB::table('fd_vital_checks')->insert($rows);
        $this->command->line('  · '.count($rows).' front-desk vital checks inserted');
    }

    /* ───────── Medicine orders (against IPD admissions) ───────── */
    protected function seedMedicineOrders(): void
    {
        if (! Schema::hasTable('medicine_orders') || DB::table('medicine_orders')->count() > 0) {
            $this->command->line('  · medicine_orders already has data — skipped'); return;
        }
        $meds = DB::table('medicines')->limit(6)->pluck('id')->all();
        $ipds = DB::table('i_p_d_patients')->limit(3)->get();
        if (empty($meds) || $ipds->isEmpty()) {
            $this->command->line('  · need medicines + IPD patients first — skipped'); return;
        }

        $cols = Schema::getColumnListing('medicine_orders');
        $rows = [];
        foreach ($ipds as $ipd) {
            foreach ($meds as $i => $medId) {
                $base = [
                    'medicine_id'    => $medId,
                    'qty'            => 2,
                    'prescribed_by'  => 1,
                    'patient_id'     => $ipd->patient_id,
                    'ipd_id'         => $ipd->id,
                    'case_id'        => $ipd->case_id ?? null,
                    'source'         => 'IPD',
                    'status'         => $i % 2 ? 'pending' : 'issued',
                    'order_by'       => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ];
                $rows[] = array_intersect_key($base, array_flip($cols));
            }
        }
        DB::table('medicine_orders')->insert($rows);
        $this->command->line('  · '.count($rows).' medicine orders inserted');
    }

    /* ───────── Pharmacy transactions ───────── */
    protected function seedPharmacyTransactions(): void
    {
        if (! Schema::hasTable('pharmacy_transactions') || DB::table('pharmacy_transactions')->count() > 0) {
            $this->command->line('  · pharmacy_transactions already has data — skipped'); return;
        }
        $patients = DB::table('patients')->limit(4)->pluck('id')->all();
        if (empty($patients)) { $this->command->line('  · no patients — skipped'); return; }

        $rows = [];
        foreach ($patients as $i => $pid) {
            $rows[] = [
                'transaction_no'   => 'PH-' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT),
                'transaction_type' => $i % 2 ? 'opd' : 'ipd',
                'patient_id'       => $pid,
                'pharmacist_id'    => 1,
                'drug_count'       => 3,
                'total_amount'     => 450 + ($i * 75),
                'discount_amount'  => 0,
                'paid_amount'      => 450 + ($i * 75),
                'payment_method'   => 'cash',
                'payment_status'   => 'paid',
                'status'           => 'completed',
                'note'             => 'Auto-seeded pharmacy transaction',
                'created_at'       => now()->subDays($i),
                'updated_at'       => now()->subDays($i),
            ];
        }
        DB::table('pharmacy_transactions')->insert($rows);
        $this->command->line('  · '.count($rows).' pharmacy transactions inserted');
    }

    /* ───────── Service charge postings ───────── */
    protected function seedServiceChargePostings(): void
    {
        if (! Schema::hasTable('service_charge_postings') || DB::table('service_charge_postings')->count() > 0) {
            $this->command->line('  · service_charge_postings already has data — skipped'); return;
        }
        if (! Schema::hasTable('service_catalogs')) {
            $this->command->line('  · service_catalogs table missing — skipped'); return;
        }
        $catalog = DB::table('service_catalogs')->limit(5)->get();
        if ($catalog->isEmpty()) { $this->command->line('  · no service catalog entries — skipped'); return; }

        $encounters = DB::table('encounters')->limit(3)->get();
        $cols = Schema::getColumnListing('service_charge_postings');
        $rows = [];
        foreach ($encounters as $enc) {
            foreach ($catalog as $i => $svc) {
                $price = (float) ($svc->price ?? $svc->unit_price ?? 100);
                $base = [
                    'organization_id'      => $enc->organization_id ?? null,
                    'branch_id'            => $enc->branch_id ?? null,
                    'encounter_id'         => $enc->id,
                    'patient_id'           => $enc->patient_id,
                    'service_catalog_id'   => $svc->id,
                    'trigger_event'        => 'manual',
                    'trigger_source_type'  => 'seeder',
                    'trigger_source_id'    => null,
                    'quantity'             => 1,
                    'unit_price'           => $price,
                    'discount_amount'      => 0,
                    'tax_amount'           => 0,
                    'net_amount'           => $price,
                    'status'               => 'posted',
                    'reason'               => 'Seeded for testing',
                    'posted_by'            => 1,
                    'created_at'           => now(), 'updated_at' => now(),
                ];
                $rows[] = array_intersect_key($base, array_flip($cols));
            }
        }
        if ($rows) {
            DB::table('service_charge_postings')->insert($rows);
            $this->command->line('  · '.count($rows).' service charge postings inserted');
        }
    }
}
