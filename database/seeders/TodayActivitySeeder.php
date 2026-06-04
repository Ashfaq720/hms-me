<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TodayActivitySeeder extends Seeder
{
    public function run(): void
    {
        $today    = today();
        $now      = now();
        $userId   = DB::table('users')->value('id');
        $patients = DB::table('patients')->inRandomOrder()->limit(40)->pluck('id')->all();
        $doctors  = DB::table('doctors')->pluck('id')->all();
        $depts    = DB::table('departments')->pluck('id')->all();
        $availBeds = DB::table('beds')->where('status', 'available')->where('is_reserved', 0)->limit(5)->get();

        if (! $patients || ! $doctors) return;

        // ── 1. OPD visits for today (10) ──
        for ($i = 0; $i < 10; $i++) {
            $deptId   = $depts[array_rand($depts)];
            $doctorId = $doctors[array_rand($doctors)];
            $caseId   = DB::table('case_references')->insertGetId([
                'created_at' => $now, 'updated_at' => $now,
            ]);

            // Move OPD visit_date forward
            $patientId = $patients[$i];
            $hour      = 9 + ($i % 8);
            $slotFrom  = sprintf('%02d:00', $hour);
            $slotTo    = sprintf('%02d:30', $hour);
            $token     = $today->format('Ymd') . '-OPD-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            DB::table('opd_patients')->insert([
                'case_id'         => $caseId,
                'patient_id'      => $patientId,
                'doctor_id'       => $doctorId,
                'department_id'   => $deptId,
                'date'            => $today,
                'visit_date'      => $today,
                'visit_type'      => ['new', 'follow_up', 'referred'][rand(0, 2)],
                'slot_time_from'  => $slotFrom,
                'slot_time_to'    => $slotTo,
                'chief_complaint' => ['Fever', 'Cough', 'Headache', 'Abdominal Pain', 'Joint Pain', 'Routine Checkup'][rand(0, 5)],
                'priority'        => ['Normal', 'Senior Citizen', 'VIP', 'Emergency'][rand(0, 3)],
                'token_no'        => $token,
                'status'          => ['Registered', 'In Consultation', 'Completed'][rand(0, 2)],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ── 2. IPD admissions for today (5) ──
        if (! empty($availBeds)) {
            $bedIdx = 0;
            for ($i = 0; $i < min(5, count($availBeds)); $i++) {
                $bed = $availBeds[$bedIdx++];
                if (! $bed) break;

                $deptId   = $depts[array_rand($depts)];
                $doctorId = $doctors[array_rand($doctors)];
                $caseId   = DB::table('case_references')->insertGetId([
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                $ipdId = DB::table('i_p_d_patients')->insertGetId([
                    'case_id'        => $caseId,
                    'patient_id'     => $patients[10 + $i],
                    'doctor_id'      => $doctorId,
                    'department_id'  => $deptId,
                    'admission_date' => $today,
                    'admission_type' => ['Planned', 'Emergency', 'Walk-in'][rand(0, 2)],
                    'patient_history'=> 'Today admission · ' . ['Chest Pain', 'Pneumonia', 'Appendicitis', 'C-Section', 'Hypertension Crisis'][rand(0, 4)],
                    'status'         => 'Admitted',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                DB::table('ipd_patient_beds')->insert([
                    'case_id'        => $caseId,
                    'ipd_patient_id' => $ipdId,
                    'bed_id'         => $bed->id,
                    'allocation_type'=> ($bed->bed_type_id ?? 0) >= 9 ? 'icu' : 'bed',
                    'from'           => $now,
                    'status'         => 'Admitted',
                    'remarks'        => 'Today admission',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                DB::table('beds')->where('id', $bed->id)->update([
                    'is_reserved' => 1,
                    'status'      => 'occupied',
                ]);
            }
        }

        // ── 3. ER patients today (3) ──
        for ($i = 0; $i < 3; $i++) {
            $priority = ['CRITICAL', 'HIGH', 'NORMAL'][$i];
            $caseId   = DB::table('case_references')->insertGetId(['created_at' => $now, 'updated_at' => $now]);
            DB::table('er_patients')->insert([
                'case_id'         => $caseId,
                'patient_id'      => $patients[15 + $i],
                'doctor_id'       => $doctors[array_rand($doctors)],
                'department_id'   => $depts[array_rand($depts)],
                'arrival_time'    => $now->copy()->subHours($i + 1),
                'priority'        => $priority,
                'description'     => ['Severe chest pain', 'Trauma after RTA', 'High-grade fever'][$i],
                'remarks'         => 'Today arrival',
                'status'          => $priority === 'CRITICAL' ? 'Triaged' : 'Registered',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ── 4. Today appointments (8) ──
        for ($i = 0; $i < 8; $i++) {
            $hour = 9 + $i;
            DB::table('appointments')->insert([
                'patient_id'     => $patients[20 + $i],
                'date'           => $today,
                'time'           => sprintf('%02d:00:00', $hour),
                'slot_time_from' => sprintf('%02d:00', $hour),
                'slot_time_to'   => sprintf('%02d:30', $hour),
                'priority'       => 'Normal',
                'doctor'         => $doctors[array_rand($doctors)],
                'specialist'     => $depts[array_rand($depts)],
                'visit_status'   => ['booked', 'checked_in', 'waiting', 'in_consultation', 'completed'][rand(0, 4)],
                'appointment_status' => 'booked',
                'source'         => 'front_desk',
                'amount'         => 500,
                'is_opd'         => 1,
                'is_ipd'         => 0,
                'is_queue'       => 0,
                'live_consult'   => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
