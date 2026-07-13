<?php

namespace App\Observers;

use App\Models\Encounter\Encounter;
use App\Models\IpdPatient;
use Illuminate\Support\Facades\Log;

/**
 * Opens a unified Encounter row when an IPD admission is created.
 *
 * Bed-day charges accrue via the IpdPatientBed observer (separate file)
 * because that's where bed allocation actually happens.
 */
class IpdPatientObserver
{
    public function created(IpdPatient $ipd): void
    {
        try {
            $branchId = optional(auth()->user())->current_branch_id;
            $orgId = optional(auth()->user())->current_organization_id;

            $encounter = Encounter::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'patient_id' => $ipd->patient_id,
                'encounter_type' => 'IPD',
                'source' => $ipd->admission_type === 'emergency' ? 'emergency' : 'walk_in',
                'doctor_id' => $ipd->doctor_id,
                'department_id' => $ipd->department_id,
                'subject_type' => IpdPatient::class,
                'subject_id' => $ipd->id,
                'status' => 'open',
                'started_at' => $ipd->admission_date ?? now(),
            ]);

            $ipd->forceFill(['encounter_id' => $encounter->id])->saveQuietly();
        } catch (\Throwable $e) {
            Log::warning('IPD encounter creation failed', [
                'ipd_id' => $ipd->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Close the encounter when the IPD record is marked discharged.
     */
    public function updated(IpdPatient $ipd): void
    {
        if (! $ipd->wasChanged('status') && ! $ipd->wasChanged('discharge_date')) {
            return;
        }

        $isDischarged = strtolower((string) $ipd->status) === 'discharged' || $ipd->discharge_date;
        if (! $isDischarged || ! $ipd->encounter_id) {
            return;
        }

        Encounter::where('id', $ipd->encounter_id)
            ->whereNull('closed_at')
            ->update([
                'status' => 'closed',
                'closed_at' => $ipd->discharge_date ?? now(),
            ]);
    }
}
