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

    protected $fillable = [
        'encounter_id', 'organization_id', 'branch_id', 'case_id',
        'patient_id', 'doctor_id', 'department_id',
        'arrival_time', 'discount_type', 'description',
        'age', 'gender', 'blood_group',
        'third_party_name', 'third_party_contact', 'relation',
        'priority', 'remarks', 'status',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    /* ───────── Lifecycle status / priority constants ─────────
       Consumed by ErPatientController for filter dropdowns + transfer
       logic. Values match the actual `status` enum used in the schema. */
    public const STATUS_WAITING       = 'WAITING';
    public const STATUS_UNDER_ASSESS  = 'UNDER_ASSESSMENT';
    public const STATUS_IN_TREATMENT  = 'IN_TREATMENT';
    public const STATUS_OBSERVATION   = 'OBSERVATION';
    public const STATUS_REFERRED      = 'REFERRED';
    public const STATUS_ADMITTED      = 'TRANSFERRED';
    public const STATUS_TRANSFERRED   = 'TRANSFERRED';
    public const STATUS_DISCHARGED    = 'DISCHARGED';
    public const STATUS_EXPIRED       = 'EXPIRED';
    public const STATUS_CANCELLED     = 'PENDING';

    public const STATUSES = [
        'PENDING', 'WAITING', 'UNDER_ASSESSMENT', 'IN_TREATMENT', 'OBSERVATION',
        'TRANSFERRED', 'REFERRED', 'DISCHARGED', 'EXPIRED',
    ];

    public const STATUSES_ACTIVE = ['WAITING', 'UNDER_ASSESSMENT', 'IN_TREATMENT', 'OBSERVATION'];

    public const PRIORITY_CRITICAL = 'CRITICAL';
    public const PRIORITY_HIGH     = 'HIGH';
    public const PRIORITY_NORMAL   = 'NORMAL';
    public const PRIORITIES = ['CRITICAL', 'HIGH', 'NORMAL'];

    public function getStatusBadgeClassAttribute(): string
    {
        return 'bg-' . $this->statusBadgeClass();
    }

    public function getTriageColorAttribute(): string
    {
        return $this->priorityBadgeClass();
    }

    public function getTriageLabelAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_CRITICAL => 'Red (Critical)',
            self::PRIORITY_HIGH     => 'Orange (Urgent)',
            self::PRIORITY_NORMAL   => 'Green (Stable)',
            default                 => $this->priority ?? '—',
        };
    }

    public function getWaitingMinutesAttribute(): int
    {
        if (! $this->arrival_time) return 0;
        $end = in_array($this->status, ['DISCHARGED','EXPIRED','REFERRED','TRANSFERRED'], true)
            ? ($this->updated_at ?? now())
            : now();
        return max(0, (int) $this->arrival_time->diffInMinutes($end));
    }

    public function caseReference()
    {
        return $this->belongsTo(\App\Models\CaseReference::class, 'case_id');
    }

    public function scopeToday($q)
    {
        return $q->whereDate('arrival_time', today());
    }

    public function scopeCritical($q)
    {
        return $q->where('priority', self::PRIORITY_CRITICAL);
    }

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
