<?php
namespace App\Models\BloodBank;

use Illuminate\Database\Eloquent\Model;

class MasterAuditLog extends Model
{
    protected $fillable = [
        'master_table', 'record_id', 'action', 'action_by', 'action_at', 'old_value', 'new_value',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
