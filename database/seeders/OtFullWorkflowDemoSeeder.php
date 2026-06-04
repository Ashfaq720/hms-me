<?php

namespace Database\Seeders;

use App\Models\Ot\OtAnesthesiaRecord;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtCleaningLog;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtConsumableUsage;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtIntraOpRecord;
use App\Models\Ot\OtNotification;
use App\Models\Ot\OtPacuRecord;
use App\Models\Ot\OtPostOpNote;
use App\Models\Ot\OtPreOpChecklist;
use App\Models\Ot\OtRequestEquipment;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtScheduleEquipment;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtSurgeryTeam;
use App\Models\Ot\OtTransfer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtFullWorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedWorkflow();
        });
    }

    private function seedWorkflow(): void
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        // ---- Actors (resolved from real master data) ----
        $patientId    = 7;     // Felicia Rutledge
        $ipdId        = 3;     // Admitted IPD record
        $caseId       = 14;    // Case file for billing
        $departmentId = 2;
        $surgeon      = 6;     // Tarek Rahman  (primary surgeon)
        $requester    = 2;     // Md. Rezaul Karim (requesting consultant)
        $createdBy    = 1;     // admin user
        $roomId       = 1;     // OT-01
        $surgeryTypeId   = 1;  // Appendectomy
        $surgeryCatId    = 1;  // Major Surgery
        $anesthesiaTypeId = 1; // General Anesthesia
        $bloodGroupId    = 2;  // A-

        // Clean previous demo rows so the seeder is rerunnable.
        $existing = OtSurgeryRequest::where('diagnosis', 'DEMO :: Acute Appendicitis (full-workflow seed)')->first();
        if ($existing) {
            foreach ($existing->schedules as $sch) {
                OtConsumableUsage::where('surgery_schedule_id', $sch->id)->delete();
                OtScheduleEquipment::where('surgery_schedule_id', $sch->id)->delete();
                OtSurgeryTeam::where('surgery_schedule_id', $sch->id)->delete();
                OtPreOpChecklist::where('surgery_schedule_id', $sch->id)->delete();
                OtTransfer::where('surgery_schedule_id', $sch->id)->delete();
                OtAnesthesiaRecord::where('surgery_schedule_id', $sch->id)->delete();
                OtIntraOpRecord::where('surgery_schedule_id', $sch->id)->delete();
                OtPostOpNote::where('surgery_schedule_id', $sch->id)->delete();
                OtPacuRecord::where('surgery_schedule_id', $sch->id)->delete();
                OtCleaningLog::where('surgery_schedule_id', $sch->id)->delete();
            }
            OtRequestEquipment::where('surgery_request_id', $existing->id)->delete();
            $existing->schedules()->forceDelete();
            $existing->forceDelete();
        }

        // ============================================================
        // PHASE 1 — SURGERY REQUEST  (Draft → Submitted → Under Review → Accepted)
        // ============================================================
        $request = OtSurgeryRequest::create([
            'case_id'              => $caseId,
            'patient_id'           => $patientId,
            'encounter_type'       => 'IPD',
            'encounter_id'         => $ipdId,
            'ipd_admission_id'     => $ipdId,
            'surgery_type_id'      => $surgeryTypeId,
            'surgery_category_id'  => $surgeryCatId,
            'requested_by_doctor_id' => $requester,
            'primary_surgeon_id'   => $surgeon,
            'department_id'        => $departmentId,
            'requested_surgery_date' => $today,
            'requested_surgery_time' => '09:00',
            'estimated_duration_minutes' => 75,
            'date_flexibility'     => 'Fixed',
            'required_ot_type'     => 'General OT',
            'priority'             => 'Urgent',
            'is_emergency'         => false,
            'is_life_threatening'  => false,
            'is_immediate_ot'      => false,
            'diagnosis'            => 'DEMO :: Acute Appendicitis (full-workflow seed)',
            'secondary_diagnosis'  => 'Localized peritoneal irritation, RIF',
            'icd_code'             => 'K35.80',
            'procedure_notes'      => 'Open appendectomy via McBurney incision. Standard GA. No comorbidity.',
            'clinical_indication'  => 'Patient presents with 18h history of RIF pain, fever, leukocytosis, USG shows non-compressible appendix 9mm with surrounding fluid.',
            'asa_grade'            => 'ASA II',
            'special_requirements' => 'Standard laparotomy set; suction; cautery.',
            'blood_required'       => true,
            'blood_units'          => 1,
            'blood_group'          => 'A-',
            'blood_group_id'       => $bloodGroupId,
            'blood_components'     => ['Whole Blood'],
            'crossmatch_required'  => true,
            'blood_bank_instruction' => 'Cross-match 1 unit; standby only.',
            'status'               => OtSurgeryRequest::STATUS_DRAFT,
            'junior_approval_required'     => true,
            'consultant_approval_required' => true,
            'created_by'           => $createdBy,
            'created_at'           => $today->copy()->subHours(6),
            'updated_at'           => $today->copy()->subHours(6),
        ]);

        // Equipment requested
        $eq = OtEquipment::take(3)->get();
        foreach ($eq as $e) {
            OtRequestEquipment::create([
                'surgery_request_id' => $request->id,
                'ot_equipment_id'    => $e->id,
                'equipment_name'     => $e->name,
                'quantity'           => 1,
                'is_mandatory'       => true,
                'setup_instruction'  => 'Verify calibration and sterility before induction.',
            ]);
        }

        // Status: Submit
        $request->update([
            'status'     => OtSurgeryRequest::STATUS_SUBMITTED,
            'updated_at' => $today->copy()->subHours(5),
        ]);
        $this->audit('surgery_request', $request->id, 'submit', OtSurgeryRequest::STATUS_DRAFT, OtSurgeryRequest::STATUS_SUBMITTED, 'Initial submission by requesting team', $createdBy);

        // Under Review
        $request->update([
            'status'       => OtSurgeryRequest::STATUS_UNDER_REVIEW,
            'reviewed_by'  => $createdBy,
            'reviewed_at'  => $today->copy()->subHours(4),
            'updated_at'   => $today->copy()->subHours(4),
        ]);
        $this->audit('surgery_request', $request->id, 'start_review', OtSurgeryRequest::STATUS_SUBMITTED, OtSurgeryRequest::STATUS_UNDER_REVIEW, 'Reviewing clinical readiness', $createdBy);

        // Junior approval
        $request->update([
            'junior_approved_by' => $createdBy,
            'junior_approved_at' => $today->copy()->subHours(3)->subMinutes(30),
        ]);
        $this->audit('surgery_request', $request->id, 'junior_approve', null, null, 'Junior consultant approval recorded', $createdBy);

        // Consultant approval
        $request->update([
            'consultant_approved_by' => $createdBy,
            'consultant_approved_at' => $today->copy()->subHours(3),
        ]);
        $this->audit('surgery_request', $request->id, 'consultant_approve', null, null, 'Senior consultant approval recorded', $createdBy);

        // Accepted → Moved to Scheduling
        $request->update([
            'status'     => OtSurgeryRequest::STATUS_ACCEPTED,
            'updated_at' => $today->copy()->subHours(3),
        ]);
        $this->audit('surgery_request', $request->id, 'accept', OtSurgeryRequest::STATUS_UNDER_REVIEW, OtSurgeryRequest::STATUS_ACCEPTED, 'All approvals satisfied', $createdBy);

        $request->update([
            'status'     => OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING,
            'updated_at' => $today->copy()->subHours(2)->subMinutes(45),
        ]);
        $this->audit('surgery_request', $request->id, 'move_to_scheduling', OtSurgeryRequest::STATUS_ACCEPTED, OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING, 'Routed to OT scheduler', $createdBy);

        // ============================================================
        // PHASE 2 — SCHEDULE  (room + start/end window)
        // ============================================================
        $start = $today->copy()->setTime(9, 0);
        $end   = $today->copy()->setTime(10, 15);

        $schedule = OtSurgerySchedule::create([
            'surgery_request_id' => $request->id,
            'ot_room_id'         => $roomId,
            'scheduled_start'    => $start,
            'scheduled_end'      => $end,
            'buffer_minutes'     => 30,
            'status'             => OtSurgerySchedule::STATUS_SCHEDULED,
            'emergency_fast_track' => false,
            'approved_by'        => $createdBy,
            'approved_at'        => $today->copy()->subHours(2)->subMinutes(30),
            'created_by'         => $createdBy,
            'created_at'         => $today->copy()->subHours(2)->subMinutes(30),
            'updated_at'         => $today->copy()->subHours(2)->subMinutes(30),
        ]);
        $request->update(['status' => OtSurgeryRequest::STATUS_SCHEDULED]);
        $this->audit('surgery_schedule', $schedule->id, 'create', null, OtSurgerySchedule::STATUS_SCHEDULED, 'Schedule created in OT-01', $createdBy);

        // Team
        $teamRows = [
            ['role' => 'Primary Surgeon', 'specialization' => 'General Surgery', 'staff_id' => $surgeon, 'staff_type' => 'doctor', 'is_primary' => true],
            ['role' => 'Assistant Surgeon', 'specialization' => 'General Surgery', 'staff_id' => $requester, 'staff_type' => 'doctor', 'is_primary' => false],
            ['role' => 'Anesthetist', 'specialization' => 'Anesthesia', 'staff_id' => $createdBy, 'staff_type' => 'user', 'is_primary' => false],
            ['role' => 'Scrub Nurse', 'specialization' => 'Scrub Nurse', 'staff_id' => $createdBy, 'staff_type' => 'user', 'is_primary' => false],
            ['role' => 'Circulating Nurse', 'specialization' => 'Circulating', 'staff_id' => $createdBy, 'staff_type' => 'user', 'is_primary' => false],
        ];
        foreach ($teamRows as $row) {
            OtSurgeryTeam::create(array_merge($row, [
                'surgery_schedule_id' => $schedule->id,
                'assigned_at'         => $today->copy()->subHours(2)->subMinutes(20),
                'notes'               => 'Demo workflow team',
            ]));
        }

        // Equipment assigned to schedule
        foreach ($eq as $e) {
            OtScheduleEquipment::create([
                'surgery_schedule_id' => $schedule->id,
                'ot_equipment_id'     => $e->id,
                'notes'               => 'Allocated for ' . $request->request_no,
            ]);
        }

        // ============================================================
        // PHASE 3 — PRE-OP CHECKLIST  (all items satisfied)
        // ============================================================
        OtPreOpChecklist::create([
            'surgery_schedule_id'    => $schedule->id,
            'consent_obtained'       => true,
            'lab_completed'          => true,
            'radiology_completed'    => true,
            'fasting_confirmed'      => true,
            'blood_arranged'         => true,
            'allergy_reviewed'       => true,
            'vitals_recorded'        => true,
            'anesthesia_clearance'   => true,
            'doctor_clearance'       => true,
            'nurse_confirmation'     => true,
            'site_marked'            => true,
            'id_band_verified'       => true,
            'vitals_snapshot'        => [
                'bp'   => '128/82 mmHg',
                'hr'   => '92 bpm',
                'spo2' => '98%',
                'temp' => '38.1 °C',
                'rr'   => '20/min',
            ],
            'notes'        => 'Cleared for OT — Demo workflow seed.',
            'is_complete'  => true,
            'completed_at' => $today->copy()->subHours(2),
            'completed_by' => $createdBy,
        ]);
        $schedule->update(['status' => OtSurgerySchedule::STATUS_READY_FOR_OT]);
        $this->audit('surgery_schedule', $schedule->id, 'pre_op_complete', OtSurgerySchedule::STATUS_PRE_OP_PENDING, OtSurgerySchedule::STATUS_READY_FOR_OT, 'All pre-op items satisfied', $createdBy);

        // ============================================================
        // PHASE 4 — TRANSFER IN  (Ward → OT)
        // ============================================================
        OtTransfer::create([
            'surgery_schedule_id' => $schedule->id,
            'direction'           => 'to_ot',
            'from_location'       => 'IPD Ward 2',
            'to_location'         => 'OT-01',
            'initiated_at'        => $today->copy()->setTime(8, 45),
            'arrived_at'          => $today->copy()->setTime(8, 55),
            'porter_id'           => $createdBy,
            'nurse_id'            => $createdBy,
            'status'              => 'completed',
            'notes'               => 'Stable on transfer. Identity & site verified at handover.',
            'created_by'          => $createdBy,
        ]);
        $schedule->update(['status' => OtSurgerySchedule::STATUS_PATIENT_RECEIVED]);
        $this->audit('surgery_schedule', $schedule->id, 'transfer_in', OtSurgerySchedule::STATUS_READY_FOR_OT, OtSurgerySchedule::STATUS_PATIENT_RECEIVED, 'Patient received in OT-01', $createdBy);

        // ============================================================
        // PHASE 5 — ANESTHESIA
        // ============================================================
        OtAnesthesiaRecord::create([
            'surgery_schedule_id'        => $schedule->id,
            'anesthesia_type_id'         => $anesthesiaTypeId,
            'anesthetist_id'             => $createdBy,
            'induction_time'             => $today->copy()->setTime(9, 5),
            'recovery_time'              => $today->copy()->setTime(10, 12),
            'pre_anesthesia_assessment'  => 'Mallampati II, mouth opening adequate, no loose teeth. NPO since 22:00 previous day.',
            'drugs_used'                 => "Induction: Propofol 150 mg IV\nMuscle relaxant: Atracurium 30 mg IV\nMaintenance: Sevoflurane 2% in O2/air\nAnalgesia: Fentanyl 100 mcg IV",
            'airway_management'          => 'ETT 7.5 mm, single attempt, cuff inflated, bilateral air entry confirmed.',
            'intra_op_vitals'            => [
                ['t' => '09:10', 'bp' => '118/76', 'hr' => 84, 'spo2' => 99],
                ['t' => '09:30', 'bp' => '122/78', 'hr' => 80, 'spo2' => 99],
                ['t' => '09:50', 'bp' => '120/76', 'hr' => 82, 'spo2' => 100],
                ['t' => '10:10', 'bp' => '125/80', 'hr' => 86, 'spo2' => 99],
            ],
            'complications'              => 'None.',
            'post_anesthesia_notes'      => 'Reversal Neostigmine 2.5 mg + Glycopyrrolate 0.4 mg. Extubated awake, breathing room air with SpO2 98%.',
            'asa_grade'                  => 'ASA II',
        ]);
        $schedule->update(['status' => OtSurgerySchedule::STATUS_ANESTHESIA_STARTED]);
        $this->audit('surgery_schedule', $schedule->id, 'anesthesia_start', OtSurgerySchedule::STATUS_PATIENT_RECEIVED, OtSurgerySchedule::STATUS_ANESTHESIA_STARTED, 'GA induction', $createdBy);

        // ============================================================
        // PHASE 6 — INTRA-OP / SURGERY EXECUTION
        // ============================================================
        $schedule->update([
            'status'       => OtSurgerySchedule::STATUS_SURGERY_RUNNING,
            'actual_start' => $today->copy()->setTime(9, 10),
        ]);

        OtIntraOpRecord::create([
            'surgery_schedule_id' => $schedule->id,
            'incision_time'       => $today->copy()->setTime(9, 12),
            'closure_time'        => $today->copy()->setTime(10, 0),
            'operative_findings'  => 'Inflamed, oedematous appendix with localized fibrinous exudate. No perforation. No gross peritoneal contamination.',
            'procedure_performed' => 'Open appendectomy via McBurney incision; mesoappendix divided; base ligated and inverted; peritoneum lavaged.',
            'operative_notes'     => 'Standard appendectomy. Layered closure. Skin closed with monofilament.',
            'specimens_collected' => 'Appendix sent to Histopathology in formalin.',
            'implants_used'       => 'None',
            'blood_loss_ml'       => 25,
            'blood_transfused_ml' => 0,
            'complications'       => 'None.',
            'post_op_instructions' => 'NPO 6h, IV fluids, IV antibiotics (Cefuroxime+Metronidazole) x 24h, early mobilization, monitor wound.',
            'counts_verified'     => true,
            'signed_by'           => $createdBy,
            'signed_at'           => $today->copy()->setTime(10, 5),
        ]);

        // Consumable usages
        $cons = OtConsumable::take(4)->get();
        foreach ($cons as $c) {
            OtConsumableUsage::create([
                'surgery_schedule_id' => $schedule->id,
                'ot_consumable_id'    => $c->id,
                'item_name'           => $c->name ?? ($c->item_name ?? 'Consumable'),
                'item_code'           => $c->code ?? null,
                'type'                => $c->type ?? 'consumable',
                'quantity'            => 2,
                'unit'                => $c->unit ?? 'pcs',
                'rate'                => $c->rate ?? 50,
                'amount'              => ($c->rate ?? 50) * 2,
                'is_billed'           => false,
                'inventory_deducted'  => false,
                'notes'               => 'Used during ' . $request->request_no,
                'recorded_by'         => $createdBy,
                'used_at'             => $today->copy()->setTime(9, 45),
            ]);
        }

        $schedule->update([
            'status'     => OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
            'actual_end' => $today->copy()->setTime(10, 5),
        ]);
        $this->audit('surgery_schedule', $schedule->id, 'surgery_complete', OtSurgerySchedule::STATUS_SURGERY_RUNNING, OtSurgerySchedule::STATUS_SURGERY_COMPLETED, 'Appendectomy completed; specimen sent for histopath', $createdBy);

        // ============================================================
        // PHASE 7 — POST-OP NOTE + PACU
        // ============================================================
        OtPostOpNote::create([
            'surgery_schedule_id' => $schedule->id,
            'procedure_summary'   => 'Open appendectomy for acute appendicitis. Standard McBurney approach. No intra-op complications.',
            'immediate_findings'  => 'Stable haemodynamics. Wound dressing dry. Pain controlled.',
            'post_op_diagnosis'   => 'Acute appendicitis — operated',
            'orders'              => "NPO 6h\nIV fluids: NS @100 mL/h x 12h\nIV Antibiotics: Cefuroxime 1.5 g 8h, Metronidazole 500 mg 8h x 24h\nIV Paracetamol 1 g 6h\nAmbulate after 6h",
            'medications'         => 'Cefuroxime, Metronidazole, Paracetamol, Pantoprazole',
            'care_instructions'   => 'Monitor wound, vitals 4-hourly, early mobilization, encourage chest physio.',
            'follow_up_plan'      => 'Discharge tomorrow if afebrile and tolerating diet. OPD review in 7 days for suture removal.',
            'disposition'         => 'PACU then back to IPD Ward 2',
            'signed_by'           => $createdBy,
            'signed_at'           => $today->copy()->setTime(10, 10),
        ]);

        OtPacuRecord::create([
            'surgery_schedule_id' => $schedule->id,
            'admitted_at'         => $today->copy()->setTime(10, 12),
            'discharged_at'       => $today->copy()->setTime(11, 5),
            'bed_no'              => 'PACU-2',
            'vitals_log'          => [
                ['time' => '10:15', 'bp' => '120/76', 'pulse' => 88, 'spo2' => 98, 'temp' => '36.8', 'pain_score' => 5, 'aldrete_score' => 7],
                ['time' => '10:30', 'bp' => '122/78', 'pulse' => 82, 'spo2' => 99, 'temp' => '36.7', 'pain_score' => 4, 'aldrete_score' => 8],
                ['time' => '10:45', 'bp' => '120/78', 'pulse' => 80, 'spo2' => 99, 'temp' => '36.7', 'pain_score' => 3, 'aldrete_score' => 9],
                ['time' => '11:00', 'bp' => '118/76', 'pulse' => 78, 'spo2' => 99, 'temp' => '36.7', 'pain_score' => 2, 'aldrete_score' => 10],
            ],
            'pain_score_log'      => json_encode([['t' => '10:15', 'score' => 5], ['t' => '10:30', 'score' => 4], ['t' => '11:00', 'score' => 2]], JSON_UNESCAPED_SLASHES),
            'medications_given'   => 'Paracetamol 1 g IV at 10:20; Ondansetron 4 mg IV at 10:25',
            'observations'        => 'Awake, oriented, comfortable. No respiratory distress. Tolerating oral sips.',
            'aldrete_score'       => 10,
            'recovery_clearance'  => true,
            'recovery_clearance_notes' => 'Aldrete 10/10 — cleared for ward.',
            'cleared_by'          => $createdBy,
            'cleared_at'          => $today->copy()->setTime(11, 0),
            'consciousness_level' => 'Alert',
            'discharge_destination' => 'IPD Ward 2',
            'status'              => 'Discharged',
            'discharged_by'       => $createdBy,
        ]);
        $schedule->update(['status' => OtSurgerySchedule::STATUS_IN_RECOVERY]);

        // Transfer back
        OtTransfer::create([
            'surgery_schedule_id' => $schedule->id,
            'direction'           => 'from_ot',
            'from_location'       => 'PACU',
            'to_location'         => 'IPD Ward 2',
            'initiated_at'        => $today->copy()->setTime(11, 5),
            'arrived_at'          => $today->copy()->setTime(11, 15),
            'porter_id'           => $createdBy,
            'nurse_id'            => $createdBy,
            'status'              => 'completed',
            'notes'               => 'Discharged from PACU; handed over to ward team with chart and orders.',
            'created_by'          => $createdBy,
        ]);
        $schedule->update(['status' => OtSurgerySchedule::STATUS_TRANSFERRED_BACK]);
        $this->audit('surgery_schedule', $schedule->id, 'transfer_back', OtSurgerySchedule::STATUS_IN_RECOVERY, OtSurgerySchedule::STATUS_TRANSFERRED_BACK, 'Patient back to IPD ward', $createdBy);

        // ============================================================
        // PHASE 8 — CLEANING + CLOSE
        // ============================================================
        OtCleaningLog::create([
            'ot_room_id'          => $roomId,
            'surgery_schedule_id' => $schedule->id,
            'cleaning_type'       => 'Terminal',
            'started_at'          => $today->copy()->setTime(11, 20),
            'completed_at'        => $today->copy()->setTime(11, 50),
            'performed_by'        => $createdBy,
            'verified_by'         => $createdBy,
            'checklist'           => [
                'surfaces_disinfected' => true,
                'floor_mopped'         => true,
                'instruments_removed'  => true,
                'biohazard_disposed'   => true,
                'air_change_completed' => true,
            ],
            'is_complete'         => true,
            'remarks'             => 'Terminal cleaning complete; OT-01 ready for next case.',
        ]);

        $schedule->update([
            'status'                 => OtSurgerySchedule::STATUS_CLOSED,
            'cleaning_buffer_until'  => $today->copy()->setTime(12, 0),
        ]);
        $this->audit('surgery_schedule', $schedule->id, 'close', OtSurgerySchedule::STATUS_TRANSFERRED_BACK, OtSurgerySchedule::STATUS_CLOSED, 'Case closed; cleaning verified', $createdBy);

        // Notification
        OtNotification::create([
            'user_id'     => $createdBy,
            'role'        => 'OT Manager',
            'type'        => 'workflow_complete',
            'title'       => 'Surgery completed: ' . $request->request_no,
            'body'        => 'Patient transferred back to IPD Ward 2. OT-01 cleaned and ready.',
            'entity_type' => 'surgery_schedule',
            'entity_id'   => $schedule->id,
            'action_url'  => '/ot/surgery-requests/' . $request->id,
            'severity'    => 'info',
        ]);

        $this->command->info('=========================================');
        $this->command->info('  OT Full Workflow Demo Seed Complete');
        $this->command->info('=========================================');
        $this->command->info('Surgery Request : ' . $request->request_no . '  (id=' . $request->id . ')');
        $this->command->info('Schedule        : ' . $schedule->schedule_no . '  (id=' . $schedule->id . ')');
        $this->command->info('Patient         : Felicia Rutledge  (id=' . $patientId . ')');
        $this->command->info('Room            : OT-01');
        $this->command->info('Status          : Closed');
        $this->command->info('Visit           : /ot/surgery-requests/' . $request->id);
    }

    private function audit(string $type, int $id, string $action, ?string $from, ?string $to, ?string $reason, int $userId): void
    {
        OtAuditLog::create([
            'entity_type' => $type,
            'entity_id'   => $id,
            'action'      => $action,
            'from_status' => $from,
            'to_status'   => $to,
            'reason'      => $reason,
            'payload'     => null,
            'user_id'     => $userId,
        ]);
    }
}
