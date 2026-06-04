<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuBillingModeAuditLog;
use App\Models\Icu\IcuPackage;
use App\Models\Icu\IcuPatientPackageEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IcuBillingModeController extends Controller
{
    public function index($admissionId)
    {
        $admission   = IcuAdmission::findOrFail($admissionId);
        $enrollments = IcuPatientPackageEnrollment::with('package')
            ->where('icu_admission_id', $admission->id)
            ->orderByDesc('id')
            ->get();
        $audits = IcuBillingModeAuditLog::where('icu_admission_id', $admission->id)
            ->orderByDesc('id')->get();

        $packages = IcuPackage::active()
            ->where('icu_type', $admission->icu_type)
            ->get();

        return view('icu.billing.mode', compact('admission', 'enrollments', 'audits', 'packages'));
    }

    /**
     * Apply a billing mode (Itemized / Package / Mixed). Closes any prior active
     * enrollment, opens a new one, writes the audit row.
     */
    public function apply(Request $request, $admissionId)
    {
        $request->validate([
            'billing_mode'       => ['required', Rule::in(['Itemized', 'Package', 'Mixed'])],
            'package_id'         => ['nullable', 'integer', Rule::exists('icu_packages', 'id')],
            'start_time'         => ['required', 'date'],
            'approval_reference' => ['nullable', 'string', 'max:100'],
            'reason'             => ['required', 'string', 'max:500'],
            'remarks'            => ['nullable', 'string', 'max:1000'],
        ]);

        if (in_array($request->billing_mode, ['Package', 'Mixed']) && ! $request->package_id) {
            return back()->with('error', 'Package selection is required for Package or Mixed mode.');
        }

        if (in_array($request->billing_mode, ['Package', 'Mixed']) && $request->package_id) {
            $admission = IcuAdmission::findOrFail($admissionId);
            $package   = IcuPackage::findOrFail($request->package_id);
            if ($package->icu_type !== $admission->icu_type) {
                return back()->with('error', "Selected package is for {$package->icu_type}, but this admission is {$admission->icu_type}.");
            }
        }

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            $current = IcuPatientPackageEnrollment::where('icu_admission_id', $admission->id)
                ->where('status', 'Active')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $oldMode    = $current?->billing_mode;
            $oldPackage = $current?->package_id;
            $startTime  = new \DateTimeImmutable($request->start_time);

            // Backdating beyond current's start needs an explicit approval reference (BRD §14)
            if ($current && $startTime < $current->start_time && ! $request->approval_reference) {
                throw new \RuntimeException('Backdated start requires an approval reference.');
            }

            // Close prior enrollment
            if ($current) {
                $current->update([
                    'status'   => 'Ended',
                    'end_time' => $startTime,
                ]);
            }

            // Open new enrollment
            $en = IcuPatientPackageEnrollment::create([
                'icu_admission_id'   => $admission->id,
                'icu_case_id'        => $admission->icu_case_id,
                'patient_id'         => $admission->patient_id,
                'package_id'         => $request->billing_mode === 'Itemized' ? null : $request->package_id,
                'billing_mode'       => $request->billing_mode,
                'start_time'         => $startTime,
                'status'             => 'Active',
                'applied_by'         => auth()->id(),
                'approval_reference' => $request->approval_reference,
                'remarks'            => $request->remarks,
            ]);

            // Audit
            IcuBillingModeAuditLog::create([
                'icu_admission_id' => $admission->id,
                'old_billing_mode' => $oldMode,
                'new_billing_mode' => $request->billing_mode,
                'old_package_id'   => $oldPackage,
                'new_package_id'   => $en->package_id,
                'changed_by'       => auth()->id(),
                'changed_at'       => now(),
                'reason'           => $request->reason,
                'created_at'       => now(),
            ]);

            DB::commit();
            return redirect()
                ->route('icu.admissions.billing.preview', $admission->id)
                ->with('success', "Billing mode set to {$request->billing_mode}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Apply failed: ' . $e->getMessage());
        }
    }

    /**
     * End the current enrollment without opening a new one (revert to Itemized).
     */
    public function end(Request $request, $admissionId)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $admissionId) {
            $en = IcuPatientPackageEnrollment::where('icu_admission_id', $admissionId)
                ->where('status', 'Active')
                ->lockForUpdate()
                ->latest('id')
                ->firstOrFail();

            $en->update(['status' => 'Ended', 'end_time' => now()]);

            IcuBillingModeAuditLog::create([
                'icu_admission_id' => $admissionId,
                'old_billing_mode' => $en->billing_mode,
                'new_billing_mode' => 'Itemized',
                'old_package_id'   => $en->package_id,
                'new_package_id'   => null,
                'changed_by'       => auth()->id(),
                'changed_at'       => now(),
                'reason'           => $request->reason,
                'created_at'       => now(),
            ]);
        });

        return back()->with('success', 'Package billing ended — back to Itemized.');
    }
}
