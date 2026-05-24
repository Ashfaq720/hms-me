<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class AmbulanceEquipment extends Model
{
    protected $table = 'amb_ambulance_equipment';
    protected $guarded = [];

    protected $casts = [
        'last_checked' => 'date',
    ];

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class, 'ambulance_id');
    }
}
