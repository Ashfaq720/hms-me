<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeRoster extends Model
{
    protected $table = 'employee_roster';

    protected $fillable = [
        'employee_id', 'shift_id', 'date', 'status', 'ward_or_department',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(EmployeeShift::class, 'shift_id');
    }
}
