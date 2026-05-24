<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtPreOpChecklist;
use App\Models\Ot\OtSurgerySchedule;
use App\Services\Ot\OtNotifier;
use Illuminate\Http\Request;

class PreOpController extends OtBaseController
{
    public function __construct(protected OtNotifier $notifier) {}

    public function index(Request $request)
    {
        $this->gate('ot_pre_op_access');
        $query = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'preOpChecklist'])
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_SCHEDULED,
                OtSurgerySchedule::STATUS_PRE_OP_PENDING,
                OtSurgerySchedule::STATUS_READY_FOR_OT,
            ])
            ->orderBy('scheduled_start');

        if ($date = $request->get('date')) {
            $query->whereDate('scheduled_start', $date);
        }

        $schedules = $query->paginate(20)->withQueryString();

        return view('ot.pre-op.index', compact('schedules'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'preOpChecklist'])
            ->findOrFail($scheduleId);

        $checklist = $schedule->preOpChecklist ?? new OtPreOpChecklist(['surgery_schedule_id' => $schedule->id]);

        return view('ot.pre-op.show', compact('schedule', 'checklist'));
    }

    public function update(Request $request, $scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        $data = $request->validate([
            'consent_obtained' => 'nullable|boolean',
            'lab_completed' => 'nullable|boolean',
            'radiology_completed' => 'nullable|boolean',
            'fasting_confirmed' => 'nullable|boolean',
            'blood_arranged' => 'nullable|boolean',
            'allergy_reviewed' => 'nullable|boolean',
            'vitals_recorded' => 'nullable|boolean',
            'anesthesia_clearance' => 'nullable|boolean',
            'doctor_clearance' => 'nullable|boolean',
            'nurse_confirmation' => 'nullable|boolean',
            'site_marked' => 'nullable|boolean',
            'id_band_verified' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'vitals_snapshot' => 'nullable|array',
        ]);

        $checklist = OtPreOpChecklist::firstOrNew(['surgery_schedule_id' => $schedule->id]);
        $checklist->fill($data);
        $checklist->save();

        if ($schedule->status === OtSurgerySchedule::STATUS_SCHEDULED) {
            $schedule->update(['status' => OtSurgerySchedule::STATUS_PRE_OP_PENDING]);
        }

        OtAuditLog::record('pre_op_checklist', $checklist->id, 'updated');

        return back()->with('success', 'Checklist updated.');
    }

    public function complete($scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);
        $checklist = OtPreOpChecklist::firstOrNew(['surgery_schedule_id' => $schedule->id]);

        if (! $checklist->exists || ! $checklist->isReady()) {
            return back()->with('error', 'All mandatory items must be checked before completion (or use emergency override).');
        }

        $checklist->update([
            'is_complete' => true,
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        $schedule->update(['status' => OtSurgerySchedule::STATUS_READY_FOR_OT]);

        OtAuditLog::record(
            'pre_op_checklist', $checklist->id, 'completed',
            null, OtSurgerySchedule::STATUS_READY_FOR_OT
        );

        $this->notifier->preOpReady($schedule->load('surgeryRequest.patient'));

        return back()->with('success', 'Pre-op complete. Patient is Ready for OT.');
    }

    public function emergencyOverride(Request $request, $scheduleId)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        $checklist = OtPreOpChecklist::firstOrNew(['surgery_schedule_id' => $schedule->id]);
        $checklist->fill([
            'emergency_override' => true,
            'emergency_override_reason' => $request->get('reason'),
            'override_approved_by' => auth()->id(),
            'is_complete' => true,
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ])->save();

        $schedule->update(['status' => OtSurgerySchedule::STATUS_READY_FOR_OT]);

        OtAuditLog::record(
            'pre_op_checklist', $checklist->id, 'emergency_override',
            null, OtSurgerySchedule::STATUS_READY_FOR_OT,
            $request->get('reason')
        );

        $this->notifier->emergencyOverride($schedule, $request->get('reason'));

        return back()->with('success', 'Emergency override applied — Ready for OT.');
    }
}
