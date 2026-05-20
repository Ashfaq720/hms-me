<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Paramedic extends Model
{
    protected $table = 'amb_paramedics';  // Specifies the table name
    protected $guarded = [];  // Mass assignment protection

    // Relationship with ambulance trips (if needed)
    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'paramedic_id');
    }
}
