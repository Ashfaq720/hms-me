<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\PatientCharge;
use App\Services\Icu\PackageBillingService;
use App\Services\Icu\PackageCoverageService;

class IcuBillingPreviewController extends Controller
{
    public function show($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient', 'bed.bedType',
            'equipmentUsageLogs.equipment',
            'activePackageEnrollment.package',
        ])->findOrFail($admissionId);

        $enrollment = app(PackageCoverageService::class)->activeEnrollment($admission);

        $charges = PatientCharge::where('charge_module', 'icu')
            ->where('case_id', $admission->case_id)
            ->whereNull('deleted_at')
            ->orderBy('date')
            ->get();

        $totals = [
            'package'  => 0,
            'extra'    => 0,
            'discount' => 0,
            'paid'     => 0,
            'total'    => 0,
        ];

        $packageLines = $charges->filter(fn($c) => str_contains((string) $c->remarks, 'pkg:'));
        $extraLines   = $charges->reject(fn($c) => str_contains((string) $c->remarks, 'pkg:'));

        $totals['package'] = (float) $packageLines->sum('net_amount');
        $totals['extra']   = (float) $extraLines->sum('net_amount');
        $totals['total']   = $totals['package'] + $totals['extra'] - $totals['discount'];

        // Covered (zeroed) equipment usage rows for transparency
        $coveredEquipment = $admission->equipmentUsageLogs->where('covered_by_package', true);

        return view('icu.billing.preview', compact(
            'admission', 'enrollment', 'packageLines', 'extraLines',
            'coveredEquipment', 'totals'
        ));
    }

    /**
     * Refresh package charges (idempotent) — gives the bill preview a manual "tick".
     */
    public function refresh($admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);
        $result    = app(PackageBillingService::class)->refresh($admission);

        return redirect()
            ->route('icu.admissions.billing.preview', $admission->id)
            ->with('success', "Package billing refreshed: {$result['reason']}");
    }
}
