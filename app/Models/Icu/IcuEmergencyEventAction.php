<?php

namespace App\Models\Icu;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IcuEmergencyEventAction extends Model
{
    protected $table   = 'icu_emergency_event_actions';
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'action_name',
        'action_time',
        'performed_by',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'action_time' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(IcuEmergencyEvent::class, 'event_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
