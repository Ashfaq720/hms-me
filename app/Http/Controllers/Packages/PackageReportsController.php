<?php

namespace App\Http\Controllers\Packages;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Package\PackageConsumptionEntry;
use App\Models\Package\PackageEnrollment;
use Illuminate\Support\Facades\DB;

class PackageReportsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_packages'    => Package::count(),
            'active_packages'   => Package::where('status', 'active')->count(),
            'total_enrolments'  => PackageEnrollment::count(),
            'active_enrolments' => PackageEnrollment::whereIn('status', ['active', 'draft', 'confirmed'])->count(),
            'total_revenue'     => (float) PackageEnrollment::sum('paid_amount'),
            'outstanding'       => (float) PackageEnrollment::sum(DB::raw('agreed_price - paid_amount')),
        ];
        return view('packages.reports.index', compact('stats'));
    }

    public function department()
    {
        $rows = DB::table('package_enrollments as pe')
            ->join('packages as p', 'p.id', '=', 'pe.package_id')
            ->leftJoin('departments as d', 'd.id', '=', 'p.department_id')
            ->select('d.name as dept', DB::raw('COUNT(pe.id) as enrolments'),
                DB::raw('SUM(pe.agreed_price) as agreed'),
                DB::raw('SUM(pe.paid_amount) as paid'))
            ->groupBy('d.name')->orderByDesc('enrolments')->get();
        return view('packages.reports.department', compact('rows'));
    }

    public function utilization()
    {
        $rows = DB::table('package_consumption_entries as pce')
            ->join('package_enrollments as pe', 'pe.id', '=', 'pce.package_enrollment_id')
            ->join('packages as p', 'p.id', '=', 'pe.package_id')
            ->select('p.code', 'p.name', 'pce.description',
                DB::raw('SUM(pce.quantity_allowed) as allowed'),
                DB::raw('SUM(pce.quantity_consumed) as consumed'),
                DB::raw('SUM(pce.quantity_extras) as extras'))
            ->groupBy('p.code', 'p.name', 'pce.description')
            ->orderBy('p.code')->get();
        return view('packages.reports.utilization', compact('rows'));
    }

    public function revenue()
    {
        $rows = DB::table('package_enrollments as pe')
            ->join('packages as p', 'p.id', '=', 'pe.package_id')
            ->select('p.code', 'p.name', 'p.package_type',
                DB::raw('COUNT(pe.id) as enrolments'),
                DB::raw('SUM(pe.agreed_price) as agreed'),
                DB::raw('SUM(pe.paid_amount) as paid'),
                DB::raw('SUM(pe.agreed_price - pe.paid_amount) as outstanding'))
            ->groupBy('p.code', 'p.name', 'p.package_type')
            ->orderByDesc('agreed')->get();
        return view('packages.reports.revenue', compact('rows'));
    }

    public function expiry()
    {
        $rows = PackageEnrollment::with('patient', 'package')
            ->whereIn('status', ['active', 'draft', 'confirmed'])
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('end_date')->get();
        return view('packages.reports.expiry', compact('rows'));
    }
}
