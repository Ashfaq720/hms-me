<?php

namespace App\Models\Icu;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IcuNursingNote extends Model
{
    protected $table = 'icu_nursing_notes';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'shift',
        'observation_time',
        'consciousness_level',
        'pain_score',
        'respiratory_support',
        'oxygen_flow',
        'position',
        'skin_condition',
        'general_condition',
        'remarks',
        'entered_by',
    ];

    protected $casts = [
        'observation_time' => 'datetime',
        'pain_score'       => 'integer',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
