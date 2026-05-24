<?php

namespace App\Models;

use App\Models\Ot\OtSurgerySchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NICU admission — one record per newborn admitted to NICU.
 *
 * Captures the moment a baby enters the unit, who the mother is, where
 * the baby came from (OT delivery / IPD transfer / ER / external), and
 * the clinical snapshot at admission. Vitals, feeding, growth, etc. are
 * separate child tables introduced in Phase B.
 */
class NicuAdmission extends Model
{
    use SoftDeletes;

    protected $table = 'nicu_admissions';

    public const SOURCE_OT       = 'OT';
    public const SOURCE_IPD      = 'IPD';
    public const SOURCE_ER       = 'ER';
    public const SOURCE_EXTERNAL = 'External';
    public const SOURCES = [self::SOURCE_OT, self::SOURCE_IPD, self::SOURCE_ER, self::SOURCE_EXTERNAL];

    public const STATUS_ADMITTED    = 'Admitted';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_DISCHARGED  = 'Discharged';
    public const STATUS_TRANSFERRED = 'Transferred';
    public const STATUS_DECEASED    = 'Deceased';
    public const STATUSES = [
        self::STATUS_ADMITTED, self::STATUS_IN_PROGRESS, self::STATUS_DISCHARGED,
        self::STATUS_TRANSFERRED, self::STATUS_DECEASED,
    ];

    public const DELIVERY_TYPES = ['Vaginal', 'C-Section', 'Assisted', 'Other'];

    protected $fillable = [
        'admission_no',
        'mother_patient_id', 'baby_patient_id',
        'case_id',
        'source_type', 'source_id',
        'bed_id', 'bed_type_id',
        'admitted_at', 'birth_at',
        'birth_weight_grams', 'birth_length_cm', 'head_circumference_cm',
        'gestational_age_weeks', 'apgar_1min', 'apgar_5min', 'delivery_type',
        'is_preterm', 'is_low_birth_weight', 'is_critical', 'is_multiple_birth',
        'status', 'service_package_id',
        'clinical_notes', 'discharge_summary', 'discharged_at', 'discharged_by',
        'admitted_by',
    ];

    protected $casts = [
        'admitted_at'        => 'datetime',
        'birth_at'           => 'datetime',
        'discharged_at'      => 'datetime',
        'birth_weight_grams' => 'decimal:2',
        'birth_length_cm'    => 'decimal:2',
        'head_circumference_cm' => 'decimal:2',
        'is_preterm'           => 'boolean',
        'is_low_birth_weight'  => 'boolean',
        'is_critical'          => 'boolean',
        'is_multiple_birth'    => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->admission_no)) {
                $year = now()->format('Y');
                $prefix = "NICU-{$year}-";
                $last = static::withTrashed()
                    ->where('admission_no', 'like', $prefix . '%')
                    ->orderByDesc('id')->first();
                $next = 1;
                if ($last && preg_match('/-(\d+)$/', $last->admission_no, $m)) {
                    $next = ((int) $m[1]) + 1;
                }
                $model->admission_no = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            }
            if (empty($model->admitted_at)) $model->admitted_at = now();
            if (empty($model->admitted_by) && auth()->check()) $model->admitted_by = auth()->id();

            // Auto-flag risk bits from the clinical snapshot.
            $model->applyRiskFlags();
        });
    }

    /**
     * Compute risk flags from birth data. Idempotent — call before
     * save() or after modifying birth_weight / gestational_age / APGAR.
     */
    public function applyRiskFlags(): void
    {
        $this->is_low_birth_weight = $this->birth_weight_grams !== null
            && $this->birth_weight_grams < 2500;
        $this->is_preterm = $this->gestational_age_weeks !== null
            && $this->gestational_age_weeks < 37;
        // APGAR < 7 at 5 min is the operational definition of critical.
        $this->is_critical = $this->apgar_5min !== null && $this->apgar_5min < 7;
    }

    /* ──────── Relations ──────── */

    public function mother()
    {
        return $this->belongsTo(Patient::class, 'mother_patient_id');
    }

    public function baby()
    {
        return $this->belongsTo(Patient::class, 'baby_patient_id');
    }

    public function caseReference()
    {
        return $this->belongsTo(CaseReference::class, 'case_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function admittedBy()
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function dischargedBy()
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /* ──────── Phase B child tables ──────── */

    public function vitals()
    {
        return $this->hasMany(\App\Models\Nicu\NicuVital::class, 'nicu_admission_id');
    }

    public function feedings()
    {
        return $this->hasMany(\App\Models\Nicu\NicuFeeding::class, 'nicu_admission_id');
    }

    public function growthRecords()
    {
        return $this->hasMany(\App\Models\Nicu\NicuGrowthRecord::class, 'nicu_admission_id');
    }

    public function medications()
    {
        return $this->hasMany(\App\Models\Nicu\NicuMedication::class, 'nicu_admission_id');
    }

    public function procedures()
    {
        return $this->hasMany(\App\Models\Nicu\NicuProcedure::class, 'nicu_admission_id');
    }

    public function infections()
    {
        return $this->hasMany(\App\Models\Nicu\NicuInfection::class, 'nicu_admission_id');
    }

    public function consents()
    {
        return $this->hasMany(\App\Models\Nicu\NicuConsent::class, 'nicu_admission_id');
    }

    /**
     * Resolve the source record by its slug. Mirrors the resolveEncounter
     * pattern used in OtSurgeryRequest.
     */
    public function resolveSource()
    {
        if (! $this->source_type || ! $this->source_id) return null;

        return match (strtoupper($this->source_type)) {
            'OT'       => OtSurgerySchedule::find($this->source_id),
            'IPD'      => IpdPatient::find($this->source_id),
            'ER'       => class_exists(\App\Models\FrontDesk\ErPatient::class)
                          ? \App\Models\FrontDesk\ErPatient::find($this->source_id) : null,
            'EXTERNAL' => null,
            default    => null,
        };
    }

    /* ──────── Helpers ──────── */

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ADMITTED, self::STATUS_IN_PROGRESS => 'bg-info',
            self::STATUS_DISCHARGED                          => 'bg-success',
            self::STATUS_TRANSFERRED                         => 'bg-secondary',
            self::STATUS_DECEASED                            => 'bg-danger',
            default                                          => 'bg-light text-dark',
        };
    }

    public function riskBadges(): array
    {
        $b = [];
        if ($this->is_preterm)          $b[] = ['Preterm',          'bg-warning text-dark'];
        if ($this->is_low_birth_weight) $b[] = ['Low Birth Weight', 'bg-warning text-dark'];
        if ($this->is_critical)         $b[] = ['Critical',         'bg-danger'];
        if ($this->is_multiple_birth)   $b[] = ['Multiple Birth',   'bg-info'];
        return $b;
    }
}
