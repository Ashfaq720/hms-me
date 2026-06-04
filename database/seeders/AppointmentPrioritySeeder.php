<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the appointment_priorities lookup table used by the appointment
 * booking modal's "Priority" dropdown. Idempotent — re-running reuses
 * existing rows by name.
 */
class AppointmentPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $defs = [
            ['Normal',      1],
            ['Urgent',      1],
            ['Emergency',   1],
            ['Follow-up',   1],
            ['Walk-in',     1],
            ['VIP',         1],
        ];

        $count = 0;
        foreach ($defs as [$name, $active]) {
            DB::table('appointment_priorities')->updateOrInsert(
                ['name' => $name],
                ['is_active' => $active, 'created_at' => $now, 'updated_at' => $now]
            );
            $count++;
        }

        $this->command->info("✓ Appointment priorities seeded: {$count}.");
    }
}
