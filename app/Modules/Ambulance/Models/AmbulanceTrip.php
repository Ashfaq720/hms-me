<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class AmbulanceTrip extends Model
{
    protected $table = 'amb_trips';
    protected $guarded = [];

    public function request()
    {
        return $this->belongsTo(AmbulanceRequest::class, 'request_id');
    }

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class, 'ambulance_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function paramedic()
    {
        return $this->belongsTo(Paramedic::class, 'paramedic_id');
    }
}
