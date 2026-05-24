<?php

namespace App\Models\Ot;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;

class OtDoctorUnavailability extends Model
{
    protected $table = 'ot_doctor_unavailability';

    public const REASON_LEAVE = 'leave';
    public const REASON_ON_CALL = 'on_call';
    public const REASON_OPD = 'opd';
    public const REASON_MEETING = 'meeting';
    public const REASON_OFF_DUTY = 'off_duty';
    public const REASON_OTHER = 'other';

    public const REASONS = [
        self::REASON_LEAVE, self::REASON_ON_CALL, self::REASON_OPD,
        self::REASON_MEETING, self::REASON_OFF_DUTY, self::REASON_OTHER,
    ];

    protected $fillable = [
        'doctor_id', 'start_at', 'end_at', 'reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
