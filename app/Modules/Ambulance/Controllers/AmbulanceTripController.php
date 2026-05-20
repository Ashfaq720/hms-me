<?php

namespace App\Modules\Ambulance\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Models\AmbulanceTrip;
use App\Modules\Ambulance\Models\Driver;
use App\Modules\Ambulance\Models\Paramedic;
use App\Modules\Ambulance\Services\DispatchService;

class AmbulanceTripController extends Controller
{
    public function assignForm(AmbulanceRequest $request)
    {
        if ($request->status !== 'NEW') {
            return redirect()->route('amb.requests.index')->with('error', 'Request is not NEW.');
        }

        // Filter dropdowns (basic filters; strict rules are in DispatchService)
        $ambulances = Ambulance::where('status','AVAILABLE')->orderBy('type')->get();
        $drivers    = Driver::where('status','ACTIVE')->orderBy('name')->get();
        $paramedics = Paramedic::where('status','ACTIVE')->orderBy('name')->get();

        return view('backend.ambulance.trips.assign', compact('request','ambulances','drivers','paramedics'));
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
            return redirect()->route('amb.requests.show', $request)->with('success', "Assigned successfully. Trip #{$trip->id}");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(AmbulanceTrip $trip, Request $r, DispatchService $dispatch)
    {
        $data = $r->validate([
            'status' => 'required|in:EN_ROUTE_PICKUP,PATIENT_ONBOARD,EN_ROUTE_HOSPITAL,COMPLETED',
            'delay_reason' => 'nullable|string|max:255',
        ]);

        try {
            $dispatch->updateTripStatus($trip, $data['status'], $data['delay_reason'] ?? null);
            return back()->with('success', 'Trip status updated.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
