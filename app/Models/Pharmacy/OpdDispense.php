<?php

namespace App\Models\Pharmacy;

use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OpdDispense extends Model
{
    protected $fillable = [
        'dispense_no',
        'opd_patient_id',
        'prescription_id',
        'patient_id',
        'pharmacist_id',
        'drug_count',
        'total_amount',
        'payment_status',
        'status',
        'note',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function items()
    {
        return $this->hasMany(OpdDispenseItem::class);
    }
}
