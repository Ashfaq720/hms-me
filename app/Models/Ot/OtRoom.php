<?php

namespace App\Models\Ot;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Model;

class OtRoom extends Model
{
    protected $table = 'ot_rooms';

    protected $fillable = [
        'code', 'name', 'type', 'floor_id', 'block',
        'is_emergency', 'is_active', 'status', 'description',
    ];

    protected $casts = [
        'is_emergency' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function equipments()
    {
        return $this->hasMany(OtEquipment::class);
    }

    public function schedules()
    {
        return $this->hasMany(OtSurgerySchedule::class);
    }

    public function cleaningLogs()
    {
        return $this->hasMany(OtCleaningLog::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
