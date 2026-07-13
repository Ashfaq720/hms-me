<?php

namespace App\Services\Icu;

use App\Http\Controllers\ICU\IcuEquipmentUsageController;
use App\Models\Bed;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuDoctorOrder;

/**
 * Performs the cross-cutting cleanup that every "ICU exit path" needs:
 * transfer-out, discharge, mortality. Centralised so the three controllers
 * stay focused on their own concerns.
 */
class AdmissionCloseoutService
{
    /**
     * Hard preconditions per BRD §10.4 — admission cannot exit ICU while these exist.
     * Returns an array of human-readable blockers; empty array means cleared.
     */
    public function listBlockers(IcuAdmission $admission): array
    {
        $blockers = [];

        $statOpen = IcuDoctorOrder::where('icu_admission_id', $admission->id)
            ->where('priority', 'STAT')
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();
        if ($statOpen > 0) {
            $blockers[] = "{$statOpen} pending STAT order(s) — complete or cancel first.";
        }

        $criticalOpenAlerts = IcuAlert::where('icu_admission_id', $admission->id)
            ->where('severity', 'Critical')
            ->whereIn('status', ['Active', 'Acknowledged'])
            ->count();
        if ($criticalOpenAlerts > 0) {
            $blockers[] = "{$criticalOpenAlerts} unresolved critical alert(s).";
        }

        return $blockers;
    }

    /**
     * Close every still-active piece of state on the admission and free the bed.
     * Caller is responsible for setting the admission's terminal status/outcome
     * (Transferred / Discharged / Expired) and persisting it.
     */
    public function closeout(IcuAdmission $admission, \DateTimeInterface $when, ?int $userId, string $reason): void
    {
        // 1) Close any still-open equipment usage and post charges
        app(IcuEquipmentUsageController::class)
            ->closeAllForAdmission($admission->id, $when, $userId, $reason);

        // 2) Auto-close any open Active/Acknowledged alerts
        IcuAlert::where('icu_admission_id', $admission->id)
            ->whereIn('status', ['Active', 'Acknowledged'])
            ->update([
                'status'       => 'Closed',
                'closed_by'    => $userId,
                'closed_at'    => $when,
                'action_taken' => 'Auto-closed: ' . $reason,
            ]);

        // 3) Cancel any Ordered/Acknowledged/InProgress doctor orders that didn't finish
        IcuDoctorOrder::where('icu_admission_id', $admission->id)
            ->whereIn('status', ['Ordered', 'Acknowledged', 'InProgress', 'OnHold', 'Modified'])
            ->update(['status' => 'Cancelled']);

        // 4) Free the bed
        if ($admission->bed_id) {
            Bed::where('id', $admission->bed_id)->update(['is_reserved' => false]);
        }
    }
}
