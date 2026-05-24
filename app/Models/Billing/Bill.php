<?php

namespace App\Models\Billing;

use App\Models\Encounter\Encounter;
use App\Models\Insurance\InsurancePolicy;
use App\Models\Insurance\Payer;
use App\Models\Organization\Branch;
use App\Models\Patient;
use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes, LogPreference, BranchScoped;

    protected string $logName = 'bill';

    protected $fillable = [
        'organization_id', 'branch_id', 'encounter_id', 'patient_id',
        'payer_id', 'insurance_policy_id',
        'bill_no', 'bill_type', 'status',
        'bill_date', 'finalized_at', 'cancelled_at', 'cancellation_reason',
        'subtotal', 'discount_total', 'tax_total', 'grand_total',
        'paid_total', 'refund_total', 'balance_due',
        'metadata', 'notes',
        'created_by', 'finalized_by', 'cancelled_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'refund_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function refunds()
    {
        return $this->hasMany(BillRefund::class);
    }

    public function discounts()
    {
        return $this->hasMany(BillDiscount::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function policy()
    {
        return $this->belongsTo(InsurancePolicy::class, 'insurance_policy_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['draft', 'provisional', 'partially_paid']);
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', ['paid', 'final']);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['final', 'paid', 'refunded'], true);
    }
}
