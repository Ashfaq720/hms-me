<?php

namespace App\Models\Nicu;

use App\Models\Encounter\Encounter;
use App\Models\IpdPatient;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NicuAdmission extends Model
{
    use SoftDeletes;

    protected $table = 'nicu_admissions';

    protected $fillable = [
        'ipd_patient_id', 'patient_id', 'encounter_id',
        'mother_patient_id', 'mother_ipd_patient_id',
        'baby_id', 'source', 'birth_type',
        'is_multiple_birth', 'birth_order',
        'birth_weight_g', 'birth_length_cm', 'head_circumference_cm',
        'gestational_age_weeks', 'apgar_1min', 'apgar_5min', 'apgar_10min',
        'admission_priority',
        'is_low_birth_weight', 'is_preterm', 'is_critical',
        'status', 'admission_time', 'discharge_time',
        'admission_notes', 'admitted_by',
    ];

    protected $casts = [
        'is_multiple_birth' => 'boolean',
        'is_low_birth_weight' => 'boolean',
        'is_preterm' => 'boolean',
        'is_critical' => 'boolean',
        'admission_time' => 'datetime',
        'discharge_time' => 'datetime',
        'birth_weight_g' => 'decimal:2',
    ];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function mother() { return $this->belongsTo(Patient::class, 'mother_patient_id'); }
    public function motherIpd() { return $this->belongsTo(IpdPatient::class, 'mother_ipd_patient_id'); }
    public function ipdPatient() { return $this->belongsTo(IpdPatient::class); }
    public function encounter() { return $this->belongsTo(Encounter::class); }

    public function resources() { return $this->hasMany(NicuResourceAllocation::class); }
    public function vitals() { return $this->hasMany(NicuVital::class); }
    public function feedingSchedules() { return $this->hasMany(NicuFeedingSchedule::class); }
    public function feedLogs() { return $this->hasMany(NicuFeedLog::class); }
    public function growthRecords() { return $this->hasMany(NicuGrowthRecord::class); }
    public function medicationOrders() { return $this->hasMany(NicuMedicationOrder::class); }
    public function procedures() { return $this->hasMany(NicuProcedure::class); }
    public function infections() { return $this->hasMany(NicuInfectionRecord::class); }
    public function consents() { return $this->hasMany(NicuConsent::class); }
    public function dischargeSummary() { return $this->hasOne(NicuDischargeSummary::class); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if (empty($row->baby_id)) {
                $row->baby_id = 'NICU-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            // Auto risk flags
            if ($row->birth_weight_g !== null && $row->birth_weight_g < 2500) {
                $row->is_low_birth_weight = true;
            }
            if ($row->gestational_age_weeks !== null && $row->gestational_age_weeks < 37) {
                $row->is_preterm = true;
            }
            if ($row->apgar_5min !== null && $row->apgar_5min < 7) {
                $row->is_critical = true;
                if ($row->admission_priority === 'ROUTINE') {
                    $row->admission_priority = 'CRITICAL';
                }
            }
        });
    }
}
