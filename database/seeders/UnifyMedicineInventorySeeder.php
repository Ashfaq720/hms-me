<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Phase 1 of the medicine / inventory unification.
 *
 * The schema is built around a unified `inventory_items` master with a
 * 1:1 `medicines.inventory_item_id` link — but earlier seeds only filled
 * the legacy pharmacy side. This seeder bridges them without removing
 * anything:
 *
 *   1. Seeds default warehouses (Main Pharmacy + 5 ward/dept stores).
 *   2. Creates one inventory_items row per medicine (is_consumable=true).
 *   3. Sets medicines.inventory_item_id → that row.
 *   4. Mirrors each medicine_batches row into inventory_item_batches under
 *      the matching warehouse.
 *   5. Writes one stock_movements row per batch with reason='opening' so
 *      the immutable audit log starts with the correct opening balance.
 *
 * Idempotent — re-runs key by code/name and skip rows that already exist.
 * Safe — no row is deleted, no existing column is touched outside of
 * setting the inventory_item_id FK on medicines (which is currently NULL
 * for every row, so no data is lost).
 */
class UnifyMedicineInventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $orgId    = (int) (DB::table('organizations')->value('id') ?? 0) ?: null;
        $branchId = (int) (DB::table('branches')->value('id') ?? 0) ?: null;

        $warehouses = $this->seedWarehouses($orgId, $branchId, $now);
        $mainWh     = $warehouses['MAIN-PHARMA'];

        $created = $this->linkMedicinesToInventoryItems($now);
        $batches = $this->mirrorBatches($mainWh, $orgId, $branchId, $now);

        $this->command->info("✓ Unified inventory seeded.");
        $this->command->line("  • Warehouses                : " . count($warehouses));
        $this->command->line("  • inventory_items created   : {$created}");
        $this->command->line("  • inventory_item_batches    : {$batches}");
        $this->command->line("  • opening stock_movements   : {$batches}");
    }

    /**
     * Default warehouse layout. Each clinical area has its own store so
     * the stock_movements ledger can attribute every consumption to a
     * named location.
     */
    protected function seedWarehouses(?int $orgId, ?int $branchId, $now): array
    {
        $defs = [
            ['MAIN-PHARMA', 'Main Pharmacy',  'pharmacy'],
            ['OPD-STORE',   'OPD Sub-Store',  'sub_store'],
            ['IPD-STORE',   'IPD Sub-Store',  'sub_store'],
            ['OT-STORE',    'OT Sub-Store',   'sub_store'],
            ['ICU-STORE',   'ICU Sub-Store',  'sub_store'],
            ['NICU-STORE',  'NICU Sub-Store', 'sub_store'],
            ['LAB-STORE',   'Lab Sub-Store',  'sub_store'],
        ];

        $out = [];
        foreach ($defs as [$code, $name, $type]) {
            $existing = DB::table('inventory_warehouses')->where('code', $code)->first();
            if ($existing) {
                $out[$code] = $existing->id;
                continue;
            }
            $out[$code] = DB::table('inventory_warehouses')->insertGetId([
                'organization_id' => $orgId,
                'branch_id'       => $branchId,
                'code'            => $code,
                'name'            => $name,
                'type'            => $type,
                'is_active'       => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
        return $out;
    }

    /**
     * For every medicine row, ensure an inventory_items row exists and
     * the FK is wired. Re-runs are no-ops.
     */
    protected function linkMedicinesToInventoryItems($now): int
    {
        $created = 0;

        DB::table('medicines')->orderBy('id')->get()->each(function ($med) use (&$created, $now) {
            // Skip if already linked
            if ($med->inventory_item_id && DB::table('inventory_items')->where('id', $med->inventory_item_id)->exists()) {
                return;
            }

            $generic = $med->medical_group_id
                ? DB::table('medical_groups')->where('id', $med->medical_group_id)->value('name')
                : null;
            $uom     = $med->medicine_unit_id
                ? DB::table('medicine_units')->where('id', $med->medicine_unit_id)->value('name')
                : null;
            $brand   = $med->company_id
                ? DB::table('companies')->where('id', $med->company_id)->value('name')
                : null;

            $code = 'MED-' . str_pad((string) $med->id, 5, '0', STR_PAD_LEFT);

            // Reuse existing inventory_items row if the code matches (re-run safe).
            $existing = DB::table('inventory_items')->where('code', $code)->first();
            $itemId   = $existing?->id;

            if (! $itemId) {
                $itemId = DB::table('inventory_items')->insertGetId([
                    'organization_id'   => DB::table('organizations')->value('id'),
                    'code'              => $code,
                    'name'              => $med->medicine_name,
                    'category'          => $med->medicine_category_id
                        ? DB::table('medicine_categories')->where('id', $med->medicine_category_id)->value('name')
                        : 'Medicine',
                    'generic_name'      => $generic,
                    'brand'             => $brand,
                    'sku'               => $code,
                    'uom'               => $uom ?: 'piece',
                    'tax_percent'       => (float) ($med->tax ?? 0),
                    'reorder_level'     => is_numeric($med->reorder_level ?? null)
                        ? (float) $med->reorder_level : 0,
                    'reorder_quantity'  => 0,
                    'storage_condition' => $med->rack_number ? 'Rack: ' . $med->rack_number : null,
                    'is_controlled'     => 0,    // flip on manually for narcotics
                    'is_consumable'     => 1,
                    'is_asset'          => 0,
                    'is_active'         => 1,
                    'description'       => $med->medicine_composition ?? null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $created++;
            }

            DB::table('medicines')->where('id', $med->id)->update([
                'inventory_item_id' => $itemId,
                'updated_at'        => $now,
            ]);
        });

        return $created;
    }

    /**
     * Mirror medicine_batches → inventory_item_batches, then emit one
     * opening stock_movements row per batch so the ledger starts with
     * the right balance.
     */
    protected function mirrorBatches(int $mainWarehouseId, ?int $orgId, ?int $branchId, $now): int
    {
        $count = 0;

        DB::table('medicine_batches')->orderBy('id')->get()->each(
            function ($mb) use ($mainWarehouseId, $orgId, $branchId, $now, &$count) {
                $itemId = DB::table('medicines')->where('id', $mb->medicine_id)->value('inventory_item_id');
                if (! $itemId) return;

                $existing = DB::table('inventory_item_batches')
                    ->where('inventory_item_id', $itemId)
                    ->where('warehouse_id', $mainWarehouseId)
                    ->where('batch_no', $mb->batch_no)
                    ->first();

                if ($existing) return;

                $batchId = DB::table('inventory_item_batches')->insertGetId([
                    'inventory_item_id' => $itemId,
                    'warehouse_id'      => $mainWarehouseId,
                    'batch_no'          => $mb->batch_no,
                    'mfg_date'          => $mb->manufacture_date,
                    'expiry_date'       => $mb->expiry_date,
                    'cost_price'        => $mb->purchase_price,
                    'selling_price'     => $mb->selling_price,
                    'current_qty'       => $mb->quantity,
                    'storage_location'  => $mb->store ?: 'Main Pharmacy',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                DB::table('stock_movements')->insert([
                    'organization_id'         => $orgId,
                    'branch_id'               => $branchId,
                    'inventory_item_id'       => $itemId,
                    'inventory_item_batch_id' => $batchId,
                    'warehouse_id'            => $mainWarehouseId,
                    'direction'               => 'in',
                    'quantity'                => $mb->quantity,
                    'unit_cost'               => $mb->purchase_price,
                    'balance_after'           => $mb->quantity,
                    'reason'                  => 'opening',
                    'reference_no'            => 'OPENING-' . $mb->batch_no,
                    'remarks'                 => 'Opening balance migrated from medicine_batches#' . $mb->id,
                    'performed_at'            => $now,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);

                $count++;
            }
        );

        return $count;
    }
}
