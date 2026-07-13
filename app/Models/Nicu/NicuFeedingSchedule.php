<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuFeedingSchedule extends Model
{
    protected $table = 'nicu_feeding_schedules';

    protected $fillable = [
        'nicu_admission_id', 'feed_type', 'interval_hours', 'volume_ml',
        'start_date', 'end_date', 'is_active', 'prescribed_by', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
    public function logs() { return $this->hasMany(NicuFeedLog::class, 'schedule_id'); }
}
