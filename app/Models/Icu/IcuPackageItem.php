<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuPackageItem extends Model
{
    protected $table = 'icu_package_items';

    protected $fillable = [
        'package_id',
        'charge_category',
        'charge_code',
        'item_name',
        'rule_type',
        'included_qty',
        'limit_period',
        'extra_charge_allowed',
    ];

    protected $casts = [
        'included_qty'         => 'integer',
        'extra_charge_allowed' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(IcuPackage::class, 'package_id');
    }
}
