<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtIntraOpRecord extends Model
{
    protected $table = 'ot_intra_op_records';

    protected $fillable = [
        'surgery_schedule_id', 'incision_time', 'closure_time',
        'operative_findings', 'procedure_performed', 'operative_notes',
        'specimens_collected', 'implants_used', 'blood_loss_ml', 'blood_transfused_ml',
        'complications', 'post_op_instructions', 'counts_verified',
        'signed_by', 'signed_at',
    ];

    protected $casts = [
        'incision_time' => 'datetime',
        'closure_time' => 'datetime',
        'signed_at' => 'datetime',
        'counts_verified' => 'boolean',
        'blood_loss_ml' => 'decimal:2',
        'blood_transfused_ml' => 'decimal:2',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function signedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'signed_by');
    }
}
