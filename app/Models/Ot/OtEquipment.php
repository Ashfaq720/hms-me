<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtEquipment extends Model
{
    protected $table = 'ot_equipments';

    protected $fillable = [
        'code', 'name', 'category', 'ot_room_id', 'inventory_item_id', 'serial_no',
        'status', 'last_service_date', 'next_service_date', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(OtRoom::class, 'ot_room_id');
    }

    /**
     * Canonical link to inventory_items (with is_asset=1).
     * Reuses the same asset registry used by ICU/CCU equipment.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryItem::class, 'inventory_item_id');
    }

    public function scopeAvailable($q)
    {
        return $q->where('is_active', true)->where('status', 'available');
    }
}
