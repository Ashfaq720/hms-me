<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ot\OtAnesthesiaType;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtScheduleEquipment;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtSurgeryTeam;
use App\Services\Ot\OtConflictService;
use App\Services\Ot\OtNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgeryScheduleController extends OtBaseController
{
    public function __construct(
        protected OtConflictService $conflicts,
        protected OtNotifier $notifier,
    ) {}

    public function index(Request $request)
    {
        $this->gate('ot_schedule_access');
        $query = OtSurgerySchedule::with(['surgeryRequest.patient', 'surgeryRequest.surgeryType', 'room'])
            ->orderBy('scheduled_start', 'desc');

        if ($date = $request->get('date')) {
            $query->whereDate('scheduled_start', $date);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($roomId = $request->get('room_id')) {
            $query->where('ot_room_id', $roomId);
        }

        $schedules = $query->paginate(20)->withQueryString();
        $rooms = OtRoom::active()->orderBy('name')->get();
        $statuses = OtSurgerySchedule::STATUSES;

        return view('ot.schedules.index', compact('schedules', 'rooms', 'statuses'));
    }

    public function create(Request $request)
    {
        $requestId = $request->get('request_id');
        $surgeryRequest = $requestId
            ? OtSurgeryRequest::with('patient', 'surgeryType')->find($requestId)
            : null;

        $availableRequests = OtSurgeryRequest::with('patient', 'surgeryType')
            ->whereIn('status', [
                OtSurgeryRequest::STATUS_ACCEPTED,
                OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING,
            ])
            ->get();

        $rooms = OtRoom::active()->orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $equipments = OtEquipment::where('is_active', true)->orderBy('name')->get();
        $anesthesiaTypes = OtAnesthesiaType::where('is_active', true)->orderBy('name')->get();

        return view('ot.schedules.create', compact(
            'surgeryRequest', 'availableRequests', 'rooms', 'doctors', 'equipments', 'anesthesiaTypes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'surgery_request_id' => 'required|exists:ot_surgery_requests,id',
            'ot_room_id' => 'required|exists:ot_rooms,id',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'buffer_minutes' => 'nullable|integer|min:0|max:240',
            'emergency_fast_track' => 'nullable|boolean',
            'team' => 'nullable|array',
            'team.*.role' => 'required_with:team|string',
            'team.*.specialization' => 'nullable|string|max:50',
            'team.*.staff_id' => 'required_with:team|integer',
            'team.*.staff_type' => 'nullable|string',
            'equipment_ids' => 'nullable|array',
            'equipment_ids.*' => 'integer|exists:ot_equipments,id',
        ]);

        // FR-09: auto-calc cleaning_buffer_until = scheduled_end + buffer_minutes
        $bufferMin = (int) ($validated['buffer_minutes'] ?? 30);
        $validated['buffer_minutes'] = $bufferMin;
        $validated['cleaning_buffer_until'] = \Carbon\Carbon::parse($validated['scheduled_end'])
            ->copy()->addMinutes($bufferMin);

        $errors = $this->conflicts->check([
            'scheduled_start' => $validated['scheduled_start'],
            'scheduled_end' => $validated['scheduled_end'],
            'ot_room_id' => $validated['ot_room_id'],
            'staff' => $validated['team'] ?? [],
            'equipment_ids' => $validated['equipment_ids'] ?? [],
        ]);

        if (! empty($errors) && empty($validated['emergency_fast_track'])) {
            return back()->withInput()->withErrors($errors);
        }

        $schedule = DB::transaction(function () use ($validated) {
            $schedule = OtSurgerySchedule::create([
                'surgery_request_id' => $validated['surgery_request_id'],
                'ot_room_id' => $validated['ot_room_id'],
                'scheduled_start' => $validated['scheduled_start'],
                'scheduled_end' => $validated['scheduled_end'],
                'buffer_minutes' => $validated['buffer_minutes'],
                'cleaning_buffer_until' => $validated['cleaning_buffer_until'],
                'emergency_fast_track' => $validated['emergency_fast_track'] ?? false,
                'status' => OtSurgerySchedule::STATUS_SCHEDULED,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['team'] ?? [] as $member) {
                OtSurgeryTeam::create([
                    'surgery_schedule_id' => $schedule->id,
                    'role' => $member['role'],
                    'specialization' => $member['specialization'] ?? null,
                    'staff_id' => $member['staff_id'],
                    'staff_type' => $member['staff_type'] ?? 'user',
                    'is_primary' => ($member['role'] === OtSurgeryTeam::ROLE_PRIMARY_SURGEON),
                    'assigned_at' => now(),
                ]);
            }

            foreach ($validated['equipment_ids'] ?? [] as $eqId) {
                OtScheduleEquipment::create([
                    'surgery_schedule_id' => $schedule->id,
                    'ot_equipment_id' => $eqId,
                ]);
            }

            OtSurgeryRequest::where('id', $schedule->surgery_request_id)
                ->update(['status' => OtSurgeryRequest::STATUS_SCHEDULED]);

            OtAuditLog::record(
                'surgery_schedule', $schedule->id, 'created',
                null, OtSurgerySchedule::STATUS_SCHEDULED
            );

            return $schedule;
        });

        $this->notifier->scheduleCreated($schedule->load('surgeryRequest.patient'));

        return redirect()
            ->route('ot.schedules.show', $schedule->id)
            ->with('success', 'Surgery scheduled successfully.');
    }

    public function show($id)
    {
        $schedule = OtSurgerySchedule::with([
            'surgeryRequest.patient', 'surgeryRequest.surgeryType',
            'room', 'teamMembers', 'equipments.equipment',
            'preOpChecklist', 'transfers', 'anesthesiaRecord',
            'intraOpRecord', 'consumableUsages', 'postOpNote', 'pacuRecord',
        ])->findOrFail($id);

        return view('ot.schedules.show', compact('schedule'));
    }

    public function edit($id)
    {
        $schedule = OtSurgerySchedule::with(['teamMembers', 'equipments'])->findOrFail($id);

        if (in_array($schedule->status, [
            OtSurgerySchedule::STATUS_SURGERY_RUNNING,
            OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
            OtSurgerySchedule::STATUS_CLOSED,
            OtSurgerySchedule::STATUS_CANCELLED,
        ])) {
            return back()->with('error', "Cannot edit a schedule in status: {$schedule->status}");
        }

        $rooms = OtRoom::active()->orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $equipments = OtEquipment::where('is_active', true)->orderBy('name')->get();

        return view('ot.schedules.edit', compact('schedule', 'rooms', 'doctors', 'equipments'));
    }

    public function update(Request $request, $id)
    {
        $schedule = OtSurgerySchedule::findOrFail($id);
        $validated = $request->validate([
            'ot_room_id' => 'required|exists:ot_rooms,id',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
        ]);

        $errors = $this->conflicts->check([
            'scheduled_start' => $validated['scheduled_start'],
            'scheduled_end' => $validated['scheduled_end'],
            'ot_room_id' => $validated['ot_room_id'],
            'exclude_schedule_id' => $schedule->id,
        ]);

        if (! empty($errors)) {
            return back()->withInput()->withErrors($errors);
        }

        $schedule->update($validated);
        OtAuditLog::record('surgery_schedule', $schedule->id, 'updated');

        return redirect()->route('ot.schedules.show', $schedule->id)->with('success', 'Schedule updated.');
    }

    public function destroy($id)
    {
        $schedule = OtSurgerySchedule::findOrFail($id);

        if (! in_array($schedule->status, [OtSurgerySchedule::STATUS_SCHEDULED, OtSurgerySchedule::STATUS_PRE_OP_PENDING])) {
            return back()->with('error', 'Only Scheduled or Pre-Op Pending schedules can be deleted.');
        }

        $schedule->delete();
        OtAuditLog::record('surgery_schedule', $schedule->id, 'deleted');

        return redirect()->route('ot.schedules.index')->with('success', 'Schedule deleted.');
    }

    public function calendar(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $rooms = OtRoom::active()->orderBy('name')->get();
        $schedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'surgeryRequest.surgeryType'])
            ->whereBetween('scheduled_start', [$start, $end])
            ->whereNotIn('status', [OtSurgerySchedule::STATUS_CANCELLED])
            ->orderBy('scheduled_start')
            ->get();

        return view('ot.schedules.calendar', compact('date', 'rooms', 'schedules'));
    }

    public function availability(Request $request)
    {
        // Probe call (no params) returns availability state without checking.
        if (! $request->filled('scheduled_start') || ! $request->filled('scheduled_end')) {
            return response()->json([
                'available' => true,
                'errors' => [],
                'hint' => 'Provide scheduled_start, scheduled_end, ot_room_id (and optional staff[], equipment_ids[]) to check conflicts.',
            ]);
        }
        try {
            $errors = $this->conflicts->check($request->only([
                'scheduled_start', 'scheduled_end', 'ot_room_id', 'staff', 'equipment_ids',
            ]));
            return response()->json(['available' => empty($errors), 'errors' => $errors]);
        } catch (\Throwable $e) {
            return response()->json(['available' => false, 'errors' => [$e->getMessage()]], 422);
        }
    }

    /**
     * FR-12: Reschedule re-runs availability checks for room, all currently
     * assigned team members and equipment at the new time, then updates the
     * times. History is kept via OtAuditLog with old + new values.
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'reason' => 'required|string|max:1000',
        ]);

        $original = OtSurgerySchedule::with(['teamMembers' => fn($q) => $q->active(), 'equipments'])->findOrFail($id);

        // Re-build conflict-check payload from current team + equipment
        $staff = $original->teamMembers->map(fn($m) => [
            'role' => $m->role,
            'staff_id' => $m->staff_id,
            'staff_type' => $m->staff_type,
        ])->toArray();

        $equipmentIds = $original->equipments->whereNull('released_at')->pluck('ot_equipment_id')->all();

        $errors = $this->conflicts->check([
            'scheduled_start' => $request->get('scheduled_start'),
            'scheduled_end' => $request->get('scheduled_end'),
            'ot_room_id' => $original->ot_room_id,
            'staff' => $staff,
            'equipment_ids' => $equipmentIds,
            'exclude_schedule_id' => $original->id,
        ]);

        if (! empty($errors)) {
            return back()->withErrors($errors);
        }

        $oldStart = $original->scheduled_start?->toDateTimeString();
        $oldEnd = $original->scheduled_end?->toDateTimeString();

        $bufferMin = (int) $original->buffer_minutes;
        $original->update([
            'scheduled_start' => $request->get('scheduled_start'),
            'scheduled_end' => $request->get('scheduled_end'),
            'cleaning_buffer_until' => \Carbon\Carbon::parse($request->get('scheduled_end'))
                ->copy()->addMinutes($bufferMin),
            'reschedule_reason' => $request->get('reason'),
        ]);

        OtAuditLog::record(
            'surgery_schedule', $original->id, 'rescheduled',
            null, null, $request->get('reason'),
            [
                'old_start' => $oldStart,
                'old_end' => $oldEnd,
                'new_start' => $request->get('scheduled_start'),
                'new_end' => $request->get('scheduled_end'),
            ]
        );

        return back()->with('success', 'Schedule rescheduled (availability re-verified for team + equipment).');
    }

    /**
     * FR-13: Cancel releases all team + equipment resources by marking them
     * released_at = now() (preserving history) so subsequent availability
     * checks correctly see them as free.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $schedule = OtSurgerySchedule::findOrFail($id);
        $from = $schedule->status;

        DB::transaction(function () use ($schedule, $request) {
            $schedule->update([
                'status' => OtSurgerySchedule::STATUS_CANCELLED,
                'cancellation_reason' => $request->get('reason'),
            ]);

            $now = now();
            OtSurgeryTeam::where('surgery_schedule_id', $schedule->id)
                ->whereNull('released_at')
                ->update([
                    'released_at' => $now,
                    'released_reason' => 'Schedule cancelled: ' . $request->get('reason'),
                ]);

            OtScheduleEquipment::where('surgery_schedule_id', $schedule->id)
                ->whereNull('released_at')
                ->update([
                    'released_at' => $now,
                    'released_reason' => 'Schedule cancelled: ' . $request->get('reason'),
                ]);
        });

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'cancelled',
            $from, OtSurgerySchedule::STATUS_CANCELLED, $request->get('reason'),
            ['team_released' => true, 'equipment_released' => true]
        );

        $this->notifier->surgeryCancelled($schedule, $request->get('reason'));

        return back()->with('success', 'Schedule cancelled.');
    }

    public function approve($id)
    {
        $schedule = OtSurgerySchedule::findOrFail($id);
        $schedule->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        OtAuditLog::record('surgery_schedule', $schedule->id, 'approved');

        return back()->with('success', 'Schedule approved.');
    }

    /**
     * Allowed forward transitions between schedule statuses.
     * Anything not listed here is rejected by updateStatus(), so the
     * schedule can never skip surgery to land in Closed, or jump from
     * Cancelled back to Scheduled, etc.
     */
    protected const ALLOWED_STATUS_TRANSITIONS = [
        OtSurgerySchedule::STATUS_SCHEDULED          => [OtSurgerySchedule::STATUS_PRE_OP_PENDING, OtSurgerySchedule::STATUS_READY_FOR_OT, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_PRE_OP_PENDING     => [OtSurgerySchedule::STATUS_READY_FOR_OT, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_READY_FOR_OT       => [OtSurgerySchedule::STATUS_TRANSFER_STARTED, OtSurgerySchedule::STATUS_PATIENT_RECEIVED, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_TRANSFER_STARTED   => [OtSurgerySchedule::STATUS_PATIENT_RECEIVED, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_PATIENT_RECEIVED   => [OtSurgerySchedule::STATUS_ANESTHESIA_STARTED, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_ANESTHESIA_STARTED => [OtSurgerySchedule::STATUS_SURGERY_RUNNING, OtSurgerySchedule::STATUS_CANCELLED],
        OtSurgerySchedule::STATUS_SURGERY_RUNNING    => [OtSurgerySchedule::STATUS_SURGERY_COMPLETED],
        OtSurgerySchedule::STATUS_SURGERY_COMPLETED  => [OtSurgerySchedule::STATUS_IN_RECOVERY, OtSurgerySchedule::STATUS_TRANSFERRED_BACK, OtSurgerySchedule::STATUS_CLOSED],
        OtSurgerySchedule::STATUS_IN_RECOVERY        => [OtSurgerySchedule::STATUS_TRANSFERRED_BACK, OtSurgerySchedule::STATUS_CLOSED],
        OtSurgerySchedule::STATUS_TRANSFERRED_BACK   => [OtSurgerySchedule::STATUS_CLOSED],
        OtSurgerySchedule::STATUS_CLOSED             => [],
        OtSurgerySchedule::STATUS_CANCELLED          => [],
    ];

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', OtSurgerySchedule::STATUSES),
            'reason' => 'nullable|string|max:1000',
        ]);

        $schedule = OtSurgerySchedule::findOrFail($id);
        $from     = $schedule->status;
        $to       = $request->get('status');

        if ($from === $to) {
            return back()->with('error', 'Status is already ' . $from . '.');
        }

        $allowed = self::ALLOWED_STATUS_TRANSITIONS[$from] ?? [];
        if (! in_array($to, $allowed, true)) {
            return back()->with('error',
                "Invalid status transition: cannot move from {$from} to {$to}. " .
                "Use the phase-specific actions (e.g. Start Surgery, Complete Surgery) " .
                "instead of jumping statuses directly."
            );
        }

        $schedule->update(['status' => $to]);

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'status_changed',
            $from, $to, $request->get('reason')
        );

        return back()->with('success', "Status changed: {$from} → {$to}.");
    }
}
