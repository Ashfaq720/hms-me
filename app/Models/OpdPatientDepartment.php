<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdPatientDepartment extends Model
{
    protected $fillable = [
        'patient_id', 'doctor_id', 'department_id',
        'appointment_date', 'reason', 'status', 'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
