<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class PassScanLog extends Model
{
    protected $table = 'pass_scan_logs';

    protected $fillable = [
        'pass_id', 'scan_type', 'result', 'reason', 'scanned_by', 'scanned_at',
    ];

    protected $casts = ['scanned_at' => 'datetime'];
}
