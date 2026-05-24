<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CounterSale extends Model
{
    protected $fillable = [
        'sale_no',
        'customer_name',
        'customer_phone',
        'pharmacist_id',
        'drug_count',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'payment_method',
        'payment_status',
        'status',
        'note',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
    ];

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function items()
    {
        return $this->hasMany(CounterSaleItem::class);
    }
}
