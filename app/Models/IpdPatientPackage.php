<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Junction record: a Service Package applied to an IPD admission.
 *
 * One admission can carry multiple packages; each goes through its own
 * state machine and is independently billable. The snapshot fields
 * (agreed_price, price_override) freeze the deal at apply-time so
 * editing the parent {@see ServicePackage} master later won't rewrite
 * patient history.
 */
class IpdPatientPackage extends Model
{
    use SoftDeletes;

    protected $table = 'ipd_patient_packages';

    public const STATUS_DRAFT            = 'Draft';
    public const STATUS_PENDING_APPROVAL = 'Pending Approval';
    public const STATUS_CONFIRMED        = 'Confirmed';
    public const STATUS_PARTIALLY_USED   = 'Partially Used';
    public const STATUS_COMPLETED        = 'Completed';
    public const STATUS_CANCELLED        = 'Cancelled';
    public const STATUS_REFUNDED         = 'Refunded';
    public const STATUS_CLOSED           = 'Closed';

    public const STATUSES = [
        self::STATUS_DRAFT, self::STATUS_PENDING_APPROVAL, self::STATUS_CONFIRMED,
        self::STATUS_PARTIALLY_USED, self::STATUS_COMPLETED,
        self::STATUS_CANCELLED, self::STATUS_REFUNDED, self::STATUS_CLOSED,
    ];

    /**
     * Statuses that count as "actively in use" — bill, modify, utilize.
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_CONFIRMED, self::STATUS_PARTIALLY_USED,
    ];

    protected $fillable = [
        'ipd_admission_id', 'service_package_id', 'bed_allocation_id',
        'agreed_price', 'price_override', 'price_override_approved_by',
        'status',
        'approved_by', 'approved_at',
        'cancellation_reason', 'cancelled_by', 'cancelled_at',
        'remarks',
        'applied_by', 'applied_at',
    ];

    protected $casts = [
        'agreed_price'   => 'decimal:2',
        'price_override' => 'decimal:2',
        'approved_at'    => 'datetime',
        'cancelled_at'   => 'datetime',
        'applied_at'     => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->applied_at)) $model->applied_at = now();
            if (empty($model->applied_by) && auth()->check()) $model->applied_by = auth()->id();
        });
    }

    /* ───────────── Relations ───────────── */

    public function ipdAdmission()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_admission_id');
    }

    /**
     * Shortcut to the patient via the IPD admission — saves callers from
     * writing optional($p->ipdAdmission)->patient everywhere.
     */
    public function patient()
    {
        return $this->hasOneThrough(
            Patient::class,
            IpdPatient::class,
            'id',                 // FK on ipd_patients
            'id',                 // FK on patients
            'ipd_admission_id',   // local key on ipd_patient_packages
            'patient_id'          // local key on ipd_patients
        );
    }

    /**
     * Shortcut to the case file the admission belongs to.
     */
    public function caseReference()
    {
        return $this->hasOneThrough(
            CaseReference::class,
            IpdPatient::class,
            'id',
            'id',
            'ipd_admission_id',
            'case_id'
        );
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /**
     * Bed allocation this package is tied to. ONE source of truth for
     * the bed — the package no longer duplicates bed info; it links.
     */
    public function bedAllocation()
    {
        return $this->belongsTo(IpdPatientBed::class, 'bed_allocation_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function priceOverrideApprover()
    {
        return $this->belongsTo(User::class, 'price_override_approved_by');
    }

    /* ───────────── Scopes ───────────── */

    public function scopeActive($q)
    {
        return $q->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeBillable($q)
    {
        return $q->whereIn('status', [
            self::STATUS_CONFIRMED, self::STATUS_PARTIALLY_USED, self::STATUS_COMPLETED,
        ]);
    }

    /* ───────────── Helpers ───────────── */

    public function effectivePrice(): float
    {
        return (float) ($this->price_override ?? $this->agreed_price);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function canBeCancelled(): bool
    {
        return ! in_array($this->status, [
            self::STATUS_CANCELLED, self::STATUS_REFUNDED, self::STATUS_CLOSED,
        ], true);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT            => 'bg-secondary',
            self::STATUS_PENDING_APPROVAL => 'bg-warning text-dark',
            self::STATUS_CONFIRMED        => 'bg-primary',
            self::STATUS_PARTIALLY_USED   => 'bg-info',
            self::STATUS_COMPLETED        => 'bg-success',
            self::STATUS_CANCELLED        => 'bg-danger',
            self::STATUS_REFUNDED         => 'bg-dark',
            self::STATUS_CLOSED           => 'bg-secondary',
            default                       => 'bg-light text-dark',
        };
    }
}
