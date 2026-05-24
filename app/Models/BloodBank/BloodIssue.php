<?php

namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\CaseReference;
use App\Models\Charges\Charge;
use App\Models\User;

class BloodIssue extends Model
{
    protected $fillable = [
        'component_collection_id',
        'blood_collection_id',
        'patient_id',
        'case_id',
        'issue_datetime',
        'doctor_id',
        'reference_name',
        'technician_name',
        'charge_id',
        'type',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_datetime' => 'datetime',
    ];

    public function componentCollection()
    {
        return $this->belongsTo(ComponentCollection::class, 'component_collection_id');
    }

    public function bloodCollection()
    {
        return $this->belongsTo(BloodCollection::class, 'blood_collection_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function caseReference()
    {
        return $this->belongsTo(CaseReference::class, 'case_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function charge()
    {
        return $this->belongsTo(Charge::class, 'charge_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
