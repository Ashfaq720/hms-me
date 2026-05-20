<?php

namespace App\Modules\Ambulance\Models;


use Illuminate\Database\Eloquent\Model;

class AmbulanceRequest extends Model
{
    protected $table = 'amb_requests';
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient::class, 'patient_id'); // adjust namespace if yours differs
    }

    public function trip()
    {
        return $this->hasOne(AmbulanceTrip::class, 'request_id');
    }
}
