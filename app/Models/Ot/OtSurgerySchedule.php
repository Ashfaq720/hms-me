<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtSurgerySchedule extends Model
{
    use SoftDeletes;

    protected $table = 'ot_surgery_schedules';

    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_PRE_OP_PENDING = 'Pre-Op Pending';
    public const STATUS_READY_FOR_OT = 'Ready for OT';
    public const STATUS_TRANSFER_STARTED = 'Transfer Started';
    public const STATUS_PATIENT_RECEIVED = 'Patient Received in OT';
    public const STATUS_ANESTHESIA_STARTED = 'Anesthesia Started';
    public const STATUS_SURGERY_RUNNING = 'Surgery Running';
    public const STATUS_SURGERY_COMPLETED = 'Surgery Completed';
    public const STATUS_IN_RECOVERY = 'In Recovery';
    public const STATUS_TRANSFERRED_BACK = 'Transferred Back';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUSES = [
        self::STATUS_SCHEDULED, self::STATUS_PRE_OP_PENDING, self::STATUS_READY_FOR_OT,
        self::STATUS_TRANSFER_STARTED, self::STATUS_PATIENT_RECEIVED,
        self::STATUS_ANESTHESIA_STARTED, self::STATUS_SURGERY_RUNNING,
        self::STATUS_SURGERY_COMPLETED, self::STATUS_IN_RECOVERY,
        self::STATUS_TRANSFERRED_BACK, self::STATUS_CLOSED, self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'schedule_no', 'surgery_request_id', 'ot_room_id', 'scheduled_start',
        'scheduled_end', 'buffer_minutes', 'cleaning_buffer_until',
        'actual_start', 'actual_end', 'status', 'emergency_fast_track',
        'cancellation_reason', 'reschedule_reason', 'rescheduled_from_schedule_id',
        'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'cleaning_buffer_until' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'approved_at' => 'datetime',
        'emergency_fast_track' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->schedule_no)) {
                $last = static::withTrashed()->latest('id')->first();
                $next = $last ? ((int) substr($last->schedule_no, 3)) + 1 : 1;
                $model->schedule_no = 'SS-' . str_pad($next, 6, '0', STR_PAD_LEFT);
            }
        });

        static::observe(\App\Observers\OtSurgeryScheduleObserver::class);
    }

    public function surgeryRequest()
    {
        return $this->belongsTo(OtSurgeryRequest::class, 'surgery_request_id');
    }

    public function room()
    {
        return $this->belongsTo(OtRoom::class, 'ot_room_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(OtSurgeryTeam::class, 'surgery_schedule_id');
    }

    public function equipments()
    {
        return $this->hasMany(OtScheduleEquipment::class, 'surgery_schedule_id');
    }

    public function preOpChecklist()
    {
        return $this->hasOne(OtPreOpChecklist::class, 'surgery_schedule_id');
    }

    public function transfers()
    {
        return $this->hasMany(OtTransfer::class, 'surgery_schedule_id');
    }

    public function anesthesiaRecord()
    {
        return $this->hasOne(OtAnesthesiaRecord::class, 'surgery_schedule_id');
    }

    public function intraOpRecord()
    {
        return $this->hasOne(OtIntraOpRecord::class, 'surgery_schedule_id');
    }

    public function consumableUsages()
    {
        return $this->hasMany(OtConsumableUsage::class, 'surgery_schedule_id');
    }

    public function postOpNote()
    {
        return $this->hasOne(OtPostOpNote::class, 'surgery_schedule_id');
    }

    public function pacuRecord()
    {
        return $this->hasOne(OtPacuRecord::class, 'surgery_schedule_id');
    }

    public function cleaningLogs()
    {
        return $this->hasMany(OtCleaningLog::class, 'surgery_schedule_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function rescheduledFromSchedule()
    {
        return $this->belongsTo(self::class, 'rescheduled_from_schedule_id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED, self::STATUS_PRE_OP_PENDING => 'bg-info',
            self::STATUS_READY_FOR_OT, self::STATUS_TRANSFER_STARTED, self::STATUS_PATIENT_RECEIVED => 'bg-primary',
            self::STATUS_ANESTHESIA_STARTED, self::STATUS_SURGERY_RUNNING => 'bg-warning text-dark',
            self::STATUS_SURGERY_COMPLETED, self::STATUS_IN_RECOVERY => 'bg-success',
            self::STATUS_TRANSFERRED_BACK, self::STATUS_CLOSED => 'bg-secondary',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
