<?php

namespace App\Models\Pharmacy;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PharmacyReturn extends Model
{
    protected $fillable = [
        'return_no', 'transaction_id', 'transaction_type',
        'patient_id', 'returned_by',
        'total_amount', 'reason', 'status', 'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(PharmacyTransaction::class, 'transaction_id');
    }

    public function items()
    {
        return $this->hasMany(PharmacyReturnItem::class, 'return_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
