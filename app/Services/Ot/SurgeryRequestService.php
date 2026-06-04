<?php

namespace App\Services\Ot;

use App\Models\Ot\OtAuditLog;
use App\Models\Ot\OtEquipment;
use App\Models\Ot\OtRequestEquipment;
use App\Models\Ot\OtSurgeryRequest;
use App\Services\Ot\OtNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SurgeryRequestService — all business logic for OT surgery requests.
 *
 * Controllers stay thin. This class owns:
 *   • create / update (with equipment sync + duplicate guard + notifications)
 *   • state transitions (submit, review, accept, reject, send-back, pending-info,
 *                        fast-track, move-to-scheduling, cancel)
 *   • hierarchical approvals (junior, consultant)
 *   • equipment synchronization
 */
class SurgeryRequestService
{
    public function __construct(protected OtNotifier $notifier) {}

    /* ════════════════════════ Create / Update ════════════════════════ */

    public function create(array $data, ?int $createdBy, string $saveAs, array $equipmentRows = []): OtSurgeryRequest
    {
        $data = $this->normalize($data);
        $this->guardDuplicate($data);

        $data['created_by'] = $createdBy;
        $data['status']     = $saveAs === 'submit'
            ? OtSurgeryRequest::STATUS_SUBMITTED
            : OtSurgeryRequest::STATUS_DRAFT;

        $req = DB::transaction(function () use ($data, $equipmentRows) {
            $req = OtSurgeryRequest::create($data);
            $this->syncEquipments($req, $equipmentRows);
            return $req;
        });

        OtAuditLog::record('surgery_request', $req->id, 'created', null, $req->status);

        if ($req->status === OtSurgeryRequest::STATUS_SUBMITTED) {
            $this->notifier->requestSubmitted($req->load('patient', 'surgeryType'));
            if ($req->is_emergency) {
                $this->notifier->emergencyRequestAlert($req);
            }
        }

        return $req;
    }

    public function update(OtSurgeryRequest $req, array $data, array $equipmentRows, string $saveAs): OtSurgeryRequest
    {
        $data = $this->normalize($data);

        DB::transaction(function () use ($req, $data, $equipmentRows) {
            $req->update($data);
            $this->syncEquipments($req, $equipmentRows, replace: true);
        });

        OtAuditLog::record('surgery_request', $req->id, 'updated', null, null, null, $data);

        $resubmitFrom = [
            OtSurgeryRequest::STATUS_DRAFT,
            OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            OtSurgeryRequest::STATUS_SENT_BACK,
        ];
        if ($saveAs === 'submit' && in_array($req->status, $resubmitFrom, true)) {
            $from = $req->status;
            $req->update(['status' => OtSurgeryRequest::STATUS_SUBMITTED]);
            OtAuditLog::record(
                'surgery_request', $req->id,
                $from === OtSurgeryRequest::STATUS_DRAFT ? 'submitted' : 'resubmitted',
                $from, OtSurgeryRequest::STATUS_SUBMITTED
            );
            $req->load('patient', 'surgeryType');
            $this->notifier->requestSubmitted($req);
            if ($req->is_emergency) {
                $this->notifier->emergencyRequestAlert($req);
            }
        }

        return $req->fresh();
    }

    /* ════════════════════════ Status transitions ════════════════════════ */

    public function submit(OtSurgeryRequest $req): array
    {
        $result = $this->transition($req, OtSurgeryRequest::STATUS_DRAFT, OtSurgeryRequest::STATUS_SUBMITTED, 'submitted');
        if ($result['ok'] && $req->fresh()->status === OtSurgeryRequest::STATUS_SUBMITTED) {
            $req->load('patient', 'surgeryType');
            $this->notifier->requestSubmitted($req);
            if ($req->is_emergency) {
                $this->notifier->emergencyRequestAlert($req);
            }
        }
        return $result;
    }

