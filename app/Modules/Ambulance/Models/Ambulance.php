<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Ambulance extends Model
{
    protected $table = 'amb_ambulances'; // Table name

    protected $guarded = [];  // Allow all fields for mass assignment

    // Relationship with vendor (if ambulance is outsourced)
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    // Relationship with trips (if required)
    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'ambulance_id');
    }

    // Add any necessary helper methods or accessors here
}
