<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $table = 'employee_shifts';

    protected $fillable = [
        'branch_id', 'name', 'start_time', 'end_time',
        'duration_minutes', 'is_night_shift', 'is_active',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];
}
