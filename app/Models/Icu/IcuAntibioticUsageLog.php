<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuAntibioticUsageLog extends Model
{
    protected $table = 'icu_antibiotic_usage_logs';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'doctor_order_id',
        'medicine_id',
        'antibiotic_name',
        'dose',
        'route',
        'frequency',
        'start_date',
        'stop_date',
        'indication',
        'culture_report_id',
        'prescribed_by',
        'is_restricted',
        'long_use_alerted_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_date'           => 'date',
        'stop_date'            => 'date',
        'long_use_alerted_at'  => 'datetime',
        'is_restricted'        => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function doctorOrder()
    {
        return $this->belongsTo(IcuDoctorOrder::class, 'doctor_order_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'Active');
    }

    public function durationDays(?\DateTimeInterface $now = null): int
    {
        $end = $this->stop_date ?: ($now ?: now());
        return max(0, $this->start_date->startOfDay()->diffInDays($end->startOfDay()));
    }
}
