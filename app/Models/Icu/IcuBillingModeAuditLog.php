<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Model;

class IcuBillingModeAuditLog extends Model
{
    protected $table   = 'icu_billing_mode_audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'icu_admission_id',
        'old_billing_mode',
        'new_billing_mode',
        'old_package_id',
        'new_package_id',
        'changed_by',
        'changed_at',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
