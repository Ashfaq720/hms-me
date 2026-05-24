<?php

namespace App\Http\Controllers\OT;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtRoom;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgerySchedule;
use App\Models\Ot\OtSurgeryType;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmergencyOtController extends OtBaseController
{
    public function index(Request $request)
    {
        $this->gate('ot_emergency_access');
        $emergencies = OtSurgerySchedule::with(['surgeryRequest.patient', 'room'])
            ->where('emergency_fast_track', true)
            ->latest()
            ->paginate(20);

        return view('ot.emergency.index', compact('emergencies'));
    }

    public function create()
    {
        $patients = Patient::select('id', 'patient_name', 'mrn')->orderBy('patient_name')->get();
        $surgeons = Doctor::orderBy('name')->get();
        $surgeryTypes = OtSurgeryType::where('is_active', true)->orderBy('name')->get();
        $rooms = OtRoom::active()->where('is_emergency', true)->orderBy('name')->get();

        return view('ot.emergency.create', compact('patients', 'surgeons', 'surgeryTypes', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'encounter_type' => 'required|in:IPD,OPD,ER',
            'surgery_type_id' => 'nullable|exists:ot_surgery_types,id',
            'primary_surgeon_id' => 'nullable|exists:doctors,id',
            'ot_room_id' => 'required|exists:ot_rooms,id',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'diagnosis' => 'nullable|string',
            'clinical_indication' => 'required|string',
            'reason' => 'required|string',
        ]);

        $schedule = DB::transaction(function () use ($data) {
            $req = OtSurgeryRequest::create([
                'patient_id' => $data['patient_id'],
                'encounter_type' => $data['encounter_type'],
                'surgery_type_id' => $data['surgery_type_id'] ?? null,
                'primary_surgeon_id' => $data['primary_surgeon_id'] ?? null,
                'requested_surgery_date' => $data['scheduled_start'],
                'priority' => 'Emergency',
                'is_emergency' => true,
                'diagnosis' => $data['diagnosis'] ?? null,
                'clinical_indication' => $data['clinical_indication'],
                'status' => OtSurgeryRequest::STATUS_SCHEDULED,
                'created_by' => auth()->id(),
            ]);

            $schedule = OtSurgerySchedule::create([
                'surgery_request_id' => $req->id,
                'ot_room_id' => $data['ot_room_id'],
                'scheduled_start' => $data['scheduled_start'],
                'scheduled_end' => $data['scheduled_end'],
                'emergency_fast_track' => true,
                'status' => OtSurgerySchedule::STATUS_SCHEDULED,
                'created_by' => auth()->id(),
            ]);

            OtAuditLog::record(
                'surgery_request', $req->id, 'emergency_created',
                null, $req->status, $data['reason']
            );
            OtAuditLog::record(
                'surgery_schedule', $schedule->id, 'emergency_created',
                null, $schedule->status, $data['reason']
            );

            return $schedule;
        });

        return redirect()
            ->route('ot.schedules.show', $schedule->id)
            ->with('success', 'Emergency surgery booked. Fast-track approval recorded.');
    }

    public function approve(Request $request, $id)
    {
        $schedule = OtSurgerySchedule::findOrFail($id);
        $schedule->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        OtAuditLog::record('surgery_schedule', $schedule->id, 'emergency_approved');

        return back()->with('success', 'Emergency case approved.');
    }
}
