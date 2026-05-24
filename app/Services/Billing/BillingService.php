<?php

namespace App\Services\Billing;

use App\Models\Billing\Bill;
use App\Models\Billing\BillDiscount;
use App\Models\Billing\BillItem;
use App\Models\Billing\BillPayment;
use App\Models\Billing\BillRefund;
use App\Models\Encounter\Encounter;
use App\Models\ServiceCharge\ServiceChargePosting;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Single source of truth for bill creation, finalization, payment and refund.
 *
 * The service-charge engine writes immutable posting rows; this service
 * consolidates them into a Bill that a cashier collects payments against
 * (SRS §5.17, §7.1, prompt library Phase 2 / Prompt 10).
 */
class BillingService
{
    /**
     * Build or refresh a draft bill from the open postings on an encounter.
     * Existing finalized bills are never touched.
     */
    public function assembleFromEncounter(Encounter $encounter, ?string $billType = null): Bill
    {
        return DB::transaction(function () use ($encounter, $billType) {
            $bill = Bill::where('encounter_id', $encounter->id)
                ->whereIn('status', ['draft', 'provisional', 'partially_paid'])
                ->first();

            if (! $bill) {
                $bill = Bill::create([
                    'organization_id' => $encounter->organization_id,
                    'branch_id' => $encounter->branch_id,
                    'encounter_id' => $encounter->id,
                    'patient_id' => $encounter->patient_id,
                    'bill_no' => $this->generateBillNo($encounter),
                    'bill_type' => $billType ?? $this->billTypeFor($encounter->encounter_type),
                    'status' => 'draft',
                    'bill_date' => now()->toDateString(),
                    'created_by' => auth()->id(),
                ]);
            }

            $this->syncItemsFromPostings($bill);
            $this->recompute($bill);
            return $bill->refresh();
        });
    }

