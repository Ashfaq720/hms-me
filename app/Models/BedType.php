<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedType extends Model
{
    protected $fillable = [
        'name',
        'base_rent',
        'description',
        'default_package_id',
        'is_icu',
        'icu_type',
        'has_ventilator_support',
        'has_monitor_support',
        'is_isolation_bed',
        'allowed_isolation_type',
        'is_active',
    ];

    protected $casts = [
        'base_rent'              => 'decimal:2',
        'is_icu'                 => 'boolean',
        'has_ventilator_support' => 'boolean',
        'has_monitor_support'    => 'boolean',
        'is_isolation_bed'       => 'boolean',
        'is_active'              => 'boolean',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function defaultPackage()
    {
        return $this->belongsTo(Package::class, 'default_package_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'bed_type_id');
    }

    public function packageLinks()
    {
        return $this->hasMany(PackageBedLink::class);
    }
}
