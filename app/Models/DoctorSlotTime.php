<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSlotTime extends Model
{
    protected $fillable = [
        'doctor_id',
        'shift_id',
        'day',
        'time_from',
        'time_to',
    ];
}
