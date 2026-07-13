<?php

namespace App\Services\Icu;

use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuPackageItem;
use App\Models\Icu\IcuPatientPackageEnrollment;

/**
 * Resolves whether a given charge (category + optional code) is covered by the
 * admission's currently-active package, given the active billing mode.
 *
 * Truth table (per BRD §8):
 *   - mode = Itemized          → never covered, every charge billed.
 *   - mode = Package or Mixed  → if item is Included or Limited (within qty), covered;
 *                                if Excluded, billed separately.
 *
 * Mixed differs from Package only at the running-bill display level (extras shown
 * separately) — coverage rules are identical.
 */
class PackageCoverageService
{
    /** Categories used by source modules to tag charges. */
    public const CATEGORIES = [
        'Bed', 'Equipment', 'Procedure', 'Medicine', 'Consumable',
        'DoctorVisit', 'Nursing', 'Lab', 'Radiology', 'Other',
    ];

    /**
     * Active enrollment for an admission, or null if none.
     */
    public function activeEnrollment(IcuAdmission $admission): ?IcuPatientPackageEnrollment
    {
        return IcuPatientPackageEnrollment::where('icu_admission_id', $admission->id)
            ->where('status', 'Active')
            ->latest('id')
            ->first();
    }

    /**
     * Decide coverage for a charge.
     *
     * Returns:
     *   ['covered' => bool, 'enrollment' => IcuPatientPackageEnrollment|null,
     *    'rule_type' => string|null, 'reason' => string]
     */
    public function resolve(
        IcuAdmission $admission,
        string $category,
        ?string $code = null,
        ?\DateTimeInterface $when = null
    ): array {
        $enrollment = $this->activeEnrollment($admission);

        // No package, or itemized mode → never covered
        if (! $enrollment || $enrollment->billing_mode === 'Itemized' || ! $enrollment->package_id) {
            return [
                'covered'    => false,
                'enrollment' => $enrollment,
                'rule_type'  => null,
                'reason'     => 'No active package coverage.',
            ];
        }

        // Charge time must fall within enrollment window
        $when = $when ?: now();
        if ($enrollment->end_time && $when > $enrollment->end_time) {
            return [
                'covered'    => false,
                'enrollment' => $enrollment,
                'rule_type'  => null,
                'reason'     => 'Charge time is after the package window ended.',
            ];
        }
        if ($when < $enrollment->start_time) {
            return [
                'covered'    => false,
                'enrollment' => $enrollment,
                'rule_type'  => null,
                'reason'     => 'Charge time is before the package started.',
            ];
        }

        // Find the most-specific matching item for this category
        $item = $this->findMatchingItem($enrollment->package_id, $category, $code);

        if (! $item) {
            return [
                'covered'    => false,
                'enrollment' => $enrollment,
                'rule_type'  => null,
                'reason'     => "Category '{$category}' is not covered by package.",
            ];
        }

        if ($item->rule_type === 'Excluded') {
            return [
                'covered'    => false,
                'enrollment' => $enrollment,
                'rule_type'  => 'Excluded',
                'reason'     => "Excluded from package '{$enrollment->package->package_name}'.",
            ];
        }

        if ($item->rule_type === 'Included') {
            return [
                'covered'    => true,
                'enrollment' => $enrollment,
                'rule_type'  => 'Included',
                'reason'     => "Included in package '{$enrollment->package->package_name}'.",
            ];
        }

        // Limited — needs caller to tell us how many already used
        return [
            'covered'    => true,        // tentative — caller must check qty via withinLimit
            'enrollment' => $enrollment,
            'rule_type'  => 'Limited',
            'item'       => $item,
            'reason'     => sprintf(
                'Limited: %d %s — extra%s billed separately.',
                $item->included_qty ?? 0,
                $item->limit_period ?? 'PerStay',
                $item->extra_charge_allowed ? '' : ' NOT'
            ),
        ];
    }

    /**
     * Most-specific match resolution: exact (category + code) wins over category-wildcard.
     */
    protected function findMatchingItem(int $packageId, string $category, ?string $code): ?IcuPackageItem
    {
        if ($code) {
            $exact = IcuPackageItem::where('package_id', $packageId)
                ->where('charge_category', $category)
                ->where('charge_code', $code)
                ->first();
            if ($exact) {
                return $exact;
            }
        }
        return IcuPackageItem::where('package_id', $packageId)
            ->where('charge_category', $category)
            ->whereNull('charge_code')
            ->first();
    }
}
