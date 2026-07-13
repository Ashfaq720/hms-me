<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuEquipmentChangeLog extends Model
{
    protected $table = 'icu_equipment_change_logs';

    protected $fillable = [
        'icu_admission_id',
        'old_equipment_id',
        'new_equipment_id',
        'old_usage_log_id',
        'new_usage_log_id',
        'change_reason',
        'changed_by',
        'changed_at',
        'remarks',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function oldEquipment()
    {
        return $this->belongsTo(IcuEquipment::class, 'old_equipment_id');
    }

    public function newEquipment()
    {
        return $this->belongsTo(IcuEquipment::class, 'new_equipment_id');
    }

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }
}
