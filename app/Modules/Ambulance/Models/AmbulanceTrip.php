<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class AmbulanceTrip extends Model
{
    protected $table = 'amb_trips';
    protected $guarded = [];

    protected $casts = [
        'started_at'           => 'datetime',
        'pickup_arrival_time'  => 'datetime',
        'patient_onboard_time' => 'datetime',
        'hospital_arrival_time'=> 'datetime',
        'completed_at'         => 'datetime',
    ];

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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function clinicalNotes()
    {
        return $this->hasMany(ClinicalNote::class, 'trip_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(TripStatusLog::class, 'trip_id')->orderBy('changed_at');
    }

    public function editAudits()
    {
        return $this->hasMany(TripEditAudit::class, 'trip_id')->orderBy('edited_at');
    }

    public function assignmentAudits()
    {
        return $this->hasMany(AssignmentAudit::class, 'trip_id')->orderBy('changed_at');
    }
}
