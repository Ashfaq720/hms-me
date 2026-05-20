<?php
namespace App\Models\Pharmacy;

use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PharmacyTransaction extends Model
{
    protected $fillable = [
        'transaction_no', 'transaction_type',
        'patient_id', 'pharmacist_id',
        'drug_count', 'total_amount', 'discount_amount', 'paid_amount',
        'payment_method', 'payment_status', 'status', 'note',
        // OPD
        'opd_patient_id', 'prescription_id',
        // Ipd
        'ipd_patient_id', 'requisition_no', 'ward_bed', 'request_source',
        // OTC
        'customer_name', 'customer_phone',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount'     => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PharmacyTransactionItem::class, 'transaction_id');
    }

    public function returns()
    {
        return $this->hasMany(PharmacyReturn::class, 'transaction_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class, 'opd_patient_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            'opd'   => 'OPD Dispense',
            'ipd'   => 'Ipd Issue',
            'otc'   => 'Counter Sale',
            default => strtoupper($this->transaction_type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->transaction_type) {
            'opd'   => 'bg-primary',
            'ipd'   => 'bg-info text-dark',
            'otc'   => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
