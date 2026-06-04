<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuEquipment;
use App\Models\Icu\IcuEquipmentChangeLog;
use App\Models\Icu\IcuEquipmentUsageLog;
use App\Models\PatientCharge;
use App\Services\Icu\PackageCoverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IcuEquipmentUsageController extends Controller
{
    /**
     * Show admission's equipment panel: active usage + history + change log.
     */
    public function index($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient',
            'bed.bedType',
            'equipmentUsageLogs.equipment',
            'equipmentChangeLogs.oldEquipment',
            'equipmentChangeLogs.newEquipment',
        ])->findOrFail($admissionId);

        // Bed-attached equipment is exclusive to its default bed; floating
        // equipment (no default bed) is shared within the admission's unit.
        $availableEquipment = IcuEquipment::available()
            ->where(function ($q) use ($admission) {
                $q->where(function ($w) use ($admission) {
                    $w->whereNotNull('default_bed_id')
                      ->where('default_bed_id', $admission->bed_id);
                })->orWhere(function ($w) use ($admission) {
                    $w->whereNull('default_bed_id')
                      ->where('icu_type', $admission->icu_type);
                });
            })
            ->orderBy('equipment_type')
            ->get();

        return view('icu.usage.index', compact('admission', 'availableEquipment'));
    }

    /**
     * Assign a piece of equipment to an active ICU admission. Opens a usage log.
     */
    public function assign(Request $request, $admissionId)
    {
        $request->validate([
            'equipment_id' => ['required', 'integer', Rule::exists('icu_equipment', 'id')],
            'start_time'   => ['nullable', 'date'],
            'remarks'      => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);
            $this->guardAdmissionActive($admission);

            $equipment = IcuEquipment::lockForUpdate()->findOrFail($request->equipment_id);

            if ($equipment->status !== 'Available') {
                throw new \RuntimeException("Equipment {$equipment->equipment_code} is not available (status: {$equipment->status}).");
            }

            $startTime = $request->start_time ?: now();

            $usage = IcuEquipmentUsageLog::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'equipment_id'     => $equipment->id,
                'equipment_type'   => $equipment->equipment_type,
                'start_time'       => $startTime,
                'billing_unit'     => $equipment->charge_type,
                'charge_rate'      => $equipment->charge_rate,
                'total_amount'     => 0,
                'status'           => 'InUse',
                'assigned_by'      => auth()->id(),
                'remarks'          => $request->remarks,
            ]);

            $equipment->update(['status' => 'InUse']);

            DB::commit();

            return back()->with('success', "{$equipment->equipment_name} assigned (usage #{$usage->id}).");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU equipment assign failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Assign failed: ' . $e->getMessage());
        }
    }

    /**
     * Stop using a specific piece of equipment. Closes the usage log,
     * computes duration and total_amount, posts a PatientCharge, and
     * frees the equipment.
     */
    public function remove(Request $request, $admissionId, $usageId)
    {
        $request->validate([
            'end_time'      => ['nullable', 'date'],
            'remove_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            $usage = IcuEquipmentUsageLog::lockForUpdate()->findOrFail($usageId);
            if ($usage->icu_admission_id != $admission->id) {
                throw new \RuntimeException('Usage log does not belong to this admission.');
            }
            if ($usage->status !== 'InUse') {
                throw new \RuntimeException('Usage is already closed.');
            }

            $endTime = $request->end_time ? new \DateTimeImmutable($request->end_time) : now();
            $this->closeUsageAndBill(
                $usage,
                $endTime,
                $admission,
                auth()->id(),
                $request->remove_reason
            );

            DB::commit();
            return back()->with('success', "Equipment usage closed and charge posted.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU equipment remove failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Remove failed: ' . $e->getMessage());
        }
    }

    /**
     * Swap one piece of equipment for another (e.g. malfunction).
     * Closes old usage (with billing), opens new usage, writes a change log.
     */
    public function change(Request $request, $admissionId, $usageId)
    {
        $request->validate([
            'new_equipment_id' => ['required', 'integer', Rule::exists('icu_equipment', 'id')],
            'change_reason'    => ['required', 'string', 'max:255'],
            'change_time'      => ['nullable', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            $oldUsage = IcuEquipmentUsageLog::lockForUpdate()->findOrFail($usageId);
            if ($oldUsage->icu_admission_id != $admission->id) {
                throw new \RuntimeException('Usage log does not belong to this admission.');
            }
            if ($oldUsage->status !== 'InUse') {
                throw new \RuntimeException('Old usage is already closed.');
            }

            $newEquipment = IcuEquipment::lockForUpdate()->findOrFail($request->new_equipment_id);
            if ($newEquipment->status !== 'Available') {
                throw new \RuntimeException("New equipment is not available (status: {$newEquipment->status}).");
            }

            $changeAt = $request->change_time ? new \DateTimeImmutable($request->change_time) : now();

            // 1) Close old usage and bill
            $this->closeUsageAndBill(
                $oldUsage,
                $changeAt,
                $admission,
                auth()->id(),
                'Changed: ' . $request->change_reason
            );

            // 2) Old equipment goes to maintenance per BRD §6.3
            IcuEquipment::where('id', $oldUsage->equipment_id)
                ->update(['status' => 'Maintenance']);

            // 3) Open new usage
            $newUsage = IcuEquipmentUsageLog::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'equipment_id'     => $newEquipment->id,
                'equipment_type'   => $newEquipment->equipment_type,
                'start_time'       => $changeAt,
                'billing_unit'     => $newEquipment->charge_type,
                'charge_rate'      => $newEquipment->charge_rate,
                'total_amount'     => 0,
                'status'           => 'InUse',
                'assigned_by'      => auth()->id(),
                'remarks'          => 'Replaced ' . $oldUsage->equipment_id . ': ' . $request->change_reason,
            ]);

            $newEquipment->update(['status' => 'InUse']);

            // 4) Audit
            IcuEquipmentChangeLog::create([
                'icu_admission_id' => $admission->id,
                'old_equipment_id' => $oldUsage->equipment_id,
                'new_equipment_id' => $newEquipment->id,
                'old_usage_log_id' => $oldUsage->id,
                'new_usage_log_id' => $newUsage->id,
                'change_reason'    => $request->change_reason,
                'changed_by'       => auth()->id(),
                'changed_at'       => $changeAt,
            ]);

            DB::commit();
            return back()->with('success', 'Equipment changed and audit log written.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU equipment change failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Change failed: ' . $e->getMessage());
        }
    }

    /**
     * Close every still-open usage log for this admission and post charges.
     * Called on ICU transfer/discharge.
     *
     * Public so the Ipd transfer flow and the future ICU discharge flow can both
     * reuse it without duplicating logic.
     */
    public function closeAllForAdmission(int $admissionId,  ? \DateTimeInterface $when = null, ?int $userId = null, string $reason = 'Auto-close on transfer/discharge') : void
    {
        $admission = IcuAdmission::findOrFail($admissionId);
        $endTime   = $when ?: now();

        $openLogs = IcuEquipmentUsageLog::where('icu_admission_id', $admissionId)
            ->where('status', 'InUse')
            ->lockForUpdate()
            ->get();

        foreach ($openLogs as $usage) {
            $this->closeUsageAndBill($usage, $endTime, $admission, $userId, $reason);
        }
    }

    // ------------------- Helpers -------------------

    protected function guardAdmissionActive(IcuAdmission $admission): void
    {
        if (! in_array($admission->status, ['Approved', 'Admitted'])) {
            throw new \RuntimeException("ICU admission is not active (status: {$admission->status}).");
        }
    }

    /**
     * Close a usage log and post its PatientCharge (idempotent: if charge already
     * posted, do nothing). Honors active package coverage — when covered, the
     * usage log is closed but no PatientCharge is posted.
     */
    protected function closeUsageAndBill(
        IcuEquipmentUsageLog $usage,
        \DateTimeInterface $endTime,
        IcuAdmission $admission,
        ?int $userId,
        string $reason
    ): void {
        if ($usage->status === 'Closed' && ($usage->patient_charge_id || $usage->covered_by_package)) {
            return;
        }

        $usage->end_time      = $endTime;
        $usage->status        = 'Closed';
        $usage->removed_by    = $userId;
        $usage->remove_reason = $reason;

        $calc                    = $usage->computeAmount($endTime);
        $usage->duration_minutes = $calc['minutes'];

        // Resolve package coverage at close-time
        $coverage = app(PackageCoverageService::class)->resolve(
            $admission,
            'Equipment',
            $usage->equipment_type,
            $endTime
        );

        if ($coverage['covered']) {
            $usage->covered_by_package    = true;
            $usage->package_enrollment_id = $coverage['enrollment']?->id;
            $usage->total_amount          = 0;
            $usage->save();
            return;
        }

        $usage->total_amount = $calc['amount'];
        $usage->save();

        // Post PatientCharge once
        if (! $usage->patient_charge_id) {
            $charge = PatientCharge::create([
                'case_id'       => $admission->case_id,
                'charge_module' => 'icu',
                'doctor_id'     => $admission->referring_doctor_id,
                'department_id' => null,
                'ipd_id'        => $admission->ipdIdForCharge(),
                'charge_item'   => sprintf(
                    '%s (%s) — %s × %d %s',
                    optional($usage->equipment)->equipment_name ?? 'Equipment',
                    optional($usage->equipment)->equipment_code ?? '-',
                    number_format((float) $usage->charge_rate, 2),
                    $calc['units'],
                    strtolower($usage->billing_unit)
                ),
                'unit_price'    => $usage->charge_rate,
                'quantity'      => $calc['units'],
                'amount'        => $usage->total_amount,
                'net_amount'    => $usage->total_amount,
                'date'          => $endTime,
                'remarks'       => 'ICU equipment usage #' . $usage->id . ' — ' . $reason,
                'created_by'    => $userId,
            ]);

            $usage->patient_charge_id = $charge->id;
            $usage->save();
        }

        // Free the equipment back to Available unless it was sent to Maintenance
        IcuEquipment::where('id', $usage->equipment_id)
            ->where('status', 'InUse')
            ->update(['status' => 'Available']);
    }
}
