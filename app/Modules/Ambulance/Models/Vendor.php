<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'amb_vendors';
    protected $guarded = [];

    public function contracts()
    {
        return $this->hasMany(VendorContract::class, 'vendor_id');
    }

    public function activeContract()
    {
        return $this->hasOne(VendorContract::class, 'vendor_id')
            ->where('status', 'ACTIVE')
            ->latest('contract_start');
    }

    public function ambulances()
    {
        return $this->hasMany(Ambulance::class, 'vendor_id');
    }

    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'vendor_id');
    }
}
