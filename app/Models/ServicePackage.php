<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Service Package master record.
 *
 * Distinct from the legacy {@see Package} model — that targets the older
 * `packages` table which is unused. This one drives the rich hospital-wide
 * package picker (Patient Type → Department → Bed Type → Package).
 */
class ServicePackage extends Model
{
    use SoftDeletes;

    protected $table = 'service_packages';

    public const TYPES = [
        'IPD', 'OPD', 'OT', 'ICU', 'CCU',
        'Diagnostic', 'Health Checkup', 'Procedure',
    ];

    public const ADMISSION_TYPES = ['Planned', 'Emergency', 'Day Care'];

    public const PATIENT_CATEGORIES = ['General', 'Corporate', 'Insurance'];

    public const STATUSES = ['Active', 'Inactive'];

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    protected $fillable = [
        'code', 'name', 'slug',
        'package_type', 'department_id',
        'admission_type', 'bed_type_id',
        'surgery_type_id', 'surgery_category_id',
        'duration_days',
        'base_price',
        'included_services_text', 'excluded_services_text',
        'patient_category',
        'requires_approval', 'approval_role',
        'status', 'remarks',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'base_price'        => 'decimal:2',
        'duration_days'     => 'integer',
        'requires_approval' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->code)) {
                $model->code = self::nextCode($model->package_type);
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . strtolower(Str::random(6));
            }
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function (self $model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Generate the next package code for a given type, e.g. PKG-OT-001.
     */
    public static function nextCode(?string $type = null): string
    {
        $typeSlug = $type ? strtoupper(preg_replace('/[^A-Z]/', '', strtoupper($type))) : 'GEN';
        $prefix   = "PKG-{$typeSlug}-";
        $last     = static::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = 1;
        if ($last && preg_match('/-(\d+)$/', $last->code, $m)) {
            $next = ((int) $m[1]) + 1;
        }
        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /* ───────────── Relations ───────────── */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function surgeryType()
    {
        return $this->belongsTo(\App\Models\Ot\OtSurgeryType::class, 'surgery_type_id');
    }

    public function surgeryCategory()
    {
        return $this->belongsTo(\App\Models\Ot\OtSurgeryCategory::class, 'surgery_category_id');
    }

    public function items()
    {
        return $this->hasMany(ServicePackageItem::class)->orderBy('sort_order');
    }

    public function bedPrices()
    {
        return $this->hasMany(ServicePackageBedPrice::class);
    }

    /**
     * Every time this package has been applied to an IPD admission.
     * Used for utilization reports and "currently in use" counts.
     */
    public function applications()
    {
        return $this->hasMany(IpdPatientPackage::class, 'service_package_id');
    }

    /**
     * Patient charges posted against this package — the bill trail.
     * Lets the package show revenue / billed amount across all
     * applications without joining the bigger billing tables manually.
     */
    public function patientCharges()
    {
        return $this->hasMany(\App\Models\PatientCharge::class, 'service_package_id');
    }

    /**
     * OT schedules using this package (when the OT booking is created
     * via the "Use Package" flow). Useful for OT scheduling reports.
     */
    public function otSchedules()
    {
        return $this->hasMany(\App\Models\Ot\OtSurgerySchedule::class, 'service_package_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ───────────── Scopes ───────────── */

    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOfType($q, string $type)
    {
        return $q->where('package_type', $type);
    }

    public function scopeForDepartment($q, ?int $departmentId)
    {
        return $departmentId ? $q->where(function ($x) use ($departmentId) {
            $x->where('department_id', $departmentId)->orWhereNull('department_id');
        }) : $q;
    }

    /* ───────────── Helpers ───────────── */

    /**
     * Return the price for a given bed type. Falls back to base_price.
     */
    public function priceForBedType(?int $bedTypeId): float
    {
        if (! $bedTypeId) {
            return (float) $this->base_price;
        }

        $variant = $this->bedPrices->firstWhere('bed_type_id', $bedTypeId);

        return $variant ? (float) $variant->price : (float) $this->base_price;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
    }
}
