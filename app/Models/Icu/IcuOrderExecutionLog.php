<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuOrderExecutionLog extends Model
{
    protected $table      = 'icu_order_execution_logs';
    public $timestamps    = false;

    protected $fillable = [
        'order_id',
        'status',
        'executed_by',
        'execution_start_time',
        'execution_end_time',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'execution_start_time' => 'datetime',
        'execution_end_time'   => 'datetime',
        'created_at'           => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(IcuDoctorOrder::class, 'order_id');
    }
}
