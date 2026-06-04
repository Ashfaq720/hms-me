<?php

namespace App\Models\Inventory;

use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes, LogPreference;

    protected string $logName = 'inventory_item';

    protected $fillable = [
        'organization_id', 'code', 'name', 'category', 'generic_name', 'brand',
        'sku', 'barcode', 'uom', 'tax_percent', 'reorder_level', 'reorder_quantity',
        'storage_condition', 'is_controlled', 'is_consumable', 'is_asset', 'is_active',
        'description',
    ];

    protected $casts = [
        'tax_percent' => 'decimal:2',
        'reorder_level' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'is_controlled' => 'boolean',
        'is_consumable' => 'boolean',
        'is_asset' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function batches()
    {
        return $this->hasMany(InventoryItemBatch::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function balance(?int $warehouseId = null): float
    {
        $query = $this->batches();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return (float) $query->sum('current_qty');
    }

    /** Live balance from stock_movements (IN − OUT) — works when batches are absent. */
    public function liveBalance(?int $warehouseId = null): float
    {
        $q = $this->movements();
        if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        return (float) $q->selectRaw("COALESCE(SUM(CASE WHEN direction='in' THEN quantity ELSE -quantity END), 0) as bal")
            ->value('bal');
    }

    /** Reverse relations — every domain that uses this canonical item. */
    public function medicine()
    {
        return $this->hasOne(\App\Models\Pharmacy\Medicine::class, 'inventory_item_id');
    }

    public function otConsumable()
    {
        return $this->hasOne(\App\Models\Ot\OtConsumable::class, 'inventory_item_id');
    }

    public function otEquipment()
    {
        return $this->hasOne(\App\Models\Ot\OtEquipment::class, 'inventory_item_id');
    }

    public function icuEquipment()
    {
        return $this->hasOne(\App\Models\Icu\IcuEquipment::class, 'inventory_item_id');
    }

    /** True when this inventory row is fronting OT, Pharmacy, or ICU asset registries. */
    public function usedByDomains(): array
    {
        $domains = [];
        if ($this->medicine()->exists())     $domains[] = 'Pharmacy';
        if ($this->otConsumable()->exists()) $domains[] = 'OT Consumable';
        if ($this->otEquipment()->exists())  $domains[] = 'OT Equipment';
        if ($this->icuEquipment()->exists()) $domains[] = 'ICU Equipment';
        return $domains;
    }
}
