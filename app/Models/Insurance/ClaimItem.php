<?php

namespace App\Models\Insurance;

use App\Models\ServiceCharge\ServiceCatalog;
use Illuminate\Database\Eloquent\Model;

class ClaimItem extends Model
{
    protected $fillable = [
        'claim_id', 'service_catalog_id', 'description',
        'quantity', 'unit_price', 'line_total',
        'approved_amount', 'denial_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }
}
