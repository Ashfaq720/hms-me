<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogPreference
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->setDescriptionForEvent(function (string $eventName) {
                $logName = $this->logName ?? class_basename($this);
                $user = auth()->user();
                
                return "{$logName} has been {$eventName}" . ($user ? " by {$user->name}" : "");
            })
            ->useLogName($this->logName ?? 'default')
            ->logAll()
            // ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
