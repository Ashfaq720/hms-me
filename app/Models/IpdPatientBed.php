<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpdPatientBed extends Model
{
     protected $fillable = [
        'case_id',
        'ipd_patient_id',
        'bed_id',
        'allocation_type',
        'from',
        'to',
        'remarks',
        'status',
    ];
    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function isIcu(): bool
    {
        return $this->allocation_type === 'icu';
    }
}
