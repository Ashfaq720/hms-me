<?php

namespace App\Models\Charges;

use Illuminate\Database\Eloquent\Model;

class TaxCategory extends Model
{
    protected $fillable = ['name','percentage'];
}
