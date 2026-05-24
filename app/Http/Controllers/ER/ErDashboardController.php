<?php

namespace App\Http\Controllers\ER;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\FrontDesk\ErPatient;
use Illuminate\Support\Carbon;

/**
 * Live ER dashboard — uses the existing er_patients table only.
 *
 * Maps BRD KPIs to existing schema:
 *   Total today    → COUNT today's arrivals
 *   Critical       → priority = CRITICAL
 *   Waiting        → status in (Waiting, Under Assessment)
 *   Bed avail.     → ER-tagged BedType beds with status = Available
 *   Triage chart   → group by priority
 *   Avg waiting    → arrival_time → discharged/admitted updated_at
 */
class ErDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $kpis = [
            'total_today'      => ErPatient::today()->count(),
            'active'           => ErPatient::active()->count(),
            'critical_active'  => ErPatient::active()->critical()->count(),
            'waiting'          => ErPatient::whereIn('status', [
                                    ErPatient::STATUS_WAITING,
                                    ErPatient::STATUS_UNDER_ASSESS,
                                  ])->count(),
            'in_treatment'     => ErPatient::where('status', ErPatient::STATUS_IN_TREATMENT)->count(),
            'admitted_today'   => ErPatient::today()->where('status', ErPatient::STATUS_ADMITTED)->count(),
            'discharged_today' => ErPatient::today()->where('status', ErPatient::STATUS_DISCHARGED)->count(),
            'expired_today'    => ErPatient::today()->where('status', ErPatient::STATUS_EXPIRED)->count(),
        ];

        // Triage breakdown for active patients only
        $triage = ErPatient::active()
            ->selectRaw('priority, count(*) c')
            ->groupBy('priority')
            ->pluck('c', 'priority');

        $triageCounts = [
            'CRITICAL' => (int) ($triage[ErPatient::PRIORITY_CRITICAL] ?? 0),
            'HIGH'     => (int) ($triage[ErPatient::PRIORITY_HIGH]     ?? 0),
            'NORMAL'   => (int) ($triage[ErPatient::PRIORITY_NORMAL]   ?? 0),
        ];

        // ER bed availability (BedType named like ER or Emergency / Observation)
        $erBedTypeIds = BedType::where(function ($q) {
            $q->whereIn('name', ['Emergency Bed', 'Observation Bed', 'ER Bed'])
              ->orWhere('name', 'like', '%Emergency%');
        })->pluck('id');

        $bedStats = [
            'total'     => Bed::whereIn('bed_type_id', $erBedTypeIds)->where('is_active', 1)->count(),
            'available' => Bed::whereIn('bed_type_id', $erBedTypeIds)
                                ->where('is_active', 1)
                                ->whereIn('status', [Bed::STATUS_AVAILABLE, Bed::STATUS_READY])
                                ->count(),
        ];
        $bedStats['occupied'] = max(0, $bedStats['total'] - $bedStats['available']);

        // Critical patients still on the floor
        $criticalPatients = ErPatient::with(['patient:id,patient_name,mrn,mobileno', 'doctor:id,name'])
            ->active()->critical()
            ->latest('arrival_time')->limit(10)->get();

        // Recent arrivals (any priority)
        $recentArrivals = ErPatient::with(['patient:id,patient_name,mrn,mobileno', 'doctor:id,name'])
            ->latest('arrival_time')->limit(10)->get();

        // Avg waiting time across active patients (live)
        $activeForAvg = ErPatient::active()->get(['arrival_time']);
        $avgWaitMin   = $activeForAvg->isEmpty() ? 0 :
            (int) round($activeForAvg->avg(fn ($e) => $e->arrival_time
                ? Carbon::parse($e->arrival_time)->diffInMinutes(now()) : 0));

        return view('er.dashboard', compact(
            'kpis', 'triageCounts', 'bedStats',
            'criticalPatients', 'recentArrivals', 'avgWaitMin'
        ));
    }
}
