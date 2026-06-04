<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtPacuRecord;
use App\Models\Ot\OtSurgerySchedule;
use App\Services\Ot\OtNotifier;
use Illuminate\Http\Request;

class PacuController extends OtBaseController
{
    public function __construct(protected OtNotifier $notifier) {}

    public function index(Request $request)
    {
        $this->gate('ot_pacu_access');

        $scope = $request->get('scope', 'active');
        $q = OtPacuRecord::with(['schedule.surgeryRequest.patient', 'schedule.room']);

        if ($scope === 'active') {
            $q->whereNull('discharged_at');
        } elseif ($scope === 'discharged') {
            $q->whereNotNull('discharged_at');
        }

        $records = $q->latest('admitted_at')->paginate(20)->appends($request->query());

        return view('ot.pacu.index', compact('records', 'scope'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'pacuRecord'])->findOrFail($scheduleId);
        $record = $schedule->pacuRecord ?? new OtPacuRecord(['surgery_schedule_id' => $schedule->id]);

        // Bed master — load available beds so admit form can pick from the list
        $beds = collect();
        if (class_exists(\App\Models\Bed::class)) {
            $beds = \App\Models\Bed::with(['bedType', 'bedGroup'])
                ->where('is_active', 1)
                ->where('is_reserved', 0)
                ->orderBy('name')
                ->get();
        }

        return view('ot.pacu.show', compact('schedule', 'record', 'beds'));
    }

    public function admit(Request $request, $scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        if ($schedule->status !== OtSurgerySchedule::STATUS_SURGERY_COMPLETED) {
            return back()->with('error', 'Surgery must be completed before admitting to PACU.');
        }

        $record = OtPacuRecord::firstOrCreate(
            ['surgery_schedule_id' => $schedule->id],
            [
                'admitted_at' => now(),
                'bed_no' => $request->get('bed_no'),
                'status' => 'In Recovery',
            ]
        );

        $schedule->update(['status' => OtSurgerySchedule::STATUS_IN_RECOVERY]);
        OtAuditLog::record(
            'pacu_record', $record->id, 'admitted',
            OtSurgerySchedule::STATUS_SURGERY_COMPLETED, OtSurgerySchedule::STATUS_IN_RECOVERY
        );

        return back()->with('success', 'Patient admitted to PACU.');
    }

    public function addVitals(Request $request, $id)
    {
        $data = $request->validate([
            'bp'            => ['nullable', 'regex:/^\d{2,3}\/\d{2,3}$/'],
            'pulse'         => ['nullable', 'integer', 'min:30', 'max:220'],
            'spo2'          => ['nullable', 'integer', 'min:50', 'max:100'],
            'temp'          => ['nullable', 'numeric', 'min:30', 'max:45'],
            'pain_score'    => ['nullable', 'integer', 'min:0', 'max:10'],
            'aldrete_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ], [
            'bp.regex'             => 'BP must be in the form 120/80.',
            'pulse.min'            => 'Pulse must be between 30 and 220 bpm.',
            'pulse.max'            => 'Pulse must be between 30 and 220 bpm.',
            'spo2.min'             => 'SpO₂ must be between 50% and 100%.',
            'spo2.max'             => 'SpO₂ must be between 50% and 100%.',
            'temp.min'             => 'Temperature must be between 30°C and 45°C.',
            'temp.max'             => 'Temperature must be between 30°C and 45°C.',
            'pain_score.min'       => 'Pain score is 0–10.',
            'pain_score.max'       => 'Pain score is 0–10.',
            'aldrete_score.min'    => 'Aldrete score is 0–10.',
            'aldrete_score.max'    => 'Aldrete score is 0–10.',
        ]);

        $record = OtPacuRecord::findOrFail($id);
        $log = $record->vitals_log ?? [];

        $log[] = [
            'time'          => now()->toDateTimeString(),
            'bp'            => $data['bp']            ?? null,
            'pulse'         => $data['pulse']         ?? null,
            'spo2'          => $data['spo2']          ?? null,
            'temp'          => $data['temp']          ?? null,
            'pain_score'    => $data['pain_score']    ?? null,
            'aldrete_score' => $data['aldrete_score'] ?? null,
            'notes'         => $data['notes']         ?? null,
            'recorded_by'   => auth()->id(),
        ];

        $record->vitals_log = $log;
        if (! empty($data['aldrete_score']) || $data['aldrete_score'] === 0 || $data['aldrete_score'] === '0') {
            $record->aldrete_score = $data['aldrete_score'];
        }
        $record->save();

        return back()->with('success', 'Vitals recorded.');
    }

    public function clearRecovery(Request $request, $id)
    {
        $request->validate([
            'recovery_clearance_notes' => 'nullable|string',
            'consciousness_level' => 'nullable|string|max:30',
        ]);

        $record = OtPacuRecord::findOrFail($id);

        if (($record->aldrete_score ?? 0) < 8) {
            return back()->with('error', 'Aldrete score must be ≥ 8 to clear for recovery.');
        }

        $record->update([
            'recovery_clearance' => true,
            'recovery_clearance_notes' => $request->get('recovery_clearance_notes'),
            'consciousness_level' => $request->get('consciousness_level'),
            'cleared_by' => auth()->id(),
            'cleared_at' => now(),
        ]);

        OtAuditLog::record('pacu_record', $record->id, 'recovery_cleared');

        return back()->with('success', 'Recovery clearance granted.');
    }

    public function discharge(Request $request, $id)
    {
        $request->validate([
            'discharge_destination' => 'required|in:IPD,ICU,CCU,Ward,Home',
            'aldrete_score' => 'nullable|integer|min:0|max:10',
        ]);

        $record = OtPacuRecord::with('schedule')->findOrFail($id);

        if (! $record->recovery_clearance) {
            return back()->with('error', 'Doctor recovery clearance is required before discharge from PACU.');
        }

        $record->update([
            'discharged_at' => now(),
            'discharge_destination' => $request->get('discharge_destination'),
            'aldrete_score' => $request->get('aldrete_score', $record->aldrete_score),
            'status' => 'Discharged',
            'discharged_by' => auth()->id(),
        ]);

        OtAuditLog::record(
            'pacu_record', $record->id, 'discharged',
            null, null, null,
            ['destination' => $request->get('discharge_destination')]
        );

        if ($record->schedule) {
            $this->notifier->pacuDischarged($record->schedule, $request->get('discharge_destination'));
        }

        return back()->with('success', 'Patient discharged from PACU.');
    }
}
