<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtPreOpChecklist;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtTransfer;
use Illuminate\Http\Request;

class OtTransferController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_transfer_access');
        $transfers = OtTransfer::with(['schedule.surgeryRequest.patient', 'schedule.room'])
            ->latest()
            ->paginate(20);

        $readySchedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'room'])
            ->where('status', OtSurgerySchedule::STATUS_READY_FOR_OT)
            ->get();

        return view('ot.transfers.index', compact('transfers', 'readySchedules'));
    }

    public function initiate(Request $request, $scheduleId)
    {
        $request->validate([
            'direction' => 'required|in:to_ot,to_pacu,to_ward,to_icu,to_ccu',
            'from_location' => 'nullable|string|max:255',
            'to_location' => 'nullable|string|max:255',
            'porter_id' => 'nullable|integer',
            'nurse_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        if ($request->get('direction') === OtTransfer::DIRECTION_TO_OT) {
            $checklist = OtPreOpChecklist::where('surgery_schedule_id', $schedule->id)->first();

            if (! $checklist || ! $checklist->isReady()) {
                return back()->with('error', 'Pre-op checklist must be complete (or emergency override applied) before transferring to OT.');
            }
        }

        $transfer = OtTransfer::create([
            'surgery_schedule_id' => $schedule->id,
            'direction' => $request->get('direction'),
            'from_location' => $request->get('from_location'),
            'to_location' => $request->get('to_location'),
            'initiated_at' => now(),
            'porter_id' => $request->get('porter_id'),
            'nurse_id' => $request->get('nurse_id'),
            'status' => 'Initiated',
            'notes' => $request->get('notes'),
            'created_by' => auth()->id(),
        ]);

        if ($request->get('direction') === OtTransfer::DIRECTION_TO_OT) {
            $schedule->update(['status' => OtSurgerySchedule::STATUS_TRANSFER_STARTED]);
        }

        OtAuditLog::record('ot_transfer', $transfer->id, 'initiated', null, $request->get('direction'));

        return back()->with('success', 'Transfer initiated.');
    }

    public function arrive(Request $request, $transferId)
    {
        $transfer = OtTransfer::with('schedule')->findOrFail($transferId);
        $transfer->update([
            'arrived_at' => now(),
            'status' => 'Arrived',
        ]);

        if ($transfer->direction === OtTransfer::DIRECTION_TO_OT) {
            $transfer->schedule->update(['status' => OtSurgerySchedule::STATUS_PATIENT_RECEIVED]);
        } elseif (in_array($transfer->direction, [OtTransfer::DIRECTION_TO_WARD, OtTransfer::DIRECTION_TO_ICU, OtTransfer::DIRECTION_TO_CCU])) {
            $transfer->schedule->update(['status' => OtSurgerySchedule::STATUS_TRANSFERRED_BACK]);
        }

        OtAuditLog::record('ot_transfer', $transfer->id, 'arrived');

        return back()->with('success', 'Patient arrival marked.');
    }
}
