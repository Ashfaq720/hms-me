<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Populates the empty operational tables so dashboard, reports, and
 * workflows have realistic data to display.
 *
 *  • Appointments (today + this week, mixed statuses)
 *  • OPD patient visits (today + last 7 days)
 *  • ER patient arrivals (today, last 24h)
 *  • Prescriptions (against OPD visits)
 *  • Transactions (payments against existing patient_charges)
 *
 * Idempotent: counts rows first, skips tables that already have data.
 * Run: php artisan db:seed --class=OperationalDataSeeder
 */
class OperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('▶ Seeding operational data…');

        $patients  = Patient::pluck('id')->all();
        $doctorIds = Doctor::pluck('id')->all();

        if (empty($patients) || empty($doctorIds)) {
            $this->command->warn('Need patients and doctors first. Run DoctorSeeder + EndToEndSampleSeeder.');
            return;
        }

        $this->seedAppointments($patients, $doctorIds);
        $this->seedOpdVisits($patients, $doctorIds);
        $this->seedErArrivals($patients, $doctorIds);
        $this->seedPrescriptions();
        $this->seedTransactions();
        $this->seedNicuAdmissions();
        $this->seedOtSchedules();

        $this->command->info('✓ Operational data seeded.');
    }

    /* ───────── NICU admissions ───────── */
    protected function seedNicuAdmissions(): void
    {
        if (! Schema::hasTable('nicu_admissions')) { $this->command->warn('  · nicu_admissions table missing — skipped'); return; }
        if (DB::table('nicu_admissions')->count() > 0) { $this->command->line('  · nicu_admissions already has data — skipped'); return; }

        $ipds = IpdPatient::limit(3)->get();
        if ($ipds->isEmpty()) { $this->command->line('  · no IPD patients to attach NICU admissions — skipped'); return; }

        $cols = Schema::getColumnListing('nicu_admissions');
        $rows = [];
        foreach ($ipds as $i => $ipd) {
            $base = [
                'ipd_patient_id'     => $ipd->id,
                'patient_id'         => $ipd->patient_id,
                'baby_id'            => $ipd->patient_id, // baby's patient row (same as mother for seed)
                'source'             => 'IPD_TRANSFER',
                'birth_type'         => $i % 2 ? 'C_SECTION' : 'NORMAL',
                'is_multiple_birth'  => 0,
                'birth_order'        => 1,
                'birth_weight_g'     => 2400 + $i * 200,
                'birth_length_cm'    => 47 + $i,
                'head_circumference_cm' => 33 + $i,
                'gestational_age_weeks' => 36 + $i,
                'apgar_1min'         => 7,
                'apgar_5min'         => 9,
                'admission_priority' => 'ROUTINE',
                'is_low_birth_weight'=> $i === 0 ? 1 : 0,
                'is_preterm'         => $i === 0 ? 1 : 0,
                'is_critical'        => 0,
                'status'             => 'ADMITTED',
                'admission_time'     => now()->subDays($i)->subHours(2),
                'admission_notes'    => 'Routine NICU monitoring — vitals stable',
                'admitted_by'        => 1,
                'created_at'         => now(), 'updated_at' => now(),
            ];
            // Only keep keys that exist in the schema (handles drift gracefully)
            $rows[] = array_intersect_key($base, array_flip($cols));
        }
        DB::table('nicu_admissions')->insert($rows);
        $this->command->line('  · '.count($rows).' NICU admissions inserted');
    }

    /* ───────── OT schedules (depends on existing OtSurgeryRequest rows) ───────── */
    protected function seedOtSchedules(): void
    {
        if (! Schema::hasTable('ot_surgery_schedules')) { $this->command->warn('  · ot_surgery_schedules table missing — skipped'); return; }
        if (DB::table('ot_surgery_schedules')->count() >= 5) { $this->command->line('  · ot_surgery_schedules already has data — skipped'); return; }

        $reqs = DB::table('ot_surgery_requests')->whereIn('status', ['Accepted','Fast Tracked','Moved to Scheduling','Submitted','Draft'])->limit(5)->get();
        $room = DB::table('ot_rooms')->first();
        if ($reqs->isEmpty() || ! $room) { $this->command->line('  · no surgery requests or OT rooms — skipped'); return; }

        $rows = [];
        $now = now();
        foreach ($reqs as $i => $req) {
            $start = $now->copy()->addDays($i + 1)->setTime(9 + ($i % 4) * 2, 0);
            $end   = $start->copy()->addMinutes(90);
            $rows[] = [
                'schedule_no'        => 'OTS-' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT),
                'surgery_request_id' => $req->id,
                'ot_room_id'         => $room->id,
                'scheduled_start'    => $start,
                'scheduled_end'      => $end,
                'buffer_minutes'     => 15,
                'status'             => 'Scheduled',
                'emergency_fast_track' => 0,
                'created_by'         => 1,
                'created_at'         => now(), 'updated_at' => now(),
            ];
        }
        DB::table('ot_surgery_schedules')->insert($rows);
        $this->command->line('  · '.count($rows).' OT schedules inserted');
    }

    /* ───────── Appointments ───────── */
    protected function seedAppointments(array $patientIds, array $doctorIds): void
    {
        if (! Schema::hasTable('appointments')) { $this->command->warn('  · appointments table missing — skipped'); return; }
        if (DB::table('appointments')->count() > 0) { $this->command->line('  · appointments already has data — skipped'); return; }

        $today = Carbon::today();
        $statuses = ['approved', 'pending', 'approved', 'declined', 'approved', 'pending'];
        $sources  = ['walk_in', 'online', 'phone', 'referral'];

        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $dayOffset = $i < 6 ? 0 : random_int(-3, 4); // first 6 today, rest within ±days
            $date = $today->copy()->addDays($dayOffset)->toDateString();
            $hour = 9 + ($i % 8);
            $rows[] = [
                'patient_id'         => $patientIds[$i % count($patientIds)],
                'date'               => $date,
                'time'               => sprintf('%02d:%02d:00', $hour, ($i * 7) % 60),
                'priority'           => $i === 0 ? 'high' : 'normal',
                'specialist'         => 'General Medicine',
                'doctor'             => $doctorIds[$i % count($doctorIds)],
                'amount'             => 500 + ($i * 50),
                'message'            => 'Routine consultation' . ($i === 0 ? ' — chest pain follow-up' : ''),
                'appointment_status' => $statuses[$i % count($statuses)],
                'visit_status'       => 'waiting',
                'source'             => $sources[$i % count($sources)],
                'is_opd'             => 1,
                'is_ipd'             => 0,
                'live_consult'       => 'no',
                'is_queue'           => 0,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }
        DB::table('appointments')->insert($rows);
        $this->command->line('  · '.count($rows).' appointments inserted');
    }

    /* ───────── OPD visits ───────── */
    protected function seedOpdVisits(array $patientIds, array $doctorIds): void
    {
        if (! Schema::hasTable('opd_patients')) { $this->command->warn('  · opd_patients table missing — skipped'); return; }
        if (DB::table('opd_patients')->count() > 0) { $this->command->line('  · opd_patients already has data — skipped'); return; }

        $today = Carbon::today();
        $complaints = ['Fever and headache', 'Cough for 3 days', 'Abdominal pain', 'Back pain', 'Routine checkup', 'High blood pressure', 'Diabetes follow-up', 'Skin rash'];
        $rows = [];
        for ($i = 0; $i < 8; $i++) {
            $dayOffset = $i < 4 ? 0 : -1 * random_int(1, 6);
            $visitDate = $today->copy()->addDays($dayOffset);
            $rows[] = [
                'encounter_id'      => null,
                'organization_id'   => null,
                'branch_id'         => null,
                'case_id'           => null,
                'visit_type'        => 'NEW',
                'patient_id'        => $patientIds[$i % count($patientIds)],
                'doctor_id'         => $doctorIds[$i % count($doctorIds)],
                'department_id'     => 1,
                'date'              => $visitDate->toDateString(),
                'visit_date'        => $visitDate->toDateString(),
                'token_no'          => 100 + $i,
                'serial_no'         => $i + 1,
                'chief_complaint'   => $complaints[$i % count($complaints)],
                'referral_source'   => $i % 3 === 0 ? 'self' : 'walk_in',
                'status'            => $i < 5 ? 'completed' : 'waiting',
                'source'            => 'reception',
                'priority'          => 'normal',
                'created_at'        => $visitDate->copy()->addHours(9)->addMinutes($i * 12),
                'updated_at'        => $visitDate->copy()->addHours(9)->addMinutes($i * 12),
            ];
        }
        DB::table('opd_patients')->insert($rows);
        $this->command->line('  · '.count($rows).' OPD visits inserted');
    }

    /* ───────── ER arrivals ───────── */
    protected function seedErArrivals(array $patientIds, array $doctorIds): void
    {
        if (! Schema::hasTable('er_patients')) { $this->command->warn('  · er_patients table missing — skipped'); return; }
        if (DB::table('er_patients')->count() > 0) { $this->command->line('  · er_patients already has data — skipped'); return; }

        $now = Carbon::now();
        $priorities = ['CRITICAL', 'HIGH', 'NORMAL', 'CRITICAL', 'HIGH'];
        $descriptions = [
            'Road traffic accident — multiple lacerations',
            'Severe chest pain, suspected MI',
            'High fever, vomiting since morning',
            'Fall from height, suspected fracture',
            'Acute asthma exacerbation',
        ];
        $statuses = ['ACTIVE', 'ACTIVE', 'TRANSFERRED', 'ACTIVE', 'DISCHARGED'];
        $rows = [];
        for ($i = 0; $i < 5; $i++) {
            $arrival = $now->copy()->subHours(random_int(1, 20));
            $rows[] = [
                'organization_id'  => null,
                'branch_id'        => null,
                'patient_id'       => $patientIds[$i % count($patientIds)],
                'doctor_id'        => $doctorIds[$i % count($doctorIds)],
                'department_id'    => 1,
                'arrival_time'     => $arrival,
                'description'      => $descriptions[$i],
                'age'              => 25 + ($i * 8),
                'gender'           => $i % 2 ? 'female' : 'male',
                'blood_group'      => ['A+', 'B+', 'O+', 'AB+', 'O-'][$i],
                'priority'         => $priorities[$i],
                'remarks'          => 'Triaged on arrival',
                'status'           => $statuses[$i],
                'created_at'       => $arrival,
                'updated_at'       => $arrival,
            ];
        }
        DB::table('er_patients')->insert($rows);
        $this->command->line('  · '.count($rows).' ER arrivals inserted');
    }

    /* ───────── Prescriptions ───────── */
    protected function seedPrescriptions(): void
    {
        if (! Schema::hasTable('prescriptions')) { $this->command->warn('  · prescriptions table missing — skipped'); return; }
        if (DB::table('prescriptions')->count() > 0) { $this->command->line('  · prescriptions already has data — skipped'); return; }

        $opdVisits = DB::table('opd_patients')->where('status', 'completed')->get();
        $ipds = IpdPatient::limit(3)->get();
        if ($opdVisits->isEmpty() && $ipds->isEmpty()) {
            $this->command->line('  · no OPD/IPD encounters to attach prescriptions — skipped');
            return;
        }

        $findings = [
            'Mild viral fever. Hydration adequate. Vitals stable.',
            'Productive cough, bilateral rhonchi. No focal consolidation.',
            'Tenderness right iliac fossa, no rebound. Differential: appendicitis vs gastritis.',
            'Hypertension stage 1. BP 142/92.',
            'T2DM, fasting BG 168 mg/dL. HbA1c last month 7.4.',
        ];
        $advice = [
            'Paracetamol 500mg TDS × 5d, increase fluids, return if fever > 38.5 persists',
            'Salbutamol inhaler 2 puffs QID, Amoxiclav 625mg TDS × 7d',
            'NPO 12h, abdominal US tomorrow, surgical consult if pain worsens',
            'Amlodipine 5mg OD, low-salt diet, follow-up in 2 weeks',
            'Metformin 500mg BD, dietary counselling, repeat HbA1c in 3 months',
        ];

        $base = [
            'opd_patient_id' => null, 'ipd_patient_id' => null, 'appointment_id' => null,
            'icd10_code' => null, 'icd10_description' => null, 'next_visit' => null,
        ];
        $rows = []; $i = 0;
        foreach ($opdVisits as $opd) {
            $date = Carbon::parse($opd->visit_date ?? $opd->date);
            $rows[] = array_merge($base, [
                'prescription_no'    => 'RX-' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT),
                'opd_patient_id'     => $opd->id,
                'patient_id'         => $opd->patient_id,
                'doctor_id'          => $opd->doctor_id,
                'date'               => $date->toDateString(),
                'findings'           => $findings[$i % count($findings)],
                'icd10_code'         => ['J11.1', 'J20.9', 'R10.4', 'I10', 'E11.9'][$i % 5],
                'icd10_description'  => ['Influenza, viral', 'Acute bronchitis', 'Other abdominal pain', 'Essential hypertension', 'Type 2 diabetes mellitus'][$i % 5],
                'advice'             => $advice[$i % count($advice)],
                'next_visit'         => $date->copy()->addDays(7)->toDateString(),
                'type'               => 'opd',
                'created_at'         => $date->copy()->addHours(10),
                'updated_at'         => $date->copy()->addHours(10),
            ]);
            $i++;
        }
        foreach ($ipds as $ipd) {
            $rows[] = array_merge($base, [
                'prescription_no'    => 'RX-' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT),
                'ipd_patient_id'     => $ipd->id,
                'patient_id'         => $ipd->patient_id,
                'doctor_id'          => $ipd->doctor_id ?? 1,
                'date'               => now()->toDateString(),
                'findings'           => 'Inpatient round — vitals stable, post-op day ' . ($i + 1),
                'advice'             => 'Continue current treatment, IV fluids, mobilize as tolerated',
                'type'               => 'ipd',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $i++;
        }
        DB::table('prescriptions')->insert($rows);
        $this->command->line('  · '.count($rows).' prescriptions inserted');
    }

    /* ───────── Transactions (payments against existing patient_charges) ───────── */
    protected function seedTransactions(): void
    {
        if (! Schema::hasTable('transactions')) { $this->command->warn('  · transactions table missing — skipped'); return; }
        if (DB::table('transactions')->count() > 0) { $this->command->line('  · transactions already has data — skipped'); return; }

        $charges = DB::table('patient_charges')->limit(10)->get();
        if ($charges->isEmpty()) {
            $this->command->line('  · no patient_charges to back transactions — skipped');
            return;
        }

        // patient_charges links via ipd_id/opd_id/appointment_id — resolve back to patient_id
        $ipdPatients = DB::table('i_p_d_patients')->pluck('patient_id', 'id')->all();
        $opdVisits   = DB::table('opd_patients')->pluck('patient_id', 'id')->all();
        $appts       = DB::table('appointments')->pluck('patient_id', 'id')->all();

        $vias = ['cash', 'card', 'bkash', 'cash', 'bank'];
        $rows = []; $i = 0;
        foreach ($charges as $c) {
            $amt = (float) ($c->net_amount ?? 0);
            if ($amt <= 0) continue;

            $patientId = $ipdPatients[$c->ipd_id ?? -1]
                ?? $opdVisits[$c->opd_id ?? -1]
                ?? $appts[$c->appointment_id ?? -1]
                ?? null;
            if (! $patientId) continue;

            $rows[] = [
                'patient_id'      => $patientId,
                'case_id'         => $c->case_id ?? 0,
                'ipd_patient_id'  => $c->ipd_id ?? null,
                'opd_patient_id'  => $c->opd_id ?? null,
                'invoice_no'      => 'INV-' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT),
                'type'            => 'payment',
                'section'         => $c->charge_module ?? 'general',
                'amount'          => $amt,
                'vat'             => 0,
                'tax'             => 0,
                'discount'        => 0,
                'net_amount'      => $amt,
                'payment_via'     => $vias[$i % count($vias)],
                'payment_date'    => now()->subDays($i)->toDateString(),
                'received_by'     => 1,
                'status'          => 'paid',
                'notes'           => 'Auto-seeded payment for charge #'.$c->id,
                'created_at'      => now()->subDays($i),
                'updated_at'      => now()->subDays($i),
            ];
            $i++;
        }
        if ($rows) {
            DB::table('transactions')->insert($rows);
            $this->command->line('  · '.count($rows).' transactions inserted');
        }
    }
}
