<?php

namespace App\Models\Icu;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuVitalLog extends Model
{
    protected $table = 'icu_vital_logs';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'bed_id',
        'heart_rate',
        'systolic_bp',
        'diastolic_bp',
        'spo2',
        'respiratory_rate',
        'temperature',
        'source_type',
        'device_id',
        'severity',
        'recorded_at',
        'entered_by',
        'remarks',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'spo2'        => 'decimal:2',
        'temperature' => 'decimal:1',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
