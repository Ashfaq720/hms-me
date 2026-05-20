<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
     protected $fillable = [
        'name',
        'status',
    ];
}
