<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'patient_id',
        'case_id',
        'ipd_patient_id',
        'opd_patient_id',
        'pathology_bill_id',
        'pharmacy_bill_id',
        'radiology_bill_id',
        'blood_bank_bill_id',
        'invoice_no',
        'type',
        'section',
        'amount',
        'vat',
        'tax',
        'discount',
        'net_amount',
        'payment_via',
        'payment_date',
        'cheque_name',
        'cheque_no',
        'cheque_date',
        'card_no',
        'card_type',
        'mfs_type',
        'mfs_no',
        'mfs_transaction_id',
        'notes',
        'received_by',
        'files',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($transaction) {

            // If invoice_no already set (manual entry), skip auto generation
            if (! empty($transaction->invoice_no)) {
                return;
            }

            $lastCode = self::where('invoice_no', 'like', 'INV-%')
                ->orderBy('id', 'desc')
                ->value('invoice_no');

            if ($lastCode) {
                // Extract numeric part
                $number     = (int) str_replace('INV-', '', $lastCode);
                $nextNumber = $number + 1;
            } else {
                $nextNumber = 1;
            }

            // Pad with leading zeros (INV-001)
            $transaction->invoice_no = 'INV-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class, 'opd_patient_id');
    }

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }

    public function case ()
    {
        return $this->belongsTo(CaseReference::class, 'case_id');
    }
}
