<?php

namespace App\Models\Charges;

use Illuminate\Database\Eloquent\Model;

class ChargeCategory extends Model
{
    protected $fillable = ['name', 'charge_type_id', 'description'];
}
