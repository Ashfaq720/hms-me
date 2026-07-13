<?php

namespace App\Modules\Ambulance\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\Driver;
use App\Modules\Ambulance\Models\Paramedic;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Models\AmbulanceTrip;
use App\Modules\Ambulance\Models\AssignmentAudit;
use App\Modules\Ambulance\Models\TripStatusLog;

class DispatchService
{
    private const NEXT_STATUS = [
        'ASSIGNED'            => 'EN_ROUTE_PICKUP',
        'EN_ROUTE_PICKUP'     => 'ARRIVED_AT_PICKUP',
        'ARRIVED_AT_PICKUP'   => 'PATIENT_ONBOARD',
        'PATIENT_ONBOARD'     => 'EN_ROUTE_HOSPITAL',
        'EN_ROUTE_HOSPITAL'   => 'ARRIVED_AT_HOSPITAL',
        'ARRIVED_AT_HOSPITAL' => 'COMPLETED',
        'COMPLETED'           => null,
    ];

    public function nextStatus(AmbulanceTrip $trip): ?string
    {
        return self::NEXT_STATUS[$trip->status] ?? null;
    }

    /**
     * Assign request → create trip with compliance checks + full audit.
     */
    public function assign(AmbulanceRequest $request, array $data, int $userId): AmbulanceTrip
    {
        return DB::transaction(function () use ($request, $data, $userId) {
            $ambulance = Ambulance::lockForUpdate()->findOrFail($data['ambulance_id']);
            $driver    = Driver::findOrFail($data['driver_id']);
            $paramedic = !empty($data['paramedic_id']) ? Paramedic::findOrFail($data['paramedic_id']) : null;

            if ($ambulance->status !== 'AVAILABLE') {
                throw new \RuntimeException("Ambulance is not available.");
            }

            $today = Carbon::today();
            if ($ambulance->fitness_expiry && Carbon::parse($ambulance->fitness_expiry)->lt($today)) {
                throw new \RuntimeException("Ambulance fitness expired.");
            }
            if ($ambulance->insurance_expiry && Carbon::parse($ambulance->insurance_expiry)->lt($today)) {
                throw new \RuntimeException("Ambulance insurance expired.");
            }
            if ($ambulance->license_validity && Carbon::parse($ambulance->license_validity)->lt($today)) {
                throw new \RuntimeException("Ambulance license expired.");
            }

            if ($driver->status !== 'ACTIVE') {
                throw new \RuntimeException("Driver is not active.");
            }
            if ($driver->license_expiry && Carbon::parse($driver->license_expiry)->lt($today)) {
                throw new \RuntimeException("Driver license expired.");
            }

            // ALS/ICU/NEONATAL require an ACLS paramedic
            $needsParamedic = in_array($ambulance->type, ['ALS', 'ICU', 'NEONATAL'], true);
            if ($needsParamedic) {
                if (!$paramedic) {
                    throw new \RuntimeException("Paramedic is required for {$ambulance->type} ambulance.");
                }
                if ($paramedic->status !== 'ACTIVE') {
                    throw new \RuntimeException("Paramedic is not active.");
                }
                if ($paramedic->cert_expiry && Carbon::parse($paramedic->cert_expiry)->lt($today)) {
                    throw new \RuntimeException("Paramedic certification expired.");
                }
                if ($paramedic->certification !== 'ACLS') {
                    throw new \RuntimeException("ACLS paramedic required for {$ambulance->type}.");
                }
            }

            if ($request->status !== 'NEW') {
                throw new \RuntimeException("Request is not in NEW status.");
            }

            $trip = AmbulanceTrip::create([
                'request_id'   => $request->id,
                'ambulance_id' => $ambulance->id,
                'driver_id'    => $driver->id,
                'paramedic_id' => $paramedic?->id,
                'vendor_id'    => $ambulance->vendor_id,
                'status'       => 'ASSIGNED',
                'started_at'   => now(),
            ]);

            $request->update(['status' => 'ASSIGNED']);
            $ambulance->update(['status' => 'ON_TRIP']);
            $driver->update(['availability' => 'ASSIGNED']);
            if ($paramedic) {
                $paramedic->update(['availability' => 'ASSIGNED']);
            }

            AssignmentAudit::create([
                'trip_id'              => $trip->id,
                'event_type'           => 'ASSIGNED',
                'prev_ambulance_id'    => null,
                'new_ambulance_id'     => $ambulance->id,
                'prev_driver_id'       => null,
                'new_driver_id'        => $driver->id,
                'prev_paramedic_id'    => null,
                'new_paramedic_id'     => $paramedic?->id,
                'override_flag'        => false,
                'emergency_request_id' => $request->request_type === 'EMERGENCY' ? $request->id : null,
                'reason'               => $data['reason'] ?? null,
                'changed_by'           => $userId,
                'changed_at'           => now(),
            ]);

            TripStatusLog::create([
                'trip_id'     => $trip->id,
                'from_status' => null,
                'to_status'   => 'ASSIGNED',
                'changed_by'  => $userId,
                'changed_at'  => now(),
            ]);

            return $trip;
        });
    }

