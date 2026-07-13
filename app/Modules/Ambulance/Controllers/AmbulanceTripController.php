<?php

namespace App\Modules\Ambulance\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Models\AmbulanceTrip;
use App\Modules\Ambulance\Models\Driver;
use App\Modules\Ambulance\Models\Paramedic;
use App\Modules\Ambulance\Models\TripEditAudit;
use App\Modules\Ambulance\Services\DispatchService;

class AmbulanceTripController extends Controller
{
    private const VALID_STATUSES = [
        'EN_ROUTE_PICKUP',
        'ARRIVED_AT_PICKUP',
        'PATIENT_ONBOARD',
        'EN_ROUTE_HOSPITAL',
        'ARRIVED_AT_HOSPITAL',
        'COMPLETED',
    ];

    // ─── Assign ──────────────────────────────────────────────────────────────

    public function assignForm(AmbulanceRequest $request)
    {
        if ($request->status !== 'NEW') {
            return redirect()->route('amb.requests.index')->with('error', 'Request is not NEW.');
        }

        $ambulances = Ambulance::where('status', 'AVAILABLE')->orderBy('type')->get();
        $drivers    = Driver::where('status', 'ACTIVE')->where('availability', 'AVAILABLE')->orderBy('name')->get();
        $paramedics = Paramedic::where('status', 'ACTIVE')->where('availability', 'AVAILABLE')->orderBy('name')->get();

        return view('backend.ambulance.trips.assign', compact('request', 'ambulances', 'drivers', 'paramedics'));
    }

