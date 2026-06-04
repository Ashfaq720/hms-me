<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks (run via `php artisan schedule:work` in production)
|--------------------------------------------------------------------------
*/

// Prune activity log entries older than 90 days, every Sunday at 02:30
Schedule::command('hms:prune-activity-log --days=90')
    ->weeklyOn(0, '02:30')
    ->onOneServer()
    ->runInBackground();
