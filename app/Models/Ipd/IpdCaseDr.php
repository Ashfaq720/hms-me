<?php

namespace App\Models\Ipd;

use Illuminate\Database\Eloquent\Model;

class IpdCaseDr extends Model
{
    protected $casts = [
        'datetime' => 'datetime',
    ];

    protected $fillable = [
        'ipd_patient_id',
        'doctor_id',
        'datetime',
        'shift',
        'note',
        'diagnosis',
        'order_to',
        'observations',
        'order',
        'priority',
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
