<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuPatientPackageEnrollment extends Model
{
    protected $table = 'icu_patient_package_enrollments';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'package_id',
        'billing_mode',
        'start_time',
        'end_time',
        'status',
        'applied_by',
        'approval_reference',
        'remarks',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(IcuPackage::class, 'package_id');
    }

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'Active');
    }
}
