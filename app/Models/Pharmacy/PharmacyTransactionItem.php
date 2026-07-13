<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class PharmacyTransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'medicine_id', 'batch_id',
        'dosage', 'duration',
        'qty_required', 'available_qty',
        'unit_price', 'subtotal', 'store',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(PharmacyTransaction::class, 'transaction_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function returnItems()
    {
        return $this->hasMany(PharmacyReturnItem::class, 'transaction_item_id');
    }
}
