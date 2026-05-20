<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuEmergencyNotification extends Model
{
    protected $table   = 'icu_emergency_notifications';
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'role',
        'notification_type',
        'sent_at',
        'acknowledged_at',
        'status',
        'created_at',
    ];

    protected $casts = [
        'sent_at'         => 'datetime',
        'acknowledged_at' => 'datetime',
        'created_at'      => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(IcuEmergencyEvent::class, 'event_id');
    }
}