    /**
     * Advance trip exactly one step forward in the lifecycle.
     */
    public function updateTripStatus(AmbulanceTrip $trip, string $newStatus, ?string $delayReason = null, ?int $userId = null): AmbulanceTrip
    {
        $current  = $trip->status;
        $expected = self::NEXT_STATUS[$current] ?? null;

        if ($expected === null || $newStatus !== $expected) {
            throw new \RuntimeException("Invalid status transition: {$current} → {$newStatus}");
        }

        $trip->status = $newStatus;

        if ($delayReason) {
            $trip->delay_reason = $delayReason;
        }

        $now = now();
        match ($newStatus) {
            'ARRIVED_AT_PICKUP'   => $trip->pickup_arrival_time   = $now,
            'PATIENT_ONBOARD'     => $trip->patient_onboard_time  = $now,
            'ARRIVED_AT_HOSPITAL' => $trip->hospital_arrival_time = $now,
            'COMPLETED'           => $trip->completed_at           = $now,
            default               => null,
        };


            // Billing is posted out-of-band by AmbulanceTripObserver listening to
            // 'COMPLETED' transitions, so this method intentionally has no inline
            // BillingService call — keeps service-to-service deps decoupled.
        if ($newStatus === 'COMPLETED') {
            $trip->ambulance()->update(['status' => 'AVAILABLE']);
            $trip->driver()->update(['availability' => 'AVAILABLE']);
            if ($trip->paramedic_id) {
                $trip->paramedic()->update(['availability' => 'AVAILABLE']);
            }
        }

        $trip->save();

        TripStatusLog::create([
            'trip_id'      => $trip->id,
            'from_status'  => $current,
            'to_status'    => $newStatus,
            'delay_reason' => $delayReason,
            'changed_by'   => $userId,
            'changed_at'   => $now,
        ]);

        return $trip;
    }

