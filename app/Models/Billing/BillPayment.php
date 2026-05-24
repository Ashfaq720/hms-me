<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $fillable = [
        'bill_id', 'receipt_no', 'method', 'reference_no',
        'amount', 'payment_date', 'status', 'notes', 'collected_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
