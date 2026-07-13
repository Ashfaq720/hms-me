<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'branch_id', 'period_label', 'period_start', 'period_end',
        'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(PayrollRunLine::class);
    }
}
