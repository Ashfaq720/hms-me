<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class BillDiscount extends Model
{
    protected $fillable = [
        'bill_id', 'kind', 'mode', 'value', 'amount_applied',
        'reason', 'status', 'requested_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'amount_applied' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
