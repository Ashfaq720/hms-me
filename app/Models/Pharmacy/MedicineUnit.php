<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class MedicineUnit extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];
}
