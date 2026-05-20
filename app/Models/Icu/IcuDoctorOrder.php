<?php

namespace App\Models\Icu;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuDoctorOrder extends Model
{
    protected $table = 'icu_doctor_orders';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'doctor_id',
        'order_type',
        'order_title',
        'order_details',
        'priority',
        'start_time',
        'frequency',
        'duration',
        'status',
        'requires_doctor_ack',
        'doctor_acknowledged_at',
        'doctor_acknowledged_by',
        'linked_module',
        'linked_id',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'start_time'             => 'datetime',
        'doctor_acknowledged_at' => 'datetime',
        'requires_doctor_ack'    => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function executionLogs()
    {
        return $this->hasMany(IcuOrderExecutionLog::class, 'order_id')->orderBy('id');
    }

    public function auditLogs()
    {
        return $this->hasMany(IcuOrderAuditLog::class, 'order_id')->orderByDesc('id');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['Ordered', 'Acknowledged', 'InProgress', 'OnHold']);
    }

    public function scopeStat($query)
    {
        return $query->where('priority', 'STAT');
    }
}
