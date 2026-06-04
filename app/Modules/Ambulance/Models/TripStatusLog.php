<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TripStatusLog extends Model
{
    protected $table = 'amb_trip_status_logs';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(AmbulanceTrip::class, 'trip_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
