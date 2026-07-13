<?php

namespace App\Http\Controllers\Er;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Er\ErClinicalNote;
use App\Models\Er\ErObservation;
use App\Models\Er\ErTransfer;
use App\Models\Er\ErTriage;
use App\Models\FrontDesk\ErPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** ER Live Dashboard — 10 KPI tiles + triage queue. */
    public function dashboard()
    {
        $today = today();
        $kpi = [
            'today_total'     => ErPatient::whereDate('arrival_time', $today)->count(),
            'critical'        => ErPatient::active()->where('priority', 'CRITICAL')->count(),
            'waiting'         => ErPatient::active()->where('status', 'WAITING')->count(),
            'under_assessment'=> ErPatient::active()->where('status', 'UNDER_ASSESSMENT')->count(),
            'in_treatment'    => ErPatient::active()->where('status', 'IN_TREATMENT')->count(),
            'observation'     => ErPatient::active()->where('status', 'OBSERVATION')->count(),
            'avg_wait_min'    => (int) ErPatient::active()->whereNotNull('arrival_time')
                                    ->get()->avg(fn ($p) => now()->diffInMinutes($p->arrival_time, true)),
            'beds_free'       => Bed::where('is_reserved', false)->where('is_active', 1)->count(),
            'beds_total'      => Bed::where('is_active', 1)->count(),
            'pending_transfers' => ErTransfer::where('status', 'PENDING')->count(),
        ];

        $triageBreakdown = ErTriage::query()
            ->select('triage_level', DB::raw('COUNT(*) as n'))
            ->whereDate('triaged_at', $today)
            ->groupBy('triage_level')->pluck('n', 'triage_level')->toArray();
        // Ensure all 5 levels present
        foreach (['RED','ORANGE','YELLOW','GREEN','BLACK'] as $l) $triageBreakdown[$l] ??= 0;

        $activePatients = ErPatient::active()->with(['patient', 'latestTriage', 'doctor'])
            ->latest('arrival_time')->take(15)->get();

        $recentTransfers = ErTransfer::with(['erPatient.patient', 'targetBed.bedType'])
            ->latest('id')->take(10)->get();

        return view('er.dashboard', compact('kpi', 'triageBreakdown', 'activePatients', 'recentTransfers'));
    }

    /** Kanban tracking board (7 status columns). */
    public function board()
    {
        $statuses = ['WAITING', 'UNDER_ASSESSMENT', 'IN_TREATMENT', 'OBSERVATION', 'TRANSFERRED', 'DISCHARGED', 'EXPIRED'];
        $patients = ErPatient::with(['patient', 'latestTriage', 'doctor'])
            ->whereDate('arrival_time', '>=', now()->subDays(2))
            ->latest('arrival_time')->get();
        $grouped = $patients->groupBy(fn ($p) => $p->status ?: 'WAITING');
        foreach ($statuses as $s) $grouped[$s] = $grouped[$s] ?? collect();
        return view('er.board', compact('statuses', 'grouped'));
    }

    public function show($id)
    {
        $er = ErPatient::with(['patient', 'doctor', 'department', 'encounter',
            'triages', 'latestTriage', 'clinicalNotes.doctor', 'observations', 'transfers.targetBed'])
            ->findOrFail($id);

        $beds = Bed::with('bedType')->where('is_active', 1)->where('is_reserved', 0)->get();
        $doctors = Doctor::orderBy('name')->get(['id', 'name']);

        $bills = $er->encounter_id
            ? \App\Models\Billing\Bill::with('payments')->where('encounter_id', $er->encounter_id)->get()
            : collect();

        $postings = $er->encounter_id
            ? \App\Models\ServiceCharge\ServiceChargePosting::with('catalog')
                ->where('encounter_id', $er->encounter_id)->where('status', 'posted')
                ->latest('id')->get()
            : collect();

        $billPayments = $bills->flatMap(fn ($b) => $b->payments);

        $billSummary = [
            'grand'      => (float) $bills->sum('grand_total'),
            'paid'       => (float) $bills->sum('paid_total'),
            'due'        => (float) $bills->sum('balance_due'),
            'count'      => $bills->count(),
            'postings'   => $postings->count(),
            'postSum'    => (float) $postings->sum('net_amount'),
            'payments'   => $billPayments->count(),
            'paymentSum' => (float) $billPayments->sum('amount'),
        ];

        return view('er.show', compact('er', 'beds', 'doctors', 'bills', 'postings', 'billPayments', 'billSummary'));
    }

    /** Triage scoring — record a new triage assessment. */
    public function storeTriage(Request $request, $id)
    {
        $er = ErPatient::findOrFail($id);
        $data = $request->validate([
            'triage_level'        => 'required|in:RED,ORANGE,YELLOW,GREEN,BLACK',
            'pain_score'          => 'nullable|integer|min:0|max:10',
            'consciousness_level' => 'nullable|string|max:32',
            'chief_complaint'     => 'nullable|string|max:500',
            'blood_pressure'      => 'nullable|string|max:16',
            'pulse'               => 'nullable|integer|min:30|max:220',
            'respiratory_rate'    => 'nullable|integer|min:5|max:60',
            'spo2'                => 'nullable|integer|min:50|max:100',
            'temperature_c'       => 'nullable|numeric|min:30|max:45',
            'blood_glucose_mgdl'  => 'nullable|numeric|min:20|max:600',
            'notes'               => 'nullable|string|max:1000',
        ]);
        $data['er_patient_id'] = $er->id;
        $data['patient_id'] = $er->patient_id;
        $data['triaged_by'] = auth()->id();
        $data['triaged_at'] = now();
        ErTriage::create($data);

        // Auto-bump priority from triage level
        $priorityMap = ['RED' => 'CRITICAL', 'ORANGE' => 'CRITICAL', 'YELLOW' => 'HIGH', 'GREEN' => 'NORMAL', 'BLACK' => 'CRITICAL'];
        $er->update([
            'priority' => $priorityMap[$data['triage_level']] ?? 'NORMAL',
            'status' => 'UNDER_ASSESSMENT',
        ]);

        return back()->with('success', "Triage recorded — {$data['triage_level']} priority.");
    }

    /** SOAP-style clinical note. */
    public function storeNote(Request $request, $id)
    {
        $er = ErPatient::findOrFail($id);
        $data = $request->validate([
            'note_type'   => 'required|in:SOAP,PROGRESS,PROCEDURE,CONSULT,DISCHARGE',
            'subjective'  => 'nullable|string',
            'objective'   => 'nullable|string',
            'assessment'  => 'nullable|string',
            'plan'        => 'nullable|string',
            'doctor_id'   => 'nullable|exists:doctors,id',
        ]);
        $data['er_patient_id'] = $er->id;
        $data['recorded_at'] = now();
        ErClinicalNote::create($data);

        if ($er->status === 'PENDING' || $er->status === 'WAITING') {
            $er->update(['status' => 'UNDER_ASSESSMENT']);
        }
        return back()->with('success', "Clinical note recorded ({$data['note_type']}).");
    }

    /** Hourly observation (vitals + IO + O2). */
    public function storeObservation(Request $request, $id)
    {
        $er = ErPatient::findOrFail($id);
        $data = $request->validate([
            'observed_at'      => 'nullable|date',
            'blood_pressure'   => 'nullable|string|max:16',
            'pulse'            => 'nullable|integer|min:30|max:220',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'spo2'             => 'nullable|integer|min:50|max:100',
            'temperature_c'    => 'nullable|numeric|min:30|max:45',
            'pain_score'       => 'nullable|integer|min:0|max:10',
            'fluid_intake_ml'  => 'nullable|numeric|min:0',
            'fluid_output_ml'  => 'nullable|numeric|min:0',
            'o2_lpm'           => 'nullable|numeric|min:0|max:15',
            'consciousness'    => 'nullable|string|max:32',
            'notes'            => 'nullable|string|max:500',
        ]);
        $data['er_patient_id'] = $er->id;
        $data['observed_at'] = $data['observed_at'] ?? now();
        $data['observed_by'] = auth()->id();
        ErObservation::create($data);
        return back()->with('success', 'Observation recorded.');
    }

    /** Transfer / Admit Patient — single endpoint for the mega-button. */
    public function transfer(Request $request, $id)
    {
        $er = ErPatient::findOrFail($id);
        $data = $request->validate([
            'target'               => 'required|in:IPD,ICU,CCU,NICU,OT,WARD,HOME,REFERRED,EXPIRED',
            'target_bed_id'        => 'nullable|exists:beds,id',
            'target_doctor_id'     => 'nullable|exists:doctors,id',
            'handover_summary'     => 'nullable|string|max:2000',
            'clinical_indication'  => 'nullable|string|max:500',
        ]);
        $data['er_patient_id'] = $er->id;
        $data['requested_at'] = now();
        $data['requested_by'] = auth()->id();

        DB::transaction(function () use ($er, $data) {
            $transfer = ErTransfer::create($data);

            // Move ER status forward
            $newStatus = match ($data['target']) {
                'DISCHARGED', 'HOME'     => 'DISCHARGED',
                'EXPIRED'                => 'EXPIRED',
                'REFERRED'               => 'REFERRED',
                default                  => 'TRANSFERRED',
            };
            $er->update(['status' => $newStatus]);

            // Close encounter if discharge/expired
            if (in_array($newStatus, ['DISCHARGED', 'EXPIRED', 'REFERRED']) && $er->encounter_id) {
                \App\Models\Encounter\Encounter::where('id', $er->encounter_id)
                    ->update(['status' => 'closed', 'closed_at' => now()]);
            }

            // Mark transfer complete so dashboard count drops
            $transfer->update([
                'status' => 'COMPLETED',
                'accepted_at' => now(),
                'completed_at' => now(),
                'accepted_by' => auth()->id(),
            ]);
        });

        return redirect()->route('er.show', $er->id)
            ->with('success', "Transfer to {$data['target']} recorded.");
    }
}
