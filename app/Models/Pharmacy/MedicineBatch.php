<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model
{
    protected $fillable = [
        'medicine_id',
        'batch_no',
        'expiry_date',
        'manufacture_date',
        'purchase_price',
        'selling_price',
        'quantity',
        'store',
        'note',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
