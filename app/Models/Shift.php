<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['name', 'time_from', 'time_to', 'is_active'];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_shift')->withTimestamps();
    }
}
