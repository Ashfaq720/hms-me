<?php

namespace App\Models\Icu;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuIntakeOutputChart extends Model
{
    protected $table = 'icu_intake_output_charts';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'entry_time',
        'entry_type',
        'category',
        'quantity_ml',
        'remarks',
        'entered_by',
    ];

    protected $casts = [
        'entry_time'  => 'datetime',
        'quantity_ml' => 'integer',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function scopeIntake($q)
    {
        return $q->where('entry_type', 'Intake');
    }

    public function scopeOutput($q)
    {
        return $q->where('entry_type', 'Output');
    }
}
