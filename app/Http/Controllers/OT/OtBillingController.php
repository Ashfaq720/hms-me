<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtBillingController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_billing_access');
        $schedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'surgeryRequest.surgeryType', 'consumableUsages'])
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
                OtSurgerySchedule::STATUS_IN_RECOVERY,
                OtSurgerySchedule::STATUS_TRANSFERRED_BACK,
                OtSurgerySchedule::STATUS_CLOSED,
            ])
            ->orderBy('actual_end', 'desc')
            ->paginate(20);

        return view('ot.billing.index', compact('schedules'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'surgeryRequest.surgeryType', 'consumableUsages',
        ])->findOrFail($scheduleId);

        $estimatedCharges = $this->estimateCharges($schedule);

        return view('ot.billing.show', compact('schedule', 'estimatedCharges'));
    }

    public function print($scheduleId)
    {
        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'surgeryRequest.surgeryType',
            'surgeryRequest.primarySurgeon', 'room', 'consumableUsages',
        ])->findOrFail($scheduleId);

        $estimatedCharges = $this->estimateCharges($schedule);

        return view('ot.billing.print', compact('schedule', 'estimatedCharges'));
    }

    public function pdf($scheduleId)
    {
        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'surgeryRequest.surgeryType',
            'surgeryRequest.primarySurgeon', 'room', 'consumableUsages',
        ])->findOrFail($scheduleId);

        $estimatedCharges = $this->estimateCharges($schedule);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ot.billing.print', [
                'schedule' => $schedule,
                'estimatedCharges' => $estimatedCharges,
                'isPdf' => true,
            ])->setPaper('a4');

            return $pdf->download('OT-Bill-' . $schedule->schedule_no . '.pdf');
        }

        // Fallback: serve print-friendly HTML with auto-print, browser saves as PDF.
        return response(view('ot.billing.print', [
            'schedule' => $schedule,
            'estimatedCharges' => $estimatedCharges,
            'autoPrint' => true,
        ]));
    }

    /**
     * Build the estimated-charges breakdown for a schedule.
     *
     * Callers MUST pass a schedule with `consumableUsages` (and ideally
     * `surgeryRequest.surgeryType`) eager-loaded. The defensive
     * loadMissing() call here prevents an accidental N+1 if a future
     * caller forgets — but the right fix is always to load up front.
     */
    protected function estimateCharges(OtSurgerySchedule $schedule): array
    {
        $schedule->loadMissing(['consumableUsages', 'surgeryRequest.surgeryType']);
        $surgeryType = $schedule->surgeryRequest->surgeryType;

        return [
            'ot_room' => $surgeryType?->ot_room_charge ?? 0,
            'surgeon' => $surgeryType?->surgeon_charge ?? 0,
            'anesthesia' => $surgeryType?->anesthesia_charge ?? 0,
            'recovery' => $surgeryType?->recovery_charge ?? 0,
            'standard' => $surgeryType?->standard_charge ?? 0,
            'consumables_total' => $schedule->consumableUsages->sum('amount'),
            'emergency_surcharge' => $schedule->emergency_fast_track
                ? round(((float) ($surgeryType?->standard_charge ?? 0)) * 0.15, 2)
                : 0,
        ];
    }

    public function postCharges(Request $request, $scheduleId)
    {
        // Charges are money-trail rows — never write them anonymously.
        if (! auth()->check()) {
            return back()->with('error', 'You must be signed in to post charges.');
        }

        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'surgeryRequest.surgeryType', 'consumableUsages',
        ])->findOrFail($scheduleId);

        $surgeryRequest = $schedule->surgeryRequest;
        $surgeryType = $surgeryRequest->surgeryType;
        $context = $this->resolveEncounterContext($surgeryRequest);

        $posted = DB::transaction(function () use ($schedule, $surgeryType, $context) {
            $posted = [];

            $standardCharges = [
                'OT Room Charge' => $surgeryType?->ot_room_charge ?? 0,
                'Surgeon Fee' => $surgeryType?->surgeon_charge ?? 0,
                'Anesthesia Fee' => $surgeryType?->anesthesia_charge ?? 0,
                'Recovery Room Charge' => $surgeryType?->recovery_charge ?? 0,
            ];

            foreach ($standardCharges as $name => $amount) {
                if ($amount > 0) {
                    $posted[] = $this->createCharge($context, $schedule, $name, (float) $amount);
                }
            }

            foreach ($schedule->consumableUsages as $usage) {
                if (! $usage->is_billed && $usage->amount > 0) {
                    $charge = $this->createCharge(
                        $context, $schedule,
                        ucfirst($usage->type) . ': ' . $usage->item_name,
                        (float) $usage->rate,
                        (int) $usage->quantity
                    );
                    if ($charge) {
                        $usage->update([
                            'is_billed' => true,
                            'patient_charge_id' => $charge->id,
                        ]);
                        $posted[] = $charge;
                    }
                }
            }

            if ($schedule->emergency_fast_track && (float) ($surgeryType?->standard_charge ?? 0) > 0) {
                $posted[] = $this->createCharge(
                    $context, $schedule, 'Emergency Surcharge',
                    (float) $surgeryType->standard_charge * 0.15
                );
            }

            return array_filter($posted);
        });

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'charges_posted',
            null, null, null,
            ['posted_count' => count($posted)]
        );

        return back()->with('success', count($posted) . ' charges posted to patient account.');
    }

    /**
     * Resolve encounter linkage for PatientCharge (case_id, ipd_id, opd_id, er_register_id).
     */
    protected function resolveEncounterContext($surgeryRequest): array
    {
        // Prefer case_id stored on the request itself (set at creation time);
        // fall back to looking up via the IPD admission.
        $context = [
            'case_id' => $surgeryRequest->case_id,
            'charge_module' => strtoupper($surgeryRequest->encounter_type ?: 'IPD') === 'IPD' ? 'ipd'
                : strtolower($surgeryRequest->encounter_type),
            'ipd_id' => null,
            'opd_id' => null,
            'er_register_id' => null,
            'doctor_id' => $surgeryRequest->primary_surgeon_id ?? $surgeryRequest->requested_by_doctor_id,
            'department_id' => $surgeryRequest->department_id,
        ];

        switch (strtoupper($surgeryRequest->encounter_type ?: '')) {
            case 'IPD':
                $context['ipd_id'] = $surgeryRequest->ipd_admission_id ?? $surgeryRequest->encounter_id;
                if ($context['ipd_id'] && empty($context['case_id'])) {
                    $ipd = \App\Models\IpdPatient::find($context['ipd_id']);
                    if ($ipd) {
                        $context['case_id'] = $ipd->case_id;
                        $context['doctor_id'] = $context['doctor_id'] ?? $ipd->doctor_id;
                        $context['department_id'] = $context['department_id'] ?? $ipd->department_id;
                    }
                }
                break;
            case 'OPD':
                $context['opd_id'] = $surgeryRequest->encounter_id;
                break;
            case 'ER':
                $context['er_register_id'] = $surgeryRequest->encounter_id;
                break;
        }

        return $context;
    }

    protected function createCharge(array $context, $schedule, string $name, float $unitPrice, int $qty = 1)
    {
        if (! class_exists(PatientCharge::class)) {
            return null;
        }

        // Defence in depth — postCharges() already guards, but createCharge
        // is reachable from elsewhere in the future. Don't write a money row
        // without a known author.
        $userId = auth()->id();
        if (! $userId) {
            return null;
        }

        $amount = $unitPrice * $qty;

        return PatientCharge::create([
            'case_id' => $context['case_id'],
            'charge_module' => $context['charge_module'],
            'doctor_id' => $context['doctor_id'],
            'department_id' => $context['department_id'],
            'ipd_id' => $context['ipd_id'],
            'opd_id' => $context['opd_id'],
            'er_register_id' => $context['er_register_id'],
            'charge_item' => $name,
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'amount' => $amount,
            'vat' => 0,
            'tax' => 0,
            'net_amount' => $amount,
            'date' => now(),
            'notes' => "OT Schedule {$schedule->schedule_no}",
            'status' => 'posted',
            'is_paid' => false,
            'is_bill_generated' => false,
            'created_by' => $userId,
        ]);
    }
}
