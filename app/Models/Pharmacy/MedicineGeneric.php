<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class MedicineGeneric extends Model
{
     protected $fillable = [
        'name',
        'status',
    ];
}
