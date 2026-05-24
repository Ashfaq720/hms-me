<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtSurgeryTeam extends Model
{
    protected $table = 'ot_surgery_teams';

    public const ROLE_PRIMARY_SURGEON = 'primary_surgeon';
    public const ROLE_ASSISTANT_SURGEON = 'assistant_surgeon';
    public const ROLE_ANESTHETIST = 'anesthetist';
    public const ROLE_SCRUB_NURSE = 'scrub_nurse';
    public const ROLE_CIRCULATING_NURSE = 'circulating_nurse';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_OTHER = 'other';

    public const ROLES = [
        self::ROLE_PRIMARY_SURGEON, self::ROLE_ASSISTANT_SURGEON, self::ROLE_ANESTHETIST,
        self::ROLE_SCRUB_NURSE, self::ROLE_CIRCULATING_NURSE, self::ROLE_TECHNICIAN,
        self::ROLE_OTHER,
    ];

    // FR-07: technician specializations
    public const SPEC_C_ARM = 'C-arm';
    public const SPEC_ENDOSCOPY = 'Endoscopy';
    public const SPEC_BIOMEDICAL = 'Biomedical';
    public const SPEC_ANESTHESIA = 'Anesthesia';
    public const SPEC_LAPAROSCOPY = 'Laparoscopy';
    public const SPEC_PERFUSION = 'Perfusion';
    public const SPEC_RADIOLOGY = 'Radiology';
    public const SPEC_OTHER = 'Other';

    public const SPECIALIZATIONS = [
        self::SPEC_C_ARM, self::SPEC_ENDOSCOPY, self::SPEC_BIOMEDICAL,
        self::SPEC_ANESTHESIA, self::SPEC_LAPAROSCOPY, self::SPEC_PERFUSION,
        self::SPEC_RADIOLOGY, self::SPEC_OTHER,
    ];

    protected $fillable = [
        'surgery_schedule_id', 'role', 'specialization',
        'staff_id', 'staff_type',
        'is_primary', 'assigned_at', 'notes',
        'released_at', 'released_reason',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function scopeActive($q)
    {
        return $q->whereNull('released_at');
    }

    /**
     * staff_id is polymorphic between Doctor and User based on staff_type.
     * (Not a true morphTo because staff_type holds short slugs 'doctor'/'user'.)
     */
    public function staff()
    {
        return match (strtolower($this->staff_type ?: 'user')) {
            'doctor' => $this->belongsTo(\App\Models\Doctor::class, 'staff_id'),
            default  => $this->belongsTo(\App\Models\User::class, 'staff_id'),
        };
    }

    /** Resolves the staff record (Doctor or User) and returns it (or null). */
    public function getStaffRecord()
    {
        if (strtolower($this->staff_type ?: '') === 'doctor') {
            return \App\Models\Doctor::find($this->staff_id);
        }
        return \App\Models\User::find($this->staff_id);
    }

    public function getStaffNameAttribute(): string
    {
        $r = $this->getStaffRecord();
        return $r?->name ?? $r?->patient_name ?? ('Staff #' . $this->staff_id);
    }
}
