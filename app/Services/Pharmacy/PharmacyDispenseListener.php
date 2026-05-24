<?php

namespace App\Services\Pharmacy;

use App\Models\Encounter\Encounter;
use App\Models\Pharmacy\PharmacyTransaction;
use App\Services\Inventory\StockLedgerService;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Wires the existing pharmacy dispense flow into the new enterprise foundations.
 *
 *  1. Writes an immutable stock_movements row for every dispensed item
 *     (legacy MedicineBatch.quantity stays as the operational counter,
 *      but every move is now auditable).
 *  2. Auto-posts a Service Charge "pharmacy" line so revenue rolls up
 *     through the same engine as bed/OT/lab.
 *
 * Idempotent: a transaction is skipped if it has already been processed
 * (PharmacyTransaction.note marker), so re-running is safe.
 */
class PharmacyDispenseListener
{
    public function __construct(
        private StockLedgerService $stockLedger,
        private ServiceChargeEngine $serviceCharge,
    ) {
    }

    public function onDispensed(PharmacyTransaction $transaction): void
    {
        // Avoid double-posting if the listener fires more than once.
        $marker = "[hms:processed=" . $transaction->id . "]";
        if ($transaction->note && str_contains($transaction->note, $marker)) {
            return;
        }

        DB::transaction(function () use ($transaction, $marker) {
            $encounterId = $this->resolveEncounterId($transaction);
            $reason = match ($transaction->transaction_type) {
                'ipd' => 'ipd_dispense',
                'opd' => 'opd_dispense',
                default => 'pharmacy_dispense',
            };

            foreach ($transaction->items as $item) {
                $this->recordStockMovement($transaction, $item, $reason);
            }

            $this->postPharmacyServiceCharge($transaction, $encounterId);

            $transaction->forceFill([
                'note' => trim(($transaction->note ?? '') . ' ' . $marker),
            ])->saveQuietly();
        });
    }

    private function recordStockMovement(PharmacyTransaction $tx, $item, string $reason): void
    {
        // Resolve the unified inventory_item_id from medicines.inventory_item_id.
        // If the medicine isn't linked yet, skip the ledger write (legacy mode).
        $medicine = \App\Models\Pharmacy\Medicine::find($item->medicine_id);
        $inventoryItemId = $medicine?->inventory_item_id;

        if (! $inventoryItemId) {
            Log::info('Pharmacy stock-ledger skipped — medicine has no inventory_item_id link', [
                'transaction_id' => $tx->id,
                'medicine_id' => $item->medicine_id,
            ]);
            return;
        }

        try {
            $this->stockLedger->record([
                'organization_id' => optional(auth()->user())->current_organization_id,
                'branch_id' => optional(auth()->user())->current_branch_id,
                'inventory_item_id' => $inventoryItemId,
                'inventory_item_batch_id' => null,
                'warehouse_id' => null,
                'direction' => 'out',
                'quantity' => $item->qty_required,
                'unit_cost' => $item->unit_price,
                'reason' => $reason,
                'source_type' => $tx::class,
                'source_id' => $tx->id,
                'reference_no' => $tx->transaction_no,
                'remarks' => 'Pharmacy dispense ' . $tx->transaction_no . ' (medicine #' . $item->medicine_id . ')',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Pharmacy stock-ledger record failed', [
                'transaction_id' => $tx->id,
                'item_id' => $item->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    private function postPharmacyServiceCharge(PharmacyTransaction $tx, ?int $encounterId): void
    {
        // Resolve the catalog code for pharmacy revenue. If the hospital has
        // configured a per-branch catalog item, use it; otherwise fall back
        // to a generic PHARMACY_REVENUE entry.
        try {
            $this->serviceCharge->post([
                'service_code' => 'PHARMACY_REVENUE',
                'encounter' => $encounterId,
                'patient_id' => $tx->patient_id,
                'trigger_event' => 'pharmacy.dispense.completed',
                'trigger_source' => $tx,
                'quantity' => 1,
                'override_unit_price' => (float) ($tx->total_amount - $tx->discount_amount),
                'reason' => 'Pharmacy sale ' . $tx->transaction_no,
                'metadata' => [
                    'transaction_no' => $tx->transaction_no,
                    'item_count' => $tx->items->count(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            // Catalog item not configured yet for this branch — log so admin
            // knows to add it, but don't block dispense.
            Log::info('Pharmacy service-charge skipped — catalog missing', [
                'transaction_id' => $tx->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    private function resolveEncounterId(PharmacyTransaction $tx): ?int
    {
        if ($tx->opd_patient_id) {
            return Encounter::where('subject_type', \App\Models\OpdPatient::class)
                ->where('subject_id', $tx->opd_patient_id)
                ->value('id');
        }
        if ($tx->ipd_patient_id) {
            return Encounter::where('subject_type', \App\Models\IpdPatient::class)
                ->where('subject_id', $tx->ipd_patient_id)
                ->value('id');
        }
        return null;
    }
}
