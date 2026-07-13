<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'request_type', 'reference_type', 'reference_id',
        'old_values', 'new_values',
        'status', 'reason',
        'requested_by', 'requested_at', 'approved_by', 'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'requested_at'=> 'datetime',
        'approved_at' => 'datetime',
    ];
}
