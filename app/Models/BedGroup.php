<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedGroup extends Model
{
    protected $fillable = [
        'name',
        'floor_id',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
