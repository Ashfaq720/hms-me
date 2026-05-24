<?php

namespace App\Models\ServiceCharge;

use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCatalog extends Model
{
    use SoftDeletes, LogPreference, BranchScoped;

    protected string $logName = 'service_catalog';

    protected $fillable = [
        'organization_id', 'branch_id', 'code', 'name', 'department_code',
        'service_type', 'charge_unit', 'base_price', 'tax_percent',
        'patient_type', 'valid_from', 'valid_to',
        'discount_allowed', 'insurance_covered', 'package_eligible', 'is_active',
        'description', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'discount_allowed' => 'boolean',
        'insurance_covered' => 'boolean',
        'package_eligible' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(ServiceChargeRule::class)->orderByDesc('priority');
    }

    public function postings()
    {
        return $this->hasMany(ServiceChargePosting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeValidOn($query, \DateTimeInterface|string|null $date = null)
    {
        $date = $date ? \Illuminate\Support\Carbon::parse($date) : now();
        return $query
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            });
    }
}
