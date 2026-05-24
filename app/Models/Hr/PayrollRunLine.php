<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class PayrollRunLine extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'base_salary', 'allowances_total',
        'deductions_total', 'tax_total', 'commission_total', 'net_pay',
        'breakdown', 'status', 'paid_at',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances_total' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'breakdown' => 'array',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function run()
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }
}
