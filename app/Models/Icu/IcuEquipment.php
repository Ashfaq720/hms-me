<?php

namespace App\Models\Icu;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Model;

class IcuEquipment extends Model
{
    protected $table = 'icu_equipment';

    protected $fillable = [
        'inventory_item_id',
        'equipment_code',
        'equipment_name',
        'equipment_type',
        'icu_type',
        'serial_no',
        'status',
        'location',
        'default_bed_id',
        'charge_type',
        'charge_rate',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'charge_rate' => 'decimal:2',
    ];

    /**
     * Canonical link to the unified inventory_items master.
     * Mirrors OtEquipment::inventoryItem() — same pattern across ICU/CCU/NICU/OT.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryItem::class, 'inventory_item_id');
    }

    public function defaultBed()
    {
        return $this->belongsTo(Bed::class, 'default_bed_id');
    }

    public function activeUsage()
    {
        return $this->hasOne(IcuEquipmentUsageLog::class, 'equipment_id')
            ->where('status', 'InUse');
    }

    public function usageLogs()
    {
        return $this->hasMany(IcuEquipmentUsageLog::class, 'equipment_id');
    }

    public function bedMappings()
    {
        return $this->hasMany(IcuBedEquipmentMapping::class, 'equipment_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available')->where('is_active', true);
    }
}
