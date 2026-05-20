<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $fillable = [
        'name',
        'rent',
        'bed_type_id',
        'bed_group_id',
        'is_reserved',
        'status',
    ];

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }
    public function bedGroup()
    {
        return $this->belongsTo(BedGroup::class);
    }
}
