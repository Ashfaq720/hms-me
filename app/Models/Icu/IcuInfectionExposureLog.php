<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuInfectionExposureLog extends Model
{
    protected $table = 'icu_infection_exposure_logs';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'infection_record_id',
        'exposure_type',
        'related_patient_id',
        'related_bed_id',
        'related_equipment_id',
        'related_staff_id',
        'exposure_time',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'exposure_time' => 'datetime',
    ];

    public function infectionRecord()
    {
        return $this->belongsTo(IcuInfectionRecord::class, 'infection_record_id');
    }

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }
}
