<?php

namespace Database\Seeders;

use App\Models\Pharmacy\Company;
use App\Models\Pharmacy\MedicalGroup;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineBatch;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Database\Seeder;

class PharmacyInventorySeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = collect([
            'Analgesics', 'Antibiotics', 'Cardiovascular', 'Gastrointestinal',
            'Respiratory', 'Anti-diabetic', 'Vitamins & Supplements',
        ])->map(fn($name) => MedicineCategory::firstOrCreate(['name' => $name], ['status' => 1]));

        // Companies
        $companies = collect([
            'Square Pharma', 'Beximco Pharma', 'Incepta Pharma',
            'Renata Ltd', 'ACI Ltd', 'Eskayef Pharma',
        ])->map(fn($name) => Company::firstOrCreate(['name' => $name], ['status' => 1]));

        // Groups
        $groups = collect([
            'Tablet', 'Capsule', 'Syrup', 'Injection', 'IV Solution', 'Cream',
        ])->map(fn($name) => MedicalGroup::firstOrCreate(['name' => $name], ['status' => 1]));

        // Units
        $units = collect([
            'Pcs', 'Bottle', 'Vial', 'Tube', 'Strip',
        ])->map(fn($name) => MedicineUnit::firstOrCreate(['name' => $name], ['status' => 1]));

        // Medicines with realistic data
        $medicinesData = [
            ['name' => 'Napa 500mg',       'composition' => 'Paracetamol',     'cat' => 0, 'comp' => 0, 'grp' => 0, 'unit' => 0, 'packing' => '10x10', 'qty' => 950],
            ['name' => 'Napa Extra',        'composition' => 'Paracetamol + Caffeine', 'cat' => 0, 'comp' => 0, 'grp' => 0, 'unit' => 0, 'packing' => '10x10', 'qty' => 400],
            ['name' => 'Amoxil 500mg',      'composition' => 'Amoxicillin',    'cat' => 1, 'comp' => 1, 'grp' => 1, 'unit' => 0, 'packing' => '5x6',   'qty' => 45],
            ['name' => 'Ciprocin 500mg',    'composition' => 'Ciprofloxacin',  'cat' => 1, 'comp' => 2, 'grp' => 0, 'unit' => 0, 'packing' => '10x10', 'qty' => 120],
            ['name' => 'Azith 500mg',       'composition' => 'Azithromycin',   'cat' => 1, 'comp' => 2, 'grp' => 0, 'unit' => 4, 'packing' => '1x3',   'qty' => 300],
            ['name' => 'Losectil 50mg',     'composition' => 'Losartan',       'cat' => 2, 'comp' => 3, 'grp' => 0, 'unit' => 0, 'packing' => '3x10',  'qty' => 0],
            ['name' => 'Amlodipine 5mg',    'composition' => 'Amlodipine',     'cat' => 2, 'comp' => 3, 'grp' => 0, 'unit' => 0, 'packing' => '5x10',  'qty' => 600],
            ['name' => 'Seclo 20mg',        'composition' => 'Omeprazole',     'cat' => 3, 'comp' => 0, 'grp' => 1, 'unit' => 0, 'packing' => '2x14',  'qty' => 800],
            ['name' => 'Pantonix 40mg',     'composition' => 'Pantoprazole',   'cat' => 3, 'comp' => 4, 'grp' => 0, 'unit' => 0, 'packing' => '2x14',  'qty' => 200],
            ['name' => 'Salbutamol Syrup',  'composition' => 'Salbutamol',     'cat' => 4, 'comp' => 5, 'grp' => 2, 'unit' => 1, 'packing' => '100ml', 'qty' => 85],
            ['name' => 'Montelukast 10mg',  'composition' => 'Montelukast',    'cat' => 4, 'comp' => 3, 'grp' => 0, 'unit' => 0, 'packing' => '3x10',  'qty' => 350],
            ['name' => 'Metformin 500mg',   'composition' => 'Metformin HCl',  'cat' => 5, 'comp' => 1, 'grp' => 0, 'unit' => 0, 'packing' => '10x10', 'qty' => 700],
            ['name' => 'Glimepiride 2mg',   'composition' => 'Glimepiride',    'cat' => 5, 'comp' => 4, 'grp' => 0, 'unit' => 0, 'packing' => '3x10',  'qty' => 150],
            ['name' => 'Normal Saline',     'composition' => 'NaCl 0.9%',      'cat' => 6, 'comp' => 2, 'grp' => 4, 'unit' => 1, 'packing' => '500ml', 'qty' => 35],
            ['name' => 'Vitamin D3 2000IU', 'composition' => 'Cholecalciferol','cat' => 6, 'comp' => 5, 'grp' => 1, 'unit' => 0, 'packing' => '3x10',  'qty' => 500],
        ];

        $stores = ['Main Pharmacy', 'OPD Pharmacy', 'Emergency Store'];

        foreach ($medicinesData as $md) {
            $medicine = Medicine::firstOrCreate(
                ['medicine_name' => $md['name']],
                [
                    'medicine_category_id' => $categories[$md['cat']]->id,
                    'company_id'           => $companies[$md['comp']]->id,
                    'medical_group_id'     => $groups[$md['grp']]->id,
                    'medicine_unit_id'     => $units[$md['unit']]->id,
                    'medicine_composition' => $md['composition'],
                    'box_packing'          => $md['packing'],
                    'reorder_level'        => (string) rand(30, 80),
                    'min_level'            => (string) rand(10, 30),
                    'tax'                  => rand(0, 15),
                    'available_qty'        => $md['qty'],
                    'status'               => 1,
                ]
            );

            // Create 1-3 batches per medicine
            $batchCount = rand(1, 3);
            $remainingQty = $md['qty'];

            for ($i = 0; $i < $batchCount; $i++) {
                $isLast = ($i === $batchCount - 1);
                $batchQty = $isLast ? $remainingQty : (int) ($remainingQty / ($batchCount - $i));
                $remainingQty -= $batchQty;

                $purchasePrice = round(rand(200, 5000) / 100, 2);
                $margin = rand(15, 40) / 100;

                // Vary expiry: some expired, some near-expiry, most valid
                $expiryMonths = match (true) {
                    $i === 0 && rand(1, 10) <= 2 => rand(-2, 0),   // ~20% chance expired
                    $i === 0 && rand(1, 10) <= 4 => rand(1, 3),    // near expiry
                    default                       => rand(6, 24),   // valid
                };

                MedicineBatch::create([
                    'medicine_id'      => $medicine->id,
                    'batch_no'         => strtoupper(substr($md['name'], 0, 3)) . '/' . now()->format('Y') . '/' . str_pad($medicine->id, 2, '0', STR_PAD_LEFT) . ($i + 1),
                    'expiry_date'      => now()->addMonths($expiryMonths)->startOfMonth(),
                    'manufacture_date' => now()->subMonths(rand(6, 18)),
                    'purchase_price'   => $purchasePrice,
                    'selling_price'    => round($purchasePrice * (1 + $margin), 2),
                    'quantity'         => $batchQty,
                    'store'            => $stores[array_rand($stores)],
                    'status'           => 1,
                ]);
            }
        }
    }
}
