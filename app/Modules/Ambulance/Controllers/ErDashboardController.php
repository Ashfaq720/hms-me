<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\AmbulanceTrip;

class ErDashboardController extends Controller
{
    public function incoming()
    {
        // Incoming = not completed
        $trips = AmbulanceTrip::with('request.patient','ambulance','driver','paramedic')
            ->whereIn('status', ['ASSIGNED','EN_ROUTE_PICKUP','PATIENT_ONBOARD','EN_ROUTE_HOSPITAL'])
            ->latest()
            ->paginate(15);

        return view('backend.ambulance.er.incoming', compact('trips'));
    }
}
