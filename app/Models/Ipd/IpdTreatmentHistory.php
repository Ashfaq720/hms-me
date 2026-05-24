<?php

namespace App\Models\Ipd;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class IpdTreatmentHistory extends Model
{
    protected $table = 'ipd_treatment_history';

    protected $fillable = [
        'ipd_id',
        'case_id',
        'patient_id',
        'doctor_id',
        'date',
        'prescribe_medicine',
        'diagnosis',
        'tx_note',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
