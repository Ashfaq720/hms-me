<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $table = 'visits';

    protected $fillable = [
        'visit_no', 'visit_type', 'patient_id', 'source_type', 'source_id',
        'doctor_id', 'department_id',
        'chief_complaint', 'history_flag', 'quick_flags',
        'status', 'waiting_started_at', 'consultation_started_at', 'consultation_ended_at',
        'cancelled_at', 'arrival_mode', 'triage_priority', 'initial_priority',
        'triage_notes', 'ambulance_trip_id',
    ];

    protected $casts = [
        'waiting_started_at'      => 'datetime',
        'consultation_started_at' => 'datetime',
        'consultation_ended_at'   => 'datetime',
        'cancelled_at'            => 'datetime',
    ];
}
