<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prescription) {
            $lastPrescription              = static::latest('id')->first();
            $nextNo                        = $lastPrescription ? ((int) substr($lastPrescription->prescription_no, 2)) + 1 : 1;
            $prescription->prescription_no = 'RX' . str_pad($nextNo, 5, '0', STR_PAD_LEFT);
        });
    }
    protected $fillable = [
        'prescription_no',
        'opd_patient_id',
        'ipd_patient_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'date',
        'findings',
        'icd10_code',
        'icd10_description',
        'advice',
        'next_visit',
        'follow_up_note',
        'radiology_orders',
        'generated_by',
        'type',
    ];

    protected $casts = [
        'date'       => 'datetime',
        'next_visit' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }

    public function symptoms()
    {
        return $this->hasMany(PresciptionSymptom::class);
    }

    public function medicines()
    {
        return $this->hasMany(PresciptionMedicine::class);
    }

    public function labInvestigations()
    {
        return $this->hasMany(PresciptionLabInvestigation::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'generated_by');
    }
}
