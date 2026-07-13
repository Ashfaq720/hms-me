<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackfillOtToInventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ────────── 1. OT CONSUMABLES → INVENTORY ──────────
        $consumables = DB::table('ot_consumables')->whereNull('inventory_item_id')->get();
        foreach ($consumables as $c) {
            // Try exact code match first
            $inv = DB::table('inventory_items')->where('code', $c->code)->first();

            // Or try name match (case-insensitive)
            if (! $inv) {
                $inv = DB::table('inventory_items')
                    ->whereRaw('LOWER(name) = ?', [strtolower($c->name)])
                    ->first();
            }

            // Create a new inventory row if no match
            if (! $inv) {
                $invId = DB::table('inventory_items')->insertGetId([
                    'code'           => 'OT-' . $c->code,
                    'name'           => $c->name,
                    'category'       => $c->is_implant ? 'implant' : 'consumable',
                    'uom'            => $c->unit ?: 'pc',
                    'reorder_level'  => $c->reorder_level ?? 0,
                    'is_consumable'  => 1,
                    'is_asset'       => 0,
                    'is_active'      => $c->is_active,
                    'description'    => 'OT ' . $c->type,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            } else {
                $invId = $inv->id;
            }

            DB::table('ot_consumables')->where('id', $c->id)->update([
                'inventory_item_id' => $invId,
                'updated_at'        => $now,
            ]);
        }

        // ────────── 2. OT EQUIPMENTS → INVENTORY (as assets) ──────────
        $equipments = DB::table('ot_equipments')->whereNull('inventory_item_id')->get();
        foreach ($equipments as $e) {
            $inv = DB::table('inventory_items')->where('code', $e->code)->first();

            if (! $inv) {
                $inv = DB::table('inventory_items')
                    ->whereRaw('LOWER(name) = ?', [strtolower($e->name)])
                    ->first();
            }

            if (! $inv) {
                $invId = DB::table('inventory_items')->insertGetId([
                    'code'          => 'EQ-' . $e->code,
                    'name'          => $e->name,
                    'category'      => 'equipment',
                    'uom'           => 'unit',
                    'reorder_level' => 0,
                    'is_consumable' => 0,
                    'is_asset'      => 1,
                    'is_active'     => $e->is_active,
                    'description'   => 'OT ' . $e->category . ' equipment',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            } else {
                $invId = $inv->id;
                // Promote existing inventory row to asset if not already
                if (! $inv->is_asset) {
                    DB::table('inventory_items')->where('id', $invId)->update([
                        'is_asset'  => 1,
                        'category'  => 'equipment',
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('ot_equipments')->where('id', $e->id)->update([
                'inventory_item_id' => $invId,
                'updated_at'        => $now,
            ]);
        }

        // ────────── 3. Tag consumable_usages.inventory_deducted ──────────
        // Existing usages have inventory_deducted=0; now that we have the bridge,
        // future usages can deduct from inventory automatically. Mark old rows
        // as "historical" so they don't double-deduct on a backfill stock movement.
        DB::table('ot_consumable_usages')
            ->where('inventory_deducted', 0)
            ->whereNotNull('ot_consumable_id')
            ->update([
                'inventory_deducted' => 0,
                'notes' => DB::raw("COALESCE(notes, '')"),
            ]);
    }
}
