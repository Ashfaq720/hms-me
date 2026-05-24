<?php

namespace App\Models\Ot;

use App\Models\BloodBank\BloodGroup;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtSurgeryRequest extends Model
{
    use SoftDeletes;

    protected $table = 'ot_surgery_requests';

    public const STATUS_DRAFT = 'Draft';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_UNDER_REVIEW = 'Under Review';
    public const STATUS_PENDING_INFORMATION = 'Pending Information';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_SENT_BACK = 'Sent Back for Correction';
    public const STATUS_FAST_TRACKED = 'Emergency Fast-Tracked';
    public const STATUS_MOVED_TO_SCHEDULING = 'Moved to Scheduling';
    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW,
        self::STATUS_PENDING_INFORMATION, self::STATUS_ACCEPTED, self::STATUS_REJECTED,
        self::STATUS_SENT_BACK, self::STATUS_FAST_TRACKED,
        self::STATUS_MOVED_TO_SCHEDULING, self::STATUS_SCHEDULED, self::STATUS_CANCELLED,
    ];

    public const BLOOD_COMPONENTS = ['Whole Blood', 'PRBC', 'FFP', 'Platelet', 'Cryoprecipitate'];

    public const OT_TYPES = [
        'General OT', 'Emergency OT', 'Orthopedic OT', 'Cardiac OT',
        'Gynecology OT', 'Minor OT', 'Major OT', 'Endoscopy OT',
    ];

    protected $fillable = [
        'request_no', 'case_id', 'patient_id', 'encounter_type', 'encounter_id', 'ipd_admission_id',
        'surgery_type_id', 'surgery_category_id', 'requested_by_doctor_id', 'primary_surgeon_id',
        'department_id', 'requested_surgery_date', 'requested_surgery_time',
        'estimated_duration_minutes', 'date_flexibility', 'flexibility_reason',
        'required_ot_type',
        'priority', 'is_emergency', 'emergency_reason', 'is_life_threatening', 'is_immediate_ot',
        'diagnosis', 'secondary_diagnosis', 'icd_code',
        'procedure_notes', 'clinical_indication', 'asa_grade', 'special_requirements',
        'blood_required', 'blood_units', 'blood_group', 'blood_group_id', 'blood_components',
        'crossmatch_required', 'blood_bank_instruction',
        'status', 'rejection_reason', 'pending_info_reason',
        'reviewed_by', 'reviewed_at',
        'junior_approval_required', 'junior_approved_by', 'junior_approved_at',
        'consultant_approval_required', 'consultant_approved_by', 'consultant_approved_at',
        'created_by',
    ];

    protected $casts = [
        'is_emergency' => 'boolean',
        'is_life_threatening' => 'boolean',
        'is_immediate_ot' => 'boolean',
        'blood_required' => 'boolean',
        'crossmatch_required' => 'boolean',
        'junior_approval_required' => 'boolean',
        'consultant_approval_required' => 'boolean',
        'blood_components' => 'array',
        'requested_surgery_date' => 'date',
        'reviewed_at' => 'datetime',
        'junior_approved_at' => 'datetime',
        'consultant_approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->request_no)) {
                $year = now()->format('Y');
                $prefix = "OTR-{$year}-";
                $last = static::withTrashed()
                    ->where('request_no', 'like', "{$prefix}%")
                    ->orderByDesc('id')
                    ->first();
                $next = 1;
                if ($last && preg_match('/-(\d+)$/', $last->request_no, $m)) {
                    $next = ((int) $m[1]) + 1;
                }
                $model->request_no = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function surgeryType()
    {
        return $this->belongsTo(OtSurgeryType::class);
    }

    public function category()
    {
        return $this->belongsTo(OtSurgeryCategory::class, 'surgery_category_id');
    }

    public function requestedByDoctor()
    {
        return $this->belongsTo(Doctor::class, 'requested_by_doctor_id');
    }

    public function primarySurgeon()
    {
        return $this->belongsTo(Doctor::class, 'primary_surgeon_id');
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_id');
    }

    public function juniorApprover()
    {
        return $this->belongsTo(User::class, 'junior_approved_by');
    }

    public function consultantApprover()
    {
        return $this->belongsTo(User::class, 'consultant_approved_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function ipdAdmission()
    {
        return $this->belongsTo(\App\Models\IpdPatient::class, 'ipd_admission_id');
    }

    public function caseReference()
    {
        return $this->belongsTo(\App\Models\CaseReference::class, 'case_id');
    }

    /**
     * Resolve the encounter record dynamically by encounter_type.
     * Use this helper instead of a true morphTo because encounter_type
     * holds a short string ('IPD' / 'OPD' / 'ER'), not a class name.
     */
    public function resolveEncounter()
    {
        return match (strtoupper($this->encounter_type ?: '')) {
            'IPD' => $this->encounter_id ? \App\Models\IpdPatient::find($this->encounter_id) : null,
            'OPD' => $this->encounter_id ? \App\Models\OpdPatient::find($this->encounter_id) : null,
            'ER'  => $this->encounter_id && class_exists(\App\Models\FrontDesk\ErPatient::class)
                ? \App\Models\FrontDesk\ErPatient::find($this->encounter_id) : null,
            default => null,
        };
    }

    public function schedules()
    {
        return $this->hasMany(OtSurgerySchedule::class, 'surgery_request_id');
    }

    public function activeSchedule()
    {
        return $this->hasOne(OtSurgerySchedule::class, 'surgery_request_id')
            ->whereNotIn('status', ['Cancelled', 'Closed']);
    }

    public function documents()
    {
        return $this->hasMany(OtDocument::class, 'surgery_request_id');
    }

    public function equipments()
    {
        return $this->hasMany(OtRequestEquipment::class, 'surgery_request_id');
    }

    /**
     * Approvals are satisfied if neither tier is required, or the
     * required tiers have a recorded approval timestamp.
     */
    public function approvalsSatisfied(): bool
    {
        if ($this->junior_approval_required && ! $this->junior_approved_at) {
            return false;
        }
        if ($this->consultant_approval_required && ! $this->consultant_approved_at) {
            return false;
        }
        return true;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW => 'bg-info',
            self::STATUS_PENDING_INFORMATION, self::STATUS_SENT_BACK => 'bg-warning text-dark',
            self::STATUS_ACCEPTED, self::STATUS_MOVED_TO_SCHEDULING => 'bg-primary',
            self::STATUS_FAST_TRACKED => 'bg-danger',
            self::STATUS_SCHEDULED => 'bg-success',
            self::STATUS_REJECTED, self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
