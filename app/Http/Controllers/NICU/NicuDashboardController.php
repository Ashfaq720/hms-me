<?php

namespace App\Http\Controllers\Nicu;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuAdmission;
use App\Models\Nicu\NicuInfectionRecord;
use App\Models\Nicu\NicuResourceAllocation;
use App\Models\Nicu\NicuVital;

class NicuDashboardController extends Controller
{
    public function dashboard()
    {
        $kpi = [
            'active_admissions' => NicuAdmission::where('status', 'admitted')->count(),
            'critical_count' => NicuAdmission::where('status', 'admitted')->where('is_critical', true)->count(),
            'preterm_count' => NicuAdmission::where('status', 'admitted')->where('is_preterm', true)->count(),
            'lbw_count' => NicuAdmission::where('status', 'admitted')->where('is_low_birth_weight', true)->count(),
            'incubators_in_use' => NicuResourceAllocation::where('resource_type', 'INCUBATOR')->where('status', 'active')->count(),
            'warmers_in_use' => NicuResourceAllocation::where('resource_type', 'WARMER')->where('status', 'active')->count(),
            'critical_alerts_24h' => NicuVital::where('alert_level', 'CRITICAL')->where('recorded_at', '>=', now()->subDay())->count(),
            'active_infections' => NicuInfectionRecord::where('status', 'active')->count(),
            'cluster_alerts' => NicuInfectionRecord::where('alert_cluster', true)->where('status', 'active')->count(),
        ];

        $admissions = NicuAdmission::with(['patient', 'mother', 'resources' => fn ($q) => $q->where('status', 'active')])
            ->where('status', 'admitted')
            ->latest('admission_time')
            ->take(20)
            ->get();

        return view('nicu.dashboard', compact('kpi', 'admissions'));
    }

    public function admissionsIndex(\Illuminate\Http\Request $request)
    {
        $q = NicuAdmission::with(['patient', 'mother', 'resources'])
            ->when($request->status, fn ($qq, $s) => $qq->where('status', $s))
            ->when($request->risk === 'critical', fn ($qq) => $qq->where('is_critical', true))
            ->when($request->risk === 'preterm',  fn ($qq) => $qq->where('is_preterm', true))
            ->when($request->risk === 'lbw',      fn ($qq) => $qq->where('is_low_birth_weight', true))
            ->when($request->search, fn ($qq, $s) => $qq->where(function ($w) use ($s) {
                $w->where('baby_id', 'like', "%{$s}%")
                  ->orWhereHas('patient', fn ($p) => $p->where('patient_name', 'like', "%{$s}%"));
            }))
            ->latest('id');

        $admissions = $q->paginate(25)->withQueryString();

        $stats = [
            'total'     => NicuAdmission::count(),
            'admitted'  => NicuAdmission::where('status', 'admitted')->count(),
            'critical'  => NicuAdmission::where('status', 'admitted')->where('is_critical', true)->count(),
            'preterm'   => NicuAdmission::where('status', 'admitted')->where('is_preterm', true)->count(),
            'lbw'       => NicuAdmission::where('status', 'admitted')->where('is_low_birth_weight', true)->count(),
            'discharged'=> NicuAdmission::where('status', 'discharged')->count(),
        ];

        return view('nicu.admissions.index', compact('admissions', 'stats'));
    }

    public function admissionShow($id)
    {
        $admission = NicuAdmission::with([
            'patient', 'mother', 'resources.bed.bedType',
        ])->findOrFail($id);

        $vitals       = \App\Models\Nicu\NicuVital::where('nicu_admission_id', $id)->latest('recorded_at')->take(20)->get();
        $latestVital  = $vitals->first();
        $growth       = \App\Models\Nicu\NicuGrowthRecord::where('nicu_admission_id', $id)->latest('measured_on')->take(10)->get();
        $feeds        = \App\Models\Nicu\NicuFeedingSchedule::where('nicu_admission_id', $id)->latest('id')->take(10)->get();
        $meds         = \App\Models\Nicu\NicuMedicationOrder::where('nicu_admission_id', $id)->latest('id')->take(10)->get();
        $procedures   = \App\Models\Nicu\NicuProcedure::where('nicu_admission_id', $id)->latest('id')->take(10)->get();
        $infections   = \App\Models\Nicu\NicuInfectionRecord::where('nicu_admission_id', $id)->latest('id')->take(10)->get();
        $consents     = \App\Models\Nicu\NicuConsent::where('nicu_admission_id', $id)->latest('id')->take(10)->get();

        return view('nicu.admissions.show', compact(
            'admission', 'vitals', 'latestVital', 'growth',
            'feeds', 'meds', 'procedures', 'infections', 'consents'
        ));
    }

    public function resourcesIndex()
    {
        $allocations = NicuResourceAllocation::with(['admission.patient', 'bed.bedType'])->latest('id')->paginate(25);
        return view('nicu.resources.index', compact('allocations'));
    }

    public function vitalsIndex()
    {
        $vitals = NicuVital::with('admission.patient')->latest('recorded_at')->paginate(50);
        return view('nicu.vitals.index', compact('vitals'));
    }

    public function feedingIndex()
    {
        $schedules = \App\Models\Nicu\NicuFeedingSchedule::with('admission.patient')->latest('id')->paginate(25);
        return view('nicu.feeding.index', compact('schedules'));
    }

    public function growthIndex()
    {
        $records = \App\Models\Nicu\NicuGrowthRecord::with('admission.patient')->latest('measured_on')->paginate(25);
        return view('nicu.growth.index', compact('records'));
    }

    public function medicationsIndex()
    {
        $orders = \App\Models\Nicu\NicuMedicationOrder::with('admission.patient')->latest('id')->paginate(25);
        return view('nicu.medications.index', compact('orders'));
    }

    public function proceduresIndex()
    {
        $procedures = \App\Models\Nicu\NicuProcedure::with('admission.patient')->latest('id')->paginate(25);
        return view('nicu.procedures.index', compact('procedures'));
    }

    public function infectionsIndex()
    {
        $infections = \App\Models\Nicu\NicuInfectionRecord::with('admission.patient')->latest('id')->paginate(25);
        return view('nicu.infections.index', compact('infections'));
    }

    public function consentsIndex()
    {
        $consents = \App\Models\Nicu\NicuConsent::with('admission.patient')->latest('id')->paginate(25);
        return view('nicu.consents.index', compact('consents'));
    }
}
