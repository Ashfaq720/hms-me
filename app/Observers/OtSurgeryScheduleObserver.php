<?php

namespace App\Observers;

use App\Models\Ot\OtSurgerySchedule;
use App\Services\Ot\OtRoomStateService;

class OtSurgeryScheduleObserver
{
    public function __construct(protected OtRoomStateService $rooms) {}

    public function created(OtSurgerySchedule $schedule): void
    {
        $this->rooms->setForSchedule($schedule);
    }

    public function updated(OtSurgerySchedule $schedule): void
    {
        if ($schedule->wasChanged('status') || $schedule->wasChanged('ot_room_id')) {
            $this->rooms->setForSchedule($schedule);
        }
    }
}
