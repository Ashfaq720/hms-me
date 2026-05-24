<?php

namespace App\Models\Package;

use Illuminate\Database\Eloquent\Model;

class PackageConsumptionEntry extends Model
{
    protected $fillable = [
        'package_enrollment_id', 'package_service_id', 'service_catalog_id',
        'description', 'quantity_allowed', 'quantity_consumed', 'quantity_extras',
        'source_type', 'source_id',
    ];

    protected $casts = [
        'quantity_allowed' => 'decimal:4',
        'quantity_consumed' => 'decimal:4',
        'quantity_extras' => 'decimal:4',
    ];

    public function enrollment()
    {
        return $this->belongsTo(PackageEnrollment::class, 'package_enrollment_id');
    }
}
