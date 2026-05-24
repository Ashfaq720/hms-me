<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendance';

    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out',
        'status', 'source', 'remarks',
    ];

    protected $casts = ['date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
