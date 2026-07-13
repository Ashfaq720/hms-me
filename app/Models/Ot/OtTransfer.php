<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtTransfer extends Model
{
    protected $table = 'ot_transfers';

    public const DIRECTION_TO_OT = 'to_ot';
    public const DIRECTION_TO_PACU = 'to_pacu';
    public const DIRECTION_TO_WARD = 'to_ward';
    public const DIRECTION_TO_ICU = 'to_icu';
    public const DIRECTION_TO_CCU = 'to_ccu';

    protected $fillable = [
        'surgery_schedule_id', 'direction', 'from_location', 'to_location',
        'initiated_at', 'arrived_at', 'porter_id', 'nurse_id', 'status',
        'notes', 'created_by',
    ];

    protected $casts = [
        'initiated_at' => 'datetime',
        'arrived_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function porter()
    {
        return $this->belongsTo(\App\Models\User::class, 'porter_id');
    }

    public function nurse()
    {
        return $this->belongsTo(\App\Models\User::class, 'nurse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
