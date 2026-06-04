<?php

namespace Database\Seeders;

use App\Models\Charges\Charge;
use App\Models\Charges\ChargeCategory;
use App\Models\Doctor;
use App\Models\DoctorSlotSetting;
use App\Models\DoctorSlotTime;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the full doctor slot / shift management chain:
 *   1. shifts (Morning / Afternoon / Evening / Night)
 *   2. doctor_shift (each doctor assigned to 2 shifts)
 *   3. doctor_slot_settings (consultation_minutes + fee)
 *   4. doctor_slot_times (7-day grid: M-F + Saturday morning)
 */
class DoctorSlotSeeder extends Seeder
{
    private array $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    private array $weekend  = ['Saturday'];

    public function run(): void
    {
        $now = now();

        // ─── 1. SHIFTS ─────────────────────────────────────────
        $shifts = [
            ['Morning',   '08:00:00', '13:00:00'],
            ['Afternoon', '14:00:00', '18:00:00'],
            ['Evening',   '18:00:00', '22:00:00'],
            ['Night',     '22:00:00', '08:00:00'],
        ];
        foreach ($shifts as [$name, $from, $to]) {
            Shift::updateOrCreate(
                ['name' => $name],
                ['time_from' => $from, 'time_to' => $to, 'is_active' => true]
            );
        }
        $morning   = Shift::where('name', 'Morning')->first();
        $afternoon = Shift::where('name', 'Afternoon')->first();
        $evening   = Shift::where('name', 'Evening')->first();

        // ─── 2. CHARGES — link consultation fee ─────────────────
        $consultCat = ChargeCategory::firstOrCreate(['name' => 'Specialist Consultation'], ['status' => 1]);
        $consultChg = Charge::where('charge_name', 'like', '%Consultation%')->first();

        // ─── 3. ASSIGN EACH DOCTOR ─────────────────────────────
        $doctors = Doctor::orderBy('id')->get();
        $assigned = 0;
        $settingsCreated = 0;
        $timesCreated = 0;

        foreach ($doctors as $i => $doc) {
            // Alternating: even doctors get Morning + Evening, odd get Afternoon + Evening
            $doctorShifts = ($i % 2 === 0)
                ? [$morning, $evening]
                : [$afternoon, $evening];

            foreach ($doctorShifts as $shift) {
                if (! $shift) continue;

                // 3a. doctor_shift M:N assignment
                DB::table('doctor_shift')->updateOrInsert(
                    ['doctor_id' => $doc->id, 'shift_id' => $shift->id],
                    ['created_at' => $now, 'updated_at' => $now]
                );
                $assigned++;

                // 3b. doctor_slot_settings — consultation duration + fee
                $fee = $consultChg
                    ? (float) $consultChg->standard_charge
                    : ($i % 2 === 0 ? 800 : 1000);
                DoctorSlotSetting::updateOrCreate(
                    ['doctor_id' => $doc->id, 'shift_id' => $shift->id],
                    [
                        'consultation_minutes' => 15,
                        'charge_category_id'   => $consultCat->id,
                        'charge_id'            => $consultChg?->id,
                        'amount'               => $fee,
                    ]
                );
                $settingsCreated++;

                // 3c. doctor_slot_times — 7-day grid (Mon-Fri or Saturday)
                // Clear any pre-existing for clean re-seed
                DoctorSlotTime::where('doctor_id', $doc->id)
                    ->where('shift_id', $shift->id)->delete();

                // Generate 30-min slots between shift.time_from and shift.time_to
                $start = strtotime($shift->time_from);
                $end   = strtotime($shift->time_to);
                if ($end < $start) $end += 86400; // wrap past midnight (Night shift)

                $days = ($shift->name === 'Night')
                    ? ['Saturday', 'Sunday']                // Night = weekend
                    : array_merge($this->weekdays, ['Saturday']);

                foreach ($days as $day) {
                    // 1-3 contiguous slot blocks per day, 1-2 hour blocks
                    $cursor = $start;
                    $blockCount = 0;
                    while ($cursor + 3600 <= $end && $blockCount < 3) {
                        $blockMinutes = [60, 90, 120][rand(0, 2)];
                        $blockEnd = min($cursor + $blockMinutes * 60, $end);
                        DoctorSlotTime::create([
                            'doctor_id' => $doc->id,
                            'shift_id'  => $shift->id,
                            'day'       => $day,
                            'time_from' => date('H:i:s', $cursor),
                            'time_to'   => date('H:i:s', $blockEnd),
                        ]);
                        $timesCreated++;
                        $cursor = $blockEnd + 1800; // 30 min break between blocks
                        $blockCount++;
                    }
                }
            }
        }

        $this->command->info("✓ Shifts:                  " . Shift::count());
        $this->command->info("✓ Doctor↔Shift links:      " . $assigned);
        $this->command->info("✓ Doctor slot settings:    " . $settingsCreated);
        $this->command->info("✓ Doctor slot time blocks: " . $timesCreated);
    }
}