    /**
     * Re-pull items from all service_charge_postings for this bill's encounter.
     * Existing items already linked to a posting are left alone.
     */
    public function syncItemsFromPostings(Bill $bill): void
    {
        if ($bill->isFinal()) {
            return;
        }
        if (! $bill->encounter_id) {
            return;
        }

        $linkedPostingIds = $bill->items()->whereNotNull('service_charge_posting_id')->pluck('service_charge_posting_id');

        $postings = ServiceChargePosting::with('catalog')
            ->where('encounter_id', $bill->encounter_id)
            ->where('status', 'posted')
            ->whereNotIn('id', $linkedPostingIds)
            ->get();

        // Build a set of catalog IDs covered by any active package enrolment on this encounter.
        $coveredCatalogIds = \App\Models\Package\PackageEnrollment::where('encounter_id', $bill->encounter_id)
            ->orWhere(function ($q) use ($bill) {
                $q->where('patient_id', $bill->patient_id)->where('status', 'active');
            })
            ->where('status', 'active')
            ->with('package.services')
            ->get()
            ->flatMap(fn ($e) => $e->package->services->pluck('service_catalog_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($postings as $posting) {
            $isCovered = ! empty($coveredCatalogIds) && in_array($posting->service_catalog_id, $coveredCatalogIds, true);

            // Package-covered: show the original price on the bill for transparency,
            // then add a matching discount so the line nets out to zero. This way
            // recompute()'s subtotal still shows the full service value, the
            // discount column shows the package coverage, and grand_total excludes it.
            $gross = (float) $posting->unit_price * (float) $posting->quantity;
            $discount = $isCovered
                ? round($gross + (float) $posting->tax_amount, 2)
                : (float) $posting->discount_amount;
            $lineTotal = $isCovered ? 0 : $posting->net_amount;

            BillItem::create([
                'bill_id' => $bill->id,
                'service_catalog_id' => $posting->service_catalog_id,
                'service_charge_posting_id' => $posting->id,
                'description' => optional($posting->catalog)->name ?? 'Service charge',
                'item_type' => $posting->catalog?->service_type ?? 'service',
                'quantity' => $posting->quantity,
                'unit_price' => $posting->unit_price,
                'discount_amount' => $discount,
                'tax_percent' => $posting->catalog?->tax_percent ?? 0,
                'tax_amount' => $posting->tax_amount,
                'line_total' => $lineTotal,
                'is_package_included' => $isCovered,
                'metadata' => [
                    'trigger_event' => $posting->trigger_event,
                    'original_net' => (float) $posting->net_amount,
                    'package_discount' => $isCovered ? $discount : 0,
                ],
            ]);
        }
    }

    /**
     * Add a manual line item (not driven by the service-charge engine).
     */
    public function addManualItem(Bill $bill, array $payload): BillItem
    {
        if ($bill->isFinal()) {
            throw new RuntimeException('Cannot add items to a finalized bill.');
        }

        $qty = (float) ($payload['quantity'] ?? 1);
        $unit = (float) $payload['unit_price'];
        $disc = (float) ($payload['discount_amount'] ?? 0);
        $taxPct = (float) ($payload['tax_percent'] ?? 0);
        $taxable = ($qty * $unit) - $disc;
        $tax = round($taxable * ($taxPct / 100), 2);
        $total = round($taxable + $tax, 2);

        $item = BillItem::create([
            'bill_id' => $bill->id,
            'description' => $payload['description'],
            'item_type' => $payload['item_type'] ?? 'manual',
            'quantity' => $qty,
            'unit_price' => $unit,
            'discount_amount' => $disc,
            'tax_percent' => $taxPct,
            'tax_amount' => $tax,
            'line_total' => $total,
        ]);

        $this->recompute($bill);
        return $item;
    }

    /**
     * Recompute bill totals from items, discounts, payments and refunds.
     */
    public function recompute(Bill $bill): Bill
    {
        $items = $bill->items()->get();
        $subtotal = (float) $items->sum(fn ($i) => (float) $i->unit_price * (float) $i->quantity);
        $itemDiscount = (float) $items->sum('discount_amount');
        $tax = (float) $items->sum('tax_amount');

        $billDiscountTotal = (float) $bill->discounts()->where('status', 'applied')->sum('amount_applied');
        $paid = (float) $bill->payments()->where('status', 'received')->sum('amount');
        $refunded = (float) $bill->refunds()->where('status', 'paid')->sum('amount');

        $grand = round($subtotal - $itemDiscount - $billDiscountTotal + $tax, 2);
        $balance = round($grand - $paid + $refunded, 2);

        $status = $bill->status;
        if (! $bill->isFinal()) {
            if ($paid <= 0) {
                $status = 'draft';
            } elseif ($paid < $grand) {
                $status = 'partially_paid';
            } else {
                $status = 'paid';
            }
        }

        $bill->update([
            'subtotal' => $subtotal,
            'discount_total' => $itemDiscount + $billDiscountTotal,
            'tax_total' => $tax,
            'grand_total' => $grand,
            'paid_total' => $paid,
            'refund_total' => $refunded,
            'balance_due' => $balance,
            'status' => $status,
        ]);
        return $bill;
    }

    /**
     * Finalize a bill: snapshot items, lock further changes, capture finalizer.
     * SRS §10.12: refuse to finalize while critical pending orders / unposted
     * charges exist on the encounter.
     */
    public function finalize(Bill $bill): Bill
    {
        return DB::transaction(function () use ($bill) {
            if ($bill->isFinal()) {
                return $bill;
            }

            // Re-pull any postings that arrived since the last assemble.
            $this->syncItemsFromPostings($bill);
            $this->recompute($bill);

            $this->guardFinalize($bill);

            $bill->update([
                'status' => 'final',
                'finalized_at' => now(),
                'finalized_by' => auth()->id(),
            ]);
            return $bill->refresh();
        });
    }

    private function guardFinalize(Bill $bill): void
    {
        if ($bill->grand_total <= 0 && $bill->items()->count() === 0) {
            throw new RuntimeException('Cannot finalize an empty bill.');
        }
        // Hook: extend with checks for pending lab/radiology orders, pharmacy
        // returns, active bed allocations, etc. — modules to call into.
    }

    /**
     * Record a payment receipt and refresh totals.
     */
    public function collectPayment(Bill $bill, array $payload): BillPayment
    {
        if ($bill->status === 'cancelled') {
            throw new RuntimeException('Cannot collect against a cancelled bill.');
        }

        $amount = (float) $payload['amount'];
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be positive.');
        }
        if ($amount > $bill->balance_due + 0.01) {
            throw new InvalidArgumentException('Payment exceeds balance due.');
        }

        return DB::transaction(function () use ($bill, $payload, $amount) {
            $payment = BillPayment::create([
                'bill_id' => $bill->id,
                'receipt_no' => $this->generateReceiptNo(),
                'method' => $payload['method'] ?? 'cash',
                'reference_no' => $payload['reference_no'] ?? null,
                'amount' => $amount,
                'payment_date' => $payload['payment_date'] ?? now()->toDateString(),
                'status' => 'received',
                'notes' => $payload['notes'] ?? null,
                'collected_by' => auth()->id(),
            ]);
            $this->recompute($bill);
            return $payment;
        });
    }

