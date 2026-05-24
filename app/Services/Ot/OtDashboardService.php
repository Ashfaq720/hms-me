<?php

namespace App\Services\Ot;

use App\Models\Ot\OtPreOpChecklist;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtTransfer;
use Carbon\Carbon;

class OtDashboardService
{
    public function delayReason(OtSurgerySchedule $schedule): string
    {
        $checklist = $schedule->preOpChecklist;
        if (! $checklist || ! $checklist->isReady()) {
            return $this->preOpReason($checklist);
        }

        $transferStarted = $schedule->transfers
            ->where('direction', OtTransfer::DIRECTION_TO_OT)
            ->isNotEmpty();
        if (! $transferStarted) return 'Patient transfer not initiated';

        $overrun = OtSurgerySchedule::where('ot_room_id', $schedule->ot_room_id)
            ->where('id', '!=', $schedule->id)
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_SURGERY_RUNNING,
                OtSurgerySchedule::STATUS_ANESTHESIA_STARTED,
            ])->exists();
        if ($overrun) return 'Previous surgery still running in this OT';

        if ($schedule->teamMembers->isEmpty()) return 'Team not assigned';
        return 'Awaiting OT team to start';
    }

    protected function preOpReason(?OtPreOpChecklist $checklist): string
    {
        if (! $checklist) return 'Pre-op checklist not started';

        $labels = [
            'consent_obtained' => 'Consent', 'lab_completed' => 'Lab',
            'radiology_completed' => 'Radiology', 'fasting_confirmed' => 'Fasting',
            'blood_arranged' => 'Blood', 'allergy_reviewed' => 'Allergy review',
            'vitals_recorded' => 'Vitals', 'anesthesia_clearance' => 'Anesthesia clearance',
            'doctor_clearance' => 'Doctor clearance', 'nurse_confirmation' => 'Nurse confirmation',
        ];

        $missing = [];
        foreach (OtPreOpChecklist::REQUIRED_ITEMS as $item) {
            if (! $checklist->{$item}) $missing[] = $labels[$item] ?? $item;
        }
        if (empty($missing)) return 'Pre-op pending';

        $shortlist = array_slice($missing, 0, 3);
        $suffix = count($missing) > 3 ? sprintf(' (+%d more)', count($missing) - 3) : '';
        return 'Pre-op: ' . implode(', ', $shortlist) . $suffix;
    }

    public function missingPreOpItems(?OtPreOpChecklist $checklist): array
    {
        $labels = [
            'consent_obtained' => 'Consent', 'lab_completed' => 'Lab',
            'radiology_completed' => 'Radiology', 'fasting_confirmed' => 'Fasting',
            'blood_arranged' => 'Blood', 'allergy_reviewed' => 'Allergy',
            'vitals_recorded' => 'Vitals', 'anesthesia_clearance' => 'Anaes. Clr.',
            'doctor_clearance' => 'Doc Clr.', 'nurse_confirmation' => 'Nurse',
        ];

        if (! $checklist) return array_values($labels);

        $missing = [];
        foreach (OtPreOpChecklist::REQUIRED_ITEMS as $item) {
            if (! $checklist->{$item}) $missing[] = $labels[$item];
        }
        return $missing;
    }

    public function runningDurationMinutes(OtSurgerySchedule $schedule): ?int
    {
        if (! $schedule->actual_start) return null;
        return $schedule->actual_start->diffInMinutes(now());
    }

    public function expectedEndTime(OtSurgerySchedule $schedule): ?Carbon
    {
        if (! $schedule->actual_start) return null;
        $duration = $schedule->surgeryRequest?->estimated_duration_minutes
            ?? $schedule->scheduled_start?->diffInMinutes($schedule->scheduled_end)
            ?? 60;
        return $schedule->actual_start->copy()->addMinutes($duration);
    }

    public function anesthesiaStatus(OtSurgerySchedule $schedule): string
    {
        $record = $schedule->anesthesiaRecord;
        if (! $record) return 'Not started';
        if ($record->recovery_time) return 'Recovery';
        if ($record->induction_time) return 'Induced';
        return 'Pre-anesthesia';
    }
}
