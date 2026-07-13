<?php

namespace App\Models\Hr;

use App\Models\Encounter\Encounter;
use Illuminate\Database\Eloquent\Model;

class DoctorWalletTransaction extends Model
{
    protected $fillable = [
        'employee_id', 'encounter_id', 'source',
        'gross_amount', 'commission_percent', 'commission_amount',
        'status', 'accrued_on', 'payroll_run_id', 'reference_no',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'accrued_on' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }
}