    /**
     * Reassign ambulance/driver/paramedic on an active trip.
     * Frees old resources and locks new ones. Writes REASSIGNED audit record.
     */
    public function reassign(AmbulanceTrip $trip, array $data, int $userId): AmbulanceTrip
    {
        return DB::transaction(function () use ($trip, $data, $userId) {
            if ($trip->status === 'COMPLETED') {
                throw new \RuntimeException("Cannot reassign a completed trip.");
            }

            $newAmbulance = Ambulance::lockForUpdate()->findOrFail($data['ambulance_id']);
            $newDriver    = Driver::findOrFail($data['driver_id']);
            $newParamedic = !empty($data['paramedic_id']) ? Paramedic::findOrFail($data['paramedic_id']) : null;

            $today = Carbon::today();

            if ($newAmbulance->id !== $trip->ambulance_id) {
                if ($newAmbulance->status !== 'AVAILABLE') {
                    throw new \RuntimeException("New ambulance is not available.");
                }
                if ($newAmbulance->fitness_expiry && Carbon::parse($newAmbulance->fitness_expiry)->lt($today)) {
                    throw new \RuntimeException("New ambulance fitness expired.");
                }
                if ($newAmbulance->insurance_expiry && Carbon::parse($newAmbulance->insurance_expiry)->lt($today)) {
                    throw new \RuntimeException("New ambulance insurance expired.");
                }
                if ($newAmbulance->license_validity && Carbon::parse($newAmbulance->license_validity)->lt($today)) {
                    throw new \RuntimeException("New ambulance license expired.");
                }
            }

            if ($newDriver->status !== 'ACTIVE') {
                throw new \RuntimeException("New driver is not active.");
            }
            if ($newDriver->license_expiry && Carbon::parse($newDriver->license_expiry)->lt($today)) {
                throw new \RuntimeException("New driver license expired.");
            }

            $needsParamedic = in_array($newAmbulance->type, ['ALS', 'ICU', 'NEONATAL'], true);
            if ($needsParamedic) {
                if (!$newParamedic) {
                    throw new \RuntimeException("Paramedic required for {$newAmbulance->type}.");
                }
                if ($newParamedic->status !== 'ACTIVE') {
                    throw new \RuntimeException("New paramedic is not active.");
                }
                if ($newParamedic->certification !== 'ACLS') {
                    throw new \RuntimeException("ACLS paramedic required for {$newAmbulance->type}.");
                }
                if ($newParamedic->cert_expiry && Carbon::parse($newParamedic->cert_expiry)->lt($today)) {
                    throw new \RuntimeException("New paramedic certification expired.");
                }
            }

            $prevAmbulanceId  = $trip->ambulance_id;
            $prevDriverId     = $trip->driver_id;
            $prevParamedicId  = $trip->paramedic_id;

            // Free old, lock new — only when actually changing
            if ($newAmbulance->id !== $prevAmbulanceId) {
                Ambulance::where('id', $prevAmbulanceId)->update(['status' => 'AVAILABLE']);
                $newAmbulance->update(['status' => 'ON_TRIP']);
            }
            if ($newDriver->id !== $prevDriverId) {
                Driver::where('id', $prevDriverId)->update(['availability' => 'AVAILABLE']);
                $newDriver->update(['availability' => 'ASSIGNED']);
            }
            if ($prevParamedicId && ($newParamedic === null || $newParamedic->id !== $prevParamedicId)) {
                Paramedic::where('id', $prevParamedicId)->update(['availability' => 'AVAILABLE']);
            }
            if ($newParamedic && $newParamedic->id !== $prevParamedicId) {
                $newParamedic->update(['availability' => 'ASSIGNED']);
            }

            $trip->ambulance_id = $newAmbulance->id;
            $trip->driver_id    = $newDriver->id;
            $trip->paramedic_id = $newParamedic?->id;
            $trip->vendor_id    = $newAmbulance->vendor_id;
            $trip->save();

            AssignmentAudit::create([
                'trip_id'              => $trip->id,
                'event_type'           => 'REASSIGNED',
                'prev_ambulance_id'    => $prevAmbulanceId,
                'new_ambulance_id'     => $newAmbulance->id,
                'prev_driver_id'       => $prevDriverId,
                'new_driver_id'        => $newDriver->id,
                'prev_paramedic_id'    => $prevParamedicId,
                'new_paramedic_id'     => $newParamedic?->id,
                'override_flag'        => (bool) ($data['override_flag'] ?? false),
                'emergency_request_id' => $data['emergency_request_id'] ?? null,
                'sla_status_at_change' => null,
                'reason'               => $data['reason'],
                'changed_by'           => $userId,
                'changed_at'           => now(),
            ]);

            return $trip;
        });
    }
}
