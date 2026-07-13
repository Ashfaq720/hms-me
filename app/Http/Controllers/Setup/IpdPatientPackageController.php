<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\IpdPatientPackage;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

/**
 * Status transitions for IPD patient packages. The package itself is
 * attached/detached via the IPD admission form; this controller handles
 * the lifecycle actions (approve / cancel / mark complete / close)
 * which happen after attachment.
 */
class IpdPatientPackageController extends Controller
{
    /**
     * Cross-admission view: every package assignment across all IPD
     * patients. Filterable by status / package / patient.
     */
    public function index(Request $request)
    {
        $this->gate('ipd_packages_apply');

        $q = IpdPatientPackage::with([
            'package:id,code,name,package_type',
            'ipdAdmission.patient:id,patient_name',
            'approver:id,name',
            'appliedBy:id,name',
        ]);

        if (in_array($request->get('status'), IpdPatientPackage::STATUSES, true)) {
            $q->where('status', $request->get('status'));
        }
        if ($pkgId = $request->get('service_package_id')) {
            $q->where('service_package_id', $pkgId);
        }
        if ($s = trim((string) $request->get('search'))) {
            $q->whereHas('ipdAdmission.patient', function ($x) use ($s) {
                $x->where('patient_name', 'like', "%{$s}%");
            });
        }

        $assignments = $q->latest('id')->paginate(25)->appends($request->query());
        $packages    = ServicePackage::active()->orderBy('name')->get(['id', 'code', 'name']);
        $statuses    = IpdPatientPackage::STATUSES;

        return view('package-assignments.index', compact('assignments', 'packages', 'statuses'));
    }

    public function approve(Request $request, IpdPatientPackage $ipdPatientPackage)
    {
        $this->gate('ipd_packages_approve');

        if ($ipdPatientPackage->status !== IpdPatientPackage::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Only pending packages can be approved.');
        }

        $ipdPatientPackage->update([
            'status'      => IpdPatientPackage::STATUS_CONFIRMED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Approval flips status to Confirmed → trigger auto-billing.
        // postCharge() is idempotent so re-calls are safe.
        $charge = app(\App\Services\PackageBillingService::class)->postCharge($ipdPatientPackage);

        $msg = 'Package approved and confirmed.';
        if ($charge) {
            $msg .= ' Auto-posted ৳' . number_format((float) $charge->net_amount, 2) . ' to patient bill.';
        }
        return back()->with('success', $msg);
    }

    public function cancel(Request $request, IpdPatientPackage $ipdPatientPackage)
    {
        $this->gate('ipd_packages_apply');

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $ipdPatientPackage->canBeCancelled()) {
            return back()->with('error', 'This package can no longer be cancelled.');
        }

        $ipdPatientPackage->update([
            'status'              => IpdPatientPackage::STATUS_CANCELLED,
            'cancellation_reason' => $request->get('cancellation_reason'),
            'cancelled_by'        => auth()->id(),
            'cancelled_at'        => now(),
        ]);

        return back()->with('success', 'Package cancelled.');
    }

    public function complete(Request $request, IpdPatientPackage $ipdPatientPackage)
    {
        $this->gate('ipd_packages_apply');

        if (! in_array($ipdPatientPackage->status, [
            IpdPatientPackage::STATUS_CONFIRMED,
            IpdPatientPackage::STATUS_PARTIALLY_USED,
        ], true)) {
            return back()->with('error', 'Only confirmed / in-use packages can be marked complete.');
        }

        $ipdPatientPackage->update(['status' => IpdPatientPackage::STATUS_COMPLETED]);

        return back()->with('success', 'Package marked complete.');
    }

    public function close(Request $request, IpdPatientPackage $ipdPatientPackage)
    {
        $this->gate('ipd_packages_apply');

        if ($ipdPatientPackage->status !== IpdPatientPackage::STATUS_COMPLETED) {
            return back()->with('error', 'Only completed packages can be closed (billing finalized).');
        }

        $ipdPatientPackage->update(['status' => IpdPatientPackage::STATUS_CLOSED]);

        return back()->with('success', 'Package closed.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && method_exists(auth()->user(), 'can') && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
