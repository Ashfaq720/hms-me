<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSlotSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'shift_id',
        'consultation_minutes',
        'charge_category_id',
        'charge_id',
        'amount',
    ];
}
