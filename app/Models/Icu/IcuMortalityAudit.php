<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuMortalityAudit extends Model
{
    protected $table = 'icu_mortality_audits';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'death_time',
        'cause_of_death',
        'code_blue_event_id',
        'resuscitation_details',
        'death_declared_by',
        'body_handover_to',
        'audit_status',
        'reviewed_by',
        'reviewed_at',
        'preventability',
        'contributing_factors',
        'clinical_remarks',
        'committee_remarks',
    ];

    protected $casts = [
        'death_time'  => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function codeBlueEvent()
    {
        return $this->belongsTo(IcuEmergencyEvent::class, 'code_blue_event_id');
    }
}
