<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Two things this seeder does, idempotently:
 *
 *   1. Backfills icu_equipment.inventory_item_id so every ICU/CCU equipment
 *      row links to the unified inventory_items master. If no matching item
 *      exists (matched on name OR equipment_code), a new inventory_items
 *      row is created with is_asset=1.
 *
 *   2. Adds a starter set of NICU equipment so the NICU equipment page
 *      isn't empty.
 *
 * Safe to re-run — every insert is guarded.
 */
class IcuEquipmentUnifySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $orgId = DB::table('organizations')->value('id');

        // ── 1. Seed NICU equipment if absent ──
        $nicuDefs = [
            // [code,             name,                       type]
            ['NICU-VENT-001',     'NICU Ventilator',          'Ventilator'],
            ['NICU-PHOTO-001',    'Phototherapy Unit',        'Other'],
            ['NICU-MONITOR-001',  'Neonatal Monitor',         'Monitor'],
            ['NICU-INFPUMP-001',  'Neonatal Infusion Pump',   'InfusionPump'],
            ['NICU-CPAP-001',     'CPAP Machine',             'OxygenSupport'],
        ];
        $nicuAdded = 0;
        foreach ($nicuDefs as [$code, $name, $type]) {
            $exists = DB::table('icu_equipment')->where('equipment_code', $code)->exists();
            if ($exists) continue;
            DB::table('icu_equipment')->insert([
                'equipment_code' => $code,
                'equipment_name' => $name,
                'equipment_type' => $type,
                'icu_type'       => 'NICU',
                'serial_no'      => 'SN-' . strtoupper(substr(md5($code), 0, 8)),
                'status'         => 'Available',
                'location'       => 'NICU Sub-Store',
                'charge_type'    => 'Day',
                'charge_rate'    => 500.00,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $nicuAdded++;
        }

        // ── 2. Backfill inventory_item_id for every icu_equipment row ──
        $linked = 0;
        $created = 0;
        DB::table('icu_equipment')->whereNull('inventory_item_id')->get()->each(
            function ($eq) use ($orgId, $now, &$linked, &$created) {
                // Try to reuse an inventory_items row by code first, then by name.
                $itemId = DB::table('inventory_items')->where('code', $eq->equipment_code)->value('id')
                    ?? DB::table('inventory_items')->where('name', $eq->equipment_name)->value('id');

                if (! $itemId) {
                    // Create a new inventory_items row tagged as an asset.
                    $code = $eq->equipment_code ?: 'EQ-' . str_pad((string) $eq->id, 5, '0', STR_PAD_LEFT);
                    $itemId = DB::table('inventory_items')->insertGetId([
                        'organization_id' => $orgId,
                        'code'            => $code,
                        'name'            => $eq->equipment_name,
                        'category'        => 'Equipment',
                        'sku'             => $code,
                        'uom'             => 'unit',
                        'tax_percent'     => 0,
                        'reorder_level'   => 0,
                        'reorder_quantity'=> 0,
                        'is_controlled'   => 0,
                        'is_consumable'   => 0,
                        'is_asset'        => 1,
                        'is_active'       => $eq->is_active ? 1 : 0,
                        'description'     => 'Auto-created from icu_equipment#' . $eq->id,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                    $created++;
                }

                DB::table('icu_equipment')->where('id', $eq->id)->update([
                    'inventory_item_id' => $itemId,
                    'updated_at'        => $now,
                ]);
                $linked++;
            }
        );

        $this->command->info('✓ ICU equipment unification:');
        $this->command->line("  • NICU equipment seeded         : {$nicuAdded}");
        $this->command->line("  • inventory_item_id linked      : {$linked}");
        $this->command->line("  • new inventory_items created   : {$created}");
    }
}
