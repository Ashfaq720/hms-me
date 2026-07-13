<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['code', 'name', 'annual_quota', 'is_paid', 'is_active'];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];
}
