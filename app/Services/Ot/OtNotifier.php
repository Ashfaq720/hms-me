<?php

namespace App\Services\Ot;

use App\Models\Ot\OtNotification;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;

class OtNotifier
{
    public function requestSubmitted(OtSurgeryRequest $req): void
    {
        $this->toRoles(['OT Manager', 'Anesthetist'], [
            'type' => 'request_submitted',
            'title' => "Surgery request {$req->request_no} submitted",
            'body' => "Patient: " . optional($req->patient)->patient_name . " · " . optional($req->surgeryType)->name,
            'entity_type' => 'surgery_request',
            'entity_id' => $req->id,
            'action_url' => route('ot.surgery-requests.show', $req->id),
            'severity' => $req->is_emergency ? 'danger' : 'info',
        ]);
    }

    /**
     * FR-18: broadcast to all responsible roles when a request is emergency
     * or has been fast-tracked. Same payload to surgeon, anesthetist, OT
     * nurse, IPD/ER nurse, OT manager.
     */
    public function emergencyRequestAlert(OtSurgeryRequest $req): void
    {
        $this->toRoles(
            ['OT Manager', 'Surgeon', 'Anesthetist', 'OT Nurse', 'Nurse', 'IPD Nurse', 'ER Nurse'],
            [
                'type' => 'emergency_request',
                'title' => "🚨 Emergency surgery request {$req->request_no}",
                'body' => trim(
                    optional($req->patient)->patient_name
                    . ' · ' . optional($req->surgeryType)->name
                    . ($req->is_life_threatening ? ' · LIFE-THREATENING' : '')
                    . ($req->is_immediate_ot ? ' · IMMEDIATE OT' : '')
                    . ($req->emergency_reason ? "\nReason: {$req->emergency_reason}" : '')
                ),
                'entity_type' => 'surgery_request',
                'entity_id' => $req->id,
                'action_url' => route('ot.surgery-requests.show', $req->id),
                'severity' => 'danger',
            ]
        );
    }

    public function scheduleCreated(OtSurgerySchedule $sch): void
    {
        $this->toRoles(['OT Manager', 'Nurse', 'Anesthetist'], [
            'type' => 'schedule_created',
            'title' => "Surgery scheduled: {$sch->schedule_no}",
            'body' => optional($sch->surgeryRequest?->patient)->patient_name . " at " . $sch->scheduled_start?->format('Y-m-d H:i'),
            'entity_type' => 'surgery_schedule',
            'entity_id' => $sch->id,
            'action_url' => route('ot.schedules.show', $sch->id),
            'severity' => $sch->emergency_fast_track ? 'danger' : 'info',
        ]);
    }

    public function preOpReady(OtSurgerySchedule $sch): void
    {
        $this->toRoles(['Nurse', 'Surgeon', 'Anesthetist'], [
            'type' => 'pre_op_ready',
            'title' => "Pre-op complete: {$sch->schedule_no}",
            'body' => "Patient ready for OT — " . optional($sch->surgeryRequest?->patient)->patient_name,
            'entity_type' => 'surgery_schedule',
            'entity_id' => $sch->id,
            'action_url' => route('ot.schedules.show', $sch->id),
            'severity' => 'success',
        ]);
    }

    public function emergencyOverride(OtSurgerySchedule $sch, string $reason): void
    {
        $this->toRoles(['OT Manager', 'Surgeon'], [
            'type' => 'emergency_override',
            'title' => "Emergency override applied: {$sch->schedule_no}",
            'body' => "Reason: {$reason}",
            'entity_type' => 'surgery_schedule',
            'entity_id' => $sch->id,
            'action_url' => route('ot.schedules.show', $sch->id),
            'severity' => 'warning',
        ]);
    }

    public function surgeryCancelled(OtSurgerySchedule $sch, string $reason): void
    {
        $this->toRoles(['OT Manager', 'Surgeon', 'Anesthetist', 'Nurse'], [
            'type' => 'schedule_cancelled',
            'title' => "Surgery cancelled: {$sch->schedule_no}",
            'body' => "Reason: {$reason}",
            'entity_type' => 'surgery_schedule',
            'entity_id' => $sch->id,
            'action_url' => route('ot.schedules.show', $sch->id),
            'severity' => 'danger',
        ]);
    }

    public function pacuDischarged(OtSurgerySchedule $sch, string $destination): void
    {
        $this->toRoles(['Nurse', 'OT Manager'], [
            'type' => 'pacu_discharged',
            'title' => "PACU discharge: {$sch->schedule_no}",
            'body' => "Destination: {$destination}",
            'entity_type' => 'surgery_schedule',
            'entity_id' => $sch->id,
            'action_url' => route('ot.pacu.show', $sch->id),
            'severity' => 'info',
        ]);
    }

    protected function toRoles(array $roles, array $payload): void
    {
        foreach ($roles as $role) {
            OtNotification::dispatch(array_merge($payload, ['role' => $role]));
        }
    }
}
