<?php

namespace App\Models\Icu;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuEmergencyEvent extends Model
{
    protected $table = 'icu_emergency_events';

    protected $fillable = [
        'event_no',
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'bed_id',
        'event_type',
        'activated_by',
        'activated_at',
        'team_notified_at',
        'first_response_at',
        'doctor_arrival_at',
        'closed_at',
        'status',
        'outcome',
        'final_remarks',
        'closed_by',
    ];

    protected $casts = [
        'activated_at'      => 'datetime',
        'team_notified_at'  => 'datetime',
        'first_response_at' => 'datetime',
        'doctor_arrival_at' => 'datetime',
        'closed_at'         => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function actions()
    {
        return $this->hasMany(IcuEmergencyEventAction::class, 'event_id')->orderBy('action_time');
    }

    public function notifications()
    {
        return $this->hasMany(IcuEmergencyNotification::class, 'event_id');
    }

    /**
     * Generate emergency event number atomically: CODE-YYYYMMDD-NNNN.
     * Caller must be inside a DB transaction.
     */
    public static function generateEventNo(?\DateTimeInterface $when = null): string
    {
        $when   = $when ?: now();
        $date   = $when->format('Ymd');
        $prefix = 'CODE-' . $date . '-';

        $last = static::where('event_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr($last->event_no, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
