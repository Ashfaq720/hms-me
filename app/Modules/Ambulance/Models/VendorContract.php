<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorContract extends Model
{
    protected $table = 'amb_vendor_contracts';
    protected $guarded = [];

    protected $casts = [
        'contract_start' => 'date',
        'contract_end'   => 'date',
        'rate_amount'    => 'decimal:2',
        'per_km_rate'    => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->contract_end->isPast();
    }
}
