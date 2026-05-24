<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'bed_group_id',
        'floor_id',
        'room_no',
        'name',
        'room_class',
        'capacity',
        'room_rent',
        'has_ac',
        'has_attached_bath',
        'has_tv',
        'has_fridge',
        'has_sofa_cum_bed',
        'has_oxygen_outlet',
        'has_central_monitor',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity'            => 'integer',
        'room_rent'           => 'decimal:2',
        'has_ac'              => 'boolean',
        'has_attached_bath'   => 'boolean',
        'has_tv'              => 'boolean',
        'has_fridge'          => 'boolean',
        'has_sofa_cum_bed'    => 'boolean',
        'has_oxygen_outlet'   => 'boolean',
        'has_central_monitor' => 'boolean',
        'is_active'           => 'boolean',
    ];

    public const CLASSES = [
        'general'       => 'General Ward',
        'semi_private'  => 'Semi-Private',
        'private_cabin' => 'Private Cabin',
        'deluxe'        => 'Deluxe',
        'vvip_suite'    => 'VVIP Suite',
        'icu'           => 'ICU',
        'ccu'           => 'CCU',
        'nicu'          => 'NICU',
        'isolation'     => 'Isolation',
        'recovery'      => 'Recovery',
        'maternity'     => 'Maternity',
    ];

    public function bedGroup()
    {
        return $this->belongsTo(BedGroup::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function packageLinks()
    {
        return $this->hasMany(PackageBedLink::class);
    }

    public function amenityList(): array
    {
        return array_filter([
            $this->has_ac              ? 'AC' : null,
            $this->has_attached_bath   ? 'Attached Bath' : null,
            $this->has_tv              ? 'TV' : null,
            $this->has_fridge          ? 'Fridge' : null,
            $this->has_sofa_cum_bed    ? 'Sofa-cum-Bed' : null,
            $this->has_oxygen_outlet   ? 'Oxygen' : null,
            $this->has_central_monitor ? 'Central Monitor' : null,
        ]);
    }
}
