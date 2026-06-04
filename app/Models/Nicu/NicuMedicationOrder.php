<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuMedicationOrder extends Model
{
    protected $table = 'nicu_medication_orders';

    protected $fillable = [
        'nicu_admission_id', 'medicine_id', 'drug_name',
        'dose_per_kg_mg', 'weight_used_kg', 'total_dose_mg',
        'route', 'frequency', 'start_date', 'end_date',
        'safety_override', 'override_reason', 'status', 'prescribed_by',
    ];

    protected $casts = [
        'safety_override' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
    public function administrations() { return $this->hasMany(NicuMedicationAdministration::class, 'order_id'); }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if ($row->total_dose_mg === null && $row->dose_per_kg_mg && $row->weight_used_kg) {
                $row->total_dose_mg = round((float) $row->dose_per_kg_mg * (float) $row->weight_used_kg, 3);
            }
        });
    }
}
