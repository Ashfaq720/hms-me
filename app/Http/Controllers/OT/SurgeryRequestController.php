<?php

namespace App\Http\Controllers\OT;

use App\Http\Requests\OT\StoreSurgeryRequest;
use App\Http\Requests\OT\UpdateSurgeryRequest;
use App\Models\BloodBank\BloodGroup;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryRequest;
use App\Models\Ot\OtSurgeryType;
use App\Models\Patient;
use App\Services\Ot\SurgeryRequestService;
use Illuminate\Http\Request;

/**
 * Thin controller — CRUD + presentation only.
 *
 * Business logic moved to:   App\Services\Ot\SurgeryRequestService
 * State transitions moved to: App\Http\Controllers\OT\SurgeryRequestStatusController
 * Validation moved to:       App\Http\Requests\OT\StoreSurgeryRequest / UpdateSurgeryRequest
 */
class SurgeryRequestController extends OtBaseController
{
    public function __construct(protected SurgeryRequestService $service) {}

    public function index(Request $request)
    {
        $this->gate('ot_surgery_request_access');

        $query = OtSurgeryRequest::with(['patient', 'surgeryType', 'primarySurgeon', 'category'])
            ->latest();

        if ($status = $request->get('status'))     $query->where('status', $status);
        if ($priority = $request->get('priority')) $query->where('priority', $priority);

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
        if ($request->boolean('emergency_only'))    $query->where('is_emergency', true);
        if ($request->boolean('pending_info_only')) $query->where('status', OtSurgeryRequest::STATUS_PENDING_INFORMATION);

        $requests = $query->paginate(20)->withQueryString();
        $statuses = OtSurgeryRequest::STATUSES;

        return view('ot.surgery-requests.index', compact('requests', 'statuses'));
    }

    public function create()
    {
        return view('ot.surgery-requests.create', $this->formData());
    }

    public function store(StoreSurgeryRequest $request)
    {
        $req = $this->service->create(
            $request->validated(),
            auth()->id(),
            $request->get('save_as', 'draft'),
            $request->input('equipments', [])
        );

        return redirect()
            ->route('ot.surgery-requests.show', $req->id)
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

        $auditLogs = OtAuditLog::where('entity_type', 'surgery_request')
            ->where('entity_id', $surgeryRequest->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $completionItems = $this->completionChecklist($surgeryRequest);
        $nextStep        = $this->nextStepFor($surgeryRequest);

        return view('ot.surgery-requests.show', compact(
            'surgeryRequest', 'auditLogs', 'completionItems', 'nextStep'
        ));
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

    public function update(UpdateSurgeryRequest $request, $id)
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

        $this->service->update(
            $surgeryRequest,
            $request->validated(),
            $request->input('equipments', []),
            $request->get('save_as', 'draft')
        );

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

    /* ──────── Presentation helpers (used only by show()) ──────── */

    protected function completionChecklist(OtSurgeryRequest $r): array
    {
        return [
            ['label' => 'Patient linked',                  'ok' => (bool) $r->patient_id],
            ['label' => 'Encounter type set',              'ok' => (bool) $r->encounter_type],
            ['label' => 'Primary diagnosis',               'ok' => filled($r->diagnosis)],
            ['label' => 'ICD-10 code',                     'ok' => filled($r->icd_code)],
            ['label' => 'Surgery type / procedure',        'ok' => (bool) $r->surgery_type_id],
            ['label' => 'Required OT type',                'ok' => filled($r->required_ot_type)],
            ['label' => 'Primary surgeon assigned',        'ok' => (bool) $r->primary_surgeon_id],
            ['label' => 'Department set',                  'ok' => (bool) $r->department_id],
            ['label' => 'Preferred date',                  'ok' => (bool) $r->requested_surgery_date],
            ['label' => 'Estimated duration',              'ok' => (bool) $r->estimated_duration_minutes],
            ['label' => 'Priority chosen',                 'ok' => filled($r->priority)],
            ['label' => 'Equipment listed',                'ok' => $r->equipments->count() > 0],
            ['label' => 'Blood arrangement decided',       'ok' => $r->blood_required !== null],
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
                'url'   => null, 'label' => null, 'icon'  => 'bi-hourglass-split', 'color' => 'info',
            ],
            OtSurgeryRequest::STATUS_UNDER_REVIEW => [
                'title' => 'Under review — coordinator action required',
                'desc'  => 'Use action buttons at the top: Accept / Send Back / Pending Info / Reject / Fast-Track.',
                'url'   => null, 'label' => null, 'icon'  => 'bi-clipboard-check', 'color' => 'info',
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
                'url'   => null, 'label' => null, 'icon'  => 'bi-x-circle', 'color' => 'secondary',
            ],
            default => [
                'title' => 'Continue workflow', 'desc' => '',
                'url'   => null, 'label' => null, 'icon' => 'bi-arrow-right', 'color' => 'primary',
            ],
        };
    }

    /** Shared dropdown data for create/edit form. */
    protected function formData(): array
    {
        return [
            'patients'        => Patient::select('id', 'patient_name', 'mrn')->orderBy('patient_name')->get(),
            'doctors'         => Doctor::select('id', 'name')->orderBy('name')->get(),
            'surgeryTypes'    => OtSurgeryType::where('is_active', true)->orderBy('name')->get(),
            'categories'      => OtSurgeryCategory::where('is_active', true)->orderBy('name')->get(),
            'departments'     => Department::orderBy('name')->get(),
            'equipmentMaster' => OtEquipment::where('is_active', true)->orderBy('name')->get(),
            'otTypes'         => OtSurgeryRequest::OT_TYPES,
            'bloodComponents' => OtSurgeryRequest::BLOOD_COMPONENTS,
            'bloodGroups'     => BloodGroup::where('is_active', true)->orderBy('abo_group')->orderBy('rh_factor')->get(),
        ];
    }
}
