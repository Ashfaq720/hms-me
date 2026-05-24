<?php

namespace App\Services\ServiceCharge;

use App\Models\Encounter\Encounter;
use App\Models\ServiceCharge\ServiceCatalog;
use App\Models\ServiceCharge\ServiceChargePosting;
use App\Models\ServiceCharge\ServiceChargeRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service-charge auto-posting engine (SRS §5.18, §10.9).
 *
 * Workflow code calls $engine->post(...) when a billable event happens
 * (admission, bed allocation, OT start, procedure complete, ...). The
 * engine looks up the catalog item, applies matching rules, computes the
 * final price and writes an immutable ServiceChargePosting row.
 */
class ServiceChargeEngine
{
    /**
     * Post a single service-charge line for a billable event.
     *
     * @param  array{
     *   service_code:string,
     *   encounter?:Encounter|int|null,
     *   patient_id?:int|null,
     *   trigger_event:string,
     *   trigger_source?:object|null,
     *   quantity?:float|int,
     *   patient_type?:string|null,
     *   package_code?:string|null,
     *   bed_type?:string|null,
     *   ward?:string|null,
     *   reason?:string|null,
     *   override_unit_price?:float|null,
     *   posted_by?:int|null,
     *   metadata?:array<string,mixed>,
     * } $payload
     */
    public function post(array $payload): ServiceChargePosting
    {
        if (empty($payload['service_code'])) {
            throw new InvalidArgumentException('service_code is required');
        }
        if (empty($payload['trigger_event'])) {
            throw new InvalidArgumentException('trigger_event is required');
        }

        return DB::transaction(function () use ($payload) {
            $catalog = $this->resolveCatalog($payload['service_code']);
            $encounter = $this->resolveEncounter($payload['encounter'] ?? null);

            $context = [
                'patient_type' => $payload['patient_type'] ?? ($encounter ? null : null),
                'package_code' => $payload['package_code'] ?? null,
                'bed_type' => $payload['bed_type'] ?? null,
                'ward' => $payload['ward'] ?? null,
                'encounter_type' => $encounter?->encounter_type,
            ];

            $quantity = (float) ($payload['quantity'] ?? 1);
            $overrideUnitPrice = $payload['override_unit_price'] ?? null;
            $unitPrice = $overrideUnitPrice !== null
                ? (float) $overrideUnitPrice
                : (float) $catalog->base_price;

            // Apply matching rules in priority order; first override wins,
            // discounts/percent rules stack on top.
            $rulesApplied = [];
            $matchedRules = $this->matchRules($catalog, $context);
            foreach ($matchedRules as $rule) {
                $unitPrice = $this->applyRule($rule, $unitPrice, $rulesApplied);
            }

            $discount = 0;
            if ($overrideUnitPrice !== null) {
                $rulesApplied[] = ['rule_kind' => 'manual_override', 'value' => $overrideUnitPrice];
            }

            $taxableAmount = $unitPrice * $quantity - $discount;
            $taxAmount = round($taxableAmount * ((float) $catalog->tax_percent / 100), 2);
            $netAmount = round($taxableAmount + $taxAmount, 2);

            $source = $payload['trigger_source'] ?? null;

            return ServiceChargePosting::create([
                'organization_id' => $catalog->organization_id,
                'branch_id' => $catalog->branch_id,
                'encounter_id' => $encounter?->id,
                'patient_id' => $payload['patient_id'] ?? $encounter?->patient_id,
                'service_catalog_id' => $catalog->id,
                'trigger_event' => $payload['trigger_event'],
                'trigger_source_type' => $source ? $source::class : null,
                'trigger_source_id' => $source?->getKey(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'net_amount' => $netAmount,
                'rules_applied' => $rulesApplied ?: null,
                'metadata' => $payload['metadata'] ?? null,
                'reason' => $payload['reason'] ?? null,
                'status' => 'posted',
                'posted_by' => $payload['posted_by'] ?? auth()->id(),
            ]);
        });
    }

    public function reverse(ServiceChargePosting $posting, ?string $reason = null): ServiceChargePosting
    {
        if ($posting->status !== 'posted') {
            throw new InvalidArgumentException('Only posted charges can be reversed');
        }
        $posting->update([
            'status' => 'reversed',
            'reversed_by' => auth()->id(),
            'reversed_at' => now(),
            'reason' => $reason ?? $posting->reason,
        ]);
        return $posting->fresh();
    }

    /**
     * Per-hour or per-day accrual helper. Returns the quantity to charge
     * given a start time and an end time (defaults to now) for a given unit.
     */
    public function accrualQuantity(Carbon $start, ?Carbon $end, string $chargeUnit): float
    {
        $end ??= now();
        return match ($chargeUnit) {
            'per_hour' => max(1, ceil($end->diffInMinutes($start) / 60)),
            'per_day' => max(1, ceil($end->diffInHours($start) / 24)),
            'per_session', 'per_use', 'per_test', 'per_dose', 'per_package' => 1,
            'per_km', 'per_unit' => 1,
            default => 1,
        };
    }

    private function resolveCatalog(string $code): ServiceCatalog
    {
        $catalog = ServiceCatalog::active()->where('code', $code)->validOn(now())->first();
        if (! $catalog) {
            throw new InvalidArgumentException("Service catalog '{$code}' not found or inactive");
        }
        return $catalog;
    }

    private function resolveEncounter(mixed $encounter): ?Encounter
    {
        if ($encounter instanceof Encounter) {
            return $encounter;
        }
        if (is_numeric($encounter)) {
            return Encounter::find((int) $encounter);
        }
        return null;
    }

    /** @return array<int,ServiceChargeRule> */
    private function matchRules(ServiceCatalog $catalog, array $context): array
    {
        $rules = $catalog->rules()->active()->get();
        $now = now()->toDateString();

        return $rules->filter(function (ServiceChargeRule $rule) use ($context, $now) {
            if ($rule->valid_from && $rule->valid_from->toDateString() > $now) {
                return false;
            }
            if ($rule->valid_to && $rule->valid_to->toDateString() < $now) {
                return false;
            }
            $value = $context[$rule->rule_kind] ?? null;
            return $value !== null && (string) $value === (string) $rule->rule_value;
        })->values()->all();
    }

    private function applyRule(ServiceChargeRule $rule, float $unitPrice, array &$applied): float
    {
        $before = $unitPrice;
        switch ($rule->adjustment_type) {
            case 'override':
                $unitPrice = (float) $rule->adjustment_value;
                break;
            case 'percent':
                $unitPrice += $unitPrice * ((float) $rule->adjustment_value / 100);
                break;
            case 'flat':
                $unitPrice += (float) $rule->adjustment_value;
                break;
        }
        $applied[] = [
            'rule_id' => $rule->id,
            'rule_kind' => $rule->rule_kind,
            'rule_value' => $rule->rule_value,
            'adjustment_type' => $rule->adjustment_type,
            'adjustment_value' => (float) $rule->adjustment_value,
            'before' => $before,
            'after' => $unitPrice,
        ];
        return $unitPrice;
    }
}
