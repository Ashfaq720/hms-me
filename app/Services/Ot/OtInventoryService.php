<?php

namespace App\Services\Ot;

use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtConsumable;
use App\Models\Ot\OtConsumableUsage;
use App\Models\Ot\OtNotification;
use App\Models\Ot\OtStockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OtInventoryService
{
    /**
     * Deduct the recorded usage from OT consumable stock (or Pharmacy
     * medicine batches if linked). Idempotent: a second call no-ops.
     */
    public function deduct(OtConsumableUsage $usage): OtConsumableUsage
    {
        if ($usage->inventory_deducted) {
            return $usage;
        }

        if (! $usage->ot_consumable_id) {
            // Custom item, no stock tracking.
            $usage->update(['inventory_deducted' => true]);
            return $usage;
        }

        return DB::transaction(function () use ($usage) {
            $consumable = OtConsumable::lockForUpdate()->find($usage->ot_consumable_id);

            if (! $consumable) {
                throw new RuntimeException("Consumable {$usage->ot_consumable_id} not found.");
            }

            $deducted = false;

            if ($consumable->linked_medicine_id) {
                $deducted = $this->deductFromMedicineBatches(
                    $consumable->linked_medicine_id,
                    (float) $usage->quantity,
                    $consumable->store
                );
            }

            // Always update OT consumable stock counter for visibility.
            $newBalance = max(0, (float) $consumable->current_stock - (float) $usage->quantity);
            $consumable->update(['current_stock' => $newBalance]);

            OtStockMovement::create([
                'ot_consumable_id' => $consumable->id,
                'surgery_schedule_id' => $usage->surgery_schedule_id,
                'consumable_usage_id' => $usage->id,
                'movement_type' => 'OUT',
                'quantity' => $usage->quantity,
                'balance_after' => $newBalance,
                'notes' => $deducted ? 'Deducted from pharmacy batches' : 'OT internal stock',
                'performed_by' => auth()->id(),
            ]);

            $usage->update(['inventory_deducted' => true]);

            OtAuditLog::record(
                'consumable_usage', $usage->id, 'inventory_deducted',
                null, null, null,
                ['new_stock' => $newBalance, 'pharmacy_linked' => $deducted]
            );

            if ($consumable->isLowStock()) {
                OtNotification::dispatch([
                    'role' => 'OT Manager',
                    'type' => 'low_stock',
                    'title' => "Low stock: {$consumable->name}",
                    'body' => "Current: {$consumable->current_stock} {$consumable->unit}. Reorder level: {$consumable->reorder_level}.",
                    'entity_type' => 'ot_consumable',
                    'entity_id' => $consumable->id,
                    'action_url' => route('ot.setup.consumables.edit', $consumable->id),
                    'severity' => 'warning',
                ]);
            }

            return $usage;
        });
    }

    /**
     * Best-effort deduction from Pharmacy medicine_batches.
     * Uses FIFO by expiry_date. Returns true if any batch was decremented.
     */
    protected function deductFromMedicineBatches(int $medicineId, float $qty, ?string $store): bool
    {
        if (! \Schema::hasTable('medicine_batches')) {
            return false;
        }

        $batches = DB::table('medicine_batches')
            ->where('medicine_id', $medicineId)
            ->when($store, fn ($q) => $q->where('store', $store))
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->lockForUpdate()
            ->get();

        $remaining = $qty;
        $touched = false;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, (float) $batch->quantity);
            DB::table('medicine_batches')
                ->where('id', $batch->id)
                ->decrement('quantity', $take);

            $remaining -= $take;
            $touched = true;
        }

        return $touched;
    }

    public function addStock(OtConsumable $consumable, float $qty, ?string $notes = null): OtStockMovement
    {
        return DB::transaction(function () use ($consumable, $qty, $notes) {
            $consumable = OtConsumable::lockForUpdate()->find($consumable->id);
            $newBalance = (float) $consumable->current_stock + $qty;
            $consumable->update(['current_stock' => $newBalance]);

            return OtStockMovement::create([
                'ot_consumable_id' => $consumable->id,
                'movement_type' => 'IN',
                'quantity' => $qty,
                'balance_after' => $newBalance,
                'notes' => $notes,
                'performed_by' => auth()->id(),
            ]);
        });
    }
}
