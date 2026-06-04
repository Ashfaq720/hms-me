<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'days',
        'reason', 'status', 'approver_user_id', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
