<?php

namespace App\Http\Controllers\OT;

use App\Models\BloodBank\BloodGroup;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRequestEquipment;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgeryType;
use App\Models\Patient;
use App\Services\Ot\OtNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurgeryRequestController extends OtBaseController
{
    public function __construct(protected OtNotifier $notifier) {}

    public function index(Request $request)
    {
        $this->gate('ot_surgery_request_access');

        $query = OtSurgeryRequest::with(['patient', 'surgeryType', 'primarySurgeon', 'category'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($q) use ($search) {
                        $q->where('patient_name', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobileno', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->boolean('emergency_only')) {
            $query->where('is_emergency', true);
        }
        if ($request->boolean('pending_info_only')) {
            $query->where('status', OtSurgeryRequest::STATUS_PENDING_INFORMATION);
        }

        $requests = $query->paginate(20)->withQueryString();
        $statuses = OtSurgeryRequest::STATUSES;

        return view('ot.surgery-requests.index', compact('requests', 'statuses'));
    }

    public function create()
    {
        return view('ot.surgery-requests.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['created_by'] = auth()->id();
        $validated['status'] = $request->get('save_as') === 'submit'
            ? OtSurgeryRequest::STATUS_SUBMITTED
            : OtSurgeryRequest::STATUS_DRAFT;

        $this->checkDuplicate($validated);

        $surgeryRequest = DB::transaction(function () use ($request, $validated) {
            $req = OtSurgeryRequest::create($validated);
            $this->syncEquipments($req, $request->input('equipments', []));
            return $req;
        });

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'created',
            null, $surgeryRequest->status
        );

        if ($surgeryRequest->status === OtSurgeryRequest::STATUS_SUBMITTED) {
            $this->notifier->requestSubmitted($surgeryRequest->load('patient', 'surgeryType'));
            if ($surgeryRequest->is_emergency) {
                $this->notifier->emergencyRequestAlert($surgeryRequest);
            }
        }

        return redirect()
            ->route('ot.surgery-requests.show', $surgeryRequest->id)
            ->with('success', 'Surgery request created successfully.');
    }

    public function show($id)
    {
        $surgeryRequest = OtSurgeryRequest::with([
            'patient', 'surgeryType', 'category', 'primarySurgeon',
            'requestedByDoctor', 'reviewer', 'juniorApprover', 'consultantApprover',
            'createdBy', 'department', 'ipdAdmission',
            'schedules.room',
            'schedules.preOpChecklist',
            'schedules.transfers',
            'schedules.teamMembers',
            'schedules.anesthesiaRecord.anesthesiaType',
            'schedules.intraOpRecord',
            'schedules.consumableUsages',
            'schedules.postOpNote',
            'schedules.pacuRecord',
            'schedules.cleaningLogs',
            'documents.uploadedBy', 'equipments.equipment', 'bloodGroup',
        ])->findOrFail($id);

        // Audit trail for this request (last 20 events)
        $auditLogs = \App\Models\Ot\OtAuditLog::where('entity_type', 'surgery_request')
            ->where('entity_id', $surgeryRequest->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        // Completion checklist — fields that should be filled before submission
        $completionItems = $this->completionChecklist($surgeryRequest);

        // Next step suggestion
        $nextStep = $this->nextStepFor($surgeryRequest);

        return view('ot.surgery-requests.show', compact(
            'surgeryRequest', 'auditLogs', 'completionItems', 'nextStep'
        ));
    }

    /**
     * Required-data checklist for a Draft surgery request, so the doctor
     * sees exactly what's missing before they can submit confidently.
     */
    protected function completionChecklist(OtSurgeryRequest $r): array
    {
        return [
            ['label' => 'Patient linked',          'ok' => (bool) $r->patient_id],
            ['label' => 'Encounter type set',      'ok' => (bool) $r->encounter_type],
            ['label' => 'Primary diagnosis',       'ok' => filled($r->diagnosis)],
            ['label' => 'ICD-10 code',             'ok' => filled($r->icd_code)],
            ['label' => 'Surgery type / procedure','ok' => (bool) $r->surgery_type_id],
            ['label' => 'Required OT type',        'ok' => filled($r->required_ot_type)],
            ['label' => 'Primary surgeon assigned','ok' => (bool) $r->primary_surgeon_id],
            ['label' => 'Department set',          'ok' => (bool) $r->department_id],
            ['label' => 'Preferred date',          'ok' => (bool) $r->requested_surgery_date],
            ['label' => 'Estimated duration',      'ok' => (bool) $r->estimated_duration_minutes],
            ['label' => 'Priority chosen',         'ok' => filled($r->priority)],
            ['label' => 'Equipment listed',        'ok' => $r->equipments->count() > 0],
            ['label' => 'Blood arrangement decided', 'ok' => $r->blood_required !== null],
            ['label' => 'Emergency reason (if emergency)', 'ok' => ! $r->is_emergency || filled($r->emergency_reason)],
        ];
    }

    protected function nextStepFor(OtSurgeryRequest $r): array
    {
        return match ($r->status) {
            OtSurgeryRequest::STATUS_DRAFT => [
                'title' => 'Complete the form and submit',
                'desc'  => 'Fill any missing required fields, then click Submit to send to the OT coordinator.',
                'url'   => route('ot.surgery-requests.edit', $r->id),
                'label' => 'Fill missing fields',
                'icon'  => 'bi-pencil',
                'color' => 'warning',
            ],
            OtSurgeryRequest::STATUS_SUBMITTED => [
                'title' => 'Waiting for OT coordinator review',
                'desc'  => 'Coordinator will start review, then accept / send back / mark pending info / reject.',
                'url'   => null,
                'label' => null,
                'icon'  => 'bi-hourglass-split',
                'color' => 'info',
            ],
            OtSurgeryRequest::STATUS_UNDER_REVIEW => [
                'title' => 'Under review — coordinator action required',
                'desc'  => 'Use action buttons at the top: Accept / Send Back / Pending Info / Reject / Fast-Track.',
                'url'   => null,
                'label' => null,
                'icon'  => 'bi-clipboard-check',
                'color' => 'info',
            ],
            OtSurgeryRequest::STATUS_PENDING_INFORMATION, OtSurgeryRequest::STATUS_SENT_BACK => [
                'title' => 'Doctor — please update and resubmit',
                'desc'  => 'Open the request, address the coordinator note, then click Save & Resubmit.',
                'url'   => route('ot.surgery-requests.edit', $r->id),
                'label' => 'Edit & Resubmit',
                'icon'  => 'bi-pencil',
                'color' => 'warning',
            ],
            OtSurgeryRequest::STATUS_ACCEPTED, OtSurgeryRequest::STATUS_FAST_TRACKED, OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING => [
                'title' => 'Ready to schedule',
                'desc'  => 'Pick OT room, time, team and equipment.',
                'url'   => route('ot.schedules.create', ['request_id' => $r->id]),
                'label' => 'Create Schedule',
                'icon'  => 'bi-calendar-plus',
                'color' => 'success',
            ],
            OtSurgeryRequest::STATUS_SCHEDULED => [
                'title' => 'Scheduled — proceed through OT workflow',
                'desc'  => 'Use the workflow buttons below to continue (Pre-Op → Transfer → Anesthesia → Surgery → PACU → Billing).',
                'url'   => $r->activeSchedule ? route('ot.schedules.show', $r->activeSchedule->id) : null,
                'label' => 'Open Schedule',
                'icon'  => 'bi-calendar-check',
                'color' => 'primary',
            ],
            OtSurgeryRequest::STATUS_REJECTED, OtSurgeryRequest::STATUS_CANCELLED => [
                'title' => 'Closed — no further action',
                'desc'  => 'This request is terminal. See reason above.',
                'url'   => null,
                'label' => null,
                'icon'  => 'bi-x-circle',
                'color' => 'secondary',
            ],
            default => [
                'title' => 'Continue workflow',
                'desc'  => '',
                'url'   => null,
                'label' => null,
                'icon'  => 'bi-arrow-right',
                'color' => 'primary',
            ],
        };
    }

    public function edit($id)
    {
        $surgeryRequest = OtSurgeryRequest::with('equipments')->findOrFail($id);

        if (! in_array($surgeryRequest->status, [
            OtSurgeryRequest::STATUS_DRAFT,
            OtSurgeryRequest::STATUS_SUBMITTED,
            OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            OtSurgeryRequest::STATUS_SENT_BACK,
        ])) {
            return back()->with('error', "Cannot edit a request in status: {$surgeryRequest->status}");
        }

        return view('ot.surgery-requests.edit', array_merge(
            $this->formData(),
            ['surgeryRequest' => $surgeryRequest]
        ));
    }

    public function update(Request $request, $id)
    {
        $surgeryRequest = OtSurgeryRequest::findOrFail($id);

        if (! in_array($surgeryRequest->status, [
            OtSurgeryRequest::STATUS_DRAFT,
            OtSurgeryRequest::STATUS_SUBMITTED,
            OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            OtSurgeryRequest::STATUS_SENT_BACK,
        ])) {
            return back()->with('error', "Cannot edit a request in status: {$surgeryRequest->status}");
        }

        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($surgeryRequest, $validated, $request) {
            $surgeryRequest->update($validated);
            $this->syncEquipments($surgeryRequest, $request->input('equipments', []), true);
        });

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'updated',
            null, null, null, $validated
        );

        // "Save & Resubmit" — works from Draft, Pending Information, Sent Back
        if ($request->get('save_as') === 'submit' && in_array($surgeryRequest->status, [
            OtSurgeryRequest::STATUS_DRAFT,
            OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            OtSurgeryRequest::STATUS_SENT_BACK,
        ])) {
            $fromStatus = $surgeryRequest->status;
            $surgeryRequest->update(['status' => OtSurgeryRequest::STATUS_SUBMITTED]);
            OtAuditLog::record(
                'surgery_request', $surgeryRequest->id,
                $fromStatus === OtSurgeryRequest::STATUS_DRAFT ? 'submitted' : 'resubmitted',
                $fromStatus, OtSurgeryRequest::STATUS_SUBMITTED
            );

            // Notify on resubmit so coordinator picks it up again
            $surgeryRequest->load('patient', 'surgeryType');
            $this->notifier->requestSubmitted($surgeryRequest);
            if ($surgeryRequest->is_emergency) {
                $this->notifier->emergencyRequestAlert($surgeryRequest);
            }
        }

        return redirect()
            ->route('ot.surgery-requests.show', $surgeryRequest->id)
            ->with('success', 'Surgery request updated.');
    }

    public function destroy($id)
    {
        $surgeryRequest = OtSurgeryRequest::findOrFail($id);

        if ($surgeryRequest->status !== OtSurgeryRequest::STATUS_DRAFT) {
            return back()->with('error', 'Only Draft requests can be deleted.');
        }

        $surgeryRequest->delete();
        OtAuditLog::record('surgery_request', $surgeryRequest->id, 'deleted');

        return redirect()
            ->route('ot.surgery-requests.index')
            ->with('success', 'Surgery request deleted.');
    }

    // ===== Status transitions =====

    public function submit($id)
    {
        $result = $this->transition($id, OtSurgeryRequest::STATUS_DRAFT, OtSurgeryRequest::STATUS_SUBMITTED, 'submitted');
        $req = OtSurgeryRequest::with('patient', 'surgeryType')->find($id);
        if ($req && $req->status === OtSurgeryRequest::STATUS_SUBMITTED) {
            $this->notifier->requestSubmitted($req);
            if ($req->is_emergency) {
                $this->notifier->emergencyRequestAlert($req);
            }
        }
        return $result;
    }

    public function review($id)
    {
        return $this->transition($id, OtSurgeryRequest::STATUS_SUBMITTED, OtSurgeryRequest::STATUS_UNDER_REVIEW, 'review_started');
    }

    public function accept(Request $request, $id)
    {
        $surgeryRequest = OtSurgeryRequest::findOrFail($id);

        if (! $surgeryRequest->approvalsSatisfied()) {
            return back()->with('error', 'Hierarchical approvals required: junior and/or consultant approval still pending.');
        }

        $from = $surgeryRequest->status;
        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_ACCEPTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'accepted',
            $from, OtSurgeryRequest::STATUS_ACCEPTED, $request->get('notes')
        );

        return back()->with('success', 'Request accepted.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $surgeryRequest = OtSurgeryRequest::findOrFail($id);
        $from = $surgeryRequest->status;

        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_REJECTED,
            'rejection_reason' => $request->get('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'rejected',
            $from, OtSurgeryRequest::STATUS_REJECTED, $request->get('reason')
        );

        return back()->with('success', 'Request rejected.');
    }

    public function sendBack(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $surgeryRequest = OtSurgeryRequest::findOrFail($id);
        $from = $surgeryRequest->status;

        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_SENT_BACK,
            'rejection_reason' => $request->get('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'sent_back',
            $from, OtSurgeryRequest::STATUS_SENT_BACK, $request->get('reason')
        );

        return back()->with('success', 'Request sent back to requesting doctor for correction.');
    }

    public function pendingInfo(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $surgeryRequest = OtSurgeryRequest::findOrFail($id);
        $from = $surgeryRequest->status;

        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            'pending_info_reason' => $request->get('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'marked_pending_information',
            $from, OtSurgeryRequest::STATUS_PENDING_INFORMATION, $request->get('reason')
        );

        return back()->with('success', 'Request marked as pending information.');
    }

    public function fastTrack(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $surgeryRequest = OtSurgeryRequest::findOrFail($id);
        $from = $surgeryRequest->status;

        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_FAST_TRACKED,
            'is_emergency' => true,
            'priority' => 'Emergency',
            'emergency_reason' => $surgeryRequest->emergency_reason ?: $request->get('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'emergency_fast_tracked',
            $from, OtSurgeryRequest::STATUS_FAST_TRACKED, $request->get('reason')
        );

        $this->notifier->emergencyRequestAlert($surgeryRequest);

        return back()->with('success', 'Request fast-tracked. Surgical team has been alerted.');
    }

    public function moveToScheduling($id)
    {
        $surgeryRequest = OtSurgeryRequest::findOrFail($id);

        if (! in_array($surgeryRequest->status, [
            OtSurgeryRequest::STATUS_ACCEPTED,
            OtSurgeryRequest::STATUS_FAST_TRACKED,
        ])) {
            return back()->with('error', "Cannot move to scheduling from status: {$surgeryRequest->status}");
        }

        $from = $surgeryRequest->status;
        $surgeryRequest->update(['status' => OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING]);
        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'moved_to_scheduling',
            $from, OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING
        );

        return back()->with('success', 'Moved to scheduling.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $surgeryRequest = OtSurgeryRequest::findOrFail($id);
        $from = $surgeryRequest->status;

        $surgeryRequest->update([
            'status' => OtSurgeryRequest::STATUS_CANCELLED,
            'rejection_reason' => $request->get('reason'),
        ]);

        OtAuditLog::record(
            'surgery_request', $surgeryRequest->id, 'cancelled',
            $from, OtSurgeryRequest::STATUS_CANCELLED, $request->get('reason')
        );

        return back()->with('success', 'Request cancelled.');
    }

    // ===== Hierarchical approval (FR-13) =====

    public function juniorApprove(Request $request, $id)
    {
        $req = OtSurgeryRequest::findOrFail($id);

        if (! $req->junior_approval_required) {
            return back()->with('error', 'Junior approval is not required on this request.');
        }
        if ($req->junior_approved_at) {
            return back()->with('error', 'Junior approval already granted.');
        }

        $req->update([
            'junior_approved_by' => auth()->id(),
            'junior_approved_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $req->id, 'junior_approval_granted',
            null, null, $request->get('notes')
        );

        return back()->with('success', 'Junior approval recorded.');
    }

    public function consultantApprove(Request $request, $id)
    {
        $req = OtSurgeryRequest::findOrFail($id);

        if (! $req->consultant_approval_required) {
            return back()->with('error', 'Consultant approval is not required on this request.');
        }
        if ($req->consultant_approved_at) {
            return back()->with('error', 'Consultant approval already granted.');
        }
        if ($req->junior_approval_required && ! $req->junior_approved_at) {
            return back()->with('error', 'Junior approval must be granted before consultant approval.');
        }

        $req->update([
            'consultant_approved_by' => auth()->id(),
            'consultant_approved_at' => now(),
        ]);

        OtAuditLog::record(
            'surgery_request', $req->id, 'consultant_approval_granted',
            null, null, $request->get('notes')
        );

        return back()->with('success', 'Consultant approval recorded.');
    }

    // ===== Helpers =====

    protected function transition($id, $expected, $next, $action)
    {
        $surgeryRequest = OtSurgeryRequest::findOrFail($id);

        if ($surgeryRequest->status !== $expected) {
            return back()->with('error', "Cannot perform this action from status: {$surgeryRequest->status}");
        }

        $surgeryRequest->update(['status' => $next]);
        OtAuditLog::record('surgery_request', $surgeryRequest->id, $action, $expected, $next);

        return back()->with('success', 'Status updated.');
    }

    protected function formData(): array
    {
        return [
            'patients' => Patient::select('id', 'patient_name', 'mrn')->orderBy('patient_name')->get(),
            'doctors' => Doctor::select('id', 'name')->orderBy('name')->get(),
            'surgeryTypes' => OtSurgeryType::where('is_active', true)->orderBy('name')->get(),
            'categories' => OtSurgeryCategory::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'equipmentMaster' => OtEquipment::where('is_active', true)->orderBy('name')->get(),
            'otTypes' => OtSurgeryRequest::OT_TYPES,
            'bloodComponents' => OtSurgeryRequest::BLOOD_COMPONENTS,
            'bloodGroups' => BloodGroup::where('is_active', true)->orderBy('abo_group')->orderBy('rh_factor')->get(),
        ];
    }

    protected function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'encounter_type' => 'required|in:IPD,OPD,ER',
            'encounter_id' => 'nullable|integer',
            'ipd_admission_id' => 'nullable|integer',
            'surgery_type_id' => 'nullable|exists:ot_surgery_types,id',
            'surgery_category_id' => 'nullable|exists:ot_surgery_categories,id',
            'requested_by_doctor_id' => 'nullable|exists:doctors,id',
            'primary_surgeon_id' => 'nullable|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'requested_surgery_date' => 'nullable|date',
            'requested_surgery_time' => 'nullable',
            'estimated_duration_minutes' => 'nullable|integer|min:5|max:1440',
            'date_flexibility' => 'nullable|in:Fixed,Flexible',
            'flexibility_reason' => 'nullable|string',
            'required_ot_type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:Low,Normal,High,Emergency',
            'is_emergency' => 'nullable|boolean',
            'emergency_reason' => 'nullable|string|required_if:is_emergency,1|required_if:priority,Emergency',
            'is_life_threatening' => 'nullable|boolean',
            'is_immediate_ot' => 'nullable|boolean',
            'diagnosis' => 'required|string',
            'secondary_diagnosis' => 'nullable|string',
            'icd_code' => 'nullable|string|max:20',
            'procedure_notes' => 'nullable|string',
            'clinical_indication' => 'nullable|string',
            'asa_grade' => 'nullable|string|max:10',
            'special_requirements' => 'nullable|string',
            'blood_required' => 'nullable|boolean',
            'blood_units' => 'nullable|integer|min:0|max:20',
            'blood_group' => 'nullable|string|max:10',
            'blood_group_id' => 'nullable|exists:blood_groups,id',
            'blood_components' => 'nullable|array',
            'blood_components.*' => 'string|in:' . implode(',', OtSurgeryRequest::BLOOD_COMPONENTS),
            'crossmatch_required' => 'nullable|boolean',
            'blood_bank_instruction' => 'nullable|string',
            'junior_approval_required' => 'nullable|boolean',
            'consultant_approval_required' => 'nullable|boolean',
        ]);

        // Preferred date cannot be earlier than today, except emergency
        if (! empty($validated['requested_surgery_date']) && empty($validated['is_emergency'])) {
            if (\Carbon\Carbon::parse($validated['requested_surgery_date'])->isPast()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'requested_surgery_date' => 'Preferred date cannot be earlier than today (unless emergency).',
                ]);
            }
        }

        return $validated;
    }

    /**
     * FR-15 duplicate-request guard. Warn admin if an active request for
     * same patient + encounter + surgery type already exists.
     */
    protected function checkDuplicate(array $data): void
    {
        if (request()->boolean('duplicate_override')) {
            return;
        }

        $exists = OtSurgeryRequest::where('patient_id', $data['patient_id'])
            ->where('encounter_type', $data['encounter_type'])
            ->where('surgery_type_id', $data['surgery_type_id'] ?? null)
            ->whereNotIn('status', [
                OtSurgeryRequest::STATUS_REJECTED,
                OtSurgeryRequest::STATUS_CANCELLED,
                OtSurgeryRequest::STATUS_SCHEDULED,
            ])
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'duplicate' => 'Active surgery request already exists for this patient + surgery type. Add ?duplicate_override=1 to bypass.',
            ]);
        }
    }

    protected function syncEquipments(OtSurgeryRequest $req, array $rows, bool $replace = false): void
    {
        if ($replace) {
            $req->equipments()->delete();
        }
        foreach ($rows as $row) {
            if (empty($row['equipment_name'])) continue;
            OtRequestEquipment::create([
                'surgery_request_id' => $req->id,
                'ot_equipment_id' => $row['ot_equipment_id'] ?? null,
                'equipment_name' => $row['equipment_name'],
                'quantity' => (int) ($row['quantity'] ?? 1),
                'is_mandatory' => ! empty($row['is_mandatory']),
                'setup_instruction' => $row['setup_instruction'] ?? null,
            ]);
        }
    }
}
