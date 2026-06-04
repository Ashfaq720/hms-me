<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuEmergencyEvent;
use App\Models\Icu\IcuEmergencyEventAction;
use App\Models\Icu\IcuEmergencyNotification;
use App\Models\Icu\IcuEquipmentUsageLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IcuEmergencyController extends Controller
{
    public function show($admissionId, $eventId)
    {
        $admission = IcuAdmission::with('ipdPatient.bedAllocations.bed')->findOrFail($admissionId);
        $event     = IcuEmergencyEvent::with(['activatedBy', 'actions.performedBy', 'notifications', 'patient'])
            ->where('icu_admission_id', $admission->id)
            ->findOrFail($eventId);

        $staffUsers = User::select('id', 'name', 'type')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $ventilatorCount = IcuEquipmentUsageLog::where('icu_admission_id', $admission->id)
            ->where('equipment_type', 'Ventilator')
            ->where('status', 'InUse')
            ->count();

        $monitorCount = IcuEquipmentUsageLog::where('icu_admission_id', $admission->id)
            ->where('equipment_type', 'Monitor')
            ->where('status', 'InUse')
            ->count();

        $criticalAlertCount = IcuAlert::where('icu_admission_id', $admission->id)
            ->whereIn('status', ['Active', 'Acknowledged'])
            ->where('severity', 'Critical')
            ->count();

        return view('icu.emergency.show', compact(
            'admission', 'event', 'staffUsers',
            'ventilatorCount', 'monitorCount', 'criticalAlertCount'
        ));
    }

    /**
     * One-click Code Blue activation. Creates the event, fans out notifications
     * to the standard ICU roles, and surfaces a Critical alert on the dashboard.
     */
    public function activate(Request $request, $admissionId)
    {
        $request->validate([
            'event_type' => ['required', Rule::in([
                'CardiacArrest', 'RespiratoryArrest', 'SevereDesaturation',
                'SuddenCollapse', 'Seizure', 'Shock', 'Other',
            ])],
        ]);

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            // Block duplicate active events for the same admission
            $alreadyOpen = IcuEmergencyEvent::where('icu_admission_id', $admission->id)
                ->whereNotIn('status', ['Closed'])
                ->lockForUpdate()
                ->exists();
            if ($alreadyOpen) {
                throw new \RuntimeException('A Code Blue is already active for this patient.');
            }

            $event = IcuEmergencyEvent::create([
                'event_no'         => IcuEmergencyEvent::generateEventNo(),
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'event_type'       => $request->event_type,
                'activated_by'     => auth()->id() ?? 0,
                'activated_at'     => now(),
                'team_notified_at' => now(),
                'status'           => 'Activated',
            ]);

            // Standard ICU notification roles per BRD §5
            $roles = ['DutyDoctor', 'NurseInCharge', 'Anesthetist', 'EmergencyResponse', 'IcuAdmin'];
            foreach ($roles as $role) {
                IcuEmergencyNotification::create([
                    'event_id'          => $event->id,
                    'role'              => $role,
                    'notification_type' => 'Dashboard',
                    'sent_at'           => now(),
                    'status'            => 'Sent',
                    'created_at'        => now(),
                ]);
            }

            // Surface as a Critical alert on the dashboard
            IcuAlert::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'alert_type'       => 'CodeBlue',
                'severity'         => 'Critical',
                'message'          => "Code Blue activated ({$event->event_no}) — {$event->event_type}",
                'source_module'    => 'icu_emergency_events',
                'source_id'        => $event->id,
                'status'           => 'Active',
            ]);

            DB::commit();

            return redirect()
                ->route('icu.admissions.emergency.show', [$admission->id, $event->id])
                ->with('success', "Code Blue activated. Event: {$event->event_no}");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Code Blue activation failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Code Blue activation failed: ' . $e->getMessage());
        }
    }

    public function markFirstResponse(Request $request, $admissionId, $eventId)
    {
        return $this->stamp($request, $admissionId, $eventId, 'first_response_at', 'ResponseStarted', 'First response recorded');
    }

    public function markDoctorArrival(Request $request, $admissionId, $eventId)
    {
        return $this->stamp($request, $admissionId, $eventId, 'doctor_arrival_at', 'InProgress', 'Doctor arrival recorded');
    }

    public function markStabilized(Request $request, $admissionId, $eventId)
    {
        return $this->stampStatus($admissionId, $eventId, 'Stabilized');
    }

    public function addAction(Request $request, $admissionId, $eventId)
    {
        $request->validate([
            'action_name' => ['required', 'string', 'max:100'],
            'action_time' => ['nullable', 'date'],
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],
            'remarks'     => ['nullable', 'string', 'max:500'],
        ]);

        $event = $this->loadEvent($admissionId, $eventId);
        if ($event->status === 'Closed') {
            return back()->with('error', 'Cannot add action — event is closed.');
        }

        IcuEmergencyEventAction::create([
            'event_id'     => $event->id,
            'action_name'  => $request->action_name,
            'action_time'  => $request->action_time ?: now(),
            'performed_by' => $request->performed_by ?: auth()->id(),
            'remarks'      => $request->remarks,
            'created_at'   => now(),
        ]);

        return back()->with('success', 'Action recorded.');
    }

    public function close(Request $request, $admissionId, $eventId)
    {
        $request->validate([
            'outcome' => ['required', Rule::in([
                'Stabilized', 'TransferredToOT', 'TransferredToHigherCare', 'Expired', 'Referred',
            ])],
            'final_remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $admissionId, $eventId) {
            $event = $this->loadEvent($admissionId, $eventId);
            if ($event->status === 'Closed') {
                throw new \RuntimeException('Event already closed.');
            }

            $event->update([
                'status'        => 'Closed',
                'outcome'       => $request->outcome,
                'final_remarks' => $request->final_remarks,
                'closed_at'     => now(),
                'closed_by'     => auth()->id(),
            ]);

            // Auto-close the linked Code Blue alert
            IcuAlert::where('source_module', 'icu_emergency_events')
                ->where('source_id', $event->id)
                ->whereIn('status', ['Active', 'Acknowledged'])
                ->update([
                    'status'       => 'Closed',
                    'closed_by'    => auth()->id(),
                    'closed_at'    => now(),
                    'action_taken' => 'Code Blue closed: ' . $request->outcome,
                ]);
        });

        return back()->with('success', 'Code Blue event closed.');
    }

    // -------- Helpers --------

    protected function loadEvent($admissionId, $eventId): IcuEmergencyEvent
    {
        $event = IcuEmergencyEvent::lockForUpdate()->findOrFail($eventId);
        if ($event->icu_admission_id != $admissionId) {
            throw new \RuntimeException('Event does not belong to this admission.');
        }
        return $event;
    }

    protected function stamp(Request $request, $admissionId, $eventId, string $col, string $nextStatus, string $actionName)
    {
        $request->validate([
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($request, $admissionId, $eventId, $col, $nextStatus, $actionName) {
            $event = $this->loadEvent($admissionId, $eventId);
            if ($event->{$col}) {
                throw new \RuntimeException("{$col} already recorded.");
            }
            $when = now();
            $event->{$col} = $when;

            // Only advance status forward
            $order = ['Activated', 'TeamNotified', 'ResponseStarted', 'InProgress', 'Stabilized', 'Closed'];
            if (array_search($nextStatus, $order) > array_search($event->status, $order)) {
                $event->status = $nextStatus;
            }
            $event->save();

            IcuEmergencyEventAction::create([
                'event_id'     => $event->id,
                'action_name'  => $actionName,
                'action_time'  => $when,
                'performed_by' => $request->performed_by ?: auth()->id(),
                'created_at'   => now(),
            ]);
        });

        return back()->with('success', 'Recorded.');
    }

    protected function stampStatus($admissionId, $eventId, string $status)
    {
        DB::transaction(function () use ($admissionId, $eventId, $status) {
            $event = $this->loadEvent($admissionId, $eventId);
            $event->status = $status;
            $event->save();
        });

        return back()->with('success', "Status: {$status}");
    }
}
