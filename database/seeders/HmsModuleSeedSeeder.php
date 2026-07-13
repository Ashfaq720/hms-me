<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Minimum demo rows for every module screen so the UI is never blank-empty.
 * Idempotent — uses count checks before inserting.
 */
class HmsModuleSeedSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAppointments();
        $this->seedServices();
        $this->seedPackages();
        $this->seedCharges();
        $this->seedBloodBank();
        $this->seedMedicines();
        $this->seedAmbulance();
        $this->seedOtMasters();
        $this->command?->info('HMS module demo data seeded.');
    }

    private function seedAppointments(): void
    {
        // Skipped — the appointments table has many NOT NULL columns that
        // the existing AppointmentController populates from a multi-step
        // form. Use the UI at /appointments to create demo appointments.
    }

    private function seedServices(): void
    {
        if (! Schema::hasTable('services') || DB::table('services')->count() > 0) return;

        foreach ([
            ['Consultation',  500],
            ['Dressing',      200],
            ['Injection',     150],
            ['ECG',           600],
            ['Nebulization',  300],
        ] as [$name, $rate]) {
            DB::table('services')->insert([
                'name' => $name,
                'description' => $name . ' service',
                'quantity' => 1,
                'rate' => $rate,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPackages(): void
    {
        if (! Schema::hasTable('packages') || DB::table('packages')->count() > 0) return;
        DB::table('packages')->insert([
            ['name' => 'Basic Health Checkup',  'discount' => 10, 'total_amount' => 2500,  'description' => 'CBC + Sugar + Lipid + Doctor review', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C-Section Package',     'discount' => 5,  'total_amount' => 45000, 'description' => 'OT room + Surgeon + Anesthesia + 3-day bed + nursing', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Executive Health Plan', 'discount' => 15, 'total_amount' => 8000,  'description' => 'Comprehensive yearly executive health checkup', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedCharges(): void
    {
        if (! Schema::hasTable('charge_categories') || ! Schema::hasTable('charge_types') || ! Schema::hasTable('unite_types') || ! Schema::hasTable('charges')) return;
        if (DB::table('charges')->count() > 0) return;

        $typeId = DB::table('charge_types')->where('name', 'OPD')->value('id')
            ?? DB::table('charge_types')->insertGetId(['name' => 'OPD', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $catId = DB::table('charge_categories')->where('name', 'Procedure')->value('id')
            ?? DB::table('charge_categories')->insertGetId(['name' => 'Procedure', 'charge_type_id' => $typeId, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $unitId = DB::table('unite_types')->where('name', 'Per Use')->value('id')
            ?? DB::table('unite_types')->insertGetId(['name' => 'Per Use', 'created_at' => now(), 'updated_at' => now()]);

        $taxId = Schema::hasTable('tax_categories')
            ? (DB::table('tax_categories')->where('name', 'Zero')->value('id')
                ?? DB::table('tax_categories')->insertGetId(['name' => 'Zero', 'percentage' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]))
            : null;

        DB::table('charges')->insert([
            'charge_type_id' => $typeId,
            'charge_category_id' => $catId,
            'unite_type_id' => $unitId,
            'tax_category_id' => $taxId,
            'charge_name' => 'General Consultation',
            'tax' => 0,
            'standard_charge' => 500,
            'description' => 'Standard OPD consultation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedBloodBank(): void
    {
        if (Schema::hasTable('blood_groups') && DB::table('blood_groups')->count() === 0) {
            foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $code) {
                $abo = rtrim($code, '+-');
                $rh = str_ends_with($code, '+') ? 'POS' : 'NEG';
                DB::table('blood_groups')->insert([
                    'code' => $code,
                    'abo_group' => $abo,
                    'rh_factor' => $rh,
                    'display_name' => $code,
                    'is_active' => 1,
                    'is_locked' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('components') && DB::table('components')->count() === 0) {
            foreach ([
                ['WB', 'Whole Blood', 35, 'DAYS'],
                ['PRBC', 'Packed Red Blood Cells', 42, 'DAYS'],
                ['FFP', 'Fresh Frozen Plasma', 365, 'DAYS'],
                ['PLT', 'Platelet Concentrate', 5, 'DAYS'],
            ] as [$code, $name, $shelfVal, $shelfUnit]) {
                DB::table('components')->insert([
                    'component_code' => $code,
                    'component_name' => $name,
                    'shelf_life_value' => $shelfVal,
                    'shelf_life_unit' => $shelfUnit,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedMedicines(): void
    {
        if (! Schema::hasTable('medicines') || DB::table('medicines')->count() > 0) return;

        $unitId = Schema::hasTable('medicine_units')
            ? DB::table('medicine_units')->value('id')
            : null;
        // medicine_unit_id is required FK on medicines — if no unit exists, skip seeding.
        if (! $unitId) {
            return;
        }

        foreach (['Paracetamol 500mg', 'Amoxicillin 500mg', 'Omeprazole 20mg', 'Metformin 500mg', 'Atorvastatin 20mg'] as $name) {
            DB::table('medicines')->insert([
                'medicine_name' => $name,
                'medicine_unit_id' => $unitId,
                'available_qty' => 100,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAmbulance(): void
    {
        if (! Schema::hasTable('amb_ambulances') || DB::table('amb_ambulances')->count() > 0) return;

        DB::table('amb_ambulances')->insert([
            'reg_no' => 'DHK-AMB-001',
            'type' => 'BLS',
            'ownership' => 'HOSPITAL',
            'stretcher_capacity' => 1,
            'attendants_capacity' => 2,
            'oxygen_capacity' => 1,
            'status' => 'AVAILABLE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedOtMasters(): void
    {
        if (Schema::hasTable('ot_rooms') && DB::table('ot_rooms')->count() === 0) {
            DB::table('ot_rooms')->insert([
                ['code' => 'OT-1', 'name' => 'OT Room 1', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'OT-2', 'name' => 'OT Room 2', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // OT Consumables (master)
        if (Schema::hasTable('ot_consumables') && DB::table('ot_consumables')->count() === 0) {
            foreach ([
                ['SUT-2-0',  'Suture 2/0 Silk',         'suture',   'pc',   150,  100, 0],
                ['GLV-S',    'Surgical Gloves Sterile', 'glove',    'pair', 30,   500, 0],
                ['IV-CANN',  'IV Cannula 18G',          'cannula',  'pc',   45,   200, 0],
                ['SY-10ML',  'Syringe 10ml',            'syringe',  'pc',   12,   300, 0],
                ['IMPL-MESH','Surgical Mesh',           'implant',  'pc',   3500, 20,  1],
                ['DRP-SET',  'IV Drip Set',             'drip',     'pc',   80,   150, 0],
            ] as [$code, $name, $type, $unit, $rate, $stock, $isImplant]) {
                DB::table('ot_consumables')->insert([
                    'code' => $code, 'name' => $name, 'type' => $type, 'unit' => $unit,
                    'rate' => $rate, 'current_stock' => $stock, 'reorder_level' => 50,
                    'is_implant' => $isImplant, 'is_active' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // OT Equipment
        if (Schema::hasTable('ot_equipments') && DB::table('ot_equipments')->count() === 0) {
            $room1 = DB::table('ot_rooms')->where('code', 'OT-1')->value('id');
            $room2 = DB::table('ot_rooms')->where('code', 'OT-2')->value('id');
            foreach ([
                ['EQ-LIGHT-1','Surgical Light LED',  'lighting',   $room1, 'SL-001'],
                ['EQ-TABLE-1','OT Table Hydraulic',  'table',      $room1, 'OT-T-001'],
                ['EQ-MONI-1', 'Vital Monitor',       'monitoring', $room1, 'VM-001'],
                ['EQ-ANES-1', 'Anesthesia Machine',  'anesthesia', $room2, 'AM-001'],
                ['EQ-CAUT-1', 'Electrocautery Unit', 'cautery',    $room2, 'EC-001'],
            ] as [$code, $name, $cat, $room, $serial]) {
                DB::table('ot_equipments')->insert([
                    'code' => $code, 'name' => $name, 'category' => $cat,
                    'ot_room_id' => $room, 'serial_no' => $serial,
                    'status' => 'available', 'is_active' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // ICU + CCU Equipment — enums use TitleCase
        if (Schema::hasTable('icu_equipment') && DB::table('icu_equipment')->count() === 0) {
            foreach ([
                ['VENT-001','Mechanical Ventilator','Ventilator',   'ICU','V-001', 'ICU-Bay-1','Hour',   250],
                ['MON-001', 'Cardiac Monitor',      'Monitor',      'ICU','M-001', 'ICU-Bay-1','Day',    500],
                ['INF-001', 'Infusion Pump',        'InfusionPump', 'ICU','IP-001','ICU-Bay-2','Day',    200],
                ['SYR-001', 'Syringe Pump',         'SyringePump',  'ICU','SP-001','ICU-Bay-3','Day',    180],
                ['VENT-002','Cardiac Ventilator',   'Ventilator',   'CCU','V-002', 'CCU-Bay-1','Hour',   280],
                ['MON-002', 'CCU Cardiac Monitor',  'Monitor',      'CCU','M-002', 'CCU-Bay-1','Day',    550],
                ['ECG-001', 'ECG Machine',          'ECG',          'CCU','E-001', 'CCU-Bay-2','Session',150],
            ] as [$code, $name, $type, $icuType, $serial, $loc, $chargeType, $rate]) {
                DB::table('icu_equipment')->insert([
                    'equipment_code' => $code, 'equipment_name' => $name,
                    'equipment_type' => $type, 'icu_type' => $icuType,
                    'serial_no' => $serial, 'status' => 'Available',
                    'location' => $loc, 'charge_type' => $chargeType,
                    'charge_rate' => $rate, 'is_active' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // Stock movements for the unified ledger so it has visible rows
        if (Schema::hasTable('stock_movements') && DB::table('stock_movements')->count() < 5) {
            $orgId = DB::table('organizations')->value('id');
            $branchId = DB::table('branches')->value('id');
            $wh = DB::table('inventory_warehouses')->first();
            if ($wh) {
                foreach (DB::table('inventory_items')->limit(5)->get(['id', 'name']) as $item) {
                    try {
                        app(\App\Services\Inventory\StockLedgerService::class)->receive([
                            'organization_id' => $orgId, 'branch_id' => $branchId,
                            'inventory_item_id' => $item->id, 'warehouse_id' => $wh->id,
                            'quantity' => 100, 'unit_cost' => 5,
                            'reason' => 'opening',
                            'batch_no' => 'OPEN-' . $item->id,
                            'expiry_date' => now()->addYear()->toDateString(),
                        ]);
                    } catch (\Throwable $e) {}
                }
            }
        }
    }
}
