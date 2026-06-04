<?php

namespace App\Models\Er;

use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErTriage extends Model
{
    protected $table = 'er_triages';

    protected $fillable = [
        'er_patient_id', 'patient_id', 'triage_level', 'pain_score',
        'consciousness_level', 'chief_complaint',
        'blood_pressure', 'pulse', 'respiratory_rate', 'spo2',
        'temperature_c', 'blood_glucose_mgdl',
        'notes', 'triaged_by', 'triaged_at',
    ];

    protected $casts = [
        'triaged_at' => 'datetime',
    ];

    public function erPatient() { return $this->belongsTo(ErPatient::class); }

    public static function levelColour(string $level): string
    {
        return [
            'RED'    => 'danger',
            'ORANGE' => 'warning',
            'YELLOW' => 'info',
            'GREEN'  => 'success',
            'BLACK'  => 'dark',
        ][$level] ?? 'secondary';
    }
}
