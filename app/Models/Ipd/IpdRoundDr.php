<?php

namespace App\Models\Ipd;

use Illuminate\Database\Eloquent\Model;

class IpdRoundDr extends Model
{
    protected $casts = [
        'datetime' => 'datetime',
    ];

    protected $fillable = [
        'ipd_patient_id',
        'datetime',
        'shift',
        'doctor_id',
        'visit_count',
        'clinical_observation',
        'notes',
    ];

    public function doctor()
    {
        return $this->belongsTo(\App\Models\Doctor::class);
    }

    public function ipdPatient()
    {
        return $this->belongsTo(\App\Models\IpdPatient::class, 'ipd_patient_id');
    }
}
