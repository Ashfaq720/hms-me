<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuDischargeSummary extends Model
{
    protected $table = 'icu_discharge_summaries';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'admission_diagnosis',
        'final_diagnosis',
        'icu_course_summary',
        'procedures_summary',
        'ventilator_summary',
        'investigation_summary',
        'medication_summary',
        'condition_at_discharge',
        'followup_advice',
        'prepared_by',
        'approved_by',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }
}
