<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtCleaningLog;
use App\Models\Ot\OtRoom;
use Illuminate\Http\Request;

class OtCleaningController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_cleaning_access');
        $logs = OtCleaningLog::with(['room', 'schedule.surgeryRequest.patient'])
            ->latest()
            ->paginate(30);

        $rooms = OtRoom::active()->get();

        return view('ot.cleaning.index', compact('logs', 'rooms'));
    }

    public function start(Request $request, $roomId)
    {
        $room = OtRoom::findOrFail($roomId);

        $log = OtCleaningLog::create([
            'ot_room_id' => $room->id,
            'surgery_schedule_id' => $request->get('surgery_schedule_id'),
            'cleaning_type' => $request->get('cleaning_type', 'routine'),
            'started_at' => now(),
            'performed_by' => auth()->id(),
            'is_complete' => false,
        ]);

        app(\App\Services\Ot\OtRoomStateService::class)->transition(
            $room, \App\Services\Ot\OtRoomStateService::ST_CLEANING_IN_PROGRESS, 'cleaning started'
        );

        OtAuditLog::record('ot_cleaning', $log->id, 'started');

        return back()->with('success', 'Cleaning started.');
    }

    public function complete(Request $request, $logId)
    {
        $log = OtCleaningLog::with('room')->findOrFail($logId);

        // checklist is cast to array on the model — accept either an array
        // payload (checklist[item]=1) or skip if not provided.
        $checklist = $request->get('checklist');
        if (is_string($checklist)) {
            $decoded = json_decode($checklist, true);
            $checklist = is_array($decoded) ? $decoded : null;
        }

        $log->update([
            'completed_at' => now(),
            'verified_by' => auth()->id(),
            'is_complete' => true,
            'checklist' => $checklist,
            'remarks' => $request->get('remarks'),
        ]);

        if ($log->room) {
            app(\App\Services\Ot\OtRoomStateService::class)->transition(
                $log->room, \App\Services\Ot\OtRoomStateService::ST_AVAILABLE, 'cleaning completed'
            );
        }

        OtAuditLog::record('ot_cleaning', $log->id, 'completed');

        return back()->with('success', 'Cleaning completed. Room marked available.');
    }
}
