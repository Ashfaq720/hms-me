<?php

namespace App\Observers;

use App\Models\Bed;
use App\Models\Encounter\Encounter;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Bed-day auto-accrual (SRS §5.5, §5.7, §5.18).
 *
 * On bed allocation create  → post one service-charge line for the day
 *                            (or hours, depending on the catalog charge_unit).
 * On bed allocation update  → if the `to` date was set, post the final
 *                            accrual line covering the elapsed period.
 *
 * The actual hourly/daily accrual is computed via
 * ServiceChargeEngine::accrualQuantity().
 *
 * The service catalog code is picked from the bed allocation's
 * `allocation_type`:  icu → BED_ICU,  nicu → BED_NICU,  ward → BED_GENERAL,
 * cabin → BED_CABIN, otherwise BED_GENERAL.
 */
class IpdPatientBedObserver
{
    public function __construct(private ServiceChargeEngine $engine)
    {
    }

    public function created(IpdPatientBed $allocation): void
    {
        // Charge gets posted on transfer/discharge so we know the elapsed time.
        // On create we only ensure the encounter still has an open bill assembly.
    }

    /**
     * When `to` is filled (bed transfer or discharge), post the accrued charge.
     */
    public function updated(IpdPatientBed $allocation): void
    {
        if (! $allocation->wasChanged('to') || ! $allocation->to) {
            return;
        }

        $from = Carbon::parse($allocation->getOriginal('from') ?? $allocation->from);
        $to = Carbon::parse($allocation->to);
        if ($to->lessThanOrEqualTo($from)) {
            return;
        }

        $serviceCode = $this->resolveServiceCode($allocation);
        if (! $serviceCode) {
            return;
        }

        $encounterId = $this->resolveEncounterId($allocation);
        $unit = $this->resolveChargeUnit($serviceCode);
        $quantity = $this->engine->accrualQuantity($from, $to, $unit);

        try {
            $this->engine->post([
                'service_code' => $serviceCode,
                'encounter' => $encounterId,
                'trigger_event' => 'ipd.bed.accrual',
                'trigger_source' => $allocation,
                'quantity' => $quantity,
                'reason' => 'Bed allocation ' . $allocation->id . ' from ' . $from->toDateString() . ' to ' . $to->toDateString(),
                'metadata' => [
                    'bed_id' => $allocation->bed_id,
                    'allocation_type' => $allocation->allocation_type,
                    'from' => $from->toDateTimeString(),
                    'to' => $to->toDateTimeString(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::info('Bed accrual skipped — catalog missing', [
                'allocation_id' => $allocation->id,
                'service_code' => $serviceCode,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    private function resolveServiceCode(IpdPatientBed $allocation): ?string
    {
        return match (strtolower((string) $allocation->allocation_type)) {
            'icu' => 'BED_ICU',
            'nicu' => 'BED_NICU',
            'ccu' => 'BED_CCU',
            'cabin', 'private' => 'BED_CABIN',
            'ward', 'general', '', null => 'BED_GENERAL',
            default => 'BED_GENERAL',
        };
    }

    private function resolveChargeUnit(string $serviceCode): string
    {
        $catalog = \App\Models\ServiceCharge\ServiceCatalog::where('code', $serviceCode)
            ->where('is_active', true)
            ->first();
        return $catalog?->charge_unit ?? 'per_day';
    }

    private function resolveEncounterId(IpdPatientBed $allocation): ?int
    {
        if (! $allocation->ipd_patient_id) {
            return null;
        }
        $ipd = IpdPatient::find($allocation->ipd_patient_id);
        return $ipd?->encounter_id;
    }
}
