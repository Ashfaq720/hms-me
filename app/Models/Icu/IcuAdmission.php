<?php
namespace App\Models\Icu;

use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\IpdPatient;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IcuAdmission extends Model
{
    protected $table = 'icu_admissions';

    protected $fillable = [
        'icu_case_id',
        'case_id',
        'patient_id',
        'source_type',
        'source_id',
        'icu_type',
        'admission_type',
        'admission_diagnosis',
        'referring_doctor_id',
        'isolation_type',
        'ventilator_required',
        'monitor_required',
        'bed_id',
        'admission_time',
        'transfer_time',
        'discharge_time',
        'status',
        'outcome',
        'outcome_remarks',
        'created_by',
        'approved_by',
        'closed_by',
        'remarks',
    ];

    protected $casts = [
        'admission_time'      => 'datetime',
        'transfer_time'       => 'datetime',
        'discharge_time'      => 'datetime',
        'ventilator_required' => 'boolean',
        'monitor_required'    => 'boolean',
    ];

    /**
     * Generate ICU case ID atomically: <ICU_TYPE>-YYYYMMDD-NNNN.
     * Caller MUST be inside a DB transaction so the SELECT...FOR UPDATE
     * holds against concurrent admissions.
     */
    public static function generateCaseId(string $icuType,  ? \DateTimeInterface $when = null) : string
    {
        $when   = $when ?: now();
        $date   = $when->format('Ymd');
        $prefix = strtoupper($icuType) . '-' . $date . '-';

        $last = static::where('icu_case_id', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextSerial = 1;
        if ($last) {
            $tail       = (int) substr($last->icu_case_id, strlen($prefix));
            $nextSerial = $tail + 1;
        }

        return $prefix . str_pad((string) $nextSerial, 4, '0', STR_PAD_LEFT);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function referringDoctor()
    {
        return $this->belongsTo(Doctor::class, 'referring_doctor_id');
    }

    public function ipdPatient()
    {
        // source_id points at ipd_patients.id when the admission originated from IPD
        return $this->belongsTo(IpdPatient::class, 'source_id');
    }

    public function overrides()
    {
        return $this->hasMany(IcuAdmissionOverride::class, 'icu_admission_id');
    }

    public function equipmentUsageLogs()
    {
        return $this->hasMany(IcuEquipmentUsageLog::class, 'icu_admission_id');
    }

    public function activeEquipmentUsage()
    {
        return $this->hasMany(IcuEquipmentUsageLog::class, 'icu_admission_id')
            ->where('status', 'InUse');
    }

    public function equipmentChangeLogs()
    {
        return $this->hasMany(IcuEquipmentChangeLog::class, 'icu_admission_id');
    }

    public function doctorOrders()
    {
        return $this->hasMany(IcuDoctorOrder::class, 'icu_admission_id');
    }

    public function nursingNotes()
    {
        return $this->hasMany(IcuNursingNote::class, 'icu_admission_id');
    }

    public function intakeOutputEntries()
    {
        return $this->hasMany(IcuIntakeOutputChart::class, 'icu_admission_id');
    }

    public function vitalLogs()
    {
        return $this->hasMany(IcuVitalLog::class, 'icu_admission_id');
    }

    public function vitalThresholds()
    {
        return $this->hasMany(IcuVitalThreshold::class, 'icu_admission_id');
    }

    public function alerts()
    {
        return $this->hasMany(IcuAlert::class, 'icu_admission_id');
    }

    public function emergencyEvents()
    {
        return $this->hasMany(IcuEmergencyEvent::class, 'icu_admission_id');
    }

    public function infectionRecords()
    {
        return $this->hasMany(IcuInfectionRecord::class, 'icu_admission_id');
    }

    public function activeInfections()
    {
        return $this->hasMany(IcuInfectionRecord::class, 'icu_admission_id')->where('is_active', true);
    }

    public function antibioticUsage()
    {
        return $this->hasMany(IcuAntibioticUsageLog::class, 'icu_admission_id');
    }

    public function infectionExposures()
    {
        return $this->hasMany(IcuInfectionExposureLog::class, 'icu_admission_id');
    }

    public function infectionOverrides()
    {
        return $this->hasMany(IcuInfectionControlOverride::class, 'icu_admission_id');
    }

    public function transfers()
    {
        return $this->hasMany(IcuTransfer::class, 'icu_admission_id');
    }

    public function dischargeSummary()
    {
        return $this->hasOne(IcuDischargeSummary::class, 'icu_admission_id');
    }

    public function mortalityAudit()
    {
        return $this->hasOne(IcuMortalityAudit::class, 'icu_admission_id');
    }

    /**
     * Pathology orders linked by case_id.
     *
     * NOTE: source filter (icu/ccu) is NOT applied here because Laravel
     * eager loading invokes this method on a fresh empty instance, so
     * $this->unitKey() would always resolve to the default 'icu'. Callers
     * MUST scope by source via a closure, e.g.
     *   ->load(['pathologyOrders' => fn($q) => $q->where('source', $admission->unitKey())])
     */
    public function pathologyOrders()
    {
        return $this->hasMany(LabInvestigationOrder::class, 'case_id', 'case_id')
            ->where('type', 'pathology');
    }

    public function radiologyOrders()
    {
        return $this->hasMany(LabInvestigationOrder::class, 'case_id', 'case_id')
            ->where('type', 'radiology');
    }

    public function packageEnrollments()
    {
        return $this->hasMany(IcuPatientPackageEnrollment::class, 'icu_admission_id');
    }

    public function activePackageEnrollment()
    {
        return $this->hasOne(IcuPatientPackageEnrollment::class, 'icu_admission_id')
            ->where('status', 'Active')
            ->latest('id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Approved', 'Admitted']);
    }

    public function isFromIpd(): bool
    {
        return strcasecmp((string) $this->source_type, 'Ipd') === 0
        && ! empty($this->source_id);
    }

    public function ipdIdForCharge(): ?int
    {
        return $this->isFromIpd() ? (int) $this->source_id : null;
    }

    /**
     * Lowercased unit key used for tagging downstream artifacts
     * (e.g. lab_investigation_order.source). Falls back to 'icu' if blank.
     */
    public function unitKey(): string
    {
        return strtolower((string) ($this->icu_type ?: 'icu'));
    }
}
