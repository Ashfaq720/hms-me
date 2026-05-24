<?php

namespace App\Models\FrontDesk;

use Illuminate\Database\Eloquent\Model;

class VitalCheck extends Model
{
    protected $table = 'fd_vital_checks';
    protected $guarded = [];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function checkedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_by');
    }
}
