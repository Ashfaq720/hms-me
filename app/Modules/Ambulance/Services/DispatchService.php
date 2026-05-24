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

class DispatchService
{
    /**
     * Assign request -> create trip with validations + audit.
     */
    public function assign(AmbulanceRequest $request, array $data, int $userId): AmbulanceTrip
    {
        return DB::transaction(function () use ($request, $data, $userId) {
            $ambulance = Ambulance::lockForUpdate()->findOrFail($data['ambulance_id']);
            $driver    = Driver::findOrFail($data['driver_id']);
            $paramedic = !empty($data['paramedic_id']) ? Paramedic::findOrFail($data['paramedic_id']) : null;

            // 1) Ambulance must be AVAILABLE and not MAINTENANCE
            if ($ambulance->status !== 'AVAILABLE') {
                throw new \RuntimeException("Ambulance is not available.");
            }
            if ($ambulance->status === 'MAINTENANCE') {
                throw new \RuntimeException("Ambulance is under maintenance.");
            }

            // 2) Compliance expiry check (fitness/insurance)
            $today = Carbon::today();
            if ($ambulance->fitness_expiry && Carbon::parse($ambulance->fitness_expiry)->lt($today)) {
                throw new \RuntimeException("Ambulance fitness expired.");
            }
            if ($ambulance->insurance_expiry && Carbon::parse($ambulance->insurance_expiry)->lt($today)) {
                throw new \RuntimeException("Ambulance insurance expired.");
            }

            // 3) Driver license validity + active
            if ($driver->status !== 'ACTIVE') {
                throw new \RuntimeException("Driver is not active.");
            }
            if ($driver->license_expiry && Carbon::parse($driver->license_expiry)->lt($today)) {
                throw new \RuntimeException("Driver license expired.");
            }

            // 4) If ALS/ICU/NEONATAL => paramedic required + ACLS
            $needsParamedic = in_array($ambulance->type, ['ALS','ICU','NEONATAL'], true);
            if ($needsParamedic) {
                if (!$paramedic) throw new \RuntimeException("Paramedic is required for {$ambulance->type} ambulance.");
                if ($paramedic->status !== 'ACTIVE') throw new \RuntimeException("Paramedic is not active.");
                if ($paramedic->cert_expiry && Carbon::parse($paramedic->cert_expiry)->lt($today)) {
                    throw new \RuntimeException("Paramedic certification expired.");
                }
                if ($paramedic->certification !== 'ACLS') {
                    throw new \RuntimeException("ACLS paramedic required for {$ambulance->type}.");
                }
            }

            // Update request
            if ($request->status !== 'NEW') {
                throw new \RuntimeException("Request is not in NEW status.");
            }

            // Create trip
            $trip = AmbulanceTrip::create([
                'request_id'   => $request->id,
                'ambulance_id' => $ambulance->id,
                'driver_id'    => $driver->id,
                'paramedic_id' => $paramedic?->id,
                'vendor_id'    => $ambulance->vendor_id,
                'status'       => 'ASSIGNED',
                'started_at'   => now(),
            ]);

            // Update states
            $request->update(['status' => 'ASSIGNED']);
            $ambulance->update(['status' => 'ON_TRIP']);

            // Audit
            AssignmentAudit::create([
                'trip_id'            => $trip->id,
                'event_type'         => 'ASSIGNED',
                'prev_ambulance_id'  => null,
                'new_ambulance_id'   => $ambulance->id,
                'prev_driver_id'     => null,
                'new_driver_id'      => $driver->id,
                'prev_paramedic_id'  => null,
                'new_paramedic_id'   => $paramedic?->id,
                'override_flag'      => false,
                'emergency_request_id' => $request->request_type === 'EMERGENCY' ? $request->id : null,
                'reason'             => $data['reason'] ?? null,
                'changed_by'         => $userId,
                'changed_at'         => now(),
            ]);

            return $trip;
        });
    }

    /**
     * Trip status sequential update (ASSIGNED -> ... -> COMPLETED)
     */
    public function updateTripStatus(AmbulanceTrip $trip, string $newStatus, ?string $delayReason = null): AmbulanceTrip
    {
        $allowedNext = [
            'ASSIGNED'         => ['EN_ROUTE_PICKUP'],
            'EN_ROUTE_PICKUP'  => ['PATIENT_ONBOARD'],
            'PATIENT_ONBOARD'  => ['EN_ROUTE_HOSPITAL'],
            'EN_ROUTE_HOSPITAL'=> ['COMPLETED'],
            'COMPLETED'        => [],
        ];

        $current = $trip->status;
        if (!in_array($newStatus, $allowedNext[$current] ?? [], true)) {
            throw new \RuntimeException("Invalid status transition: {$current} -> {$newStatus}");
        }

        $trip->status = $newStatus;

        if ($delayReason) {
            $trip->delay_reason = $delayReason;
        }

        if ($newStatus === 'COMPLETED') {
            $trip->completed_at = now();
            // Make ambulance AVAILABLE again
            $trip->ambulance()->update(['status' => 'AVAILABLE']);

            // Billing hook (you have billing module)
            // TODO: call your BillingService here.
        }

        $trip->save();
        return $trip;
    }
}
