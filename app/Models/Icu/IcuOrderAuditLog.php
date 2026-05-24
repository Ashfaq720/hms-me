<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuOrderAuditLog extends Model
{
    protected $table   = 'icu_order_audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'action_type',
        'old_value',
        'new_value',
        'reason',
        'changed_by',
        'changed_at',
        'created_at',
    ];

    protected $casts = [
        'old_value'  => 'array',
        'new_value'  => 'array',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(IcuDoctorOrder::class, 'order_id');
    }
}
