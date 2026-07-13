<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuMortalityAudit;
use App\Services\PdfService;
use App\Services\Icu\AdmissionCloseoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Mpdf\Output\Destination;

class IcuMortalityController extends Controller
{
    public function __construct(private AdmissionCloseoutService $closeout) {}

    /**
     * Mortality patients list (Reports → Mortality), scoped by ICU/CCU/NICU/PICU.
     */
    public function index(Request $request)
    {
        $icuType = strtoupper((string) $request->input('icu_type', 'ICU')) ?: 'ICU';
        $icuType = in_array($icuType, ['ICU', 'CCU', 'NICU', 'PICU'], true) ? $icuType : 'ICU';

        $from = $request->filled('from') ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay() : null;
        $to   = $request->filled('to') ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay()   : null;
        $search = trim((string) $request->input('q', ''));

        $query = IcuAdmission::query()
            ->with(['patient:id,patient_name,mrn,dob,gender,mobileno', 'bed:id,name', 'mortalityAudit'])
            ->where('icu_type', $icuType)
            ->whereHas('mortalityAudit');

        if ($from && $to) {
            $query->whereHas('mortalityAudit', fn($q) => $q->whereBetween('death_time', [$from, $to]));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('icu_case_id', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($p) use ($search) {
                        $p->where('patient_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%")
                            ->orWhere('mobileno', 'like', "%{$search}%");
                    });
            });
        }

        $admissions = $query->orderByDesc('discharge_time')->orderByDesc('id')->get();

        return view('icu.mortality.index', [
            'admissions' => $admissions,
            'icuType'    => $icuType,
            'from'       => $from,
            'to'         => $to,
            'search'     => $search,
        ]);
    }

    public function create($admissionId)
    {
        $admission = IcuAdmission::with(['patient', 'emergencyEvents', 'mortalityAudit'])
            ->findOrFail($admissionId);

        $doctors = DB::table('doctors')->select('id', 'name')->orderBy('name')->get();

        $codeBlueEvents = $admission->emergencyEvents;

        return view('icu.mortality.create', compact('admission', 'codeBlueEvents', 'doctors'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'death_time'           => ['required', 'date'],
            'final_diagnosis'      => ['required', 'string', 'max:2000'],
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
                    'final_diagnosis'       => $request->final_diagnosis,
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
            dd($e->getMessage());
            DB::rollBack();
            Log::error('ICU mortality store failed', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    public function show($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient',
            'mortalityAudit.codeBlueEvent',
            'mortalityAudit.declaredByDoctor',
            'mortalityAudit.deathReviewedByDoctor',
        ])
            ->findOrFail($admissionId);

        if (! $admission->mortalityAudit) {
            return redirect()->route('icu.admissions.show', $admission->id)
                ->with('error', 'No mortality audit recorded.');
        }

        $doctors = DB::table('doctors')->select('id', 'name')->orderBy('name')->get();

        return view('icu.mortality.show', compact('admission', 'doctors'));
    }

    public function print($admissionId)
    {
        $admission = IcuAdmission::with([
            'patient',
            'mortalityAudit.codeBlueEvent',
            'mortalityAudit.declaredByDoctor',
            'mortalityAudit.deathReviewedByDoctor',
        ])->findOrFail($admissionId);

        if (! $admission->mortalityAudit) {
            return redirect()->route('icu.admissions.show', $admission->id)
                ->with('error', 'No mortality audit recorded.');
        }

        $html = view('icu.mortality.pdf', compact('admission'))->render();

        $mpdf = PdfService::create([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12,
            'default_font_size' => 10,
        ]);
        $mpdf->SetTitle('Mortality Audit - ' . $admission->icu_case_id);
        $mpdf->SetFooter('{PAGENO} / {nbpg}');
        $mpdf->WriteHTML($html);

        $safeCaseId = preg_replace('/[^A-Za-z0-9_-]+/', '-', $admission->icu_case_id);
        $fileName = 'mortality-audit-' . trim($safeCaseId, '-') . '.pdf';

        return Response::make($mpdf->Output($fileName, Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Audit committee review.
     */
    public function review(Request $request, $admissionId)
    {
        $request->validate([
            'death_reviewed_by'    => ['nullable', 'integer', 'exists:doctors,id'],
            'review_date'          => ['nullable', 'date'],
            'primary_cause'        => ['nullable', 'string', 'max:2000'],
            'preventability'       => ['required', Rule::in(['Preventable', 'NonPreventable', 'Indeterminate'])],
            'contributing_factors' => ['nullable', 'string', 'max:2000'],
            'clinical_remarks'     => ['nullable', 'string', 'max:2000'],
            'committee_remarks'    => ['nullable', 'string', 'max:2000'],
        ]);

        $reviewDate = $request->review_date ? new \DateTimeImmutable($request->review_date) : null;

        $audit = IcuMortalityAudit::where('icu_admission_id', $admissionId)->firstOrFail();
        $audit->update([
            'death_reviewed_by'    => $request->death_reviewed_by,
            'review_date'          => $reviewDate,
            'primary_cause'        => $request->primary_cause,
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
