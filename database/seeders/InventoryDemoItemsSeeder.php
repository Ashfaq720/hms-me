<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a starter set of NON-medicine inventory items so /inventory/items
 * shows real data under ?type=consumable and ?type=asset.
 *
 * Idempotent — keys on `code`. Skips rows that already exist.
 */
class InventoryDemoItemsSeeder extends Seeder
{
    public function run(): void
    {
        $orgId = DB::table('organizations')->value('id');
        $now = now();

        $defs = [
            // [code,           name,                    category,     uom,         consumable, asset, controlled, reorder]
            ['CNSM-GAUZE-01',   'Sterile Gauze 10x10cm', 'Wound Care', 'piece',     1, 0, 0, 200],
            ['CNSM-SYR-05',     'Syringe 5ml',           'Consumable', 'piece',     1, 0, 0, 500],
            ['CNSM-SYR-10',     'Syringe 10ml',          'Consumable', 'piece',     1, 0, 0, 500],
            ['CNSM-IV-18G',     'IV Cannula 18G',        'Consumable', 'piece',     1, 0, 0, 200],
            ['CNSM-GLOVES-M',   'Examination Gloves M',  'Consumable', 'box',       1, 0, 0, 50],
            ['CNSM-MASK-N95',   'N95 Mask',              'PPE',        'piece',     1, 0, 0, 300],
            ['CNSM-COT-PAD',    'Cotton Pad',            'Wound Care', 'piece',     1, 0, 0, 200],
            ['CNSM-SUTURE',     'Surgical Suture 2-0',   'Surgical',   'pack',      1, 0, 1, 50],
            ['CNSM-FOLEY-16',   'Foley Catheter 16Fr',   'Catheter',   'piece',     1, 0, 0, 30],

            ['AST-BP-DGT',      'Digital BP Monitor',    'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-OXIMETER',    'Pulse Oximeter',        'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-ECG-12L',     '12-Lead ECG Machine',   'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-DEFIB',       'Defibrillator',         'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-WHEELCH',     'Wheelchair',            'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-STRETCHER',   'Hospital Stretcher',    'Equipment',  'unit',      0, 1, 0, 0],
            ['AST-AUTOCLAVE',   'Steam Autoclave',       'Equipment',  'unit',      0, 1, 0, 0],
        ];

        $count = 0;
        foreach ($defs as [$code, $name, $cat, $uom, $cnsm, $ast, $ctrl, $reorder]) {
            DB::table('inventory_items')->updateOrInsert(
                ['code' => $code],
                [
                    'organization_id'  => $orgId,
                    'name'             => $name,
                    'category'         => $cat,
                    'sku'              => $code,
                    'uom'              => $uom,
                    'tax_percent'      => 0,
                    'reorder_level'    => $reorder,
                    'reorder_quantity' => 0,
                    'is_consumable'    => $cnsm,
                    'is_asset'         => $ast,
                    'is_controlled'    => $ctrl,
                    'is_active'        => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
            $count++;
        }

        // Add a starter batch in Main Pharmacy for each consumable so stock pages
        // have non-zero data; assets don't get batches (one-of-a-kind units).
        $mainWh = DB::table('inventory_warehouses')->where('code', 'MAIN-PHARMA')->value('id');
        if ($mainWh) {
            DB::table('inventory_items')
                ->where('is_consumable', 1)->where('is_asset', 0)
                ->where('code', 'like', 'CNSM-%')
                ->get()->each(function ($item) use ($mainWh, $now, $orgId) {
                    $exists = DB::table('inventory_item_batches')
                        ->where('inventory_item_id', $item->id)
                        ->where('warehouse_id', $mainWh)->exists();
                    if ($exists) return;

                    $qty = max(($item->reorder_level ?? 0) * 2, 100);
                    $batchId = DB::table('inventory_item_batches')->insertGetId([
                        'inventory_item_id' => $item->id,
                        'warehouse_id'      => $mainWh,
                        'batch_no'          => 'OPEN-' . $item->code,
                        'mfg_date'          => now()->subMonths(3)->toDateString(),
                        'expiry_date'       => now()->addYears(2)->toDateString(),
                        'cost_price'        => 5.00,
                        'selling_price'     => 7.50,
                        'current_qty'       => $qty,
                        'storage_location'  => 'Main Pharmacy',
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]);
                    DB::table('stock_movements')->insert([
                        'organization_id'         => $orgId,
                        'inventory_item_id'       => $item->id,
                        'inventory_item_batch_id' => $batchId,
                        'warehouse_id'            => $mainWh,
                        'direction'               => 'in',
                        'quantity'                => $qty,
                        'unit_cost'               => 5.00,
                        'balance_after'           => $qty,
                        'reason'                  => 'opening',
                        'reference_no'            => 'OPENING-' . $item->code,
                        'remarks'                 => 'Opening balance for consumable.',
                        'performed_at'            => $now,
                        'created_at'              => $now,
                        'updated_at'              => $now,
                    ]);
                });
        }

        $this->command->info("✓ Inventory demo items seeded: {$count}.");
    }
}
