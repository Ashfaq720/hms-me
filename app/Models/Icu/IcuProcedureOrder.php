<?php

namespace App\Models\Icu;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuProcedureOrder extends Model
{
    protected $table = 'icu_procedure_order';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'doctor_id',
        'category',
        'type',
        'priority',
        'start_datetime',
        'details',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
    ];

    public static array $categoryTypes = [
        'General'       => ['Intubation', 'Endoscopy'],
        'Emergency'     => ['Tracheostomy'],
        'ICU Procedure' => ['Dialysis'],
        'Surgical'      => ['Appendectomy'],
        'Cosmetic'      => ['Rhinoplasty'],
        'Orthopedic'    => ['Hip Replacement'],
        'Gynecology'    => ['D&C'],
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
