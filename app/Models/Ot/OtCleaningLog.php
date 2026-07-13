<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtCleaningLog extends Model
{
    protected $table = 'ot_cleaning_logs';

    protected $fillable = [
        'ot_room_id', 'surgery_schedule_id', 'cleaning_type', 'started_at',
        'completed_at', 'performed_by', 'verified_by', 'checklist',
        'is_complete', 'remarks',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_complete' => 'boolean',
        'checklist' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(OtRoom::class, 'ot_room_id');
    }

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }
}
