<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedType extends Model
{
    protected $fillable = [
        'name',
        'is_icu',
        'icu_type',
        'has_ventilator_support',
        'has_monitor_support',
        'is_isolation_bed',
        'allowed_isolation_type',
        'is_active',
    ];

    protected $casts = [
        'is_icu'                 => 'boolean',
        'has_ventilator_support' => 'boolean',
        'has_monitor_support'    => 'boolean',
        'is_isolation_bed'       => 'boolean',
        'is_active'              => 'boolean',
    ];
}
