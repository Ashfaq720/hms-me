<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class MedicalGroup extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];
}
