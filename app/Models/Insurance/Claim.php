<?php

namespace App\Models\Insurance;

use App\Models\Encounter\Encounter;
use App\Models\Patient;
use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use LogPreference, BranchScoped;

    protected string $logName = 'claim';

    protected $fillable = [
        'organization_id', 'branch_id', 'payer_id',
        'insurance_policy_id', 'patient_id', 'encounter_id', 'pre_authorization_id',
        'claim_no', 'bill_reference',
        'gross_amount', 'patient_copay', 'claim_amount',
        'approved_amount', 'settled_amount', 'status',
        'claim_date', 'submission_date', 'settlement_date',
        'attachments', 'denial_reason',
        'created_by', 'approved_by',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'patient_copay' => 'decimal:2',
        'claim_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
        'claim_date' => 'date',
        'submission_date' => 'date',
        'settlement_date' => 'date',
        'attachments' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function policy()
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $claim) {
            if (empty($claim->claim_no)) {
                $claim->claim_no = 'CLM-' . date('Ym') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
