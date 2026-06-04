<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'group_type',
        'gender_preference',
        'notes',
        'floor_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const GROUP_TYPES = [
        'ward'         => 'Ward',
        'cabin_block'  => 'Cabin Block',
        'icu_wing'     => 'ICU Wing',
        'nicu_wing'    => 'NICU Wing',
        'ccu_wing'     => 'CCU Wing',
        'isolation'    => 'Isolation Block',
        'recovery'     => 'Recovery',
        'day_care'     => 'Day Care',
        'maternity'    => 'Maternity Ward',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
