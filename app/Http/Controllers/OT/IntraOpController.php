<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtIntraOpRecord;
use App\Models\Ot\OtPostOpNote;
use App\Models\Ot\OtSurgerySchedule;
use Illuminate\Http\Request;

class IntraOpController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_intra_op_access');

        $scope = $request->get('scope', 'active');
        $activeStatuses = [
            OtSurgerySchedule::STATUS_PATIENT_RECEIVED,
            OtSurgerySchedule::STATUS_ANESTHESIA_STARTED,
            OtSurgerySchedule::STATUS_SURGERY_RUNNING,
            OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
        ];

        $q = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'intraOpRecord']);

        if ($scope === 'active') {
            $q->whereIn('status', $activeStatuses);
        } elseif ($scope === 'completed') {
            $q->whereIn('status', [
                OtSurgerySchedule::STATUS_IN_RECOVERY,
                OtSurgerySchedule::STATUS_TRANSFERRED_BACK,
                OtSurgerySchedule::STATUS_CLOSED,
            ]);
        }
        // 'all' → no filter

        $schedules = $q->orderBy('scheduled_start', 'desc')->paginate(20)->appends($request->query());

        return view('ot.intra-op.index', compact('schedules', 'scope'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'room', 'teamMembers',
            'anesthesiaRecord', 'intraOpRecord', 'consumableUsages',
        ])->findOrFail($scheduleId);

        $record = $schedule->intraOpRecord ?? new OtIntraOpRecord(['surgery_schedule_id' => $schedule->id]);

        return view('ot.intra-op.show', compact('schedule', 'record'));
    }

    public function startSurgery($scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        if ($schedule->status !== OtSurgerySchedule::STATUS_ANESTHESIA_STARTED &&
            $schedule->status !== OtSurgerySchedule::STATUS_PATIENT_RECEIVED) {
            return back()->with('error', 'Patient must be received in OT (and anesthesia started) before starting surgery.');
        }

        $from = $schedule->status;
        $schedule->update([
            'status' => OtSurgerySchedule::STATUS_SURGERY_RUNNING,
            'actual_start' => now(),
        ]);

        OtIntraOpRecord::firstOrCreate(
            ['surgery_schedule_id' => $schedule->id],
            ['incision_time' => now()]
        );

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'surgery_started',
            $from, OtSurgerySchedule::STATUS_SURGERY_RUNNING
        );

        return back()->with('success', 'Surgery started.');
    }

    public function update(Request $request, $scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        $data = $request->validate([
            'incision_time' => 'nullable|date',
            'closure_time' => 'nullable|date|after_or_equal:incision_time',
            'operative_findings' => 'nullable|string',
            'procedure_performed' => 'nullable|string',
            'operative_notes' => 'nullable|string',
            'specimens_collected' => 'nullable|string',
            'implants_used' => 'nullable|string',
            'blood_loss_ml' => 'nullable|numeric|min:0',
            'blood_transfused_ml' => 'nullable|numeric|min:0',
            'complications' => 'nullable|string',
            'post_op_instructions' => 'nullable|string',
            'counts_verified' => 'nullable|boolean',
        ]);

        $record = OtIntraOpRecord::firstOrNew(['surgery_schedule_id' => $schedule->id]);
        $record->fill($data);
        $record->save();

        OtAuditLog::record('intra_op_record', $record->id, 'updated');

        return back()->with('success', 'Operative record saved.');
    }

    public function completeSurgery(Request $request, $scheduleId)
    {
        $schedule = OtSurgerySchedule::with('intraOpRecord', 'postOpNote')->findOrFail($scheduleId);

        if ($schedule->status !== OtSurgerySchedule::STATUS_SURGERY_RUNNING) {
            return back()->with('error', 'Surgery is not running.');
        }

        if (! $schedule->intraOpRecord || empty($schedule->intraOpRecord->operative_notes)) {
            return back()->with('error', 'Operative notes are required before closing the surgery.');
        }

        if (! $schedule->postOpNote || empty($schedule->postOpNote->procedure_summary)) {
            return back()->with('error', 'Post-op notes are required before closing the surgery.');
        }

        $schedule->update([
            'status' => OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
            'actual_end' => now(),
        ]);

        if ($schedule->intraOpRecord) {
            $schedule->intraOpRecord->update([
                'closure_time' => $schedule->intraOpRecord->closure_time ?? now(),
                'signed_by' => auth()->id(),
                'signed_at' => now(),
            ]);
        }

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'surgery_completed',
            OtSurgerySchedule::STATUS_SURGERY_RUNNING, OtSurgerySchedule::STATUS_SURGERY_COMPLETED
        );

        return back()->with('success', 'Surgery marked completed.');
    }
}
