<?php

namespace App\Services\Package;

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the applicable price for a package given the patient context
 * (bed type, duration, department, patient category). Rules are matched
 * most-specific first; falls back to packages.total_amount if no rule matches.
 *
 * Specificity score: 1 point per matched axis (max 4).
 * On tie, the rule with the latest valid_from (or most recent created_at) wins.
 */
class PackagePriceResolver
{
    public function resolve(Package $package, array $context = []): array
    {
        $today = Carbon::parse($context['date'] ?? now());

        $rules = DB::table('package_price_rules')
            ->where('package_id', $package->id)
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today);
            })
            ->orderByDesc('valid_from')
            ->orderByDesc('id')
            ->get();

        $best = null;
        $bestScore = -1;

        foreach ($rules as $rule) {
            $score = 0;
            // Bed type
            if (! is_null($rule->bed_type_id)) {
                if (! isset($context['bed_type_id']) || $context['bed_type_id'] != $rule->bed_type_id) continue;
                $score++;
            }
            // Department
            if (! is_null($rule->department_id)) {
                if (! isset($context['department_id']) || $context['department_id'] != $rule->department_id) continue;
                $score++;
            }
            // Duration
            if (! is_null($rule->duration_days)) {
                if (! isset($context['duration_days']) || $context['duration_days'] != $rule->duration_days) continue;
                $score++;
            }
            // Patient category
            if ($rule->patient_category !== 'ANY' && ! empty($context['patient_category'])) {
                if ($context['patient_category'] !== $rule->patient_category) continue;
                $score++;
            }
            if ($score > $bestScore) {
                $best = $rule;
                $bestScore = $score;
            }
        }

        if ($best) {
            return [
                'price'   => (float) $best->price,
                'rule_id' => $best->id,
                'source'  => 'rule',
                'matched_axes' => $bestScore,
            ];
        }

        return [
            'price'   => (float) $package->total_amount,
            'rule_id' => null,
            'source'  => 'base',
            'matched_axes' => 0,
        ];
    }

    /**
     * Filter all active packages applicable to a given context. Used by the
     * front-desk admission package picker.
     */
    public function applicablePackages(array $context = [])
    {
        $q = Package::query()->where('is_active', true)->where('status', 'active');

        if (! empty($context['patient_type'])) {
            // patient_type maps to package_type for the dropdown (OPD/IPD/OT/...)
            $q->where(function ($w) use ($context) {
                $w->where('package_type', $context['patient_type'])
                    ->orWhereIn('package_type', $this->compatibleTypes($context['patient_type']));
            });
        }
        if (! empty($context['department_id'])) {
            $q->where(function ($w) use ($context) {
                $w->whereNull('department_id')->orWhere('department_id', $context['department_id']);
            });
        }
        if (! empty($context['admission_type'])) {
            $q->where(function ($w) use ($context) {
                $w->whereNull('admission_type')
                    ->orWhere('admission_type', 'ANY')
                    ->orWhere('admission_type', $context['admission_type']);
            });
        }
        if (! empty($context['bed_type_id'])) {
            $q->where(function ($w) use ($context) {
                $w->whereNull('bed_type_id')->orWhere('bed_type_id', $context['bed_type_id']);
            });
        }

        return $q->orderBy('package_type')->orderBy('name')->get()->map(function (Package $p) use ($context) {
            $res = $this->resolve($p, $context);
            return [
                'id' => $p->id, 'code' => $p->code, 'name' => $p->name,
                'package_type' => $p->package_type, 'admission_type' => $p->admission_type,
                'price' => $res['price'], 'price_source' => $res['source'],
                'matched_axes' => $res['matched_axes'],
                'validity_days' => $p->validity_days,
                'description' => $p->description,
            ];
        });
    }

    /**
     * Map patient_type → package_types that can apply.
     * IPD admission → IPD/ICU/CCU/NICU/OT/MATERNITY packages all available.
     */
    private function compatibleTypes(string $patientType): array
    {
        return match (strtoupper($patientType)) {
            'IPD'    => ['IPD', 'ICU', 'CCU', 'NICU', 'OT', 'MATERNITY'],
            'OPD'    => ['OPD', 'DIAGNOSTIC', 'WELLNESS', 'PATHOLOGY', 'RADIOLOGY'],
            'ER'     => ['IPD', 'OT', 'ICU', 'CCU', 'NICU'],
            'OT'     => ['OT'],
            default  => [],
        };
    }
}
