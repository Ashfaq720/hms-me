<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuEmergencyEvent;
use App\Models\Icu\IcuVitalLog;
use Illuminate\Http\Request;

class IcuMonitoringDashboardController extends Controller
{
    /**
     * Multi-patient ICU live dashboard.
     */
    public function index(Request $request)
    {
        $icuType = $request->input('icu_type');

        $admissions = IcuAdmission::with(['patient', 'bed.bedType'])
            ->whereIn('status', ['Admitted', 'Approved'])
            ->when($icuType, fn($q) => $q->where('icu_type', $icuType))
            ->get();

        // Last vital per admission (single batched query)
        $latestVitals = IcuVitalLog::whereIn('icu_admission_id', $admissions->pluck('id'))
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('icu_admission_id')
            ->map(fn($g) => $g->first());

        // Open critical/warning alerts grouped by admission
        $openAlerts = IcuAlert::whereIn('icu_admission_id', $admissions->pluck('id'))
            ->whereIn('status', ['Active', 'Acknowledged'])
            ->get()
            ->groupBy('icu_admission_id');

        // Active Code Blue events
        $activeCodes = IcuEmergencyEvent::whereIn('icu_admission_id', $admissions->pluck('id'))
            ->whereNotIn('status', ['Closed'])
            ->get()
            ->keyBy('icu_admission_id');

        // High-level KPI (scoped to current unit when filtered)
        $totalIcuBeds = Bed::whereHas('bedType', function ($q) use ($icuType) {
            $q->where('is_icu', true)->when($icuType, fn($s) => $s->where('icu_type', $icuType));
        })->count();
        $occupiedBeds  = $admissions->whereNotNull('bed_id')->count();
        $criticalCount = $openAlerts->flatten()->where('severity', 'Critical')->count();

        // Per-unit bed totals (ICU / CCU) — when filtered, only the current unit
        $bedsByUnit = Bed::whereHas('bedType', function ($q) use ($icuType) {
            $q->where('is_icu', true)
              ->whereIn('icu_type', ['ICU', 'CCU'])
              ->when($icuType, fn($s) => $s->where('icu_type', $icuType));
        })
            ->with('bedType:id,icu_type')
            ->get()
            ->groupBy(fn($b) => optional($b->bedType)->icu_type)
            ->map->count();

        return view('icu.dashboard.index', compact(
            'admissions', 'latestVitals', 'openAlerts', 'activeCodes',
            'totalIcuBeds', 'occupiedBeds', 'criticalCount', 'bedsByUnit', 'icuType'
        ));
    }
}
