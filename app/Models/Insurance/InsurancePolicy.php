<?php

namespace App\Models\Insurance;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class InsurancePolicy extends Model
{
    protected $fillable = [
        'payer_id', 'patient_id', 'policy_no', 'plan_name',
        'valid_from', 'valid_to', 'coverage_limit', 'deductible', 'copay_percent',
        'subscriber_name', 'relationship', 'status',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'coverage_limit' => 'decimal:2',
        'deductible' => 'decimal:2',
        'copay_percent' => 'decimal:2',
    ];

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
