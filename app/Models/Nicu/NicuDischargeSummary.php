<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuDischargeSummary extends Model
{
    protected $table = 'nicu_discharge_summaries';

    protected $fillable = [
        'nicu_admission_id', 'discharge_date', 'discharge_weight_g',
        'final_diagnosis', 'treatment_summary', 'discharge_medications',
        'feeding_advice', 'vaccination_plan', 'follow_up_date', 'follow_up_advice',
        'discharge_disposition', 'approved_by',
    ];

    protected $casts = [
        'discharge_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
}
