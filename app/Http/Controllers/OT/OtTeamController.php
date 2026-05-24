<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtSurgeryTeam;
use App\Services\Ot\OtConflictService;
use Illuminate\Http\Request;

class OtTeamController extends OtBaseController
{
    public function __construct(protected OtConflictService $conflicts) {}

    public function index(Request $request)
    {
        $this->gate('ot_team_access');
        $schedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'teamMembers'])
            ->whereNotIn('status', [
                OtSurgerySchedule::STATUS_CLOSED,
                OtSurgerySchedule::STATUS_CANCELLED,
            ])
            ->orderBy('scheduled_start')
            ->paginate(20);

        return view('ot.teams.index', compact('schedules'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'teamMembers'])->findOrFail($scheduleId);
        $doctors = Doctor::orderBy('name')->get();
        $roles = OtSurgeryTeam::ROLES;

        return view('ot.teams.show', compact('schedule', 'doctors', 'roles'));
    }

    public function assign(Request $request, $scheduleId)
    {
        $request->validate([
            'role' => 'required|in:' . implode(',', OtSurgeryTeam::ROLES),
            'specialization' => 'nullable|string|max:50',
            'staff_id' => 'required|integer',
            'staff_type' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        $reasons = $this->conflicts->staffConflictReasons(
            (int) $request->get('staff_id'),
            $request->get('staff_type', 'user'),
            $schedule->scheduled_start,
            $schedule->scheduled_end,
            $schedule->id
        );

        if (! empty($reasons)) {
            return back()->with('error', 'Staff conflict: ' . implode('; ', $reasons));
        }

        $member = OtSurgeryTeam::create([
            'surgery_schedule_id' => $schedule->id,
            'role' => $request->get('role'),
            'specialization' => $request->get('specialization'),
            'staff_id' => $request->get('staff_id'),
            'staff_type' => $request->get('staff_type', 'user'),
            'is_primary' => $request->get('role') === OtSurgeryTeam::ROLE_PRIMARY_SURGEON,
            'assigned_at' => now(),
            'notes' => $request->get('notes'),
        ]);

        OtAuditLog::record('surgery_team', $member->id, 'assigned');

        return back()->with('success', 'Team member assigned.');
    }

    public function remove($memberId)
    {
        $member = OtSurgeryTeam::findOrFail($memberId);
        $member->delete();
        OtAuditLog::record('surgery_team', $member->id, 'removed');

        return back()->with('success', 'Team member removed.');
    }
}
