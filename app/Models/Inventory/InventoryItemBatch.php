<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryItemBatch extends Model
{
    protected $fillable = [
        'inventory_item_id', 'warehouse_id', 'batch_no', 'serial_no',
        'mfg_date', 'expiry_date', 'cost_price', 'selling_price',
        'current_qty', 'storage_location',
    ];

    protected $casts = [
        'mfg_date' => 'date',
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'current_qty' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
        });
    }

    public function scopeFifo($query)
    {
        return $query->orderByRaw('expiry_date IS NULL')->orderBy('expiry_date');
    }
}
