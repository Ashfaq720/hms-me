<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyProcessSeeder extends Seeder
{
    public function run(): void
    {
        $now    = now();
        $userId = DB::table('users')->value('id');

        // ──────────────────────────────────────
        // 1. COMPANIES & MEDICAL GROUPS — real BD pharma
        // ──────────────────────────────────────
        $companies = ['Square Pharmaceuticals', 'Beximco Pharmaceuticals', 'Incepta Pharmaceuticals',
                      'Renata Limited', 'Healthcare Pharmaceuticals', 'ACI Pharmaceuticals',
                      'ACME Laboratories', 'Eskayef Bangladesh', 'Drug International', 'Opsonin Pharma'];
        foreach ($companies as $c) {
            DB::table('companies')->updateOrInsert(['name' => $c], [
                'status' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $groups = ['Tablet & Capsule', 'Injection / IV', 'Syrup & Suspension', 'Topical / Cream',
                   'Inhaler / Nebule', 'Eye / Ear Drops', 'Rectal / Vaginal', 'IV Fluid'];
        foreach ($groups as $g) {
            DB::table('medical_groups')->updateOrInsert(['name' => $g], [
                'status' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ──────────────────────────────────────
        // 2. UPDATE medicines with company + group
        // ──────────────────────────────────────
        $companyIds = DB::table('companies')->pluck('id')->all();
        $groupIds   = DB::table('medical_groups')->pluck('id')->all();
        DB::table('medicines')
            ->whereNull('company_id')
            ->update(['company_id' => $companyIds[0] ?? null]);
        DB::table('medicines')
            ->whereNull('medical_group_id')
            ->update(['medical_group_id' => $groupIds[0] ?? null]);

        // ──────────────────────────────────────
        // 3. MEDICINE BATCHES (each med gets 2 batches with expiry dates)
        // ──────────────────────────────────────
        $medicines = DB::table('medicines')->get();
        foreach ($medicines as $m) {
            if (DB::table('medicine_batches')->where('medicine_id', $m->id)->exists()) continue;
            $rate = 0;
            if ($m->inventory_item_id) {
                $rate = (float) DB::table('inventory_items')->where('id', $m->inventory_item_id)->value('tax_percent') ?: 5;
            }
            // Batch 1 — current stock
            DB::table('medicine_batches')->insert([
                'medicine_id'      => $m->id,
                'batch_no'         => 'BAT-' . str_pad($m->id, 4, '0', STR_PAD_LEFT) . '-A',
                'manufacture_date' => now()->subMonths(3)->toDateString(),
                'expiry_date'      => now()->addMonths(rand(8, 24))->toDateString(),
                'purchase_price'   => $rate * 0.7,
                'selling_price'    => $rate,
                'quantity'         => rand(80, 200),
                'store'            => 'Main Pharmacy',
                'status'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            // Batch 2 — newer stock
            DB::table('medicine_batches')->insert([
                'medicine_id'      => $m->id,
                'batch_no'         => 'BAT-' . str_pad($m->id, 4, '0', STR_PAD_LEFT) . '-B',
                'manufacture_date' => now()->subMonths(1)->toDateString(),
                'expiry_date'      => now()->addMonths(rand(18, 36))->toDateString(),
                'purchase_price'   => $rate * 0.7,
                'selling_price'    => $rate,
                'quantity'         => rand(50, 150),
                'store'            => 'Main Pharmacy',
                'status'           => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ──────────────────────────────────────
        // 4. PRESCRIPTION_MEDICINES — fill the gap (159 rx, only 1 line item)
        // ──────────────────────────────────────
        $medicineIds = $medicines->pluck('id')->all();
        $dosages     = ['1+0+1', '1+1+1', '0+0+1', '1+0+0', '2+0+2'];
        $frequencies = ['Twice daily', 'Three times daily', 'Once at night', 'Once in morning', 'q8h'];
        $durations   = ['3 days', '5 days', '7 days', '10 days', '14 days'];

        $rxWithoutItems = DB::table('prescriptions')
            ->whereNotIn('id', DB::table('presciption_medicines')->pluck('prescription_id'))
            ->limit(80)
            ->get();
        foreach ($rxWithoutItems as $rx) {
            $count = rand(2, 4);
            $used = [];
            for ($i = 0; $i < $count; $i++) {
                $mid = $medicineIds[array_rand($medicineIds)];
                if (in_array($mid, $used)) continue;
                $used[] = $mid;
                DB::table('presciption_medicines')->insert([
                    'prescription_id' => $rx->id,
                    'medicine_id'     => $mid,
                    'dosage'          => $dosages[array_rand($dosages)],
                    'frequency'       => $frequencies[array_rand($frequencies)],
                    'duration'        => $durations[array_rand($durations)],
                    'note'            => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }

        // ──────────────────────────────────────
        // 5. PHARMACY TRANSACTIONS (actual dispensing sales)
        // ──────────────────────────────────────
        $patients = DB::table('patients')->inRandomOrder()->limit(30)->pluck('id')->all();
        $opdIds   = DB::table('opd_patients')->inRandomOrder()->limit(15)->pluck('id')->all();
        $ipdIds   = DB::table('i_p_d_patients')->inRandomOrder()->limit(10)->pluck('id')->all();
        $batches  = DB::table('medicine_batches')->get();

        for ($i = 0; $i < 25; $i++) {
            $type = ['opd', 'ipd', 'otc'][$i % 3];
            $patientId = $patients[$i % count($patients)];
            $opdId     = ($type === 'opd') ? ($opdIds[$i % count($opdIds)] ?? null) : null;
            $ipdId     = ($type === 'ipd') ? ($ipdIds[$i % count($ipdIds)] ?? null) : null;

            // 2-4 line items per transaction
            $lineCount = rand(2, 4);
            $total     = 0;
            $items     = [];
            for ($j = 0; $j < $lineCount; $j++) {
                $batch = $batches->random();
                $med   = $medicines->where('id', $batch->medicine_id)->first();
                if (! $med) continue;
                $qty   = rand(1, 10);
                $price = (float) $batch->selling_price ?: rand(10, 100);
                $sub   = $qty * $price;
                $total += $sub;
                $items[] = [
                    'medicine_id'   => $batch->medicine_id,
                    'batch_id'      => $batch->id,
                    'dosage'        => $dosages[array_rand($dosages)],
                    'duration'      => $durations[array_rand($durations)],
                    'qty_required'  => $qty,
                    'available_qty' => $batch->quantity,
                    'unit_price'    => $price,
                    'subtotal'      => $sub,
                    'store'         => 'Main Pharmacy',
                ];
            }

            if (empty($items)) continue;
            $paid = $i % 4 === 0 ? $total * 0.6 : $total;  // 25% partial
            $tNo  = 'PHR-' . now()->format('YmdHis') . '-' . ($i + 1);

            $txId = DB::table('pharmacy_transactions')->insertGetId([
                'transaction_no'   => $tNo,
                'transaction_type' => $type,
                'patient_id'       => $patientId,
                'pharmacist_id'    => $userId,
                'drug_count'       => count($items),
                'total_amount'     => $total,
                'discount_amount'  => 0,
                'paid_amount'      => $paid,
                'payment_method'   => ['cash', 'card', 'mobile_banking'][$i % 3],
                'payment_status'   => $paid >= $total ? 'paid' : 'partial',
                'status'           => 'completed',
                'opd_patient_id'   => $opdId,
                'ipd_patient_id'   => $ipdId,
                'request_source'   => $type === 'otc' ? 'WALK_IN' : 'CLINICAL',
                'created_at'       => now()->subDays(rand(0, 14)),
                'updated_at'       => $now,
            ]);

            foreach ($items as $row) {
                DB::table('pharmacy_transaction_items')->insert(array_merge($row, [
                    'transaction_id' => $txId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]));

                // Deduct stock — create stock_movement (skip if no inventory link)
                $invId = DB::table('medicines')->where('id', $row['medicine_id'])->value('inventory_item_id');
                if ($invId) {
                    $whId = DB::table('inventory_warehouses')->where('is_active', 1)->value('id');
                    if ($whId) {
                        // compute running balance for this item
                        $prevBal = (float) DB::table('stock_movements')
                            ->where('inventory_item_id', $invId)
                            ->selectRaw("COALESCE(SUM(CASE WHEN direction='in' THEN quantity ELSE -quantity END), 0) as bal")
                            ->value('bal');
                        $newBal = $prevBal - $row['qty_required'];
                        DB::table('stock_movements')->insert([
                            'inventory_item_id' => $invId,
                            'warehouse_id'      => $whId,
                            'direction'         => 'out',
                            'quantity'          => $row['qty_required'],
                            'unit_cost'         => $row['unit_price'],
                            'balance_after'     => $newBal,
                            'reason'            => 'pharmacy_dispense',
                            'source_type'       => 'App\\Models\\Pharmacy\\PharmacyTransaction',
                            'source_id'         => $txId,
                            'reference_no'      => $tNo,
                            'remarks'           => 'Auto-deduct from pharmacy sale',
                            'performed_by'      => $userId,
                            'performed_at'      => $now,
                            'created_at'        => $now,
                            'updated_at'        => $now,
                        ]);
                    }
                }
            }
        }

        // ──────────────────────────────────────
        // 6. CONTROLLED DRUGS — regulatory dispense register entries
        // ──────────────────────────────────────
        $controlled = DB::table('medicines')
            ->where(function ($q) {
                $q->where('medicine_name', 'like', '%Insulin%')
                  ->orWhere('medicine_name', 'like', '%Morphine%')
                  ->orWhere('medicine_name', 'like', '%Pethidine%');
            })
            ->get();

        foreach ($controlled as $i => $m) {
            if (DB::table('controlled_drugs')->where('medicine_id', $m->id)->exists()) continue;
            DB::table('controlled_drugs')->insert([
                'entry_no'         => 'CD-' . now()->format('Ymd') . '-' . str_pad($m->id, 3, '0', STR_PAD_LEFT),
                'entry_date'       => now()->subDays($i)->toDateString(),
                'doctor_name'      => DB::table('doctors')->value('name') ?: 'Dr. Demo',
                'dea_number'       => 'BDEA-' . rand(10000, 99999),
                'medicine_id'      => $m->id,
                'generic_name'     => DB::table('medicine_generics')->value('name') ?: 'Insulin',
                'lot_number'       => 'LOT-' . rand(1000, 9999),
                'schedule'         => 'II',
                'expiration_date'  => now()->addMonths(18)->toDateString(),
                'action_type'      => 'removed',
                'quantity'         => rand(1, 10),
                'unit'             => 'vial',
                'inventory_status' => 'available',
                'notes'            => 'Seeded controlled-drug log',
                'created_by'       => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
