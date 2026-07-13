<?php

namespace App\Models\Icu;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Model;

class IcuMonitoringDevice extends Model
{
    protected $table = 'icu_monitoring_devices';

    protected $fillable = [
        'device_code',
        'device_name',
        'device_type',
        'bed_id',
        'status',
        'last_signal_at',
        'is_active',
    ];

    protected $casts = [
        'last_signal_at' => 'datetime',
        'is_active'      => 'boolean',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
