<?php

namespace App\Observers;

use App\Models\Pharmacy\PharmacyTransaction;
use App\Services\Pharmacy\PharmacyDispenseListener;

class PharmacyTransactionObserver
{
    public function __construct(private PharmacyDispenseListener $listener)
    {
    }

    /**
     * Fire once a transaction reaches a "dispensed/completed" state.
     *
     * The existing controllers either create the transaction already in a
     * completed state (counter sale) or transition to "completed" after
     * payment. We cover both: on create when status is final, and on update
     * when status transitions into a final state.
     */
    public function created(PharmacyTransaction $transaction): void
    {
        if ($this->isFinal($transaction->status)) {
            $this->listener->onDispensed($transaction);
        }
    }

    public function updated(PharmacyTransaction $transaction): void
    {
        if (! $transaction->wasChanged('status')) {
            return;
        }
        $original = $transaction->getOriginal('status');
        if (! $this->isFinal($original) && $this->isFinal($transaction->status)) {
            $this->listener->onDispensed($transaction);
        }
    }

    private function isFinal(?string $status): bool
    {
        return in_array(strtolower((string) $status), ['completed', 'dispensed', 'paid'], true);
    }
}
