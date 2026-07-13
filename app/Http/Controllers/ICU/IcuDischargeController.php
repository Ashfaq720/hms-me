<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuDischargeSummary;
use App\Services\Icu\AdmissionCloseoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IcuDischargeController extends Controller
{
    public function __construct(private AdmissionCloseoutService $closeout) {}

    public function create($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient', 'bed.bedType',
            'doctorOrders', 'equipmentUsageLogs.equipment', 'antibioticUsage', 'emergencyEvents',
            'dischargeSummary',
        ])->findOrFail($admissionId);

        $blockers = $this->closeout->listBlockers($admission);

        // Pre-fill defaults from existing data
        $defaults = [
            'admission_diagnosis'    => $admission->admission_diagnosis,
            'final_diagnosis'        => optional($admission->dischargeSummary)->final_diagnosis ?? $admission->admission_diagnosis,
            'icu_course_summary'     => optional($admission->dischargeSummary)->icu_course_summary,
            'procedures_summary'     => optional($admission->dischargeSummary)->procedures_summary
                ?? $admission->doctorOrders->where('order_type', 'Procedure')->where('status', 'Completed')
                    ->pluck('order_title')->implode("\n"),
            'ventilator_summary'     => optional($admission->dischargeSummary)->ventilator_summary
                ?? $admission->equipmentUsageLogs->filter(fn($u) => $u->equipment_type === 'Ventilator')
                    ->map(fn($u) => sprintf(
                        '%s: %s → %s (%d min)',
                        optional($u->equipment)->equipment_code,
                        $u->start_time?->format('Y-m-d H:i'),
                        $u->end_time?->format('Y-m-d H:i') ?? '(open)',
                        (int) ($u->duration_minutes ?? 0)
                    ))->implode("\n"),
            'medication_summary'     => optional($admission->dischargeSummary)->medication_summary
                ?? $admission->antibioticUsage->map(fn($a) => "{$a->antibiotic_name} {$a->dose} {$a->frequency} ({$a->start_date?->format('Y-m-d')} → " . ($a->stop_date?->format('Y-m-d') ?? 'ongoing') . ")")->implode("\n"),
            'investigation_summary'  => optional($admission->dischargeSummary)->investigation_summary,
            'condition_at_discharge' => optional($admission->dischargeSummary)->condition_at_discharge,
            'followup_advice'        => optional($admission->dischargeSummary)->followup_advice,
        ];

        return view('icu.discharge.create', compact('admission', 'blockers', 'defaults'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'outcome' => ['required', Rule::in(['Recovered', 'Referred', 'LAMA'])],
            'discharge_time'         => ['required', 'date'],

            'final_diagnosis'        => ['required', 'string', 'max:2000'],
            'icu_course_summary'     => ['nullable', 'string', 'max:5000'],
            'procedures_summary'     => ['nullable', 'string', 'max:5000'],
            'ventilator_summary'     => ['nullable', 'string', 'max:5000'],
            'investigation_summary'  => ['nullable', 'string', 'max:5000'],
            'medication_summary'     => ['nullable', 'string', 'max:5000'],
            'condition_at_discharge' => ['required', 'string', 'max:255'],
            'followup_advice'        => ['nullable', 'string', 'max:5000'],

            'force' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::lockForUpdate()->findOrFail($admissionId);

            if (! in_array($admission->status, ['Approved', 'Admitted'])) {
                throw new \RuntimeException("ICU admission is not active (status: {$admission->status}).");
            }

            $blockers = $this->closeout->listBlockers($admission);
            if ($blockers && ! $request->boolean('force')) {
                throw new \RuntimeException(implode(' ', $blockers));
            }

            $when = new \DateTimeImmutable($request->discharge_time);

            // 1) Persist discharge summary (one per admission — updateOrCreate)
            IcuDischargeSummary::updateOrCreate(
                ['icu_admission_id' => $admission->id],
                [
                    'icu_case_id'            => $admission->icu_case_id,
                    'patient_id'             => $admission->patient_id,
                    'admission_diagnosis'    => $admission->admission_diagnosis,
                    'final_diagnosis'        => $request->final_diagnosis,
                    'icu_course_summary'     => $request->icu_course_summary,
                    'procedures_summary'     => $request->procedures_summary,
                    'ventilator_summary'     => $request->ventilator_summary,
                    'investigation_summary'  => $request->investigation_summary,
                    'medication_summary'     => $request->medication_summary,
                    'condition_at_discharge' => $request->condition_at_discharge,
                    'followup_advice'        => $request->followup_advice,
                    'prepared_by'            => auth()->id(),
                ]
            );

            // 2) Closeout cross-cutting state
            $this->closeout->closeout($admission, $when, auth()->id(), 'Discharge: ' . $request->outcome);

            // 3) Mark admission terminal
            $admission->update([
                'status'          => 'Discharged',
                'discharge_time'  => $when,
                'outcome'         => $request->outcome,
                'outcome_remarks' => $request->condition_at_discharge,
                'closed_by'       => auth()->id(),
                'bed_id'          => null,
            ]);

            DB::commit();
            return redirect()->route('icu.admissions.discharge.summary', $admission->id)
                ->with('success', 'ICU discharge completed.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU discharge failed', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Discharge failed: ' . $e->getMessage());
        }
    }

    public function summary($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient', 'bed.bedType', 'dischargeSummary', 'mortalityAudit', 'transfers',
        ])->findOrFail($admissionId);

        return view('icu.discharge.summary', compact('admission'));
    }
}
