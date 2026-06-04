<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtAnesthesiaType extends Model
{
    protected $table = 'ot_anesthesia_types';

    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
