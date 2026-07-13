<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtPostOpNote;
use App\Models\Ot\OtSurgerySchedule;
use Illuminate\Http\Request;

class PostOpController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_post_op_access');

        $scope = $request->get('scope', 'active');
        $q = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'postOpNote']);

        if ($scope === 'active') {
            $q->whereIn('status', [
                OtSurgerySchedule::STATUS_SURGERY_RUNNING,
                OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
                OtSurgerySchedule::STATUS_IN_RECOVERY,
            ]);
        } elseif ($scope === 'completed') {
            $q->whereIn('status', [
                OtSurgerySchedule::STATUS_TRANSFERRED_BACK,
                OtSurgerySchedule::STATUS_CLOSED,
            ]);
        }

        $schedules = $q->orderBy('actual_end', 'desc')->paginate(20)->appends($request->query());

        return view('ot.post-op.index', compact('schedules', 'scope'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'postOpNote'])->findOrFail($scheduleId);
        $note = $schedule->postOpNote ?? new OtPostOpNote(['surgery_schedule_id' => $schedule->id]);

        return view('ot.post-op.show', compact('schedule', 'note'));
    }

    public function store(Request $request, $scheduleId)
    {
        return $this->save($request, $scheduleId);
    }

    public function update(Request $request, $scheduleId)
    {
        return $this->save($request, $scheduleId);
    }

    protected function save(Request $request, $scheduleId)
    {
        $data = $request->validate([
            'procedure_summary' => 'required|string',
            'immediate_findings' => 'nullable|string',
            'post_op_diagnosis' => 'nullable|string',
            'orders' => 'nullable|string',
            'medications' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
            'disposition' => 'nullable|in:PACU,Ward,ICU,CCU,Home',
        ]);

        $note = OtPostOpNote::firstOrNew(['surgery_schedule_id' => $scheduleId]);
        $note->fill($data);
        $note->signed_by = auth()->id();
        $note->signed_at = now();
        $note->save();

        OtAuditLog::record('post_op_note', $note->id, 'saved');

        return back()->with('success', 'Post-op notes saved.');
    }
}
