<?php

namespace Database\Seeders;

use App\Models\Ot\OtAnesthesiaType;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryType;
use Illuminate\Database\Seeder;

class OtSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRooms();
        $this->seedEquipments();
        $this->seedCategories();
        $this->seedAnesthesiaTypes();
        $this->seedSurgeryTypes();
        $this->seedConsumables();

        $this->command->info('OT master data seeded.');
    }

    protected function seedRooms(): void
    {
        $rooms = [
            ['code' => 'OT-01', 'name' => 'Major OT 1', 'type' => 'Major', 'is_emergency' => false],
            ['code' => 'OT-02', 'name' => 'Major OT 2', 'type' => 'Major', 'is_emergency' => false],
            ['code' => 'OT-03', 'name' => 'Minor OT', 'type' => 'Minor', 'is_emergency' => false],
            ['code' => 'OT-ER', 'name' => 'Emergency OT', 'type' => 'Emergency', 'is_emergency' => true],
            ['code' => 'OT-DC', 'name' => 'Day Care OT', 'type' => 'Day Care', 'is_emergency' => false],
        ];

        foreach ($rooms as $r) {
            OtRoom::firstOrCreate(
                ['code' => $r['code']],
                array_merge($r, [
                    'block' => 'Surgical Block',
                    'is_active' => true,
                    'status' => 'available',
                ])
            );
        }
    }

    protected function seedEquipments(): void
    {
        $items = [
            ['code' => 'EQ-ANES-01', 'name' => 'Anesthesia Workstation', 'category' => 'Anesthesia'],
            ['code' => 'EQ-MON-01', 'name' => 'Patient Monitor (5-para)', 'category' => 'Monitoring'],
            ['code' => 'EQ-MON-02', 'name' => 'Patient Monitor (Multi-para)', 'category' => 'Monitoring'],
            ['code' => 'EQ-VEN-01', 'name' => 'Mechanical Ventilator', 'category' => 'Ventilation'],
            ['code' => 'EQ-DIA-01', 'name' => 'Diathermy / Electrocautery', 'category' => 'Surgical'],
            ['code' => 'EQ-LIG-01', 'name' => 'OT Light (LED)', 'category' => 'Lighting'],
            ['code' => 'EQ-TBL-01', 'name' => 'Operating Table', 'category' => 'Furniture'],
            ['code' => 'EQ-SUC-01', 'name' => 'Suction Unit', 'category' => 'Surgical'],
            ['code' => 'EQ-LAP-01', 'name' => 'Laparoscopy Tower', 'category' => 'Endoscopy'],
            ['code' => 'EQ-CARM-01', 'name' => 'C-Arm', 'category' => 'Imaging'],
            ['code' => 'EQ-DEFIB-01', 'name' => 'Defibrillator', 'category' => 'Emergency'],
        ];

        foreach ($items as $e) {
            OtEquipment::firstOrCreate(
                ['code' => $e['code']],
                array_merge($e, ['status' => 'available', 'is_active' => true])
            );
        }
    }

    protected function seedCategories(): void
    {
        foreach ([
            ['name' => 'Major Surgery', 'code' => 'MAJOR'],
            ['name' => 'Minor Surgery', 'code' => 'MINOR'],
            ['name' => 'Day Care', 'code' => 'DAYCARE'],
            ['name' => 'Emergency', 'code' => 'EMERGENCY'],
            ['name' => 'Endoscopy', 'code' => 'ENDO'],
        ] as $c) {
            OtSurgeryCategory::firstOrCreate(['name' => $c['name']], $c + ['is_active' => true]);
        }
    }

    protected function seedAnesthesiaTypes(): void
    {
        foreach ([
            ['name' => 'General Anesthesia', 'code' => 'GA'],
            ['name' => 'Spinal Anesthesia', 'code' => 'SA'],
            ['name' => 'Epidural Anesthesia', 'code' => 'EA'],
            ['name' => 'Local Anesthesia', 'code' => 'LA'],
            ['name' => 'Regional Block', 'code' => 'RB'],
            ['name' => 'MAC Sedation', 'code' => 'MAC'],
        ] as $a) {
            OtAnesthesiaType::firstOrCreate(['name' => $a['name']], $a + ['is_active' => true]);
        }
    }

    protected function seedSurgeryTypes(): void
    {
        $major = OtSurgeryCategory::where('code', 'MAJOR')->first()?->id;
        $minor = OtSurgeryCategory::where('code', 'MINOR')->first()?->id;
        $daycare = OtSurgeryCategory::where('code', 'DAYCARE')->first()?->id;
        $endo = OtSurgeryCategory::where('code', 'ENDO')->first()?->id;

        $types = [
            ['name' => 'Appendectomy', 'code' => 'APPDX', 'category_id' => $major, 'standard_duration_minutes' => 90, 'standard_charge' => 20000, 'surgeon_charge' => 8000, 'anesthesia_charge' => 4000, 'ot_room_charge' => 5000, 'recovery_charge' => 1500],
            ['name' => 'Cholecystectomy (Lap)', 'code' => 'LAPCHOLE', 'category_id' => $major, 'standard_duration_minutes' => 120, 'standard_charge' => 40000, 'surgeon_charge' => 18000, 'anesthesia_charge' => 6000, 'ot_room_charge' => 8000, 'recovery_charge' => 2500],
            ['name' => 'C-Section', 'code' => 'CS', 'category_id' => $major, 'standard_duration_minutes' => 60, 'standard_charge' => 35000, 'surgeon_charge' => 15000, 'anesthesia_charge' => 5000, 'ot_room_charge' => 7000, 'recovery_charge' => 2000],
            ['name' => 'Hernia Repair', 'code' => 'HERNIA', 'category_id' => $major, 'standard_duration_minutes' => 75, 'standard_charge' => 25000, 'surgeon_charge' => 10000, 'anesthesia_charge' => 4000, 'ot_room_charge' => 5500, 'recovery_charge' => 1500],
            ['name' => 'Tonsillectomy', 'code' => 'TONS', 'category_id' => $minor, 'standard_duration_minutes' => 45, 'standard_charge' => 12000, 'surgeon_charge' => 5000, 'anesthesia_charge' => 2000, 'ot_room_charge' => 3000, 'recovery_charge' => 1000],
            ['name' => 'Circumcision', 'code' => 'CIRC', 'category_id' => $minor, 'standard_duration_minutes' => 30, 'standard_charge' => 8000, 'surgeon_charge' => 3500, 'anesthesia_charge' => 1500, 'ot_room_charge' => 2000, 'recovery_charge' => 500],
            ['name' => 'Cataract Surgery', 'code' => 'CATARACT', 'category_id' => $daycare, 'standard_duration_minutes' => 30, 'standard_charge' => 18000, 'surgeon_charge' => 9000, 'anesthesia_charge' => 1500, 'ot_room_charge' => 3500, 'recovery_charge' => 1000],
            ['name' => 'Colonoscopy', 'code' => 'COLO', 'category_id' => $endo, 'standard_duration_minutes' => 45, 'standard_charge' => 15000, 'surgeon_charge' => 6000, 'anesthesia_charge' => 2500, 'ot_room_charge' => 3000, 'recovery_charge' => 1500],
        ];

        foreach ($types as $t) {
            OtSurgeryType::firstOrCreate(['name' => $t['name']], $t + ['is_active' => true]);
        }
    }

    protected function seedConsumables(): void
    {
        $items = [
            ['name' => 'Surgical Gloves (Sterile)', 'code' => 'CON-GLV-01', 'type' => 'consumable', 'unit' => 'pair', 'rate' => 80, 'current_stock' => 500, 'reorder_level' => 50],
            ['name' => 'Surgical Mask N95', 'code' => 'CON-MSK-01', 'type' => 'consumable', 'unit' => 'pc', 'rate' => 40, 'current_stock' => 800, 'reorder_level' => 100],
            ['name' => 'Surgical Gown (Disposable)', 'code' => 'CON-GWN-01', 'type' => 'consumable', 'unit' => 'pc', 'rate' => 150, 'current_stock' => 200, 'reorder_level' => 30],
            ['name' => 'Surgical Drape Set', 'code' => 'CON-DRP-01', 'type' => 'consumable', 'unit' => 'set', 'rate' => 400, 'current_stock' => 100, 'reorder_level' => 20],
            ['name' => 'Suture - Vicryl 3-0', 'code' => 'CON-SUT-01', 'type' => 'consumable', 'unit' => 'pc', 'rate' => 350, 'current_stock' => 150, 'reorder_level' => 20],
            ['name' => 'Suture - Silk 2-0', 'code' => 'CON-SUT-02', 'type' => 'consumable', 'unit' => 'pc', 'rate' => 200, 'current_stock' => 150, 'reorder_level' => 20],
            ['name' => 'Gauze Pack', 'code' => 'CON-GAZ-01', 'type' => 'consumable', 'unit' => 'pack', 'rate' => 60, 'current_stock' => 400, 'reorder_level' => 50],
            ['name' => 'IV Cannula 18G', 'code' => 'CON-IVC-01', 'type' => 'consumable', 'unit' => 'pc', 'rate' => 50, 'current_stock' => 300, 'reorder_level' => 50],
            ['name' => 'Hernia Mesh (Prolene)', 'code' => 'IMP-MSH-01', 'type' => 'implant', 'unit' => 'pc', 'rate' => 3500, 'current_stock' => 20, 'reorder_level' => 5, 'is_implant' => true],
            ['name' => 'Hip Replacement Set', 'code' => 'IMP-HIP-01', 'type' => 'implant', 'unit' => 'set', 'rate' => 85000, 'current_stock' => 5, 'reorder_level' => 2, 'is_implant' => true],
            ['name' => 'Scalpel Handle #4', 'code' => 'INS-SCP-01', 'type' => 'instrument', 'unit' => 'pc', 'rate' => 800, 'current_stock' => 30, 'reorder_level' => 5],
            ['name' => 'Mosquito Forceps', 'code' => 'INS-FOR-01', 'type' => 'instrument', 'unit' => 'pc', 'rate' => 600, 'current_stock' => 40, 'reorder_level' => 8],
            ['name' => 'Propofol 20ml', 'code' => 'MED-PRO-01', 'type' => 'medicine', 'unit' => 'vial', 'rate' => 250, 'current_stock' => 80, 'reorder_level' => 15],
            ['name' => 'Lignocaine 2%', 'code' => 'MED-LIG-01', 'type' => 'medicine', 'unit' => 'vial', 'rate' => 60, 'current_stock' => 120, 'reorder_level' => 20],
            ['name' => 'Atropine 0.6mg', 'code' => 'MED-ATR-01', 'type' => 'medicine', 'unit' => 'amp', 'rate' => 30, 'current_stock' => 200, 'reorder_level' => 30],
        ];

        foreach ($items as $i) {
            OtConsumable::firstOrCreate(
                ['code' => $i['code']],
                array_merge($i, ['is_active' => true])
            );
        }
    }
}
