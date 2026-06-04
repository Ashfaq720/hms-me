<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class PharmacyReturnItem extends Model
{
    protected $fillable = [
        'return_id', 'transaction_item_id', 'medicine_id',
        'qty_returned', 'unit_price', 'subtotal', 'store',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function return()
    {
        return $this->belongsTo(PharmacyReturn::class, 'return_id');
    }

    public function transactionItem()
    {
        return $this->belongsTo(PharmacyTransactionItem::class, 'transaction_item_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
