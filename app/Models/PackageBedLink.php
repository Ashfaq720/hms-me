<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageBedLink extends Model
{
    protected $fillable = [
        'package_id',
        'bed_type_id',
        'room_id',
        'bed_id',
        'override_price',
        'is_default',
    ];

    protected $casts = [
        'override_price' => 'decimal:2',
        'is_default'     => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
