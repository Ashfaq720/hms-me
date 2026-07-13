<?php

namespace App\Models\Opd;

use App\Models\OpdPatient;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Model;

class OpdMedication extends Model
{
    protected $fillable = [
        'opd_patient_id',
        'medicine_id',
        'datetime',
        'dosage',
        'medicated_by',
        'remarks',
        'notes',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class, 'opd_patient_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
