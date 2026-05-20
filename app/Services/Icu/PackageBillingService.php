<?php

namespace App\Services\Icu;

use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuPatientPackageEnrollment;
use App\Models\PatientCharge;
use Illuminate\Support\Facades\DB;

/**
 * Generates the package's own day/hour/fixed-rate charges. Idempotent:
 *   - Day:   posts at most one PatientCharge per ICU calendar-day per enrollment.
 *   - Fixed: posts at most one PatientCharge for the enrollment lifetime.
 *   - Hour:  computes elapsed hours since enrollment start and posts a single
 *            running charge that is replaced (delete + recreate) on each refresh
 *            until the enrollment ends, then frozen.
 *
 * Idempotency is enforced via the `remarks` field carrying a stable key
 *   pkg:{enrollment_id}:{period}
 * which lets refreshes detect "already posted" without a separate ledger.
 */
class PackageBillingService
{
    public function refresh(IcuAdmission $admission, ?\DateTimeInterface $now = null): array
    {
        $now        = $now ?: now();
        $enrollment = app(PackageCoverageService::class)->activeEnrollment($admission);

        if (! $enrollment || ! $enrollment->package_id || $enrollment->billing_mode === 'Itemized') {
            return ['posted' => 0, 'reason' => 'No package billing applicable'];
        }

        $package = $enrollment->package;
        if (! $package || ! $package->is_active) {
            return ['posted' => 0, 'reason' => 'Package inactive'];
        }

        $start = $enrollment->start_time;
        $end   = $enrollment->end_time && $enrollment->end_time < $now ? $enrollment->end_time : $now;

        return match ($package->billing_unit) {
            'Day'   => $this->postDailyCharges($admission, $enrollment, $start, $end),
            'Hour'  => $this->postRunningHourly($admission, $enrollment, $start, $end),
            'Fixed' => $this->postFixedOnce($admission, $enrollment, $start),
            default => ['posted' => 0, 'reason' => "Unknown billing unit: {$package->billing_unit}"],
        };
    }

    protected function postDailyCharges(
        IcuAdmission $admission,
        IcuPatientPackageEnrollment $en,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): array {
        $package = $en->package;
        $rate    = (float) $package->rate;

        // Iterate calendar-day buckets; cheap because ICU stays are days, not years.
        $cursor = (clone $start);
        if ($cursor instanceof \Carbon\Carbon) {
            $cursor = $cursor->copy();
        } else {
            $cursor = \Carbon\Carbon::instance(\DateTime::createFromImmutable($cursor instanceof \DateTimeImmutable ? $cursor : new \DateTimeImmutable('@' . $cursor->getTimestamp())));
        }
        $endC = \Carbon\Carbon::instance(\DateTime::createFromImmutable($end instanceof \DateTimeImmutable ? $end : new \DateTimeImmutable('@' . $end->getTimestamp())));

        $posted = 0;
        DB::transaction(function () use ($admission, $en, $rate, $cursor, $endC, $package, &$posted) {
            while ($cursor->startOfDay()->lte($endC->copy()->startOfDay())) {
                $key = "pkg:{$en->id}:day:" . $cursor->toDateString();

                $exists = PatientCharge::where('charge_module', 'icu')
                    ->where('case_id', $admission->case_id)
                    ->where('remarks', 'like', "%{$key}%")
                    ->exists();

                if (! $exists) {
                    PatientCharge::create([
                        'case_id'       => $admission->case_id,
                        'charge_module' => 'icu',
                        'doctor_id'     => $admission->referring_doctor_id,
                        'ipd_id'        => $admission->ipdIdForCharge(),
                        'charge_item'   => sprintf('%s — package day %s', $package->package_name, $cursor->toDateString()),
                        'unit_price'    => $rate,
                        'quantity'      => 1,
                        'amount'        => $rate,
                        'net_amount'    => $rate,
                        'date'          => $cursor->copy()->endOfDay(),
                        'remarks'       => $key,
                        'created_by'    => auth()->id(),
                    ]);
                    $posted++;
                }

                $cursor->addDay();
            }
        });

        return ['posted' => $posted, 'reason' => "Day-rate package refresh, {$posted} new line(s)."];
    }

    protected function postRunningHourly(
        IcuAdmission $admission,
        IcuPatientPackageEnrollment $en,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): array {
        $package = $en->package;
        $rate    = (float) $package->rate;

        $startC = \Carbon\Carbon::instance(\DateTime::createFromImmutable($start instanceof \DateTimeImmutable ? $start : new \DateTimeImmutable('@' . $start->getTimestamp())));
        $endC   = \Carbon\Carbon::instance(\DateTime::createFromImmutable($end   instanceof \DateTimeImmutable ? $end   : new \DateTimeImmutable('@' . $end->getTimestamp())));
        $hours  = max(1, (int) ceil($startC->diffInMinutes($endC) / 60));
        $amount = round($rate * $hours, 2);

        $key = "pkg:{$en->id}:hour";

        $posted = 0;
        DB::transaction(function () use ($admission, $en, $package, $rate, $hours, $amount, $key, &$posted, $endC) {
            // Replace any prior running charge for this enrollment
            PatientCharge::where('charge_module', 'icu')
                ->where('case_id', $admission->case_id)
                ->where('remarks', 'like', "%{$key}%")
                ->delete();

            PatientCharge::create([
                'case_id'       => $admission->case_id,
                'charge_module' => 'icu',
                'doctor_id'     => $admission->referring_doctor_id,
                'ipd_id'        => $admission->ipdIdForCharge(),
                'charge_item'   => sprintf('%s — package %d hr', $package->package_name, $hours),
                'unit_price'    => $rate,
                'quantity'      => $hours,
                'amount'        => $amount,
                'net_amount'    => $amount,
                'date'          => $endC,
                'remarks'       => $key,
                'created_by'    => auth()->id(),
            ]);
            $posted++;
        });

        return ['posted' => $posted, 'reason' => "Hourly package refreshed at {$hours} hr."];
    }

    protected function postFixedOnce(
        IcuAdmission $admission,
        IcuPatientPackageEnrollment $en,
        \DateTimeInterface $start
    ): array {
        $package = $en->package;
        $rate    = (float) $package->rate;
        $key     = "pkg:{$en->id}:fixed";

        $exists = PatientCharge::where('charge_module', 'icu')
            ->where('case_id', $admission->case_id)
            ->where('remarks', 'like', "%{$key}%")
            ->exists();

        if ($exists) {
            return ['posted' => 0, 'reason' => 'Fixed package charge already posted.'];
        }

        PatientCharge::create([
            'case_id'       => $admission->case_id,
            'charge_module' => 'icu',
            'doctor_id'     => $admission->referring_doctor_id,
            'ipd_id'        => $admission->ipdIdForCharge(),
            'charge_item'   => $package->package_name . ' — fixed package fee',
            'unit_price'    => $rate,
            'quantity'      => 1,
            'amount'        => $rate,
            'net_amount'    => $rate,
            'date'          => $start,
            'remarks'       => $key,
            'created_by'    => auth()->id(),
        ]);

        return ['posted' => 1, 'reason' => 'Fixed package charge posted.'];
    }
}
