<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuInfectionRecord extends Model
{
    protected $table = 'nicu_infection_records';

    protected $fillable = [
        'nicu_admission_id', 'infection_type', 'organism',
        'detected_on', 'resolved_on', 'isolation_required',
        'alert_cluster', 'antibiotics_used', 'status', 'notes', 'reported_by',
    ];

    protected $casts = [
        'detected_on' => 'date',
        'resolved_on' => 'date',
        'alert_cluster' => 'boolean',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            $window = now()->subDays(14);
            $similar = static::where('infection_type', $row->infection_type)
                ->where('detected_on', '>=', $window)
                ->count();
            if ($similar >= 2) {
                $row->alert_cluster = true;
            }
        });
    }
}