    /**
     * Approve and pay a refund. Refund requests should be created separately
     * and only an authorized user (checked at controller) can mark approved.
     */
    public function approveRefund(BillRefund $refund): BillRefund
    {
        return DB::transaction(function () use ($refund) {
            if (! in_array($refund->status, ['pending', 'approved'], true)) {
                throw new RuntimeException('Refund not in approvable state.');
            }
            $refund->update([
                'status' => 'paid',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'paid_at' => now(),
            ]);
            $this->recompute($refund->bill);
            return $refund->refresh();
        });
    }

    /**
     * Apply a discount or waiver (requires an approver permission at the controller).
     */
    public function applyDiscount(Bill $bill, array $payload): BillDiscount
    {
        if ($bill->isFinal()) {
            throw new RuntimeException('Cannot discount a finalized bill.');
        }
        $mode = $payload['mode'] ?? 'percent';
        $value = (float) $payload['value'];
        $subtotal = (float) $bill->subtotal;
        $applied = $mode === 'percent'
            ? round($subtotal * ($value / 100), 2)
            : round($value, 2);

        return DB::transaction(function () use ($bill, $payload, $mode, $value, $applied) {
            $discount = BillDiscount::create([
                'bill_id' => $bill->id,
                'kind' => $payload['kind'] ?? 'discount',
                'mode' => $mode,
                'value' => $value,
                'amount_applied' => $applied,
                'reason' => $payload['reason'] ?? 'Discount applied',
                'status' => 'applied',
                'requested_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $this->recompute($bill);
            return $discount;
        });
    }

    public function cancel(Bill $bill, string $reason): Bill
    {
        return DB::transaction(function () use ($bill, $reason) {
            if ($bill->isFinal() && $bill->status !== 'final') {
                throw new RuntimeException('Cannot cancel an already-cancelled or refunded bill.');
            }
            $bill->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $reason,
            ]);
            return $bill->refresh();
        });
    }

    private function billTypeFor(string $encounterType): string
    {
        return strtolower(match ($encounterType) {
            'OPD' => 'opd',
            'IPD' => 'ipd',
            'ER' => 'er',
            'ICU', 'CCU', 'NICU' => 'icu',
            'OT' => 'ot',
            'PROCEDURE' => 'procedure',
            'LAB_ONLY' => 'lab',
            'RADIOLOGY_ONLY' => 'radiology',
            'PHARMACY_ONLY' => 'pharmacy',
            'AMBULANCE' => 'ambulance',
            default => 'other',
        });
    }

    private function generateBillNo(Encounter $encounter): string
    {
        $branchPrefix = optional($encounter->branch)->invoice_prefix ?? 'INV';
        return $branchPrefix . '-' . date('Ym') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateReceiptNo(): string
    {
        return 'RCP-' . date('Ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
