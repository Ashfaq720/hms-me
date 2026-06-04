<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtConsumable extends Model
{
    protected $table = 'ot_consumables';

    protected $fillable = [
        'name', 'code', 'type', 'unit', 'rate', 'current_stock', 'reorder_level',
        'inventory_item_id', 'linked_medicine_id', 'store', 'is_implant', 'is_active',
    ];

    protected $casts = [
        'is_implant' => 'boolean',
        'is_active' => 'boolean',
        'rate' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
    ];

    /**
     * Canonical link to the inventory master. Stock truth lives in stock_movements,
     * not in this row's deprecated current_stock column.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryItem::class, 'inventory_item_id');
    }

    /** Live stock balance from stock_movements (IN − OUT). */
    public function liveStock(): float
    {
        if (! $this->inventory_item_id) return (float) $this->current_stock;
        return (float) \DB::table('stock_movements')
            ->where('inventory_item_id', $this->inventory_item_id)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction='in' THEN quantity ELSE -quantity END), 0) as balance")
            ->value('balance');
    }

    public function isLowStock(): bool
    {
        $stock = $this->liveStock();
        return $this->reorder_level > 0 && $stock <= (float) $this->reorder_level;
    }

    /** Deprecated — kept for legacy code paths. */
    public function linkedMedicine()
    {
        return $this->belongsTo(\App\Models\Pharmacy\Medicine::class, 'linked_medicine_id');
    }
}
