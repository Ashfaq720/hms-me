<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePriceRule extends Model
{
    protected $table = 'package_price_rules';
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function package()    { return $this->belongsTo(Package::class); }
    public function bedType()    { return $this->belongsTo(\App\Models\BedType::class, 'bed_type_id'); }
    public function department() { return $this->belongsTo(\App\Models\Department::class); }
}
