<?php

namespace App\Policies\Ot;

use App\Models\Ot\OtSurgeryRequest;
use App\Models\User;

/**
 * OT Surgery Request authorization.
 *
 * Rules:
 *   • The requesting doctor (or their supervisor) can edit / withdraw / re-submit
 *     until the request reaches a terminal status.
 *   • OT Coordinators / Surgeons / Anesthetists can view ALL requests but only
 *     act on transitions when their role permits.
 *   • Patient role can only view their own request.
 *
 * Super Admin + Admin bypass via Gate::before().
 */
class OtSurgeryRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ot_surgery_request_access');
    }

    public function view(User $user, OtSurgeryRequest $req): bool
    {
        if ($user->can('ot_surgery_request_access')) return true;

        // The doctor who requested it can view it
        $doctor = $user->doctor ?? null;
        if ($doctor && $req->requested_by_doctor_id === $doctor->id) return true;

        // The primary surgeon can view their cases
        if ($doctor && $req->primary_surgeon_id === $doctor->id) return true;

        // The patient owner
        if ($user->hasRole('Patient') && optional($user->patient ?? null)->id === $req->patient_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('ot_surgery_request_create')
            || $user->hasAnyRole(['Doctor', 'Surgeon', 'OT Coordinator']);
    }

    /** Only editable in non-terminal statuses, and only by the requester or coordinator. */
    public function update(User $user, OtSurgeryRequest $req): bool
    {
        $editableStatuses = [
            OtSurgeryRequest::STATUS_DRAFT,
            OtSurgeryRequest::STATUS_SUBMITTED,
            OtSurgeryRequest::STATUS_PENDING_INFORMATION,
            OtSurgeryRequest::STATUS_SENT_BACK,
        ];
        if (! in_array($req->status, $editableStatuses, true)) return false;

        if ($user->can('ot_surgery_request_edit')) return true;

        $doctor = $user->doctor ?? null;
        if ($doctor && $req->requested_by_doctor_id === $doctor->id) return true;

        return false;
    }

    /** Only Drafts can be deleted, and only by the requester or coordinator. */
    public function delete(User $user, OtSurgeryRequest $req): bool
    {
        if ($req->status !== OtSurgeryRequest::STATUS_DRAFT) return false;
        if ($user->can('ot_surgery_request_delete')) return true;

        $doctor = $user->doctor ?? null;
        return $doctor && $req->requested_by_doctor_id === $doctor->id;
    }

    /* ───────── Status-transition gates (called from SurgeryRequestStatusController) ───────── */

    public function review(User $user, OtSurgeryRequest $req): bool
    {
        return $user->hasAnyRole(['OT Coordinator']) || $user->can('ot_surgery_request_review');
    }

    public function accept(User $user, OtSurgeryRequest $req): bool
    {
        return $this->review($user, $req);
    }

    public function reject(User $user, OtSurgeryRequest $req): bool
    {
        return $this->review($user, $req);
    }

    public function fastTrack(User $user, OtSurgeryRequest $req): bool
    {
        return $user->hasAnyRole(['OT Coordinator', 'ER Doctor']) || $user->can('ot_emergency_access');
    }

    public function juniorApprove(User $user, OtSurgeryRequest $req): bool
    {
        // Junior doctors with the explicit permission
        return $user->can('ot_surgery_request_junior_approve')
            || $user->hasRole('Doctor');
    }

    public function consultantApprove(User $user, OtSurgeryRequest $req): bool
    {
        return $user->can('ot_surgery_request_consultant_approve')
            || $user->hasRole('Surgeon');
    }
}
