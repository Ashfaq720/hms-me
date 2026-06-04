<?php

namespace App\Services;

use App\Models\IpdPatientPackage;
use App\Models\NicuAdmission;
use App\Models\PatientCharge;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;

/**
 * Posts service-package charges to the patient bill (patient_charges).
 *
 * Triggered when an IpdPatientPackage transitions into Confirmed (either
 * directly on attach when no approval is required, or later when the
 * approver clicks Approve). Idempotent — guards against double-posting
 * if the same package is approved-then-cancelled-then-approved-again.
 */
class PackageBillingService
{
    /**
     * Post one bundled charge for the package, stamped with
     * service_package_id so the bill view can group / report on it.
     *
     * Returns the created PatientCharge, or null if:
     *   - the package isn't billable (Cancelled / Refunded / Closed)
     *   - the package was already billed
     *   - no authenticated user (defence-in-depth — money rows must be authored)
     */
    public function postCharge(IpdPatientPackage $att): ?PatientCharge
    {
        if (! auth()->check()) return null;

        // Only billable statuses post charges. Pending Approval shouldn't
        // post yet — wait until approval flips it to Confirmed.
        if (! in_array($att->status, [
            IpdPatientPackage::STATUS_CONFIRMED,
            IpdPatientPackage::STATUS_PARTIALLY_USED,
            IpdPatientPackage::STATUS_COMPLETED,
        ], true)) {
            return null;
        }

        // Idempotency — never double-bill the same attachment.
        $existing = PatientCharge::where('service_package_id', $att->service_package_id)
            ->where('ipd_id', $att->ipd_admission_id)
            ->whereNull('deleted_at')
            ->first();
        if ($existing) return $existing;

        $att->loadMissing(['package', 'ipdAdmission']);
        $package = $att->package;
        $ipd     = $att->ipdAdmission;
        if (! $package || ! $ipd) return null;

        return DB::transaction(function () use ($att, $package, $ipd) {
            return PatientCharge::create([
                'case_id'            => $ipd->case_id,
                'service_package_id' => $package->id,
                'charge_module'      => 'ipd',
                'doctor_id'          => $ipd->doctor_id,
                'department_id'      => $ipd->department_id,
                'ipd_id'             => $ipd->id,
                'charge_item'        => $package->code . ' — ' . $package->name,
                'unit_price'         => $att->effectivePrice(),
                'quantity'           => 1,
                'amount'             => $att->effectivePrice(),
                'vat'                => 0,
                'tax'                => 0,
                'net_amount'         => $att->effectivePrice(),
                'date'               => now(),
                'notes'              => 'Package auto-billed on '
                                       . ($att->status === IpdPatientPackage::STATUS_CONFIRMED
                                          ? 'confirmation' : 'approval'),
                'status'             => 'posted',
                'is_paid'            => false,
                'is_bill_generated'  => false,
                'created_by'         => auth()->id(),
            ]);
        });
    }

    /**
     * Post a bundled package charge for a NICU admission. Stamped
     * against the baby's case_id (not the mother's IPD admission) and
     * scoped by service_package_id so reports stay consistent across
     * IPD / OT / NICU sources.
     */
    public function postChargeForNicu(NicuAdmission $adm): ?PatientCharge
    {
        if (! auth()->check()) return null;
        if (! $adm->service_package_id || ! $adm->case_id) return null;
        if (! in_array($adm->status, [
            NicuAdmission::STATUS_ADMITTED,
            NicuAdmission::STATUS_IN_PROGRESS,
            NicuAdmission::STATUS_DISCHARGED,
        ], true)) {
            return null;
        }

        // Idempotency — one bundled charge per (case, package).
        $existing = PatientCharge::where('service_package_id', $adm->service_package_id)
            ->where('case_id', $adm->case_id)
            ->where('charge_module', 'nicu')
            ->whereNull('deleted_at')
            ->first();
        if ($existing) return $existing;

        $package = ServicePackage::find($adm->service_package_id);
        if (! $package) return null;

        $price = (float) ($package->priceForBedType($adm->bed_type_id) ?? $package->base_price);

        return DB::transaction(function () use ($adm, $package, $price) {
            return PatientCharge::create([
                'case_id'            => $adm->case_id,
                'service_package_id' => $package->id,
                'charge_module'      => 'nicu',
                'department_id'      => $package->department_id,
                'charge_item'        => $package->code . ' — ' . $package->name,
                'unit_price'         => $price,
                'quantity'           => 1,
                'amount'             => $price,
                'vat'                => 0,
                'tax'                => 0,
                'net_amount'         => $price,
                'date'               => now(),
                'notes'              => 'NICU package auto-billed on admission '.$adm->admission_no,
                'status'             => 'posted',
                'is_paid'            => false,
                'is_bill_generated'  => false,
                'created_by'         => auth()->id(),
            ]);
        });
    }

    /**
     * Bulk-post charges for every billable, not-yet-billed package on
     * an IPD admission. Called after sync to handle anything that
     * landed in Confirmed status during create/update.
     */
    public function postPendingForAdmission(int $ipdAdmissionId): int
    {
        $packages = IpdPatientPackage::where('ipd_admission_id', $ipdAdmissionId)
            ->whereIn('status', [
                IpdPatientPackage::STATUS_CONFIRMED,
                IpdPatientPackage::STATUS_PARTIALLY_USED,
                IpdPatientPackage::STATUS_COMPLETED,
            ])
            ->get();

        $posted = 0;
        foreach ($packages as $att) {
            if ($this->postCharge($att)) {
                $posted++;
            }
        }
        return $posted;
    }
}
