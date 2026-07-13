<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuFeedLog extends Model
{
    protected $table = 'nicu_feed_logs';

    protected $fillable = [
        'nicu_admission_id', 'schedule_id', 'fed_at', 'feed_type',
        'volume_ml', 'tolerance', 'notes', 'fed_by',
    ];

    protected $casts = ['fed_at' => 'datetime'];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
    public function schedule() { return $this->belongsTo(NicuFeedingSchedule::class, 'schedule_id'); }
}
