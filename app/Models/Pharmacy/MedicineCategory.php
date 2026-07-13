<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class MedicineCategory extends Model
{
     protected $fillable = [
        'name',
        'status',
    ];
}
