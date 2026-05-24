<?php

namespace App\Models\FrontDesk;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Encounter\Encounter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class ErPatient extends Model
{
    protected $table = 'er_patients';
    protected $guarded = [];

    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    public function patient()    { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function doctor()     { return $this->belongsTo(Doctor::class, 'doctor_id'); }
    public function department() { return $this->belongsTo(Department::class, 'department_id'); }
    public function encounter()  { return $this->belongsTo(Encounter::class, 'encounter_id'); }

    public function triages()       { return $this->hasMany(\App\Models\Er\ErTriage::class)->orderByDesc('triaged_at'); }
    public function latestTriage()  { return $this->hasOne(\App\Models\Er\ErTriage::class)->latestOfMany('triaged_at'); }
    public function clinicalNotes() { return $this->hasMany(\App\Models\Er\ErClinicalNote::class)->orderByDesc('recorded_at'); }
    public function observations()  { return $this->hasMany(\App\Models\Er\ErObservation::class)->orderByDesc('observed_at'); }
    public function transfers()     { return $this->hasMany(\App\Models\Er\ErTransfer::class)->orderByDesc('requested_at'); }

    public function scopeActive($q)
    {
        return $q->whereNotIn('status', ['DISCHARGED', 'EXPIRED', 'REFERRED', 'TRANSFERRED']);
    }

    public function statusBadgeClass(): string
    {
        return [
            'PENDING'         => 'secondary',
            'WAITING'         => 'warning text-dark',
            'UNDER_ASSESSMENT'=> 'info',
            'IN_TREATMENT'    => 'primary',
            'OBSERVATION'     => 'info',
            'TRANSFERRED'     => 'success',
            'REFERRED'        => 'dark',
            'DISCHARGED'      => 'success',
            'EXPIRED'         => 'dark',
        ][$this->status] ?? 'secondary';
    }

    public function priorityBadgeClass(): string
    {
        return [
            'CRITICAL' => 'danger',
            'HIGH'     => 'warning text-dark',
            'NORMAL'   => 'success',
        ][$this->priority] ?? 'secondary';
    }
}
