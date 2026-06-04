<?php

namespace Database\Seeders;

use App\Models\Bed;
use App\Models\BedGroup;
use App\Models\BedType;
use App\Models\Floor;
use App\Models\Package;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RealBedRoomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Enrich existing floors + add real floors
        $floors = [
            ['id' => 1, 'name' => 'Ground Floor', 'code' => 'GF',  'building' => 'Main Block',     'description' => 'Reception · OPD · Pharmacy · Emergency'],
            ['name'     => '1st Floor',          'code' => 'F1',  'building' => 'Main Block',     'description' => 'General Wards · Day Care'],
            ['name'     => '2nd Floor',          'code' => 'F2',  'building' => 'Main Block',     'description' => 'Cabin Block · VIP Suites'],
            ['name'     => '3rd Floor',          'code' => 'F3',  'building' => 'Critical Care Block', 'description' => 'ICU · CCU · HDU'],
            ['name'     => '4th Floor',          'code' => 'F4',  'building' => 'Critical Care Block', 'description' => 'OT · Recovery'],
            ['name'     => '5th Floor',          'code' => 'F5',  'building' => 'Maternity Wing', 'description' => 'NICU · Labour · Postnatal'],
        ];
        $floorIds = [];
        foreach ($floors as $i => $f) {
            if (isset($f['id'])) {
                $floor = Floor::find($f['id']);
                if ($floor) $floor->update($f);
                else $floor = Floor::create($f);
            } else {
                $floor = Floor::firstOrCreate(['name' => $f['name']], $f);
            }
            $floorIds[$i] = $floor->id;
        }

        // 2. Update existing bed types with base_rent + descriptions
        $bedTypes = [
            'General' => ['base_rent' => 1500, 'description' => 'Multi-bed general ward'],
            'Cabin'   => ['base_rent' => 4000, 'description' => 'Private cabin with attached amenities'],
            'ICU'     => ['base_rent' => 8000, 'description' => 'Intensive Care Unit'],
            'NICU'    => ['base_rent' => 6500, 'description' => 'Neonatal Intensive Care'],
            'CCU'     => ['base_rent' => 7500, 'description' => 'Coronary Care Unit'],
            'HDU'     => ['base_rent' => 5500, 'description' => 'High Dependency Unit'],
            'Deluxe'  => ['base_rent' => 7000, 'description' => 'Deluxe cabin with sofa-cum-bed'],
            'VIP Suite' => ['base_rent' => 15000, 'description' => 'Premium suite with lounge'],
            'Isolation' => ['base_rent' => 5000, 'description' => 'Negative-pressure isolation room'],
        ];
        foreach ($bedTypes as $name => $extra) {
            BedType::firstOrCreate(['name' => $name], array_merge(['is_active' => true], $extra))
                   ->update($extra);
        }

        // 3. Bed Groups (Ward/Wings)
        $groups = [
            // [floor_index, name, code, group_type, gender_preference, notes]
            [1, 'Ward A - Male',     'WA-M', 'ward',         'male',   'Male general ward (10 beds)'],
            [1, 'Ward B - Female',   'WB-F', 'ward',         'female', 'Female general ward (10 beds)'],
            [1, 'Day Care',          'DC',   'day_care',     'any',    'Day-care procedures'],
            [2, 'Cabin Block A',     'CBA',  'cabin_block',  'any',    'Private cabins'],
            [2, 'Deluxe Block',      'DXB',  'cabin_block',  'any',    'Deluxe rooms'],
            [2, 'VIP Suite Block',   'VIP',  'cabin_block',  'any',    'Premium suites'],
            [3, 'ICU Wing',          'ICUW', 'icu_wing',     'any',    'Intensive care'],
            [3, 'CCU Wing',          'CCUW', 'ccu_wing',     'any',    'Coronary care'],
            [3, 'HDU',               'HDU',  'icu_wing',     'any',    'High-dependency unit'],
            [3, 'Isolation Block',   'ISO',  'isolation',    'any',    'Negative-pressure isolation'],
            [4, 'Recovery Room',     'REC',  'recovery',     'any',    'Post-op recovery'],
            [5, 'NICU Wing',         'NICU', 'nicu_wing',    'any',    'Newborn intensive care'],
            [5, 'Maternity Ward',    'MAT',  'maternity',    'female', 'Postnatal'],
        ];
        $groupIds = [];
        foreach ($groups as [$fi, $name, $code, $gt, $gen, $notes]) {
            $bg = BedGroup::firstOrCreate(
                ['name' => $name],
                ['floor_id' => $floorIds[$fi], 'code' => $code, 'group_type' => $gt, 'gender_preference' => $gen, 'notes' => $notes, 'is_active' => true]
            );
            $bg->update(['floor_id' => $floorIds[$fi], 'code' => $code, 'group_type' => $gt, 'gender_preference' => $gen, 'notes' => $notes, 'is_active' => true]);
            $groupIds[$name] = $bg->id;
        }

        // 4. Rooms (real entries)
        $type = fn ($n) => BedType::where('name', $n)->value('id');

        $rooms = [
            // Ward A Male (general)
            ['Ward A - Male',   '101', 'general',      6, 1500, ['has_oxygen_outlet' => true]],
            ['Ward A - Male',   '102', 'general',      6, 1500, ['has_oxygen_outlet' => true]],
            // Ward B Female
            ['Ward B - Female', '103', 'general',      6, 1500, ['has_oxygen_outlet' => true]],
            ['Ward B - Female', '104', 'general',      6, 1500, ['has_oxygen_outlet' => true]],
            // Day Care
            ['Day Care',        '110', 'general',      4, 1200, []],
            // Cabin Block A (private)
            ['Cabin Block A',   '201', 'private_cabin', 1, 4000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true]],
            ['Cabin Block A',   '202', 'private_cabin', 1, 4000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true]],
            ['Cabin Block A',   '203', 'private_cabin', 1, 4000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true]],
            ['Cabin Block A',   '204', 'private_cabin', 1, 4000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true]],
            // Deluxe
            ['Deluxe Block',    '210', 'deluxe',        1, 7000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true, 'has_fridge' => true, 'has_sofa_cum_bed' => true]],
            ['Deluxe Block',    '211', 'deluxe',        1, 7000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true, 'has_fridge' => true, 'has_sofa_cum_bed' => true]],
            // VIP Suite
            ['VIP Suite Block', '220', 'vvip_suite',    1, 15000, ['has_ac' => true, 'has_attached_bath' => true, 'has_tv' => true, 'has_fridge' => true, 'has_sofa_cum_bed' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // ICU
            ['ICU Wing',        'ICU-1', 'icu',         1, 8000, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['ICU Wing',        'ICU-2', 'icu',         1, 8000, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['ICU Wing',        'ICU-3', 'icu',         1, 8000, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['ICU Wing',        'ICU-4', 'icu',         1, 8000, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // CCU
            ['CCU Wing',        'CCU-1', 'ccu',         1, 7500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['CCU Wing',        'CCU-2', 'ccu',         1, 7500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // HDU
            ['HDU',             'HDU-1', 'general',     2, 5500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // Isolation
            ['Isolation Block', 'ISO-1', 'isolation',   1, 5000, ['has_ac' => true, 'has_attached_bath' => true, 'has_oxygen_outlet' => true]],
            // Recovery
            ['Recovery Room',   'REC-1', 'recovery',    4, 2500, ['has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // NICU
            ['NICU Wing',       'NICU-1', 'nicu',       1, 6500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['NICU Wing',       'NICU-2', 'nicu',       1, 6500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            ['NICU Wing',       'NICU-3', 'nicu',       1, 6500, ['has_ac' => true, 'has_oxygen_outlet' => true, 'has_central_monitor' => true]],
            // Maternity
            ['Maternity Ward',  '501', 'maternity',     4, 3000, ['has_ac' => true, 'has_attached_bath' => true]],
            ['Maternity Ward',  '502', 'maternity',     4, 3000, ['has_ac' => true, 'has_attached_bath' => true]],
        ];

        $createdRoomIds = [];
        foreach ($rooms as [$groupName, $roomNo, $class, $cap, $rent, $amenities]) {
            if (! isset($groupIds[$groupName])) continue;
            $bg = BedGroup::find($groupIds[$groupName]);
            $room = Room::updateOrCreate(
                ['bed_group_id' => $bg->id, 'room_no' => $roomNo],
                array_merge([
                    'floor_id'   => $bg->floor_id,
                    'room_class' => $class,
                    'capacity'   => $cap,
                    'room_rent'  => $rent,
                    'is_active'  => true,
                ], $amenities)
            );
            $createdRoomIds[$roomNo] = $room->id;
        }

        // 5. Beds — populate per room
        $bedSeed = [
            // [room_no, bed_type_name, bed_no, rent, amenity, nursing, status, package_lookup]
            ['101', 'General', '01', 1500, 0, 200, 'available', null],
            ['101', 'General', '02', 1500, 0, 200, 'occupied',  null],
            ['101', 'General', '03', 1500, 0, 200, 'available', null],
            ['101', 'General', '04', 1500, 0, 200, 'available', null],
            ['101', 'General', '05', 1500, 0, 200, 'cleaning',  null],
            ['101', 'General', '06', 1500, 0, 200, 'available', null],
            ['102', 'General', '01', 1500, 0, 200, 'available', null],
            ['102', 'General', '02', 1500, 0, 200, 'available', null],
            ['103', 'General', '01', 1500, 0, 200, 'occupied',  null],
            ['104', 'General', '01', 1500, 0, 200, 'available', null],
            ['201', 'Cabin',  '01', 4000, 500, 500, 'occupied', 'CABIN'],
            ['202', 'Cabin',  '01', 4000, 500, 500, 'available', 'CABIN'],
            ['203', 'Cabin',  '01', 4000, 500, 500, 'available', 'CABIN'],
            ['204', 'Cabin',  '01', 4000, 500, 500, 'reserved',  'CABIN'],
            ['210', 'Deluxe', '01', 7000, 1000, 700, 'available', 'DELUXE'],
            ['211', 'Deluxe', '01', 7000, 1000, 700, 'available', 'DELUXE'],
            ['220', 'VIP Suite', '01', 15000, 2000, 1500, 'available', 'VIP'],
            ['ICU-1', 'ICU', '01', 8000, 0, 1000, 'occupied',  'ICU'],
            ['ICU-2', 'ICU', '01', 8000, 0, 1000, 'available', 'ICU'],
            ['ICU-3', 'ICU', '01', 8000, 0, 1000, 'available', 'ICU'],
            ['ICU-4', 'ICU', '01', 8000, 0, 1000, 'maintenance','ICU'],
            ['CCU-1', 'CCU', '01', 7500, 0, 900, 'available', 'CCU'],
            ['CCU-2', 'CCU', '01', 7500, 0, 900, 'available', 'CCU'],
            ['HDU-1', 'HDU', '01', 5500, 0, 700, 'available', null],
            ['HDU-1', 'HDU', '02', 5500, 0, 700, 'available', null],
            ['ISO-1', 'Isolation', '01', 5000, 0, 800, 'available', null],
            ['REC-1', 'General', '01', 2500, 0, 500, 'available', null],
            ['REC-1', 'General', '02', 2500, 0, 500, 'available', null],
            ['NICU-1', 'NICU', '01', 6500, 0, 1000, 'occupied', 'NICU'],
            ['NICU-2', 'NICU', '01', 6500, 0, 1000, 'available', 'NICU'],
            ['NICU-3', 'NICU', '01', 6500, 0, 1000, 'available', 'NICU'],
            ['501', 'General', '01', 3000, 0, 500, 'available', 'MATERNITY'],
            ['501', 'General', '02', 3000, 0, 500, 'available', 'MATERNITY'],
            ['502', 'General', '01', 3000, 0, 500, 'available', 'MATERNITY'],
            ['502', 'General', '02', 3000, 0, 500, 'available', 'MATERNITY'],
        ];

        // Package keyword lookup for default_package_id mapping
        $pkgByKeyword = function (string $kw): ?int {
            $patterns = [
                'CABIN'     => ['code' => null, 'name' => 'cabin', 'type' => null],
                'DELUXE'    => ['code' => null, 'name' => 'deluxe', 'type' => null],
                'VIP'       => ['code' => null, 'name' => 'vip', 'type' => null],
                'ICU'       => ['code' => null, 'name' => null, 'type' => 'ICU'],
                'CCU'       => ['code' => null, 'name' => null, 'type' => 'CCU'],
                'NICU'      => ['code' => null, 'name' => null, 'type' => 'NICU'],
                'MATERNITY' => ['code' => null, 'name' => null, 'type' => 'MATERNITY'],
            ];
            $rule = $patterns[$kw] ?? null;
            if (! $rule) return null;

            $q = Package::query()->where('is_active', true);
            if ($rule['type']) $q->where('package_type', $rule['type']);
            if ($rule['name']) $q->where('name', 'like', '%' . $rule['name'] . '%');
            return $q->value('id');
        };

        foreach ($bedSeed as [$roomNo, $btName, $bedNo, $rent, $amenity, $nursing, $status, $pkgKw]) {
            if (! isset($createdRoomIds[$roomNo])) continue;
            $room = Room::find($createdRoomIds[$roomNo]);
            $btId = $type($btName);
            if (! $btId) continue;

            $pkgId = $pkgKw ? $pkgByKeyword($pkgKw) : null;
            $bedName = $roomNo . '-' . $bedNo;

            Bed::updateOrCreate(
                ['name' => $bedName],
                [
                    'bed_no'             => $bedNo,
                    'rent'               => $rent,
                    'amenity_charge'     => $amenity,
                    'nursing_charge'     => $nursing,
                    'bed_type_id'        => $btId,
                    'bed_group_id'       => $room->bed_group_id,
                    'room_id'            => $room->id,
                    'is_active'          => true,
                    'is_reserved'        => $status === 'reserved' ? 1 : 0,
                    'status'             => $status,
                    'default_package_id' => $pkgId,
                ]
            );
        }
    }
}
