<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1. Medicine Categories (real BD pharmacy categories)
        $categories = ['Antibiotic', 'Analgesic', 'Antipyretic', 'Antihypertensive',
                       'Antidiabetic', 'Antacid', 'Vitamins & Supplements', 'IV Fluids',
                       'Cardiac', 'Respiratory', 'Anti-Allergic', 'Steroid'];
        $catIds = [];
        foreach ($categories as $name) {
            DB::table('medicine_categories')->updateOrInsert(
                ['name' => $name],
                ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
            $catIds[$name] = DB::table('medicine_categories')->where('name', $name)->value('id');
        }

        // 2. Medicine Units
        $units = ['Tablet', 'Capsule', 'Vial', 'Bottle', 'Ampoule', 'Strip', 'Tube', 'ML', 'MG'];
        $unitIds = [];
        foreach ($units as $u) {
            DB::table('medicine_units')->updateOrInsert(
                ['name' => $u],
                ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
            $unitIds[$u] = DB::table('medicine_units')->where('name', $u)->value('id');
        }

        // 3. Medicine Generics (active ingredients)
        $generics = [
            'Paracetamol', 'Amoxicillin', 'Ciprofloxacin', 'Azithromycin', 'Metronidazole',
            'Omeprazole', 'Pantoprazole', 'Ranitidine', 'Metformin', 'Glimepiride',
            'Atorvastatin', 'Amlodipine', 'Telmisartan', 'Losartan', 'Atenolol',
            'Cetirizine', 'Loratadine', 'Diclofenac', 'Ibuprofen', 'Aspirin',
            'Insulin', 'Salbutamol', 'Furosemide', 'Spironolactone', 'Warfarin',
            'Clopidogrel', 'Heparin', 'Dexamethasone', 'Prednisolone', 'Hydrocortisone',
        ];
        foreach ($generics as $g) {
            DB::table('medicine_generics')->updateOrInsert(
                ['name' => $g],
                ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // 4. Back-fill medicines from inventory_items (category=medicine) that lack a pharmacy row
        $invMedicines = DB::table('inventory_items')
            ->where('category', 'medicine')
            ->whereNotIn('id', function ($q) {
                $q->from('medicines')->select('inventory_item_id');
            })
            ->get();

        foreach ($invMedicines as $inv) {
            // Guess category from name
            $catId = $catIds['Antibiotic'] ?? null;
            foreach (['Paracetamol' => 'Analgesic', 'Amox' => 'Antibiotic', 'Metformin' => 'Antidiabetic',
                      'Omeprazole' => 'Antacid', 'Atorvastatin' => 'Cardiac', 'Insulin' => 'Antidiabetic',
                      'Salbutamol' => 'Respiratory'] as $kw => $cat) {
                if (stripos($inv->name, $kw) !== false) { $catId = $catIds[$cat] ?? $catId; break; }
            }

            DB::table('medicines')->insert([
                'inventory_item_id'    => $inv->id,
                'medicine_name'        => $inv->name,
                'medicine_category_id' => $catId,
                'medicine_unit_id'     => $unitIds['Tablet'] ?? null,
                'min_level'            => 10,
                'reorder_level'        => 20,
                'tax'                  => $inv->tax_percent ?? 0,
                'available_qty'        => 100,
                'status'               => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        // 5. Set medicine_category_id on the original 5 medicines that have category NULL
        foreach (DB::table('medicines')->whereNull('medicine_category_id')->get() as $m) {
            $catId = $catIds['Analgesic'] ?? null;
            foreach (['Paracetamol' => 'Analgesic', 'Amox' => 'Antibiotic', 'Metformin' => 'Antidiabetic',
                      'Omeprazole' => 'Antacid', 'Atorvastatin' => 'Cardiac'] as $kw => $cat) {
                if (stripos($m->medicine_name, $kw) !== false) { $catId = $catIds[$cat] ?? $catId; break; }
            }
            DB::table('medicines')->where('id', $m->id)->update([
                'medicine_category_id' => $catId,
                'updated_at'           => $now,
            ]);
        }
    }
}
