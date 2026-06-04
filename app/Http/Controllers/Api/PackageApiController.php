<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Package\PackagePriceResolver;
use Illuminate\Http\Request;

class PackageApiController extends Controller
{
    /**
     * GET /api/packages/applicable
     *  ?patient_type=IPD&department_id=3&admission_type=PLANNED&bed_type_id=7&duration_days=3&patient_category=GENERAL
     * Returns JSON list of packages with resolved price for the picker.
     */
    public function applicable(Request $request, PackagePriceResolver $resolver)
    {
        $context = $request->only([
            'patient_type', 'department_id', 'admission_type',
            'bed_type_id', 'duration_days', 'patient_category',
        ]);
        $list = $resolver->applicablePackages($context);
        return response()->json([
            'context' => $context,
            'count' => $list->count(),
            'packages' => $list->values(),
        ]);
    }
}
