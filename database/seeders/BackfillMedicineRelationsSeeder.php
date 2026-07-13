<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Backfills medicines.medical_group_id + medicines.company_id from the
 * existing masters (medical_groups, companies). Uses a keyword map so
 * the Drug Master / Inventory pages show real "Group / Composition"
 * + "Brand" data instead of em-dashes.
 *
 * Re-runnable: skips medicines that already have the FK set.
 */
class BackfillMedicineRelationsSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure the masters can express every clinical class our demo
        // medicines actually fall into.
        foreach (['Anxiolytic', 'Antiemetic', 'Hematinic', 'Vitamin / Supplement'] as $g) {
            DB::table('medical_groups')->updateOrInsert(
                ['name' => $g], ['status' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $groups    = DB::table('medical_groups')->pluck('id', 'name')->all();
        $companies = DB::table('companies')->pluck('id', 'name')->all();

        if (! $groups || ! $companies) {
            $this->command->warn('  ⚠ Skipping — medical_groups or companies master is empty.');
            return;
        }

        // Map medicine-name keywords → medical_group name
        $groupMap = [
            'paracetamol'   => 'Analgesic',
            'aspirin'       => 'Analgesic',
            'ibuprofen'     => 'Analgesic',
            'diclofenac'    => 'Analgesic',
            'amoxicillin'   => 'Antibiotic',
            'ciprofloxacin' => 'Antibiotic',
            'azithromycin'  => 'Antibiotic',
            'metronidazole' => 'Antibiotic',
            'omeprazole'    => 'PPI',
            'pantoprazole'  => 'PPI',
            'ranitidine'    => 'PPI',
            'metformin'     => 'Antidiabetic',
            'glibenclamide' => 'Antidiabetic',
            'insulin'       => 'Antidiabetic',
            'atorvastatin'  => 'Statin',
            'amlodipine'    => 'Antihypertensive',
            'losartan'      => 'Antihypertensive',
            'salbutamol'    => 'Bronchodilator',
            'cetirizine'    => 'Antihistamine',
            'loratadine'    => 'Antihistamine',
            'heparin'       => 'Anticoagulant',
            'adrenaline'    => 'Analgesic',     // closest available fallback
            'tramadol'      => 'Analgesic',
            'diazepam'      => 'Anxiolytic',
            'ondansetron'   => 'Antiemetic',
            'ferrous'       => 'Hematinic',
            'folic'         => 'Hematinic',
            'vitamin'       => 'Vitamin / Supplement',
        ];

        // Distribute medicines across companies pseudo-randomly but deterministically.
        $companyList = array_values($companies);

        $count = 0;
        DB::table('medicines')->orderBy('id')->get()->each(
            function ($m) use ($groups, $companyList, $groupMap, &$count) {
                $update = [];

                if (! $m->medical_group_id) {
                    $name = strtolower($m->medicine_name);
                    $groupName = null;
                    foreach ($groupMap as $kw => $g) {
                        if (str_contains($name, $kw)) { $groupName = $g; break; }
                    }
                    if ($groupName && isset($groups[$groupName])) {
                        $update['medical_group_id'] = $groups[$groupName];
                    }
                }

                if (! $m->company_id) {
                    // round-robin across companies for variety
                    $update['company_id'] = $companyList[($m->id - 1) % count($companyList)];
                }

                // Composition fallback: if blank, use medicine name as the composition seed
                if (empty($m->medicine_composition)) {
                    $update['medicine_composition'] = $m->medicine_name;
                }

                if ($update) {
                    $update['updated_at'] = now();
                    DB::table('medicines')->where('id', $m->id)->update($update);
                    $count++;
                }
            }
        );

        // Mirror brand into inventory_items so the Inventory page also shows it.
        DB::statement("
            UPDATE inventory_items i
            JOIN medicines m ON m.inventory_item_id = i.id
            JOIN companies c ON c.id = m.company_id
            SET i.brand = c.name
            WHERE i.brand IS NULL OR i.brand = ''
        ");

        $this->command->info("✓ Medicine relations backfilled on {$count} rows.");
    }
}
