<?php

namespace App\Models\Insurance;

use App\Models\Encounter\Encounter;
use Illuminate\Database\Eloquent\Model;

class PreAuthorization extends Model
{
    protected $fillable = [
        'insurance_policy_id', 'encounter_id', 'pre_auth_no',
        'requested_amount', 'approved_amount', 'diagnosis', 'justification',
        'status', 'payer_reference_no', 'valid_until',
        'submitted_by', 'submitted_at', 'decided_at', 'decision_reason',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'valid_until' => 'date',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function policy()
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }
}
