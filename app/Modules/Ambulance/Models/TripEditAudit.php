<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TripEditAudit extends Model
{
    protected $table = 'amb_trip_edit_audits';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(AmbulanceTrip::class, 'trip_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
