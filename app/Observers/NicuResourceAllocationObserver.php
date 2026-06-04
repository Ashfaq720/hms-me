<?php

namespace App\Observers;

use App\Models\Nicu\NicuResourceAllocation;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Facades\Log;

class NicuResourceAllocationObserver
{
    public function updated(NicuResourceAllocation $allocation): void
    {
        // Accrue once on release
        if (! $allocation->wasChanged('status') || $allocation->status !== 'released') {
            return;
        }
        if (! $allocation->from || ! $allocation->to) {
            return;
        }

        $code = $this->resolveServiceCode($allocation->resource_type);
        if (! $code) return;

        $days = max(\Carbon\Carbon::parse($allocation->from)->diffInDays($allocation->to), 1);

        $admission = $allocation->admission;
        if (! $admission || ! $admission->encounter_id) {
            return;
        }

        try {
            app(ServiceChargeEngine::class)->post([
                'service_code' => $code,
                'encounter' => $admission->encounter_id,
                'trigger_event' => 'nicu.resource.usage',
                'quantity' => $days,
                'reason' => 'NICU resource ' . $allocation->resource_type . ' used ' . $days . 'd',
            ]);
        } catch (\Throwable $e) {
            Log::info('NICU resource charge skipped', ['code' => $code, 'reason' => $e->getMessage()]);
        }
    }

    private function resolveServiceCode(string $type): ?string
    {
        return match (strtoupper($type)) {
            'INCUBATOR'  => 'INCUBATOR_DAY',
            'WARMER'     => 'WARMER_DAY',
            'ISOLATION'  => 'NICU_ISOLATION_DAY',
            'NICU_BED'   => 'BED_NICU',
            default      => null,
        };
    }
}
