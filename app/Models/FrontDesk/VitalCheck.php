<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class VitalCheck extends Model
{
    protected $table = 'fd_vital_checks';

    protected $fillable = [
        'patient_id', 'patient_type',
        'opd_patient_id', 'ipd_patient_id', 'er_patient_id',
        'patient_token', 'patient_name', 'gender', 'age',
        'weight', 'height',
        'blood_pressure', 'temperature', 'heart_rate', 'respiratory_rate', 'spo2',
        'machine_fetched', 'machine_device_id',
        'remarks', 'checked_by', 'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function checkedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_by');
    }
}
