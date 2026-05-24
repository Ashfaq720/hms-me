<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtPostOpNote extends Model
{
    protected $table = 'ot_post_op_notes';

    protected $fillable = [
        'surgery_schedule_id', 'procedure_summary', 'immediate_findings',
        'post_op_diagnosis', 'orders', 'medications', 'care_instructions',
        'follow_up_plan', 'disposition', 'signed_by', 'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
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
