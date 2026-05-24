<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClinicalNote extends Model
{
    protected $table = 'amb_clinical_notes';
    protected $guarded = [];

    protected $casts = [
        'oxygen_given'    => 'boolean',
        'ventilator_used' => 'boolean',
        'recorded_at'     => 'datetime',
    ];

    public function trip()
    {
        return $this->belongsTo(AmbulanceTrip::class, 'trip_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
