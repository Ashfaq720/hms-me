<?php

namespace App\Observers;

use App\Models\Nicu\NicuProcedure;
use App\Services\ServiceCharge\ServiceChargeEngine;
use Illuminate\Support\Facades\Log;

class NicuProcedureObserver
{
    public function created(NicuProcedure $procedure): void
    {
        $this->maybePost($procedure);
    }

    public function updated(NicuProcedure $procedure): void
    {
        if ($procedure->wasChanged('status') && $procedure->status === 'completed') {
            $this->maybePost($procedure);
        }
    }

    private function maybePost(NicuProcedure $procedure): void
    {
        $code = $this->resolveServiceCode($procedure->procedure_code);
        if (! $code) return;

        $admission = $procedure->admission;
        if (! $admission || ! $admission->encounter_id) {
            return;
        }

        // Avoid duplicate posting (idempotent on procedure id)
        $exists = \App\Models\ServiceCharge\ServiceChargePosting::where('encounter_id', $admission->encounter_id)
            ->where('trigger_event', 'nicu.procedure.' . $procedure->id)
            ->exists();
        if ($exists) {
            return;
        }

        try {
            app(ServiceChargeEngine::class)->post([
                'service_code' => $code,
                'encounter' => $admission->encounter_id,
                'trigger_event' => 'nicu.procedure.' . $procedure->id,
                'quantity' => 1,
                'reason' => 'NICU procedure: ' . $procedure->procedure_name,
            ]);
        } catch (\Throwable $e) {
            Log::info('NICU procedure charge skipped', ['code' => $code, 'reason' => $e->getMessage()]);
        }
    }

    private function resolveServiceCode(string $code): ?string
    {
        return match (strtoupper($code)) {
            'PHOTOTHERAPY' => 'PHOTOTHERAPY',
            'INTUBATION'   => 'NICU_INTUBATION',
            'SURFACTANT'   => 'SURFACTANT_ADMIN',
            default        => null,
        };
    }
}
