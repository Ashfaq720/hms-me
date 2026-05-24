<?php

namespace App\Models\Telemedicine;

use App\Models\Encounter\Encounter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class TelemedicineSession extends Model
{
    protected $fillable = [
        'encounter_id', 'appointment_id', 'patient_id', 'doctor_id',
        'meeting_provider', 'meeting_url', 'meeting_id', 'access_token',
        'scheduled_at', 'started_at', 'ended_at', 'duration_seconds',
        'status', 'is_recorded', 'recording_path', 'consent_given',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_recorded' => 'boolean',
        'consent_given' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }
}
