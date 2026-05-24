<?php

namespace App\Models\Er;

use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErObservation extends Model
{
    protected $table = 'er_observations';
    protected $guarded = [];
    protected $casts = ['observed_at' => 'datetime', 'alert_critical' => 'boolean'];

    public function erPatient() { return $this->belongsTo(ErPatient::class); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            // Auto-flag critical if any vital is outside safe range
            $critical = false;
            if ($row->spo2 !== null && $row->spo2 < 90) $critical = true;
            if ($row->pulse !== null && ($row->pulse < 50 || $row->pulse > 130)) $critical = true;
            if ($row->respiratory_rate !== null && ($row->respiratory_rate < 10 || $row->respiratory_rate > 30)) $critical = true;
            if ($row->temperature_c !== null && ((float) $row->temperature_c < 35 || (float) $row->temperature_c > 39.5)) $critical = true;
            $row->alert_critical = $critical;
        });
    }
}
