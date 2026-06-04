<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemBatch;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Owns ALL writes to inventory stock — counters on InventoryItemBatch are
 * updated in lockstep with an immutable stock_movements row.
 */
class StockLedgerService
{
    /**
     * Record an inbound stock receipt (GRN, opening balance, return-in).
     */
    public function receive(array $payload): StockMovement
    {
        return $this->record(array_merge($payload, [
            'direction' => 'in',
        ]));
    }

    /**
     * Record a dispense / consumption (out).
     */
    public function dispense(array $payload): StockMovement
    {
        return $this->record(array_merge($payload, [
            'direction' => 'out',
        ]));
    }

    /**
     * Record a stock-count adjustment with an explicit direction.
     */
    public function adjust(array $payload, bool $increase = true): StockMovement
    {
        return $this->record(array_merge($payload, [
            'direction' => $increase ? 'adjustment_in' : 'adjustment_out',
        ]));
    }

    /**
     * Generic record method. Handles batch resolution + counter update + ledger insert.
     */
    public function record(array $payload): StockMovement
    {
        if (empty($payload['inventory_item_id'])) {
            throw new InvalidArgumentException('inventory_item_id is required');
        }
        if (! isset($payload['quantity'])) {
            throw new InvalidArgumentException('quantity is required');
        }
        if (empty($payload['direction'])) {
            throw new InvalidArgumentException('direction is required');
        }
        if (empty($payload['reason'])) {
            throw new InvalidArgumentException('reason is required');
        }

        return DB::transaction(function () use ($payload) {
            $batch = $this->resolveBatch($payload);
            $quantity = (float) $payload['quantity'];
            if ($quantity <= 0) {
                throw new InvalidArgumentException('quantity must be positive');
            }

            $isOut = in_array($payload['direction'], ['out', 'adjustment_out', 'transfer_out'], true);
            $delta = $isOut ? -$quantity : +$quantity;

            $currentBalance = (float) ($batch?->current_qty ?? 0);
            $newBalance = $currentBalance + $delta;
            if ($isOut && $newBalance < 0) {
                throw new InvalidArgumentException("Insufficient stock: requested {$quantity}, available {$currentBalance}");
            }

            if ($batch) {
                $batch->forceFill(['current_qty' => $newBalance])->save();
            }

            return StockMovement::create([
                'organization_id' => $payload['organization_id'] ?? null,
                'branch_id' => $payload['branch_id'] ?? null,
                'inventory_item_id' => $payload['inventory_item_id'],
                'inventory_item_batch_id' => $batch?->id,
                'warehouse_id' => $payload['warehouse_id'] ?? $batch?->warehouse_id,
                'direction' => $payload['direction'],
                'quantity' => $quantity,
                'unit_cost' => $payload['unit_cost'] ?? $batch?->cost_price ?? 0,
                'balance_after' => $newBalance,
                'reason' => $payload['reason'],
                'source_type' => $payload['source_type'] ?? null,
                'source_id' => $payload['source_id'] ?? null,
                'reference_no' => $payload['reference_no'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
                'performed_by' => $payload['performed_by'] ?? auth()->id(),
                'performed_at' => $payload['performed_at'] ?? now(),
            ]);
        });
    }

    private function resolveBatch(array $payload): ?InventoryItemBatch
    {
        if (! empty($payload['inventory_item_batch_id'])) {
            return InventoryItemBatch::lockForUpdate()
                ->findOrFail($payload['inventory_item_batch_id']);
        }

        $itemId = $payload['inventory_item_id'];
        $direction = $payload['direction'];
        $isOut = in_array($direction, ['out', 'adjustment_out', 'transfer_out'], true);

        if ($isOut) {
            // FIFO: use the oldest non-expired batch with stock.
            return InventoryItemBatch::where('inventory_item_id', $itemId)
                ->when(! empty($payload['warehouse_id']), fn ($q) => $q->where('warehouse_id', $payload['warehouse_id']))
                ->where('current_qty', '>', 0)
                ->notExpired()
                ->fifo()
                ->lockForUpdate()
                ->first();
        }

        // Inbound without an explicit batch: create a synthetic batch row.
        return InventoryItemBatch::create([
            'inventory_item_id' => $itemId,
            'warehouse_id' => $payload['warehouse_id'] ?? null,
            'batch_no' => $payload['batch_no'] ?? 'AUTO-' . now()->format('Ymd-His'),
            'expiry_date' => $payload['expiry_date'] ?? null,
            'cost_price' => $payload['unit_cost'] ?? 0,
            'selling_price' => $payload['selling_price'] ?? 0,
            'current_qty' => 0,
            'storage_location' => $payload['storage_location'] ?? null,
        ]);
    }
}
