<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuVital extends Model
{
    protected $table = 'nicu_vitals';

    protected $fillable = [
        'nicu_admission_id', 'recorded_at',
        'heart_rate', 'respiratory_rate', 'spo2', 'temperature_c', 'blood_glucose_mgdl',
        'source', 'device_id',
        'alert_apnea', 'alert_hypothermia', 'alert_spo2_critical', 'alert_hr_abnormal',
        'alert_level', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'alert_apnea' => 'boolean',
        'alert_hypothermia' => 'boolean',
        'alert_spo2_critical' => 'boolean',
        'alert_hr_abnormal' => 'boolean',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            $alerts = 0;
            if ($row->spo2 !== null && $row->spo2 < 88) { $row->alert_spo2_critical = true; $alerts++; }
            if ($row->heart_rate !== null && ($row->heart_rate < 100 || $row->heart_rate > 180)) { $row->alert_hr_abnormal = true; $alerts++; }
            if ($row->respiratory_rate !== null && $row->respiratory_rate < 20) { $row->alert_apnea = true; $alerts++; }
            if ($row->temperature_c !== null && (float) $row->temperature_c < 36.0) { $row->alert_hypothermia = true; $alerts++; }
            $row->alert_level = $row->alert_spo2_critical || $row->alert_apnea ? 'CRITICAL'
                : ($alerts > 0 ? 'WARNING' : 'NORMAL');
        });
    }
}
