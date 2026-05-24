<?php

namespace App\Models\Inventory;

use App\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Model;

/**
 * Immutable stock-ledger entry. NEVER UPDATE; only INSERT (SRS §5.20).
 *
 * Mass assignment is intentionally limited because callers should use
 * StockLedgerService::record() rather than build the model by hand.
 */
class StockMovement extends Model
{
    use BranchScoped;

    public $timestamps = true;

    protected $fillable = [
        'organization_id', 'branch_id',
        'inventory_item_id', 'inventory_item_batch_id', 'warehouse_id',
        'direction', 'quantity', 'unit_cost', 'balance_after',
        'reason', 'source_type', 'source_id', 'reference_no',
        'remarks', 'performed_by', 'performed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'balance_after' => 'decimal:4',
        'performed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Enforce immutability at the model level — saved rows can never be updated.
        static::updating(function () {
            throw new \RuntimeException('stock_movements is an immutable ledger; create a new entry instead.');
        });
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryItemBatch::class, 'inventory_item_batch_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
