<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillOtGapsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $userId = DB::table('users')->value('id');

        // Approved surgery requests waiting for schedule
        $requests = DB::table('ot_surgery_requests')
            ->whereNotIn('id', DB::table('ot_surgery_schedules')->pluck('surgery_request_id'))
            ->limit(8)
            ->get();

        $rooms = DB::table('ot_rooms')->where('is_active', 1)->pluck('id')->all();
        if (empty($rooms)) return;
        $consumables = DB::table('ot_consumables')->whereNotNull('inventory_item_id')->get();

        $nextId = (int) DB::table('ot_surgery_schedules')->max('id') + 1;
        $statuses = ['Scheduled', 'In Progress', 'Completed', 'In Recovery', 'Cancelled'];

        foreach ($requests as $i => $r) {
            $start = $now->copy()->subDays(rand(0, 30))->setTime(rand(8, 17), [0, 30][rand(0, 1)]);
            $end   = $start->copy()->addMinutes($r->estimated_duration_minutes ?: 120);
            $status = $statuses[$i % count($statuses)];

            $scheduleId = DB::table('ot_surgery_schedules')->insertGetId([
                'schedule_no'          => 'SCH-' . str_pad($nextId++, 6, '0', STR_PAD_LEFT),
                'surgery_request_id'   => $r->id,
                'ot_room_id'           => $rooms[array_rand($rooms)],
                'scheduled_start'      => $start,
                'scheduled_end'        => $end,
                'buffer_minutes'       => 30,
                'actual_start'         => in_array($status, ['Completed', 'In Progress', 'In Recovery']) ? $start : null,
                'actual_end'           => in_array($status, ['Completed', 'In Recovery']) ? $end : null,
                'status'               => $status,
                'emergency_fast_track' => $r->is_emergency ?? 0,
                'created_by'           => $userId,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            // For completed surgeries, log 3-5 consumable usages each
            if (in_array($status, ['Completed', 'In Recovery']) && $consumables->isNotEmpty()) {
                $picks = $consumables->random(min(rand(3, 5), $consumables->count()));
                foreach ($picks as $c) {
                    $qty = rand(1, 4);
                    DB::table('ot_consumable_usages')->insert([
                        'surgery_schedule_id' => $scheduleId,
                        'ot_consumable_id'    => $c->id,
                        'item_name'           => $c->name,
                        'item_code'           => $c->code,
                        'type'                => $c->type,
                        'quantity'            => $qty,
                        'unit'                => $c->unit,
                        'rate'                => $c->rate,
                        'amount'              => $qty * $c->rate,
                        'is_billed'           => true,
                        'inventory_deducted'  => false, // observer will fire if model is used, but raw insert won't trigger
                        'notes'               => 'Seeded post-op usage',
                        'recorded_by'         => $userId,
                        'used_at'             => $start->copy()->addMinutes(rand(15, 60)),
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);

                    // Manually create stock_movement (since raw insert skips the observer)
                    $whId = DB::table('inventory_warehouses')->where('is_active', 1)->value('id');
                    if ($whId && $c->inventory_item_id) {
                        DB::table('stock_movements')->insert([
                            'inventory_item_id' => $c->inventory_item_id,
                            'warehouse_id'      => $whId,
                            'direction'         => 'out',
                            'quantity'          => $qty,
                            'unit_cost'         => $c->rate,
                            'reason'            => 'OT consumable usage (seeded)',
                            'source_type'       => 'App\\Models\\Ot\\OtConsumableUsage',
                            'source_id'         => DB::getPdo()->lastInsertId(),
                            'reference_no'      => 'OT-USE-SEED',
                            'remarks'           => 'Backfill from seeded usage',
                            'performed_by'      => $userId,
                            'performed_at'      => $start,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]);
                    }
                }
            }
        }
    }
}
