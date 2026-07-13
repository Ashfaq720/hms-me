<?php

namespace App\Models\PatientPortal;

use App\Models\Encounter\Encounter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class PatientFeedback extends Model
{
    protected $table = 'patient_feedback';

    protected $fillable = [
        'patient_id', 'encounter_id', 'rating',
        'category', 'comments', 'extra',
    ];

    protected $casts = ['extra' => 'array'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }
}
