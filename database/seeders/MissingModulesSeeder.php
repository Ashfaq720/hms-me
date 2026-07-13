<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fills the modules the legacy seeders never populated:
 *   - Pharmacy masters (medicine_categories / units / generics / companies / medical_groups / suppliers)
 *   - Organization + branches + a small employee roster
 *   - A handful of demo lab orders (pathology + radiology) tied to existing OPD encounters
 *   - A handful of demo bills with line items per OPD/IPD encounter
 *
 * Idempotent — every insert uses firstOrCreate / updateOrInsert.
 */
class MissingModulesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $this->seedPharmacyMasters($now);
        $this->seedMedicineBatches($now);
        $org   = $this->seedOrganization($now);
        $main  = $this->seedBranches($org, $now);
        $this->seedEmployees($org, $main, $now);
        $this->seedDemoLabOrders($now);
        $this->seedDemoBills($org, $main, $now);

        $this->command->info('✓ Missing modules seeded.');
    }

    /**
     * One stocked batch per active medicine so the Pharmacy Transaction
     * `getMedicineQty` endpoint and the store() pricing logic both return
     * a real unit_price + available qty.
     */
    protected function seedMedicineBatches($now): void
    {
        $count = 0;
        DB::table('medicines')->where('status', 1)->orderBy('id')->get()->each(function ($m) use ($now, &$count) {
            $base = 5 + ($m->id * 1.5);
            DB::table('medicine_batches')->updateOrInsert(
                ['medicine_id' => $m->id, 'batch_no' => 'BATCH-' . $m->id . '-A', 'store' => 'Main Pharmacy'],
                [
                    'expiry_date'      => now()->addYears(2)->toDateString(),
                    'manufacture_date' => now()->subMonths(6)->toDateString(),
                    'purchase_price'   => round($base, 2),
                    'selling_price'    => round($base * 1.4, 2),
                    'quantity'         => max(50, $m->available_qty ?? 100),
                    'status'           => 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
            $count++;
        });
        $this->command->line("  • Medicine batches seeded: {$count}");
    }

    protected function seedPharmacyMasters($now): void
    {
        $categories = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops', 'Inhaler', 'Suppository'];
        foreach ($categories as $n) {
            DB::table('medicine_categories')->updateOrInsert(
                ['name' => $n], ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $units = ['mg', 'ml', 'IU', 'mcg', 'g', 'piece', 'strip', 'bottle', 'vial'];
        foreach ($units as $n) {
            DB::table('medicine_units')->updateOrInsert(
                ['name' => $n], ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $generics = [
            'Paracetamol', 'Amoxicillin', 'Azithromycin', 'Ciprofloxacin', 'Metronidazole',
            'Omeprazole', 'Ranitidine', 'Metformin', 'Glibenclamide', 'Atorvastatin',
            'Amlodipine', 'Losartan', 'Aspirin', 'Ibuprofen', 'Diclofenac',
            'Salbutamol', 'Cetirizine', 'Loratadine', 'Insulin Glargine', 'Heparin',
        ];
        foreach ($generics as $n) {
            DB::table('medicine_generics')->updateOrInsert(
                ['name' => $n], ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $companies = ['Square Pharma', 'Beximco', 'Incepta', 'Renata', 'ACI', 'Sanofi', 'GSK', 'Pfizer'];
        foreach ($companies as $n) {
            DB::table('companies')->updateOrInsert(
                ['name' => $n], ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $groups = ['Antibiotic', 'Analgesic', 'Antipyretic', 'Antihypertensive', 'Antidiabetic',
            'Antihistamine', 'Bronchodilator', 'PPI', 'Statin', 'Anticoagulant'];
        foreach ($groups as $n) {
            DB::table('medical_groups')->updateOrInsert(
                ['name' => $n], ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $suppliers = [
            ['Medilink Distribution', '+880-2-9876543', 'Mr Karim', '+880-1700-111000', 'DL-MED-001'],
            ['HealthCare Suppliers', '+880-2-1234567', 'Mr Hasan', '+880-1700-222000', 'DL-HEL-002'],
            ['BD Pharma Trade', '+880-2-7654321', 'Ms Sumi', '+880-1700-333000', 'DL-BDP-003'],
        ];
        foreach ($suppliers as [$name, $tel, $person, $personTel, $lic]) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_name' => $name],
                [
                    'contact_supplier'         => $tel,
                    'contact_person_name'      => $person,
                    'contact_person_telephone' => $personTel,
                    'drug_license_number'      => $lic,
                    'address'                  => 'Dhaka, Bangladesh',
                    'status'                   => 1,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ]
            );
        }
    }

    protected function seedOrganization($now): int
    {
        // Reuse existing row when present
        $existing = DB::table('organizations')->where('code', 'HMS-MAIN')->first();
        if ($existing) return $existing->id;

        return DB::table('organizations')->insertGetId([
            'code'             => 'HMS-MAIN',
            'name'             => 'HMS Demo Hospital',
            'legal_name'       => 'HMS Demo Hospital Ltd.',
            'contact_email'    => 'admin@hms.local',
            'contact_phone'    => '+880-2-0000000',
            'tax_number'       => 'TIN-000000',
            'country'          => 'BD',
            'timezone'         => 'Asia/Dhaka',
            'default_currency' => 'BDT',
            'is_active'        => 1,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    protected function seedBranches(int $orgId, $now): int
    {
        $defs = [
            ['MAIN', 'Main Branch — Dhaka',  'main',     'M', 'IM', 'HC-M'],
            ['CTG',  'Chittagong Branch',    'satellite','C', 'IC', 'HC-C'],
        ];
        $mainId = 0;
        foreach ($defs as [$code, $name, $type, $mrnP, $invP, $hcP]) {
            $existing = DB::table('branches')->where('code', $code)->first();
            if ($existing) {
                if ($code === 'MAIN') $mainId = $existing->id;
                continue;
            }
            $id = DB::table('branches')->insertGetId([
                'organization_id'    => $orgId,
                'code'               => $code,
                'name'               => $name,
                'type'               => $type,
                'city'               => $code === 'MAIN' ? 'Dhaka' : 'Chittagong',
                'country'            => 'BD',
                'phone'              => '+880-2-1112223',
                'email'              => strtolower($code) . '@hms.local',
                'mrn_prefix'         => $mrnP,
                'invoice_prefix'     => $invP,
                'health_card_prefix' => $hcP,
                'is_active'          => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
            if ($code === 'MAIN') $mainId = $id;
        }
        return $mainId;
    }

    protected function seedEmployees(int $orgId, int $branchId, $now): void
    {
        // Pull the 11 demo doctors that already exist and create employee records.
        $doctors = DB::table('doctors')->select('id','user_id','name','department_id')->get();
        $i = 1;
        foreach ($doctors as $doc) {
            $code = 'EMP-DOC-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            DB::table('employees')->updateOrInsert(
                ['employee_code' => $code],
                [
                    'organization_id'  => $orgId,
                    'branch_id'        => $branchId,
                    'user_id'          => $doc->user_id,
                    'first_name'       => Str::before($doc->name, ' ') ?: $doc->name,
                    'last_name'        => Str::contains($doc->name, ' ') ? Str::after($doc->name, ' ') : null,
                    'department_id'    => $doc->department_id,
                    'staff_type'       => 'doctor',
                    'employment_type'  => 'visiting',
                    'status'           => 'active',
                    'base_salary'      => 0,
                    'joining_date'     => now()->subYears(2)->toDateString(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
            $i++;
        }

        // A small non-doctor roster
        $extra = [
            ['Aminul',  'Islam',  'nurse',          'full_time', 35000],
            ['Sabina',  'Akter',  'nurse',          'full_time', 32000],
            ['Roni',    'Kabir',  'receptionist',   'full_time', 22000],
            ['Tania',   'Sultana','cashier',        'full_time', 24000],
            ['Mahmud',  'Rahman', 'pharmacist',     'full_time', 28000],
            ['Sumi',    'Begum',  'lab_technician', 'full_time', 26000],
        ];
        foreach ($extra as [$first, $last, $type, $emp, $sal]) {
            $code = 'EMP-' . strtoupper(substr($type, 0, 3)) . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            DB::table('employees')->updateOrInsert(
                ['employee_code' => $code],
                [
                    'organization_id'  => $orgId,
                    'branch_id'        => $branchId,
                    'first_name'       => $first,
                    'last_name'        => $last,
                    'staff_type'       => $type,
                    'employment_type'  => $emp,
                    'status'           => 'active',
                    'base_salary'      => $sal,
                    'joining_date'     => now()->subYear()->toDateString(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
            $i++;
        }
    }

    protected function seedDemoLabOrders($now): void
    {
        $opd = DB::table('opd_patients')->limit(3)->get(['id','patient_id','doctor_id']);
        if ($opd->isEmpty()) return;

        $pathType = DB::table('lab_investigation_types')->where('name', 'Pathology')->value('id');
        $radType  = DB::table('lab_investigation_types')->where('name', 'Radiology')->value('id');
        $someInv  = DB::table('lab_investigations')->limit(6)->get(['id','category_id']);
        if ($someInv->count() < 2) return;

        $orderIdx = 1;
        foreach ($opd as $row) {
            foreach (['pathology' => $pathType, 'radiology' => $radType] as $type => $typeId) {
                if (! $typeId) continue;
                $orderNo = sprintf('LAB-%s-%04d', strtoupper(substr($type, 0, 3)), $orderIdx++);
                $existing = DB::table('lab_investigation_order')->where('order_number', $orderNo)->first();
                if ($existing) continue;

                $orderId = DB::table('lab_investigation_order')->insertGetId([
                    'order_number' => $orderNo,
                    'opd_id'       => $row->id,
                    'patient_id'   => $row->patient_id,
                    'doctor_id'    => $row->doctor_id,
                    'datetime'     => now()->subDays(rand(0, 5)),
                    'remarks'      => "Demo $type order",
                    'priority'     => 'Regular',
                    'source'       => 'OPD',
                    'lab_name'     => 'In-House Lab',
                    'type'         => $type,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                // 2 random tests per order from the right type
                $tests = $someInv->random(min(2, $someInv->count()));
                foreach ($tests as $inv) {
                    DB::table('lab_investigation_order_request')->insert([
                        'lab_inv_order_id'    => $orderId,
                        'lab_inv_id'          => $inv->id,
                        'lab_inv_type_id'     => $typeId,
                        'lab_inv_category_id' => $inv->category_id,
                        'status'              => 'Pending',
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }
        }
    }

    protected function seedDemoBills(int $orgId, int $branchId, $now): void
    {
        $opd = DB::table('opd_patients')->limit(3)->get(['id','patient_id']);
        $ipd = DB::table('i_p_d_patients')->limit(2)->get(['id','patient_id']);

        $idx = 1;
        $mkBill = function (string $type, $row) use ($orgId, $branchId, $now, &$idx) {
            $billNo = sprintf('INV-%s-%04d', date('Y'), $idx++);
            $existing = DB::table('bills')->where('bill_no', $billNo)->first();
            if ($existing) return;

            $sub = rand(500, 5000);
            $tax = round($sub * 0.05, 2);
            $tot = $sub + $tax;
            $paid = ($idx % 2 === 0) ? $tot : round($tot / 2, 2);
            $billId = DB::table('bills')->insertGetId([
                'organization_id' => $orgId,
                'branch_id'       => $branchId,
                'patient_id'      => $row->patient_id,
                'bill_no'         => $billNo,
                'bill_type'       => $type,
                'status'          => $paid >= $tot ? 'paid' : 'partially_paid',
                'bill_date'       => now()->toDateString(),
                'subtotal'        => $sub,
                'discount_total'  => 0,
                'tax_total'       => $tax,
                'grand_total'     => $tot,
                'paid_total'      => $paid,
                'refund_total'    => 0,
                'balance_due'     => max(0, $tot - $paid),
                'notes'           => "Demo $type bill",
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            DB::table('bill_items')->insert([
                [
                    'bill_id'             => $billId,
                    'description'         => 'Consultation Fee',
                    'item_type'           => 'consultation',
                    'quantity'            => 1,
                    'unit_price'          => round($sub * 0.4, 2),
                    'discount_amount'     => 0,
                    'tax_percent'         => 5,
                    'tax_amount'          => round($sub * 0.4 * 0.05, 2),
                    'line_total'          => round($sub * 0.4, 2),
                    'is_package_included' => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                [
                    'bill_id'             => $billId,
                    'description'         => 'Diagnostic Charge',
                    'item_type'           => 'diagnostic',
                    'quantity'            => 1,
                    'unit_price'          => round($sub * 0.6, 2),
                    'discount_amount'     => 0,
                    'tax_percent'         => 5,
                    'tax_amount'          => round($sub * 0.6 * 0.05, 2),
                    'line_total'          => round($sub * 0.6, 2),
                    'is_package_included' => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
            ]);
        };

        foreach ($opd as $r) $mkBill('opd', $r);
        foreach ($ipd as $r) $mkBill('ipd', $r);
    }
}
