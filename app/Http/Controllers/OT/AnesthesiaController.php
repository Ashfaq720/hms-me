<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAnesthesiaRecord;
use App\Models\Ot\OtAnesthesiaType;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtSurgerySchedule;
use Illuminate\Http\Request;

class AnesthesiaController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_anesthesia_access');
        $schedules = OtSurgerySchedule::with(['surgeryRequest.patient', 'room', 'anesthesiaRecord'])
            ->whereIn('status', [
                OtSurgerySchedule::STATUS_PATIENT_RECEIVED,
                OtSurgerySchedule::STATUS_ANESTHESIA_STARTED,
                OtSurgerySchedule::STATUS_SURGERY_RUNNING,
                OtSurgerySchedule::STATUS_SURGERY_COMPLETED,
            ])
            ->orderBy('scheduled_start', 'desc')
            ->paginate(20);

        return view('ot.anesthesia.index', compact('schedules'));
    }

    public function show($scheduleId)
    {
        $schedule = OtSurgerySchedule::with(['surgeryRequest.patient', 'anesthesiaRecord.anesthesiaType'])
            ->findOrFail($scheduleId);

        $types = OtAnesthesiaType::where('is_active', true)->orderBy('name')->get();
        $record = $schedule->anesthesiaRecord ?? new OtAnesthesiaRecord(['surgery_schedule_id' => $schedule->id]);

        return view('ot.anesthesia.show', compact('schedule', 'record', 'types'));
    }

    public function store(Request $request, $scheduleId)
    {
        return $this->saveRecord($request, $scheduleId);
    }

    public function update(Request $request, $scheduleId)
    {
        return $this->saveRecord($request, $scheduleId);
    }

    protected function saveRecord(Request $request, $scheduleId)
    {
        $data = $request->validate([
            'anesthesia_type_id' => 'nullable|exists:ot_anesthesia_types,id',
            'anesthetist_id' => 'nullable|integer',
            'induction_time' => 'nullable|date',
            'recovery_time' => 'nullable|date|after_or_equal:induction_time',
            'pre_anesthesia_assessment' => 'nullable|string',
            'drugs_used' => 'nullable|string',
            'airway_management' => 'nullable|string',
            'intra_op_vitals' => 'nullable|array',
            'complications' => 'nullable|string',
            'post_anesthesia_notes' => 'nullable|string',
            'asa_grade' => 'nullable|string|max:10',
        ]);

        $record = OtAnesthesiaRecord::firstOrNew(['surgery_schedule_id' => $scheduleId]);
        $record->fill($data);
        $record->save();

        OtAuditLog::record('anesthesia_record', $record->id, 'updated');

        return back()->with('success', 'Anesthesia record saved.');
    }

    public function start(Request $request, $scheduleId)
    {
        $schedule = OtSurgerySchedule::findOrFail($scheduleId);

        if ($schedule->status !== OtSurgerySchedule::STATUS_PATIENT_RECEIVED) {
            return back()->with('error', 'Patient must be received in OT before starting anesthesia.');
        }

        $schedule->update(['status' => OtSurgerySchedule::STATUS_ANESTHESIA_STARTED]);

        $record = OtAnesthesiaRecord::firstOrNew(['surgery_schedule_id' => $scheduleId]);
        if (! $record->induction_time) {
            $record->induction_time = now();
        }
        $record->save();

        OtAuditLog::record(
            'surgery_schedule', $schedule->id, 'anesthesia_started',
            OtSurgerySchedule::STATUS_PATIENT_RECEIVED, OtSurgerySchedule::STATUS_ANESTHESIA_STARTED
        );

        return back()->with('success', 'Anesthesia started.');
    }
}
