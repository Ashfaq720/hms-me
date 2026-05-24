<?php

namespace App\Services\Ot;

use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgerySchedule;

class OtRoomStateService
{
    public const ST_AVAILABLE = 'available';
    public const ST_BOOKED = 'booked';
    public const ST_PATIENT_RECEIVED = 'patient_received';
    public const ST_IN_SURGERY = 'in_surgery';
    public const ST_CLEANING_REQUIRED = 'cleaning_required';
    public const ST_CLEANING_IN_PROGRESS = 'cleaning_in_progress';
    public const ST_READY = 'ready';
    public const ST_UNDER_MAINTENANCE = 'under_maintenance';

    public function setForSchedule(OtSurgerySchedule $schedule): void
    {
        $room = OtRoom::find($schedule->ot_room_id);
        if (! $room) return;

        $next = $this->mapStatus($schedule->status);
        if ($next && $room->status !== $next) {
            $this->transition($room, $next, "schedule:{$schedule->schedule_no} → {$schedule->status}");
        }
    }

    public function transition(OtRoom $room, string $to, ?string $reason = null): void
    {
        $from = $room->status;
        if ($from === $to) return;
        $room->update(['status' => $to]);
        OtAuditLog::record('ot_room', $room->id, 'state_changed', $from, $to, $reason);
    }

    public function badge(string $state): array
    {
        return match ($state) {
            self::ST_AVAILABLE => ['success', 'Available'],
            self::ST_READY => ['success', 'Ready'],
            self::ST_BOOKED => ['info', 'Booked'],
            self::ST_PATIENT_RECEIVED => ['primary', 'Patient Received'],
            self::ST_IN_SURGERY => ['danger', 'In Surgery'],
            self::ST_CLEANING_REQUIRED => ['warning', 'Cleaning Required'],
            self::ST_CLEANING_IN_PROGRESS => ['warning', 'Cleaning…'],
            self::ST_UNDER_MAINTENANCE => ['dark', 'Maintenance'],
            default => ['secondary', ucwords(str_replace('_', ' ', $state))],
        };
    }

    protected function mapStatus(?string $scheduleStatus): ?string
    {
        return match ($scheduleStatus) {
            OtSurgerySchedule::STATUS_SCHEDULED,
            OtSurgerySchedule::STATUS_PRE_OP_PENDING,
            OtSurgerySchedule::STATUS_READY_FOR_OT,
            OtSurgerySchedule::STATUS_TRANSFER_STARTED => self::ST_BOOKED,

            OtSurgerySchedule::STATUS_PATIENT_RECEIVED,
            OtSurgerySchedule::STATUS_ANESTHESIA_STARTED => self::ST_PATIENT_RECEIVED,

            OtSurgerySchedule::STATUS_SURGERY_RUNNING => self::ST_IN_SURGERY,

            OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
            OtSurgerySchedule::STATUS_IN_RECOVERY,
            OtSurgerySchedule::STATUS_TRANSFERRED_BACK => self::ST_CLEANING_REQUIRED,

            OtSurgerySchedule::STATUS_CLOSED,
            OtSurgerySchedule::STATUS_CANCELLED => self::ST_AVAILABLE,
            default => null,
        };
    }
}
