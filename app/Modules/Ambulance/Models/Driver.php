<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'amb_drivers';  // Specifies the table name
    protected $guarded = [];  // Allows mass assignment for all attributes

    // Accessor for the driver's full name (optional)
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Relationship with ambulance trips (if needed)
    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'driver_id');
    }
}
