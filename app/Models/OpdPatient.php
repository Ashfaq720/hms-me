<?php

namespace App\Models;

use App\Models\FrontDesk\VitalCheck;
use App\Models\Opd\OpdMedication;
use App\Models\PatientHistory;
use App\Models\ConsultationNote;
use Illuminate\Database\Eloquent\Model;

class OpdPatient extends Model
{
    protected $table    = 'opd_patients';
    protected $fillable = [
        'case_id',
        'patient_id',
        'doctor_id',
        'shift_id',
        'slot_time_from',
        'slot_time_to',
        'department_id',
        'date',
        'visit_date',
        'serial_no',
        'token_no',
        'remarks',
        'status',
        'visit_type',
        'parent_visit_id',
        'root_visit_id',
        'chief_complaint',
        'referral_source',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function vitalChecks()
    {
        return $this->hasMany(VitalCheck::class, 'opd_patient_id', 'id');
    }

    public function parentVisit()
    {
        return $this->belongsTo(OpdPatient::class, 'parent_visit_id');
    }

    public function rootVisit()
    {
        return $this->belongsTo(OpdPatient::class, 'root_visit_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'opd_patient_id')->latest('date');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'opd_patient_id')->latest('payment_date');
    }

    public function charges()
    {
        return $this->hasMany(PatientCharge::class, 'opd_id')->latest('date');
    }

    public function medications()
    {
        return $this->hasMany(OpdMedication::class, 'opd_patient_id')->latest('datetime');
    }

    public function recheckups()
    {
        return $this->hasMany(OpdPatient::class, 'parent_visit_id');
    }

    public function consultationNote()
    {
        return $this->hasOne(ConsultationNote::class, 'opd_patient_id');
    }

    public function documents()
    {
        return $this->hasMany(OpdPatientDocument::class, 'opd_patient_id')->latest();
    }

    public function patientHistories()
    {
        return $this->hasOneThrough(
            PatientHistory::class,
            Patient::class,
            'id',
            'patient_id',
            'patient_id',
            'id'
        );
    }

    public function getVisitTypeLabelAttribute(): string
    {
        return match ($this->visit_type) {
            'new'        => 'New',
            'follow_up'  => 'Follow-up',
            'recheckup'  => 'Re-checkup',
            'referred'   => 'Referred',
            'emergency'  => 'Emergency',
            default      => ucfirst($this->visit_type ?? ''),
        };
    }

    public function getVisitTypeBadgeAttribute(): string
    {
        return match ($this->visit_type) {
            'new'        => 'bg-primary',
            'follow_up'  => 'bg-info',
            'recheckup'  => 'bg-secondary',
            'referred'   => 'bg-warning text-dark',
            'emergency'  => 'bg-danger',
            default      => 'bg-secondary',
        };
    }
}
