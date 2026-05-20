<?php

namespace App\Models\FrontDesk;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class ErPatient extends Model
{
    protected $table = 'er_patients';
    protected $guarded = [];

    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