    public function review(OtSurgeryRequest $req): array
    {
        return $this->transition($req, OtSurgeryRequest::STATUS_SUBMITTED, OtSurgeryRequest::STATUS_UNDER_REVIEW, 'review_started');
    }

    public function accept(OtSurgeryRequest $req, ?int $userId, ?string $notes = null): array
    {
        if (! $req->approvalsSatisfied()) {
            return ['ok' => false, 'message' => 'Hierarchical approvals required: junior and/or consultant approval still pending.'];
        }
        $from = $req->status;
        $req->update([
            'status'      => OtSurgeryRequest::STATUS_ACCEPTED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'accepted', $from, OtSurgeryRequest::STATUS_ACCEPTED, $notes);
        return ['ok' => true, 'message' => 'Request accepted.'];
    }

    public function reject(OtSurgeryRequest $req, ?int $userId, string $reason): array
    {
        $from = $req->status;
        $req->update([
            'status'           => OtSurgeryRequest::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by'      => $userId,
            'reviewed_at'      => now(),
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'rejected', $from, OtSurgeryRequest::STATUS_REJECTED, $reason);
        return ['ok' => true, 'message' => 'Request rejected.'];
    }

    public function sendBack(OtSurgeryRequest $req, ?int $userId, string $reason): array
    {
        $from = $req->status;
        $req->update([
            'status'           => OtSurgeryRequest::STATUS_SENT_BACK,
            'rejection_reason' => $reason,
            'reviewed_by'      => $userId,
            'reviewed_at'      => now(),
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'sent_back', $from, OtSurgeryRequest::STATUS_SENT_BACK, $reason);
        return ['ok' => true, 'message' => 'Request sent back to requesting doctor for correction.'];
    }

    public function pendingInfo(OtSurgeryRequest $req, ?int $userId, string $reason): array
    {
        $from = $req->status;
        $req->update([
            'status'              => OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            'pending_info_reason' => $reason,
            'reviewed_by'         => $userId,
            'reviewed_at'         => now(),
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'marked_pending_information', $from, OtSurgeryRequest::STATUS_PENDING_INFORMATION, $reason);
        return ['ok' => true, 'message' => 'Request marked as pending information.'];
    }

    public function fastTrack(OtSurgeryRequest $req, ?int $userId, string $reason): array
    {
        $from = $req->status;
        $req->update([
            'status'           => OtSurgeryRequest::STATUS_FAST_TRACKED,
            'is_emergency'     => true,
            'priority'         => 'Emergency',
            'emergency_reason' => $req->emergency_reason ?: $reason,
            'reviewed_by'      => $userId,
            'reviewed_at'      => now(),
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'emergency_fast_tracked', $from, OtSurgeryRequest::STATUS_FAST_TRACKED, $reason);
        $this->notifier->emergencyRequestAlert($req);
        return ['ok' => true, 'message' => 'Request fast-tracked. Surgical team has been alerted.'];
    }

    public function moveToScheduling(OtSurgeryRequest $req): array
    {
        if (! in_array($req->status, [OtSurgeryRequest::STATUS_ACCEPTED, OtSurgeryRequest::STATUS_FAST_TRACKED])) {
            return ['ok' => false, 'message' => "Cannot move to scheduling from status: {$req->status}"];
        }
        $from = $req->status;
        $req->update(['status' => OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING]);
        OtAuditLog::record('surgery_request', $req->id, 'moved_to_scheduling', $from, OtSurgeryRequest::STATUS_MOVED_TO_SCHEDULING);
        return ['ok' => true, 'message' => 'Moved to scheduling.'];
    }

    public function cancel(OtSurgeryRequest $req, string $reason): array
    {
        $from = $req->status;
        $req->update([
            'status'           => OtSurgeryRequest::STATUS_CANCELLED,
            'rejection_reason' => $reason,
        ]);
        OtAuditLog::record('surgery_request', $req->id, 'cancelled', $from, OtSurgeryRequest::STATUS_CANCELLED, $reason);
        return ['ok' => true, 'message' => 'Request cancelled.'];
    }

    /* ════════════════════════ Hierarchical approvals (FR-13) ════════════════════════ */

    public function juniorApprove(OtSurgeryRequest $req, ?int $userId, ?string $notes = null): array
    {
        if (! $req->junior_approval_required) {
            return ['ok' => false, 'message' => 'Junior approval is not required on this request.'];
        }
        if ($req->junior_approved_at) {
            return ['ok' => false, 'message' => 'Junior approval already granted.'];
        }
        $req->update(['junior_approved_by' => $userId, 'junior_approved_at' => now()]);
        OtAuditLog::record('surgery_request', $req->id, 'junior_approval_granted', null, null, $notes);
        return ['ok' => true, 'message' => 'Junior approval recorded.'];
    }

    public function consultantApprove(OtSurgeryRequest $req, ?int $userId, ?string $notes = null): array
    {
        if (! $req->consultant_approval_required) {
            return ['ok' => false, 'message' => 'Consultant approval is not required on this request.'];
        }
        if ($req->consultant_approved_at) {
            return ['ok' => false, 'message' => 'Consultant approval already granted.'];
        }
        if ($req->junior_approval_required && ! $req->junior_approved_at) {
            return ['ok' => false, 'message' => 'Junior approval must be granted before consultant approval.'];
        }
        $req->update(['consultant_approved_by' => $userId, 'consultant_approved_at' => now()]);
        OtAuditLog::record('surgery_request', $req->id, 'consultant_approval_granted', null, null, $notes);
        return ['ok' => true, 'message' => 'Consultant approval recorded.'];
    }

    /* ════════════════════════ Equipment sync ════════════════════════ */

    public function syncEquipments(OtSurgeryRequest $req, array $rows, bool $replace = false): void
    {
        if ($replace) {
            $req->equipments()->delete();
        }
        foreach ($rows as $row) {
            $otEquipmentId = $row['ot_equipment_id'] ?? null;
            $equipmentName = trim($row['equipment_name'] ?? '');

            if ($otEquipmentId && $equipmentName === '') {
                $equipmentName = (string) OtEquipment::whereKey($otEquipmentId)->value('name');
            }
            if (! $otEquipmentId && $equipmentName === '') continue;

            OtRequestEquipment::create([
                'surgery_request_id' => $req->id,
                'ot_equipment_id'    => $otEquipmentId ?: null,
                'equipment_name'     => $equipmentName,
                'quantity'           => (int) ($row['quantity'] ?? 1),
                'is_mandatory'       => ! empty($row['is_mandatory']),
                'setup_instruction'  => $row['setup_instruction'] ?? null,
            ]);
        }
    }

    /* ════════════════════════ Helpers ════════════════════════ */

    /** Generic linear transition: only fires if current status === $expected. */
    protected function transition(OtSurgeryRequest $req, string $expected, string $next, string $action): array
    {
        if ($req->status !== $expected) {
            return ['ok' => false, 'message' => "Cannot perform this action from status: {$req->status}"];
        }
        $req->update(['status' => $next]);
        OtAuditLog::record('surgery_request', $req->id, $action, $expected, $next);
        return ['ok' => true, 'message' => 'Status updated.'];
    }

    /** Drop empty hidden-marker entries from blood_components chip-picker. */
    protected function normalize(array $data): array
    {
        if (isset($data['blood_components'])) {
            $data['blood_components'] = array_values(array_filter(
                $data['blood_components'],
                fn ($v) => is_string($v) && $v !== ''
            ));
        }
        return $data;
    }

    /** FR-15 duplicate-request guard. */
    protected function guardDuplicate(array $data): void
    {
        if (request()->boolean('duplicate_override')) return;

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
            throw ValidationException::withMessages([
                'duplicate' => 'Active surgery request already exists for this patient + surgery type. Add ?duplicate_override=1 to bypass.',
            ]);
        }
    }
}
