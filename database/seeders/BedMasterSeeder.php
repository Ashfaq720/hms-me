<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\BedGroup;
use App\Models\BedType;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Real-world hospital bed master.
 *
 * Idempotent — re-running won't duplicate. Layout follows the typical
 * Bangladeshi multi-speciality hospital: 5 floors with wards, cabins,
 * ICU/CCU/NICU/PICU on dedicated upper floors, and ER on the ground.
 *
 * Run:  php artisan db:seed --class=BedMasterSeeder
 */
class BedMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding floors…');
        $floors = $this->seedFloors();

        $this->command->info('▶ Seeding rooms…');
        $rooms = $this->seedRooms($floors);

        $this->command->info('▶ Seeding bed types…');
        $types = $this->seedBedTypes();

        $this->command->info('▶ Seeding bed groups…');
        $groups = $this->seedBedGroups($rooms);

        $this->command->info('▶ Seeding beds…');
        $count = $this->seedBeds($groups, $types);

        $this->command->info("✓ Bed master seeded: {$count} beds across "
            . $groups->count() . ' groups, '
            . $rooms->count() . ' rooms, '
            . $floors->count() . ' floors.');
    }

    /* ───────── Floors ───────── */

    protected function seedFloors()
    {
        $names = [
            'Ground Floor', '1st Floor', '2nd Floor',
            '3rd Floor', '4th Floor', '5th Floor',
        ];
        $out = collect();
        foreach ($names as $n) {
            $out->put($n, Floor::firstOrCreate(['name' => $n], ['is_active' => 1]));
        }
        return $out;
    }

    /* ───────── Rooms ───────── */

    protected function seedRooms($floors)
    {
        $map = [
            'Ground Floor' => [
                'Emergency Room',
                'OPD Block A',
                'OPD Block B',
                'Reception / Triage',
                'OT Recovery Room',
            ],
            '1st Floor' => [
                'Pathology Lab',
                'Radiology',
                'Pharmacy',
                'Day Care Unit',
                'Dialysis Unit',
            ],
            '2nd Floor' => [
                'Male General Ward',
                'Female General Ward',
                'Pediatric Ward',
                'Isolation Room A',
            ],
            '3rd Floor' => [
                'Cabin Block A',
                'Cabin Block B',
                'Deluxe Cabin Wing',
                'VIP Suite',
            ],
            '4th Floor' => [
                'ICU Wing',
                'CCU Wing',
                'HDU Wing',
                'Burn Unit',
            ],
            '5th Floor' => [
                'NICU',
                'PICU',
                'Labor & Delivery Room',
                'Postnatal Ward',
                'Psychiatric Ward',
            ],
        ];

        $out = collect();
        foreach ($map as $floorName => $roomNames) {
            $floor = $floors->get($floorName);
            if (! $floor) continue;
            foreach ($roomNames as $rn) {
                $out->put($rn, Room::firstOrCreate(
                    ['name' => $rn, 'floor_id' => $floor->id],
                    ['is_active' => 1]
                ));
            }
        }
        return $out;
    }

    /* ───────── Bed Types ───────── */

    protected function seedBedTypes()
    {
        $defs = [
            // [name, is_icu, icu_type, has_ventilator, has_monitor, is_isolation, allowed_isolation_type]
            ['General Bed',         false, null,   false, false, false, null],
            ['Semi-Cabin',          false, null,   false, false, false, null],
            ['Cabin',               false, null,   false, true,  false, null],
            ['Deluxe Cabin',        false, null,   false, true,  false, null],
            ['VIP Cabin',           false, null,   false, true,  false, null],

            ['ICU Bed',             true,  'ICU',  true,  true,  false, null],
            ['CCU Bed',             true,  'CCU',  true,  true,  false, null],
            ['NICU Bed',            true,  'NICU', true,  true,  false, null],
            ['PICU Bed',            true,  'PICU', true,  true,  false, null],
            ['HDU Bed',             true,  'ICU',  false, true,  false, null],

            ['Emergency Bed',       false, null,   false, true,  false, null],
            ['OT Recovery Bed',     false, null,   false, true,  false, null],
            ['Isolation Bed',       false, null,   false, false, true,  'Contact'],
            ['Observation Bed',     false, null,   false, true,  false, null],
            ['Day Care Bed',        false, null,   false, false, false, null],
            ['Dialysis Bed',        false, null,   false, true,  false, null],

            ['Labor Bed',           false, null,   false, true,  false, null],
            ['Postnatal Bed',       false, null,   false, false, false, null],
            ['Burn Unit Bed',       false, null,   false, true,  true,  'Standard'],
            ['Psychiatric Bed',     false, null,   false, false, false, null],

            ['Incubator',           true,  'NICU', true,  true,  false, null],
            ['Warmer',              true,  'NICU', false, true,  false, null],
        ];

        $out = collect();
        foreach ($defs as [$name, $isIcu, $icuType, $vent, $mon, $iso, $isoType]) {
            $t = BedType::firstOrCreate(
                ['name' => $name],
                [
                    'is_icu'                  => $isIcu,
                    'icu_type'                => $icuType,
                    'has_ventilator_support'  => $vent,
                    'has_monitor_support'     => $mon,
                    'is_isolation_bed'        => $iso,
                    'allowed_isolation_type'  => $isoType,
                    'is_active'               => 1,
                ]
            );
            $out->put($name, $t);
        }
        return $out;
    }

    /* ───────── Bed Groups (Wards) ───────── */

    protected function seedBedGroups($rooms)
    {
        // group name => room name
        $defs = [
            'ER Triage Bay'         => 'Emergency Room',
            'ER Observation Bay'    => 'Emergency Room',
            'OT Recovery Bay'       => 'OT Recovery Room',
            'Day Care Bay'          => 'Day Care Unit',
            'Dialysis Bay'          => 'Dialysis Unit',

            'Male Ward Bay A'       => 'Male General Ward',
            'Male Ward Bay B'       => 'Male General Ward',
            'Female Ward Bay A'     => 'Female General Ward',
            'Female Ward Bay B'     => 'Female General Ward',
            'Pediatric Bay'         => 'Pediatric Ward',
            'Isolation Bay A'       => 'Isolation Room A',

            'Cabin Block A — Singles' => 'Cabin Block A',
            'Cabin Block B — Singles' => 'Cabin Block B',
            'Deluxe Cabin Bay'      => 'Deluxe Cabin Wing',
            'VIP Suite Bay'         => 'VIP Suite',

            'ICU Bay'               => 'ICU Wing',
            'CCU Bay'               => 'CCU Wing',
            'HDU Bay'               => 'HDU Wing',
            'Burn Unit Bay'         => 'Burn Unit',

            'NICU Bay'              => 'NICU',
            'NICU Incubator Bay'    => 'NICU',
            'NICU Warmer Bay'       => 'NICU',
            'PICU Bay'              => 'PICU',

            'Labor Bay'             => 'Labor & Delivery Room',
            'Postnatal Bay'         => 'Postnatal Ward',
            'Psychiatric Bay'       => 'Psychiatric Ward',
        ];

        $out = collect();
        foreach ($defs as $groupName => $roomName) {
            $room = $rooms->get($roomName);
            if (! $room) continue;
            $g = BedGroup::firstOrCreate(
                ['name' => $groupName, 'room_id' => $room->id],
                ['is_active' => 1]
            );
            $out->put($groupName, $g);
        }
        return $out;
    }

    /* ───────── Beds ───────── */

    protected function seedBeds($groups, $types): int
    {
        // [group, type, gender, count, rent, billing, oxygen, code_prefix]
        $plans = [
            ['ER Triage Bay',           'Emergency Bed',  'Any',      4,  1500,  'Hourly',  true,  'ER'],
            ['ER Observation Bay',      'Observation Bed','Any',      6,  1200,  'Daily',   true,  'ERO'],
            ['OT Recovery Bay',         'OT Recovery Bed','Any',      4,  2500,  'Daily',   true,  'OTR'],
            ['Day Care Bay',            'Day Care Bed',   'Any',      6,   800,  'Hourly',  false, 'DAY'],
            ['Dialysis Bay',            'Dialysis Bed',   'Any',      6,  3500,  'Hourly',  true,  'DIA'],

            ['Male Ward Bay A',         'General Bed',    'Male',     10,  1000, 'Daily',   false, 'GMW-A'],
            ['Male Ward Bay B',         'General Bed',    'Male',     10,  1000, 'Daily',   false, 'GMW-B'],
            ['Female Ward Bay A',       'General Bed',    'Female',   10,  1000, 'Daily',   false, 'GFW-A'],
            ['Female Ward Bay B',       'General Bed',    'Female',   10,  1000, 'Daily',   false, 'GFW-B'],
            ['Pediatric Bay',           'General Bed',    'Child',    8,   1200, 'Daily',   false, 'PED'],
            ['Isolation Bay A',         'Isolation Bed',  'Any',      4,   2500, 'Daily',   true,  'ISO'],

            ['Cabin Block A — Singles', 'Cabin',          'Any',      6,   3500, 'Daily',   true,  'CAB-A'],
            ['Cabin Block B — Singles', 'Cabin',          'Any',      6,   3500, 'Daily',   true,  'CAB-B'],
            ['Deluxe Cabin Bay',        'Deluxe Cabin',   'Any',      4,   5500, 'Daily',   true,  'DLX'],
            ['VIP Suite Bay',           'VIP Cabin',      'Any',      2,  12000, 'Daily',   true,  'VIP'],

            ['ICU Bay',                 'ICU Bed',        'Any',      8,   8000, 'Daily',   true,  'ICU'],
            ['CCU Bay',                 'CCU Bed',        'Any',      6,   7500, 'Daily',   true,  'CCU'],
            ['HDU Bay',                 'HDU Bed',        'Any',      6,   5000, 'Daily',   true,  'HDU'],
            ['Burn Unit Bay',           'Burn Unit Bed',  'Any',      4,   4500, 'Daily',   true,  'BUR'],

            ['NICU Bay',                'NICU Bed',       'Neonatal', 6,   7000, 'Daily',   true,  'NICU'],
            ['NICU Incubator Bay',      'Incubator',      'Neonatal', 4,   8500, 'Daily',   true,  'INC'],
            ['NICU Warmer Bay',         'Warmer',         'Neonatal', 3,   6500, 'Daily',   true,  'WRM'],
            ['PICU Bay',                'PICU Bed',       'Child',    6,   7500, 'Daily',   true,  'PICU'],

            ['Labor Bay',               'Labor Bed',      'Female',   6,   3500, 'Hourly',  true,  'LAB'],
            ['Postnatal Bay',           'Postnatal Bed',  'Female',   8,   2000, 'Daily',   false, 'PNT'],
            ['Psychiatric Bay',         'Psychiatric Bed','Any',      6,   2500, 'Daily',   false, 'PSY'],
        ];

        $total = 0;
        foreach ($plans as [$gName, $tName, $gender, $count, $rent, $billing, $oxy, $prefix]) {
            $group = $groups->get($gName);
            $type  = $types->get($tName);
            if (! $group || ! $type) continue;

            for ($i = 1; $i <= $count; $i++) {
                $code = sprintf('BED-%s-%03d', $prefix, $i);
                $name = $this->bedDisplayName($prefix, $i);

                $bed = Bed::firstOrCreate(
                    ['code' => $code],
                    [
                        'name'               => $name,
                        'bed_no'             => $name,           // human-readable: ER-01, ICU-03, …
                        'rent'               => $rent,
                        'bed_type_id'        => $type->id,
                        'bed_group_id'       => $group->id,
                        'room_id'            => $group->room_id, // derive from the group's room
                        'bed_gender'         => $gender,
                        'status'             => Bed::STATUS_AVAILABLE,
                        'is_reserved'        => false,
                        'billing_type'       => $billing,
                        'has_oxygen_support' => $oxy,
                        'is_active'          => true,
                    ]
                );

                // Backfill bed_no / room_id on legacy rows that pre-date this fix.
                if (empty($bed->bed_no) || empty($bed->room_id)) {
                    $bed->update([
                        'bed_no'  => $bed->bed_no ?? $name,
                        'room_id' => $bed->room_id ?? $group->room_id,
                    ]);
                }

                $total++;
            }
        }
        return $total;
    }

    protected function bedDisplayName(string $prefix, int $i): string
    {
        // Human-friendly bed name e.g. "ICU-01", "Cabin A-03"
        return sprintf('%s-%02d', $prefix, $i);
    }
}
