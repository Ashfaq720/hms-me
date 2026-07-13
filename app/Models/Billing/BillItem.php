<?php

namespace App\Models\Billing;

use App\Models\ServiceCharge\ServiceCatalog;
use App\Models\ServiceCharge\ServiceChargePosting;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id', 'service_catalog_id', 'service_charge_posting_id',
        'description', 'item_type', 'quantity', 'unit_price',
        'discount_amount', 'tax_percent', 'tax_amount', 'line_total',
        'is_package_included', 'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'is_package_included' => 'boolean',
        'metadata' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function service()
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }

    public function posting()
    {
        return $this->belongsTo(ServiceChargePosting::class, 'service_charge_posting_id');
    }
}
