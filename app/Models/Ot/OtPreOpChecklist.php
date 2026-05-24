<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtPreOpChecklist extends Model
{
    protected $table = 'ot_pre_op_checklists';

    protected $fillable = [
        'surgery_schedule_id', 'consent_obtained', 'lab_completed', 'radiology_completed',
        'fasting_confirmed', 'blood_arranged', 'allergy_reviewed', 'vitals_recorded',
        'anesthesia_clearance', 'doctor_clearance', 'nurse_confirmation', 'site_marked',
        'id_band_verified', 'vitals_snapshot', 'notes', 'emergency_override',
        'emergency_override_reason', 'override_approved_by', 'is_complete',
        'completed_at', 'completed_by',
    ];

    protected $casts = [
        'consent_obtained' => 'boolean', 'lab_completed' => 'boolean',
        'radiology_completed' => 'boolean', 'fasting_confirmed' => 'boolean',
        'blood_arranged' => 'boolean', 'allergy_reviewed' => 'boolean',
        'vitals_recorded' => 'boolean', 'anesthesia_clearance' => 'boolean',
        'doctor_clearance' => 'boolean', 'nurse_confirmation' => 'boolean',
        'site_marked' => 'boolean', 'id_band_verified' => 'boolean',
        'emergency_override' => 'boolean', 'is_complete' => 'boolean',
        'vitals_snapshot' => 'array', 'completed_at' => 'datetime',
    ];

    public const REQUIRED_ITEMS = [
        'consent_obtained', 'lab_completed', 'radiology_completed', 'fasting_confirmed',
        'blood_arranged', 'allergy_reviewed', 'vitals_recorded', 'anesthesia_clearance',
        'doctor_clearance', 'nurse_confirmation',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'completed_by');
    }

    public function overrideApprovedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'override_approved_by');
    }

    public function isReady(): bool
    {
        if ($this->emergency_override) {
            return true;
        }

        foreach (self::REQUIRED_ITEMS as $item) {
            if (! $this->{$item}) {
                return false;
            }
        }

        return true;
    }

    public function completionPercent(): int
    {
        $total = count(self::REQUIRED_ITEMS);
        $done = 0;
        foreach (self::REQUIRED_ITEMS as $item) {
            if ($this->{$item}) {
                $done++;
            }
        }

        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }
}
