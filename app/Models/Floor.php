<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = ['name', 'code', 'building', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bedGroups()
    {
        return $this->hasMany(BedGroup::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
