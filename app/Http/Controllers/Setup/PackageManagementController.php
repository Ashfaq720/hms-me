<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\IpdPatientPackage;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Package Management dashboard + drill-down pages.
 *
 * Owns the landing pages for the Package Management top-level menu.
 * Heavy CRUD lives in ServicePackageController; status transitions in
 * IpdPatientPackageController. This controller is read-only aggregation.
 */
class PackageManagementController extends Controller
{
    /**
     * Top-level dashboard — KPI cards + recent activity + revenue snapshot.
     */
    public function dashboard()
    {
        $this->gate('service_packages_access');

        $kpis = [
            'total_packages'      => ServicePackage::count(),
            'active_packages'     => ServicePackage::active()->count(),
            'total_assignments'   => IpdPatientPackage::count(),
            'pending_approval'    => IpdPatientPackage::where('status', IpdPatientPackage::STATUS_PENDING_APPROVAL)->count(),
            'active_assignments'  => IpdPatientPackage::active()->count(),
            'completed'           => IpdPatientPackage::where('status', IpdPatientPackage::STATUS_COMPLETED)->count(),
            'cancelled'           => IpdPatientPackage::where('status', IpdPatientPackage::STATUS_CANCELLED)->count(),
        ];

        // Revenue snapshot — sum of agreed prices on billable assignments.
        $billableTotal = IpdPatientPackage::billable()
            ->sum(DB::raw('COALESCE(price_override, agreed_price)'));

        // Top 5 most-used packages
        $topPackages = ServicePackage::withCount('applications')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        // Recent activity — last 10 assignments
        $recentAssignments = IpdPatientPackage::with([
                'package:id,code,name,package_type',
                'ipdAdmission.patient:id,patient_name',
                'appliedBy:id,name',
            ])
            ->latest('id')
            ->take(10)
            ->get();

        return view('package-management.dashboard', compact(
            'kpis', 'billableTotal', 'topPackages', 'recentAssignments'
        ));
    }

    /**
     * Categories overview — one card per package type with counts and
     * average price, click to drill into master filtered by that type.
     */
    public function categories()
    {
        $this->gate('service_packages_access');

        $stats = ServicePackage::query()
            ->selectRaw('
                package_type,
                COUNT(*)                            as total,
                SUM(CASE WHEN status = "Active"   THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "Inactive" THEN 1 ELSE 0 END) as inactive,
                ROUND(AVG(base_price), 2)           as avg_price,
                MIN(base_price)                     as min_price,
                MAX(base_price)                     as max_price
            ')
            ->groupBy('package_type')
            ->get()
            ->keyBy('package_type');

        // Ensure every spec-defined type appears, even with 0 rows
        $categories = collect(ServicePackage::TYPES)->map(function ($type) use ($stats) {
            $row = $stats->get($type);
            return [
                'type'      => $type,
                'total'     => $row->total     ?? 0,
                'active'    => $row->active    ?? 0,
                'inactive'  => $row->inactive  ?? 0,
                'avg_price' => $row->avg_price ?? 0,
                'min_price' => $row->min_price ?? 0,
                'max_price' => $row->max_price ?? 0,
            ];
        });

        return view('package-management.categories', compact('categories'));
    }

    /**
     * Pending Approval workqueue — only rows awaiting approval, with
     * inline approve / cancel / open buttons.
     */
    public function pendingApproval(Request $request)
    {
        $this->gate('ipd_packages_approve');

        $assignments = IpdPatientPackage::with([
                'package:id,code,name,package_type,base_price,requires_approval,approval_role',
                'ipdAdmission.patient:id,patient_name',
                'appliedBy:id,name',
            ])
            ->where('status', IpdPatientPackage::STATUS_PENDING_APPROVAL)
            ->latest('id')
            ->paginate(25);

        return view('package-management.pending-approval', compact('assignments'));
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && method_exists(auth()->user(), 'can') && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
