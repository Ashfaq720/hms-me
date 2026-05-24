<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuTransfer;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\PatientCharge;
use App\Services\Icu\AdmissionCloseoutService;
use App\Services\Icu\PackageCoverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IcuTransferController extends Controller
{
    public function __construct(private AdmissionCloseoutService $closeout)
    {}

    public function create($admissionId)
    {
        $admission = IcuAdmission::with('bed.bedType', 'patient')->findOrFail($admissionId);

        // Buckets: ICU/CCU/NICU/PICU beds vs regular ward beds
        $allBeds  = Bed::with('bedType')->where('is_reserved', false)->get();
        $icuBeds  = $allBeds->filter(fn($b) => optional($b->bedType)->is_icu)->values();
        $wardBeds = $allBeds->filter(fn($b) => ! optional($b->bedType)->is_icu)->values();

        $blockers = $this->closeout->listBlockers($admission);

        return view('icu.transfer.create', compact('admission', 'icuBeds', 'wardBeds', 'blockers'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'transfer_type'   => ['required', Rule::in(['IcuToIcu', 'IcuToCcu', 'IcuToWard', 'IcuToHigherCare', 'IcuToOT'])],
            'to_bed_id'       => ['nullable', 'integer', Rule::exists('beds', 'id')],
            'transfer_reason' => ['required', 'string', 'max:1000'],
            'transfer_time'   => ['required', 'date'],
            'force'           => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::lockForUpdate()->findOrFail($admissionId);

            if (! in_array($admission->status, ['Approved', 'Admitted'])) {
                throw new \RuntimeException("ICU admission is not active (status: {$admission->status}).");
            }

            // Hard-block per BRD §10.4 unless force-override
            $blockers = $this->closeout->listBlockers($admission);
            if ($blockers && ! $request->boolean('force')) {
                throw new \RuntimeException(implode(' ', $blockers));
            }

            $type     = $request->transfer_type;
            $when     = new \DateTimeImmutable($request->transfer_time);
            $newBedId = $request->to_bed_id;

            // Validate destination bed for moves that need one
            if (in_array($type, ['IcuToIcu', 'IcuToCcu', 'IcuToWard'])) {
                if (! $newBedId) {
                    throw new \RuntimeException('Destination bed is required for this transfer type.');
                }
                $newBed = Bed::with('bedType')->findOrFail($newBedId);
                if ($newBed->is_reserved) {
                    throw new \RuntimeException('Selected destination bed is already occupied.');
                }
                if ($type === 'IcuToCcu' && optional($newBed->bedType)->icu_type !== 'CCU') {
                    throw new \RuntimeException('Destination must be a CCU bed.');
                }
                if ($type === 'IcuToWard' && optional($newBed->bedType)->is_icu) {
                    throw new \RuntimeException('Destination must be a regular ward bed (not ICU).');
                }
                if ($type === 'IcuToIcu' && ! optional($newBed->bedType)->is_icu) {
                    throw new \RuntimeException('Destination must be an ICU bed.');
                }
            }

            // 1) Post the prior ICU/CCU bed-stay charge for the bed being vacated
            //    (covered by package? skipped — package billing posts its own lines)
            $this->postBedStayCharge($admission, $when);

            // 2) Run closeout (equipment charges, alert close, free old bed)
            $reason = "Transferred ({$type}): " . $request->transfer_reason;
            $this->closeout->closeout($admission, $when, auth()->id(), $reason);

            // 3) Transfer history row
            $toUnit = match ($type) {
                'IcuToCcu'        => 'CCU',
                'IcuToWard'       => 'Ipd',
                'IcuToIcu'        => 'ICU',
                'IcuToHigherCare' => 'External',
                'IcuToOT'         => 'OT',
            };

            // 3a) Close the open ICU IpdPatientBed allocation (mirror created on admission)
            if ($admission->case_id) {
                $currentAlloc = IpdPatientBed::where('case_id', $admission->case_id)
                    ->where('allocation_type', 'icu')
                    ->whereNull('to')
                    ->orderByDesc('id')
                    ->first();
                if ($currentAlloc) {
                    $currentAlloc->update([
                        'to'     => $request->transfer_time,
                        'status' => 'TRANSFERRED',
                    ]);
                }
            }

            // 4) Mirror an Ipd allocation when transferring back to a ward bed and source was Ipd
            $toIpdId = null;
            if ($type === 'IcuToWard' && $admission->isFromIpd()) {
                $ipd = IpdPatient::find($admission->source_id);
                if ($ipd) {
                    IpdPatientBed::create([
                        'case_id'         => $admission->case_id ?? $ipd->case_id,
                        'ipd_patient_id'  => $ipd->id,
                        'bed_id'          => $newBedId,
                        'allocation_type' => 'bed',
                        'from'            => $request->transfer_time,
                        'status'          => 'TRANSFERRED',
                        'remarks'         => 'From ICU ' . $admission->icu_case_id,
                    ]);
                    Bed::where('id', $newBedId)->update(['is_reserved' => true]);
                    $toIpdId = $ipd->id;
                }
            } elseif (in_array($type, ['IcuToIcu', 'IcuToCcu']) && $newBedId) {
                Bed::where('id', $newBedId)->update(['is_reserved' => true]);
            }

            IcuTransfer::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'transfer_type'    => $type,
                'from_unit'        => $admission->icu_type,
                'to_unit'          => $toUnit,
                'from_bed_id'      => $admission->bed_id,
                'to_bed_id'        => $newBedId,
                'to_ipd_id'        => $toIpdId,
                'transfer_reason'  => $request->transfer_reason,
                'transfer_time'    => $when,
                'requested_by'     => auth()->id(),
                'status'           => 'Completed',
            ]);

            // 5) Update admission terminal state
            // For ICU→ICU/CCU we keep the admission open and just move the bed; close out
            // pending state but keep status 'Admitted' and update icu_type if needed.
            if (in_array($type, ['IcuToIcu', 'IcuToCcu'])) {
                $admission->update([
                    'bed_id'   => $newBedId,
                    'icu_type' => $type === 'IcuToCcu' ? 'CCU' : $admission->icu_type,
                ]);
            } else {
                $admission->update([
                    'status'        => 'Transferred',
                    'transfer_time' => $when,
                    'closed_by'     => auth()->id(),
                    'bed_id'        => null,
                ]);
            }

            DB::commit();
            return redirect()->route('icu.admissions.show', $admission->id)
                ->with('success', "Transfer ({$type}) completed.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU transfer failed', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Post a PatientCharge for the ICU/CCU bed-stay the patient is leaving.
     * Stay starts at the most-recent transfer-into this bed, or admission_time
     * for the first bed. Skipped when the package covers category 'Bed' (the
     * package's own day/hour/fixed lines stand in for it).
     */
    protected function postBedStayCharge(IcuAdmission $admission, \DateTimeInterface $when): void
    {

        if (! $admission->bed_id) {
            return;
        }

        $bed  = Bed::find($admission->bed_id);
        $rate = (float) ($bed?->rent ?? 0);

        $lastInto = IcuTransfer::where('icu_admission_id', $admission->id)
            ->where('to_bed_id', $admission->bed_id)
            ->orderByDesc('transfer_time')
            ->first();
        $start = $lastInto?->transfer_time ?: $admission->admission_time;
        if (! $start) {
            return;
        }

        $startC = \Carbon\Carbon::parse($start);
        $endC   = \Carbon\Carbon::parse($when);

        $days = $startC->copy()->startOfDay()->diffInDays($endC->copy()->startOfDay());
        if ($endC->format('H:i:s') > '12:00:00') {
            $days++;
        }
        $days = max($days, 1);

        $key           = "icu-bed:{$admission->id}:{$admission->bed_id}:" . $startC->toIso8601String();
        $alreadyPosted = PatientCharge::where('charge_module', 'icu')
            ->where('case_id', $admission->case_id)
            ->where('remarks', 'like', "%{$key}%")
            ->exists();
        if ($alreadyPosted) {
            return;
        }

        $coverage = app(PackageCoverageService::class)->resolve($admission, 'Bed', null, $endC);
        if (! empty($coverage['covered'])) {
            return;
        }

        $unit    = $admission->icu_type === 'CCU' ? 'CCU' : 'ICU';
        $bedName = $bed->name ?: ('Bed #' . $bed->id);

        PatientCharge::create([
            'case_id'       => $admission->case_id,
            'charge_module' => 'icu',
            'doctor_id'     => $admission->referring_doctor_id,
            'ipd_id'        => $admission->ipdIdForCharge(),
            'charge_item'   => "{$unit} bed: {$bedName}",
            'unit_price' => $rate,
            'quantity' => $days,
            'amount' => $rate * $days,
            'net_amount' => $rate * $days,
            'date' => $endC,
            'remarks' => $key,
            'created_by' => auth()->id(),
        ]);
    }
}
