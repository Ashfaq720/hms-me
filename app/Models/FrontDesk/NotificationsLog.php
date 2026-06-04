<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class NotificationsLog extends Model
{
    protected $table = 'notifications_logs';

    protected $fillable = [
        'channel', 'template_key', 'to_address', 'message',
        'reference_type', 'reference_id',
        'status', 'provider_response', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];
}
