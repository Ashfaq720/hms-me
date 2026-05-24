<?php

namespace App\Services\Ot;

use App\Models\Ot\OtDoctorUnavailability;
use App\Models\Ot\OtScheduleEquipment;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtSurgeryTeam;
use Carbon\Carbon;

class OtConflictService
{
    public function roomAvailable(int $roomId, Carbon $start, Carbon $end, ?int $excludeScheduleId = null): bool
    {
        return ! $this->overlapsQuery($start, $end, $excludeScheduleId)
            ->where('ot_room_id', $roomId)
            ->exists();
    }

    /**
     * Staff availability — checks BOTH existing OT schedule overlaps AND
     * (for doctors) the doctor unavailability roster (leave / on-call /
     * OPD / off-duty / meeting). Returns true if free.
     */
    public function staffAvailable(int $staffId, string $staffType, Carbon $start, Carbon $end, ?int $excludeScheduleId = null): bool
    {
        return empty($this->staffConflictReasons($staffId, $staffType, $start, $end, $excludeScheduleId));
    }

    /**
     * Returns array of human-readable conflict reasons for a staff slot.
     * Empty array = staff is free.
     */
    public function staffConflictReasons(int $staffId, string $staffType, Carbon $start, Carbon $end, ?int $excludeScheduleId = null): array
    {
        $reasons = [];

        // Check existing OT team assignments
        $conflictingScheduleIds = $this->overlapsQuery($start, $end, $excludeScheduleId)->pluck('id');
        if ($conflictingScheduleIds->isNotEmpty()) {
            $busyOnSchedule = OtSurgeryTeam::active()
                ->whereIn('surgery_schedule_id', $conflictingScheduleIds)
                ->where('staff_id', $staffId)
                ->where('staff_type', $staffType)
                ->exists();
            if ($busyOnSchedule) {
                $reasons[] = 'already assigned to another OT in this slot';
            }
        }

        // Check doctor unavailability roster (FR-04/FR-05)
        if ($staffType === 'doctor') {
            $unavailability = OtDoctorUnavailability::where('doctor_id', $staffId)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_at', [$start, $end])
                        ->orWhereBetween('end_at', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_at', '<=', $start)
                                ->where('end_at', '>=', $end);
                        });
                })
                ->get();

            foreach ($unavailability as $u) {
                $reasons[] = sprintf(
                    'doctor is %s (%s – %s)',
                    str_replace('_', ' ', $u->reason),
                    $u->start_at->format('M d H:i'),
                    $u->end_at->format('M d H:i')
                );
            }
        }

        return $reasons;
    }

    public function equipmentAvailable(int $equipmentId, Carbon $start, Carbon $end, ?int $excludeScheduleId = null): bool
    {
        $conflictingScheduleIds = $this->overlapsQuery($start, $end, $excludeScheduleId)->pluck('id');

        if ($conflictingScheduleIds->isEmpty()) {
            return true;
        }

        return ! OtScheduleEquipment::whereIn('surgery_schedule_id', $conflictingScheduleIds)
            ->whereNull('released_at')
            ->where('ot_equipment_id', $equipmentId)
            ->exists();
    }

    public function check(array $params): array
    {
        $errors = [];

        $start = Carbon::parse($params['scheduled_start']);
        $end = Carbon::parse($params['scheduled_end']);
        $exclude = $params['exclude_schedule_id'] ?? null;

        if ($end->lessThanOrEqualTo($start)) {
            $errors[] = 'End time must be after start time.';
        }

        if (! empty($params['ot_room_id']) && ! $this->roomAvailable($params['ot_room_id'], $start, $end, $exclude)) {
            $errors[] = 'OT room is already booked during the selected slot.';
        }

        foreach ($params['staff'] ?? [] as $member) {
            if (empty($member['staff_id'])) {
                continue;
            }
            $type = $member['staff_type'] ?? 'user';
            $reasons = $this->staffConflictReasons(
                (int) $member['staff_id'], $type, $start, $end, $exclude
            );
            foreach ($reasons as $r) {
                $errors[] = sprintf(
                    '%s (id %s): %s',
                    ucwords(str_replace('_', ' ', $member['role'] ?? 'staff')),
                    $member['staff_id'],
                    $r
                );
            }
        }

        foreach ($params['equipment_ids'] ?? [] as $equipmentId) {
            if (! $this->equipmentAvailable((int) $equipmentId, $start, $end, $exclude)) {
                $errors[] = "Equipment id {$equipmentId} is already booked during the selected slot.";
            }
        }

        return $errors;
    }

    protected function overlapsQuery(Carbon $start, Carbon $end, ?int $excludeScheduleId = null)
    {
        $q = OtSurgerySchedule::query()
            ->whereNotIn('status', [
                OtSurgerySchedule::STATUS_CANCELLED,
                OtSurgerySchedule::STATUS_CLOSED,
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_start', [$start, $end])
                    ->orWhereBetween('scheduled_end', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('scheduled_start', '<=', $start)
                            ->where('scheduled_end', '>=', $end);
                    });
            });

        if ($excludeScheduleId) {
            $q->where('id', '!=', $excludeScheduleId);
        }

        return $q;
    }
}
