<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuPackage extends Model
{
    protected $table = 'icu_packages';

    protected $fillable = [
        'package_code',
        'package_name',
        'icu_type',
        'rate',
        'billing_unit',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate'      => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(IcuPackageItem::class, 'package_id');
    }

    public function included()
    {
        return $this->items()->where('rule_type', 'Included');
    }

    public function excluded()
    {
        return $this->items()->where('rule_type', 'Excluded');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
