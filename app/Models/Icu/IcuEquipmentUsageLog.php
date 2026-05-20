<?php

namespace App\Models\Icu;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\PatientCharge;
use Illuminate\Database\Eloquent\Model;

class IcuEquipmentUsageLog extends Model
{
    protected $table = 'icu_equipment_usage_logs';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'bed_id',
        'equipment_id',
        'equipment_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'billing_unit',
        'charge_rate',
        'total_amount',
        'covered_by_package',
        'package_enrollment_id',
        'status',
        'assigned_by',
        'removed_by',
        'remove_reason',
        'patient_charge_id',
        'remarks',
    ];

    protected $attributes = [
        'covered_by_package' => false,
    ];

    protected $casts = [
        'start_time'         => 'datetime',
        'end_time'           => 'datetime',
        'charge_rate'        => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'covered_by_package' => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function equipment()
    {
        return $this->belongsTo(IcuEquipment::class, 'equipment_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patientCharge()
    {
        return $this->belongsTo(PatientCharge::class, 'patient_charge_id');
    }

    /**
     * Compute billable amount based on duration and billing unit.
     * For an open log, pass a $now to get the running estimate.
     */
    public function computeAmount(?\DateTimeInterface $now = null): array
    {
        $start = $this->start_time;
        $end   = $this->end_time ?: ($now ?: now());

        $minutes = max(0, $start->diffInMinutes($end));

        switch ($this->billing_unit) {
            case 'Hour':
                $units = max(1, (int) ceil($minutes / 60));
                break;
            case 'Day':
                // Hospital policy in BRD §3.3: noon cutoff; default to ceil-day, min 1
                $days  = $minutes / (60 * 24);
                $units = max(1, (int) ceil($days));
                break;
            case 'Session':
                $units = 1;
                break;
            case 'Fixed':
                $units = 1;
                break;
            default:
                $units = 1;
        }

        $amount = round((float) $this->charge_rate * $units, 2);

        return [
            'minutes' => $minutes,
            'units'   => $units,
            'amount'  => $amount,
        ];
    }
}
