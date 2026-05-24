<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuAlert extends Model
{
    protected $table = 'icu_alerts';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'bed_id',
        'alert_type',
        'vital_type',
        'observed_value',
        'severity',
        'message',
        'source_module',
        'source_id',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'action_taken',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function scopeOpen($q)
    {
        return $q->whereIn('status', ['Active', 'Acknowledged']);
    }

    public function scopeCritical($q)
    {
        return $q->where('severity', 'Critical');
    }
}
