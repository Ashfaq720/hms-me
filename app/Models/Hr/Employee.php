<?php

namespace App\Models\Hr;

use App\Models\Organization\Branch;
use App\Models\Organization\Organization;
use App\Models\User;
use App\Traits\BranchScoped;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, LogPreference, BranchScoped;

    protected string $logName = 'employee';

    protected $fillable = [
        'organization_id', 'branch_id', 'user_id', 'employee_code',
        'first_name', 'last_name', 'email', 'phone', 'national_id',
        'dob', 'gender', 'photo_path',
        'department_id', 'designation_id', 'staff_type',
        'joining_date', 'exit_date', 'employment_type', 'status',
        'base_salary', 'bank_name', 'bank_account', 'extra',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'exit_date' => 'date',
        'base_salary' => 'decimal:2',
        'extra' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class);
    }

    public function attendance()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function roster()
    {
        return $this->hasMany(EmployeeRoster::class);
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(DoctorWalletTransaction::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }
}
