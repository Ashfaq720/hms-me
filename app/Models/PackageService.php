<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageService extends Model
{
    protected $table = 'package_services';

    protected $fillable = [
        'package_id',
        'charge_id',
        'service_id',
        'service_catalog_id',
        'is_included',
        'quantity',
        'rate',
        'amount',
        'note',
    ];

    protected $casts = [
        'is_included' => 'boolean',
        'quantity'    => 'float',
        'rate'        => 'float',
        'amount'      => 'float',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Canonical relation — package services reference rows from /admin/charges.
     */
    public function charge()
    {
        return $this->belongsTo(\App\Models\Charges\Charge::class, 'charge_id');
    }

    /** Deprecated — kept for legacy code paths only. */
    public function catalog()
    {
        return $this->belongsTo(\App\Models\ServiceCharge\ServiceCatalog::class, 'service_catalog_id');
    }

    /** Deprecated — legacy `services` table. */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
