<?php

namespace App\Models\ServiceCharge;

use App\Models\Encounter\Encounter;
use App\Models\Organization\Branch;
use App\Models\Patient;
use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

/**
 * Immutable record of every auto-posted service charge.
 *
 * Postings should be created via the ServiceChargeEngine service rather
 * than by callers writing this model directly.
 */
class ServiceChargePosting extends Model
{
    use BranchScoped;

    protected $fillable = [
        'organization_id', 'branch_id', 'encounter_id', 'patient_id',
        'service_catalog_id', 'trigger_event',
        'trigger_source_type', 'trigger_source_id',
        'quantity', 'unit_price', 'discount_amount', 'tax_amount', 'net_amount',
        'rules_applied', 'metadata', 'reason',
        'status', 'reversed_by', 'reversed_at',
        'posted_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'rules_applied' => 'array',
        'metadata' => 'array',
        'reversed_at' => 'datetime',
    ];

    public function catalog()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function trigger()
    {
        return $this->morphTo(__FUNCTION__, 'trigger_source_type', 'trigger_source_id');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }
}
