<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuMedicationAdministration extends Model
{
    protected $table = 'nicu_medication_administrations';

    protected $fillable = [
        'order_id', 'administered_at', 'dose_given_mg',
        'status', 'notes', 'administered_by',
    ];

    protected $casts = ['administered_at' => 'datetime'];

    public function order() { return $this->belongsTo(NicuMedicationOrder::class, 'order_id'); }
}
