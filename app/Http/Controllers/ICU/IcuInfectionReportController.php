<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAntibioticUsageLog;
use App\Models\Icu\IcuInfectionRecord;
use Illuminate\Http\Request;

class IcuInfectionReportController extends Controller
{
    /**
     * BRD §9 infection control reports:
     *   - current isolation patient list
     *   - infection-type breakdown
     *   - ICU-acquired infection rate
     *   - antibiotic usage list
     *   - device-associated infection list
     */
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        $icuType = $request->input('icu_type');
        $isolationType = $request->input('isolation_type');

        // Active isolation patients
        $isolationQuery = IcuInfectionRecord::with('admission.bed.bedType')
            ->where('is_active', true)
            ->where('isolation_type', '!=', 'None');
        if ($isolationType) {
            $isolationQuery->where('isolation_type', $isolationType);
        }
        if ($icuType) {
            $isolationQuery->whereHas('admission', fn($q) => $q->where('icu_type', $icuType));
        }
        $isolationPatients = $isolationQuery->get();

        // Infection records in window
        $records = IcuInfectionRecord::whereBetween('tagged_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($icuType, fn($q) => $q->whereHas('admission', fn($s) => $s->where('icu_type', $icuType)))
            ->get();

        $byStatus = $records->groupBy('infection_status')->map->count();
        $bySource = $records->groupBy('suspected_source')->map->count();
        $byOrganism = $records->whereNotNull('organism')->groupBy('organism')->map->count();
        $icuAcquired = $records->where('suspected_source', 'IcuAcquired')->count();
        $deviceAssociated = $records->where('suspected_source', 'DeviceAssociated')->count();

        // Total ICU patients in window (denominator for infection rate)
        $totalIcuPatients = IcuAdmission::whereBetween('admission_time', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($icuType, fn($q) => $q->where('icu_type', $icuType))
            ->count();

        $infectionRate = $totalIcuPatients > 0
            ? round(($records->count() / $totalIcuPatients) * 100, 1)
            : 0;

        // Antibiotic usage
        $antibiotics = IcuAntibioticUsageLog::with('admission')
            ->whereBetween('start_date', [$from, $to])
            ->when($icuType, fn($q) => $q->whereHas('admission', fn($s) => $s->where('icu_type', $icuType)))
            ->orderByDesc('id')
            ->get();

        $longRunning = $antibiotics->filter(fn($a) => $a->status === 'Active' && $a->durationDays() > 7);

        return view('icu.infection.reports', compact(
            'from', 'to', 'icuType', 'isolationType',
            'isolationPatients', 'records', 'byStatus', 'bySource', 'byOrganism',
            'icuAcquired', 'deviceAssociated', 'totalIcuPatients', 'infectionRate',
            'antibiotics', 'longRunning'
        ));
    }
}
