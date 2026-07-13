<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class CounterSaleItem extends Model
{
    protected $fillable = [
        'counter_sale_id',
        'medicine_id',
        'dosage',
        'qty',
        'unit_price',
        'subtotal',
        'store',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function sale()
    {
        return $this->belongsTo(CounterSale::class, 'counter_sale_id');
    }
}
