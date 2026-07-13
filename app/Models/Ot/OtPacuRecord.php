<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtPacuRecord extends Model
{
    protected $table = 'ot_pacu_records';

    protected $fillable = [
        'surgery_schedule_id', 'admitted_at', 'discharged_at', 'bed_no',
        'vitals_log', 'pain_score_log', 'medications_given', 'observations',
        'aldrete_score', 'discharge_destination', 'status', 'discharged_by',
        'recovery_clearance', 'recovery_clearance_notes', 'cleared_by',
        'cleared_at', 'consciousness_level',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
        'cleared_at' => 'datetime',
        'recovery_clearance' => 'boolean',
        'vitals_log' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function dischargedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'discharged_by');
    }

    public function clearedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'cleared_by');
    }
}
