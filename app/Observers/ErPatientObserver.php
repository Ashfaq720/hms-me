<?php

namespace App\Observers;

use App\Models\Encounter\Encounter;
use App\Models\FrontDesk\ErPatient;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Facades\Log;

/**
 * When an ER patient is registered:
 *   1. Open a unified Encounter row (encounter_type='ER').
 *   2. Auto-post the ER triage / registration fee.
 *
 * Both steps are best-effort — failures log but never break the existing
 * front-desk registration flow.
 */
class ErPatientObserver
{
    public function __construct(private ServiceChargeEngine $serviceCharge)
    {
    }

    public function created(ErPatient $er): void
    {
        $encounter = $this->ensureEncounter($er);
        $this->autoPostTriage($er, $encounter);
    }

    private function ensureEncounter(ErPatient $er): ?Encounter
    {
        try {
            if ($er->encounter_id) {
                return Encounter::find($er->encounter_id);
            }
            $branchId = $er->branch_id ?? optional(auth()->user())->current_branch_id;
            $orgId = $er->organization_id ?? optional(auth()->user())->current_organization_id;

            $encounter = Encounter::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'patient_id' => $er->patient_id,
                'encounter_type' => 'ER',
                'source' => 'walk_in',
                'doctor_id' => $er->doctor_id,
                'department_id' => $er->department_id,
                'subject_type' => ErPatient::class,
                'subject_id' => $er->id,
                'status' => 'open',
                'chief_complaint' => $er->description,
                'started_at' => $er->arrival_time ?? now(),
            ]);

            $er->forceFill(['encounter_id' => $encounter->id])->saveQuietly();
            return $encounter;
        } catch (\Throwable $e) {
            Log::warning('ER encounter creation failed', [
                'er_id' => $er->id,
                'reason' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function autoPostTriage(ErPatient $er, ?Encounter $encounter): void
    {
        if (! $encounter) return;
        try {
            $this->serviceCharge->post([
                'service_code' => 'ADMIN_REG',     // ER triage/reg fee (fallback to registration catalog)
                'encounter' => $encounter,
                'patient_id' => $er->patient_id,
                'trigger_event' => 'er.registration',
                'trigger_source' => $er,
                'quantity' => 1,
                'reason' => 'ER arrival fee (auto)',
                'metadata' => ['priority' => $er->priority],
            ]);
        } catch (\Throwable $e) {
            Log::info('ER auto-post skipped — catalog missing', [
                'er_id' => $er->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }
}
