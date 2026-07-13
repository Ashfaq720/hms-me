<?php

namespace App\Observers;

use App\Models\Encounter\Encounter;
use App\Models\OpdPatient;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Facades\Log;

/**
 * When an OPD visit is created:
 *   1. Open a unified Encounter row (so cross-module reporting works).
 *   2. Auto-post the consultation service charge.
 *
 * Both steps are best-effort: failures are logged but never block the
 * existing front-desk workflow.
 */
class OpdPatientObserver
{
    public function __construct(private ServiceChargeEngine $serviceCharge)
    {
    }

    public function created(OpdPatient $opd): void
    {
        $encounter = $this->ensureEncounter($opd);
        $this->autoPostConsultation($opd, $encounter);
    }

    private function ensureEncounter(OpdPatient $opd): ?Encounter
    {
        try {
            $branchId = optional(auth()->user())->current_branch_id;
            $orgId = optional(auth()->user())->current_organization_id;

            $encounter = Encounter::create([
                'organization_id' => $orgId,
                'branch_id' => $branchId,
                'patient_id' => $opd->patient_id,
                'encounter_type' => 'OPD',
                'source' => $opd->visit_type === 'appointment' ? 'appointment' : 'walk_in',
                'doctor_id' => $opd->doctor_id,
                'department_id' => $opd->department_id,
                'subject_type' => OpdPatient::class,
                'subject_id' => $opd->id,
                'status' => 'open',
                'chief_complaint' => $opd->chief_complaint,
                'started_at' => $opd->date ?? now(),
            ]);

            $opd->forceFill(['encounter_id' => $encounter->id])->saveQuietly();
            return $encounter;
        } catch (\Throwable $e) {
            Log::warning('OPD encounter creation failed', [
                'opd_id' => $opd->id,
                'reason' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function autoPostConsultation(OpdPatient $opd, ?Encounter $encounter): void
    {
        try {
            $this->serviceCharge->post([
                'service_code' => 'CONSULT_OPD',
                'encounter' => $encounter,
                'patient_id' => $opd->patient_id,
                'trigger_event' => 'opd.visit.created',
                'trigger_source' => $opd,
                'quantity' => 1,
                'reason' => 'OPD visit created (auto)',
                'metadata' => [
                    'doctor_id' => $opd->doctor_id,
                    'token_no' => $opd->token_no,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::info('OPD consultation auto-post skipped — catalog missing', [
                'opd_id' => $opd->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }
}
