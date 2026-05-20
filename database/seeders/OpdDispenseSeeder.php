<?php

namespace Database\Seeders;

use App\Models\OpdPatient;
use App\Models\Pharmacy\OpdDispense;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpdDispenseSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacists = User::take(5)->pluck('id')->toArray();
        $statuses = ['completed', 'completed', 'completed', 'pending_approval', 'partial', 'cancelled'];
        $paymentStatuses = ['paid', 'paid', 'paid', 'unpaid', 'partial', 'unpaid'];

        // Get OPD patients with their prescriptions
        $opdPatients = OpdPatient::with('patient')->get();
        $prescriptions = Prescription::whereNotNull('opd_patient_id')->get()->keyBy('opd_patient_id');

        $counter = OpdDispense::max('id') ?? 0;

        foreach ($opdPatients as $opd) {
            if (!$opd->patient) {
                continue;
            }

            $counter++;
            $prescription = $prescriptions->get($opd->id);
            $statusIdx = array_rand($statuses);

            OpdDispense::create([
                'dispense_no'      => 'ODPD-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                'opd_patient_id'   => $opd->id,
                'prescription_id'  => $prescription?->id,
                'patient_id'       => $opd->patient_id,
                'pharmacist_id'    => $pharmacists[array_rand($pharmacists)],
                'drug_count'       => rand(1, 5),
                'total_amount'     => rand(50, 800) + (rand(0, 99) / 100),
                'payment_status'   => $paymentStatuses[$statusIdx],
                'status'           => $statuses[$statusIdx],
                'created_at'       => now()->subDays(rand(0, 14))->setTime(rand(8, 17), rand(0, 59)),
            ]);
        }

        // Add a few more recent dispenses for today's stats
        $recentOpds = $opdPatients->take(4);
        foreach ($recentOpds as $opd) {
            if (!$opd->patient) {
                continue;
            }

            $counter++;
            $prescription = $prescriptions->get($opd->id);

            OpdDispense::create([
                'dispense_no'      => 'ODPD-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                'opd_patient_id'   => $opd->id,
                'prescription_id'  => $prescription?->id,
                'patient_id'       => $opd->patient_id,
                'pharmacist_id'    => $pharmacists[array_rand($pharmacists)],
                'drug_count'       => rand(1, 4),
                'total_amount'     => rand(100, 500) + (rand(0, 99) / 100),
                'payment_status'   => 'paid',
                'status'           => 'completed',
                'created_at'       => now()->setTime(rand(8, 16), rand(0, 59)),
            ]);
        }
    }
}
