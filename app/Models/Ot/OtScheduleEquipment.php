<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtScheduleEquipment extends Model
{
    protected $table = 'ot_schedule_equipments';

    protected $fillable = [
        'surgery_schedule_id', 'ot_equipment_id', 'notes',
        'released_at', 'released_reason',
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function equipment()
    {
        return $this->belongsTo(OtEquipment::class, 'ot_equipment_id');
    }
}
