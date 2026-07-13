<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Patient-level authorization. Use via Gate / @can / authorize() — e.g.
 *
 *     $this->authorize('view', $patient);
 *     @can('update', $patient) ... @endcan
 *
 * Super Admin and Admin bypass every check via Gate::before() in
 * PermissionServiceProvider — so this only kicks in for other roles.
 */
class PatientPolicy
{
    /** Anyone with patient_view permission can browse the list. */
    public function viewAny(User $user): bool
    {
        return $user->can('patient_view') || $user->can('patient_access');
    }

    /**
     * View a single patient.
     * • Doctors: only if they're the attending doctor on any encounter
     * • Patient role: only their own profile
     * • Front desk / Reception / Cashier / Nurse: anyone with patient_view
     */
    public function view(User $user, Patient $patient): bool
    {
        if ($user->can('patient_view') || $user->can('patient_access')) {
            return true;
        }
        if ($user->hasRole('Doctor')) {
            return $this->isAttendingDoctor($user, $patient);
        }
        if ($user->hasRole('Patient')) {
            return optional($user->patient ?? null)->id === $patient->id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('patient_create');
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->can('patient_edit')) return true;
        // Doctors can update notes for their own patients
        if ($user->hasRole('Doctor') && $this->isAttendingDoctor($user, $patient)) return true;
        return false;
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patient_delete');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->can('patient_delete');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->can('patient_delete');
    }

    /** A patient seeing their own profile via /my-account-style routes. */
    public function viewSelf(User $user, Patient $patient): bool
    {
        return $user->hasRole('Patient')
            && optional($user->patient ?? null)->id === $patient->id;
    }

    /* ───────── Helpers ───────── */

    protected function isAttendingDoctor(User $user, Patient $patient): bool
    {
        $doctor = $user->doctor ?? null;
        if (! $doctor) return false;
        $dId = $doctor->id;

        return DB::table('i_p_d_patients')->where('patient_id', $patient->id)->where('doctor_id', $dId)->exists()
            || DB::table('opd_patients')->where('patient_id', $patient->id)->where('doctor_id', $dId)->exists()
            || DB::table('appointments')->where('patient_id', $patient->id)->where('doctor', $dId)->exists();
    }
}
