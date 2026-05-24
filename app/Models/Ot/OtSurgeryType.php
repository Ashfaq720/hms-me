<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtSurgeryType extends Model
{
    protected $table = 'ot_surgery_types';

    protected $fillable = [
        'name', 'code', 'category_id', 'standard_duration_minutes',
        'standard_charge', 'surgeon_charge', 'anesthesia_charge',
        'ot_room_charge', 'recovery_charge', 'is_active', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'standard_charge' => 'decimal:2',
        'surgeon_charge' => 'decimal:2',
        'anesthesia_charge' => 'decimal:2',
        'ot_room_charge' => 'decimal:2',
        'recovery_charge' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(OtSurgeryCategory::class, 'category_id');
    }
}
