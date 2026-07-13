<?php

namespace App\Models\ServiceCharge;

use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;

class ServiceChargeRule extends Model
{
    use LogPreference;

    protected string $logName = 'service_charge_rule';

    protected $fillable = [
        'service_catalog_id', 'branch_id', 'rule_kind', 'rule_value',
        'adjustment_type', 'adjustment_value', 'valid_from', 'valid_to',
        'priority', 'requires_approval', 'is_active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:4',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function catalog()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
