<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuGrowthRecord extends Model
{
    protected $table = 'nicu_growth_records';

    protected $fillable = [
        'nicu_admission_id', 'measured_on',
        'weight_g', 'length_cm', 'head_circumference_cm',
        'weight_change_pct', 'alert_weight_loss', 'notes', 'measured_by',
    ];

    protected $casts = [
        'measured_on' => 'date',
        'alert_weight_loss' => 'boolean',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            $admission = NicuAdmission::find($row->nicu_admission_id);
            if (! $admission) return;

            $prev = static::where('nicu_admission_id', $row->nicu_admission_id)
                ->orderByDesc('measured_on')->first();
            $baseline = $prev?->weight_g ?? $admission->birth_weight_g;
            if ($baseline && $row->weight_g !== null) {
                $pct = (((float) $row->weight_g - (float) $baseline) / (float) $baseline) * 100;
                $row->weight_change_pct = round($pct, 2);
                if ($pct < -10) {
                    $row->alert_weight_loss = true;
                }
            }
        });
    }
}
