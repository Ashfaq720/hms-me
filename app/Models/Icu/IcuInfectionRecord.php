<?php

namespace App\Models\Icu;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuInfectionRecord extends Model
{
    protected $table = 'icu_infection_records';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'infection_status',
        'infection_name',
        'organism',
        'isolation_type',
        'suspected_source',
        'first_detected_at',
        'confirmed_at',
        'confirmed_by',
        'lab_report_id',
        'remarks',
        'tagged_by',
        'tagged_at',
        'is_active',
    ];

    protected $casts = [
        'first_detected_at' => 'datetime',
        'confirmed_at'      => 'datetime',
        'tagged_at'         => 'datetime',
        'is_active'         => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function exposureLogs()
    {
        return $this->hasMany(IcuInfectionExposureLog::class, 'infection_record_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
