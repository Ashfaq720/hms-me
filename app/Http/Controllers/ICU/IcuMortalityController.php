<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuMortalityAudit;
use App\Services\Icu\AdmissionCloseoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IcuMortalityController extends Controller
{
    public function __construct(private AdmissionCloseoutService $closeout) {}

    public function create($admissionId)
    {
        $admission = IcuAdmission::with(['patient', 'emergencyEvents', 'mortalityAudit'])
            ->findOrFail($admissionId);

        $codeBlueEvents = $admission->emergencyEvents;

        return view('icu.mortality.create', compact('admission', 'codeBlueEvents'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'death_time'           => ['required', 'date'],
            'cause_of_death'       => ['required', 'string', 'max:2000'],
            'code_blue_event_id'   => ['nullable', 'integer', 'exists:icu_emergency_events,id'],
            'resuscitation_details' => ['nullable', 'string', 'max:5000'],
            'death_declared_by'    => ['required', 'integer', 'exists:doctors,id'],
            'body_handover_to'     => ['nullable', 'string', 'max:200'],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::lockForUpdate()->findOrFail($admissionId);

            if (in_array($admission->status, ['Discharged', 'Expired', 'Cancelled'])) {
                throw new \RuntimeException("Admission already terminal (status: {$admission->status}).");
            }

            $when = new \DateTimeImmutable($request->death_time);

            IcuMortalityAudit::updateOrCreate(
                ['icu_admission_id' => $admission->id],
                [
                    'icu_case_id'           => $admission->icu_case_id,
                    'patient_id'            => $admission->patient_id,
                    'death_time'            => $when,
                    'cause_of_death'        => $request->cause_of_death,
                    'code_blue_event_id'    => $request->code_blue_event_id,
                    'resuscitation_details' => $request->resuscitation_details,
                    'death_declared_by'     => $request->death_declared_by,
                    'body_handover_to'      => $request->body_handover_to,
                    'audit_status'          => 'Pending',
                ]
            );

            // Forced closeout — death overrides any blockers
            $this->closeout->closeout($admission, $when, auth()->id(), 'Mortality');

            $admission->update([
                'status'          => 'Expired',
                'discharge_time'  => $when,
                'outcome'         => 'Expired',
                'outcome_remarks' => $request->cause_of_death,
                'closed_by'       => auth()->id(),
                'bed_id'          => null,
            ]);

            DB::commit();
            return redirect()->route('icu.admissions.mortality.show', $admission->id)
                ->with('success', 'Mortality recorded — audit created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU mortality store failed', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    public function show($admissionId)
    {
        $admission = IcuAdmission::with(['patient', 'mortalityAudit.codeBlueEvent'])
            ->findOrFail($admissionId);

        if (! $admission->mortalityAudit) {
            return redirect()->route('icu.admissions.show', $admission->id)
                ->with('error', 'No mortality audit recorded.');
        }

        return view('icu.mortality.show', compact('admission'));
    }

    /**
     * Audit committee review.
     */
    public function review(Request $request, $admissionId)
    {
        $request->validate([
            'preventability'       => ['required', Rule::in(['Preventable', 'NonPreventable', 'Indeterminate'])],
            'contributing_factors' => ['nullable', 'string', 'max:2000'],
            'clinical_remarks'     => ['nullable', 'string', 'max:2000'],
            'committee_remarks'    => ['nullable', 'string', 'max:2000'],
        ]);

        $audit = IcuMortalityAudit::where('icu_admission_id', $admissionId)->firstOrFail();
        $audit->update([
            'preventability'       => $request->preventability,
            'contributing_factors' => $request->contributing_factors,
            'clinical_remarks'     => $request->clinical_remarks,
            'committee_remarks'    => $request->committee_remarks,
            'audit_status'         => 'Completed',
            'reviewed_by'          => auth()->id(),
            'reviewed_at'          => now(),
        ]);

        return back()->with('success', 'Audit review saved.');
    }
}