    public function assignStore(AmbulanceRequest $request, Request $r, DispatchService $dispatch)
    {
        $data = $r->validate([
            'ambulance_id' => 'required|exists:amb_ambulances,id',
            'driver_id'    => 'required|exists:amb_drivers,id',
            'paramedic_id' => 'nullable|exists:amb_paramedics,id',
            'reason'       => 'nullable|string|max:500',
        ]);

        try {
            $trip = $dispatch->assign($request, $data, auth()->id());
            return redirect()->route('amb.requests.show', $request)
                ->with('success', "Assigned successfully. Trip #{$trip->id}");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── Status update ────────────────────────────────────────────────────────

    public function updateStatus(AmbulanceTrip $trip, Request $r, DispatchService $dispatch)
    {
        $data = $r->validate([
            'status'       => 'required|in:' . implode(',', self::VALID_STATUSES),
            'delay_reason' => 'nullable|string|max:255',
        ]);

        if ($trip->status === 'COMPLETED') {
            return back()->with('error', 'Trip is already completed and is read-only.');
        }

        try {
            $dispatch->updateTripStatus($trip, $data['status'], $data['delay_reason'] ?? null, auth()->id());
            return back()->with('success', 'Trip status updated to ' . str_replace('_', ' ', $data['status']) . '.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─── Trip edit ────────────────────────────────────────────────────────────

    public function edit(AmbulanceTrip $trip)
    {
        if ($trip->status === 'COMPLETED') {
            return redirect()->route('amb.trips.show', $trip)
                ->with('error', 'Completed trips are read-only.');
        }

        $trip->load('request');
        $canEditPickup = in_array($trip->status, ['ASSIGNED', 'EN_ROUTE_PICKUP', 'ARRIVED_AT_PICKUP']);

        return view('backend.ambulance.trips.edit', compact('trip', 'canEditPickup'));
    }

    public function update(AmbulanceTrip $trip, Request $r)
    {
        if ($trip->status === 'COMPLETED') {
            return redirect()->route('amb.trips.show', $trip)
                ->with('error', 'Completed trips are read-only.');
        }

        $canEditPickup = in_array($trip->status, ['ASSIGNED', 'EN_ROUTE_PICKUP', 'ARRIVED_AT_PICKUP']);

        $rules = [
            'priority'             => 'required|in:LOW,HIGH,CRITICAL,NORMAL',
            'case_tag'             => 'nullable|in:TRAUMA,STROKE,CARDIAC,RESPIRATORY,OTHER',
            'drop_location'        => 'nullable|string|max:255',
            'destination_hospital' => 'nullable|string|max:255',
            'eta_minutes'          => 'nullable|integer|min:1|max:999',
            'edit_reason'          => 'required|string|max:500',
        ];
        if ($canEditPickup) {
            $rules['pick_up_location'] = 'required|string|max:255';
        }

        $data      = $r->validate($rules);
        $editReason = $data['edit_reason'];
        $userId    = auth()->id();
        $now       = now();
        $req       = $trip->request;

        // Fields that live on the request record
        $requestFields = ['priority', 'case_tag', 'drop_location', 'destination_hospital'];
        if ($canEditPickup) {
            $requestFields[] = 'pick_up_location';
        }

        $requestUpdates = [];
        foreach ($requestFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $oldVal = $req->{$field};
            $newVal = $data[$field] ?? null;
            if ($oldVal != $newVal) {
                TripEditAudit::create([
                    'trip_id'      => $trip->id,
                    'edited_field' => $field,
                    'old_value'    => $oldVal,
                    'new_value'    => $newVal,
                    'edit_reason'  => $editReason,
                    'edited_by'    => $userId,
                    'edited_at'    => $now,
                ]);
                $requestUpdates[$field] = $newVal;
            }
        }
        if ($requestUpdates) {
            $req->update($requestUpdates);
        }

        // ETA lives on the trip record
        $newEta = $data['eta_minutes'] ?? null;
        if ($newEta !== null && (string) $trip->eta_minutes !== (string) $newEta) {
            TripEditAudit::create([
                'trip_id'      => $trip->id,
                'edited_field' => 'eta_minutes',
                'old_value'    => $trip->eta_minutes,
                'new_value'    => $newEta,
                'edit_reason'  => $editReason,
                'edited_by'    => $userId,
                'edited_at'    => $now,
            ]);
            $trip->eta_minutes = $newEta;
            $trip->save();
        }

        return redirect()->route('amb.trips.show', $trip)
            ->with('success', 'Trip details updated.');
    }

    // ─── Reassign ─────────────────────────────────────────────────────────────

    public function reassignForm(AmbulanceTrip $trip)
    {
        if ($trip->status === 'COMPLETED') {
            return redirect()->route('amb.trips.show', $trip)
                ->with('error', 'Completed trips cannot be reassigned.');
        }

        $trip->load('ambulance', 'driver', 'paramedic');

        // Offer current assignment + any AVAILABLE alternatives
        $ambulances = Ambulance::where(function ($q) use ($trip) {
            $q->where('status', 'AVAILABLE')
              ->orWhere('id', $trip->ambulance_id);
        })->orderBy('type')->get();

        $drivers = Driver::where('status', 'ACTIVE')
            ->where(function ($q) use ($trip) {
                $q->where('availability', 'AVAILABLE')
                  ->orWhere('id', $trip->driver_id);
            })->orderBy('name')->get();

        $paramedics = Paramedic::where('status', 'ACTIVE')
            ->where(function ($q) use ($trip) {
                $q->where('availability', 'AVAILABLE')
                  ->orWhere('id', $trip->paramedic_id);
            })->orderBy('name')->get();

        return view('backend.ambulance.trips.reassign', compact('trip', 'ambulances', 'drivers', 'paramedics'));
    }

    public function reassignStore(AmbulanceTrip $trip, Request $r, DispatchService $dispatch)
    {
        $data = $r->validate([
            'ambulance_id' => 'required|exists:amb_ambulances,id',
            'driver_id'    => 'required|exists:amb_drivers,id',
            'paramedic_id' => 'nullable|exists:amb_paramedics,id',
            'reason'       => 'required|string|max:500',
        ]);

        try {
            $dispatch->reassign($trip, $data, auth()->id());
            return redirect()->route('amb.trips.show', $trip)
                ->with('success', 'Trip reassigned successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(AmbulanceTrip $trip)
    {
        $trip->load([
            'request.patient',
            'ambulance',
            'driver',
            'paramedic',
            'vendor',
            'clinicalNotes.recorder',
            'statusLogs.changedBy',
            'editAudits.editor',
            'assignmentAudits',
        ]);

        $nextStatus = app(DispatchService::class)->nextStatus($trip);

        return view('backend.ambulance.trips.show', compact('trip', 'nextStatus'));
    }
}
