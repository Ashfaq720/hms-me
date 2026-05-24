<?php

namespace App\Models\Icu;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Model;

class IcuBedEquipmentMapping extends Model
{
    protected $table = 'icu_bed_equipment_mapping';

    protected $fillable = [
        'bed_id',
        'equipment_id',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function equipment()
    {
        return $this->belongsTo(IcuEquipment::class, 'equipment_id');
    }
}
