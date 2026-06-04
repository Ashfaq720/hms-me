<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    protected $table = 'passes';

    protected $fillable = [
        'pass_no', 'pass_type', 'patient_id', 'reference_type', 'reference_id',
        'full_name', 'mobile', 'relationship', 'id_type', 'id_number',
        'purpose', 'photo_path', 'valid_from', 'valid_to', 'special_permission',
        'status', 'cancel_reason', 'cancelled_by', 'cancelled_at', 'created_by',
    ];

    protected $casts = [
        'valid_from'    => 'datetime',
        'valid_to'      => 'datetime',
        'cancelled_at'  => 'datetime',
    ];
}
