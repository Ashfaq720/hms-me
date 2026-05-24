<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class BillRefund extends Model
{
    protected $fillable = [
        'bill_id', 'bill_payment_id', 'refund_no', 'amount', 'reason',
        'status', 'requested_by', 'approved_by', 'approved_at', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function payment()
    {
        return $this->belongsTo(BillPayment::class, 'bill_payment_id');
    }
}
