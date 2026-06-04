<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'history_type',
        'description',
        'recorded_by',
    ];

    public const TYPES = [
        'medical'  => 'Medical History',
        'surgical' => 'Surgical History',
        'family'   => 'Family History',
        'allergy'  => 'Allergy',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->history_type] ?? ucfirst($this->history_type);
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->history_type) {
            'medical'  => 'bg-primary',
            'surgical' => 'bg-warning text-dark',
            'family'   => 'bg-info text-dark',
            'allergy'  => 'bg-danger',
            default    => 'bg-secondary',
        };
    }
}
