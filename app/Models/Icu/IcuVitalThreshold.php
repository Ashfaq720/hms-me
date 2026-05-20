<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuVitalThreshold extends Model
{
    protected $table = 'icu_vital_thresholds';

    protected $fillable = [
        'icu_admission_id',
        'patient_id',
        'vital_type',
        'normal_min',
        'normal_max',
        'warning_min',
        'warning_max',
        'critical_min',
        'critical_max',
        'configured_by',
    ];

    protected $casts = [
        'normal_min'   => 'float',
        'normal_max'   => 'float',
        'warning_min'  => 'float',
        'warning_max'  => 'float',
        'critical_min' => 'float',
        'critical_max' => 'float',
    ];
}
