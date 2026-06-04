<?php

namespace App\Models\Encounter;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Organization\Branch;
use App\Models\Organization\Organization;
use App\Models\Patient;
use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encounter extends Model
{
    use SoftDeletes, LogPreference, BranchScoped;

    protected string $logName = 'encounter';

    protected $fillable = [
        'encounter_no', 'organization_id', 'branch_id', 'patient_id',
        'encounter_type', 'source', 'parent_encounter_id',
        'appointment_id', 'doctor_id', 'department_id',
        'subject_type', 'subject_id',
        'status', 'started_at', 'closed_at',
        'chief_complaint', 'notes', 'is_medico_legal',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_medico_legal' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $encounter) {
            if (empty($encounter->encounter_no)) {
                $encounter->encounter_no = self::generateEncounterNumber($encounter->encounter_type);
            }
            if (empty($encounter->started_at)) {
                $encounter->started_at = now();
            }
        });
    }

    public static function generateEncounterNumber(string $type): string
    {
        $prefix = match ($type) {
            'OPD' => 'OPD',
            'IPD' => 'IPD',
            'ER' => 'ER',
            'ICU' => 'ICU',
            'CCU' => 'CCU',
            'NICU' => 'NICU',
            'OT' => 'OT',
            'PROCEDURE' => 'PRC',
            'LAB_ONLY' => 'LAB',
            'RADIOLOGY_ONLY' => 'RAD',
            'PHARMACY_ONLY' => 'PHR',
            'AMBULANCE' => 'AMB',
            'TELEMEDICINE' => 'TEL',
            'HEALTH_CHECKUP' => 'HCK',
            default => 'ENC',
        };

        return sprintf('%s-%s-%06d', $prefix, date('Ym'), random_int(1, 999999));
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_encounter_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_encounter_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function serviceChargePostings()
    {
        return $this->hasMany(\App\Models\ServiceCharge\ServiceChargePosting::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['draft', 'open', 'in_progress', 'on_hold']);
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
