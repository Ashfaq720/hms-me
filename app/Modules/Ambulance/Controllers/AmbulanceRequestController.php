<?php

namespace App\Modules\Ambulance\Controllers;

use App\Modules\Ambulance\Models\Ambulance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Models\Driver;

class AmbulanceRequestController extends Controller
{
    public function index()
    {
        $requests = AmbulanceRequest::with('patient', 'trip')
            ->latest()
            ->paginate(15);

        return view('backend.ambulance.requests.index', compact('requests'));
    }

    public function create()
    {
        // If you have lots of patients, later you can use ajax select2.
        $patients = \App\Models\Patient::select('id', 'patient_name', 'mobileno')->latest()->limit(200)->get();

        $drivers = Driver::get();
        $ambulances = Ambulance::get();

        return view('backend.ambulance.requests.create', compact('patients', 'drivers', 'ambulances'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'patient_id'       => 'required|integer|exists:patients,id',
            'contact_no'       => 'nullable|string|max:20',
            'request_type'     => 'required|in:NORMAL,EMERGENCY,TRANSFER,SCHEDULED',
            'priority'         => 'required|in:LOW,HIGH,CRITICAL,NORMAL',
            'date'             => 'required|date',
            'time'             => 'required|date_format:H:i',
            'pick_up_location' => 'required|string|max:255',
            'drop_location'    => 'nullable|string|max:255',
            'ambulance_id'     => 'nullable|integer|exists:amb_ambulances,id',
            'driver_id'        => 'nullable|integer|exists:amb_drivers,id',
        ]);

        $data['status']     = 'NEW';
        $data['source']     = 'ER_DESK';
        $data['created_by'] = auth()->id() ?? null;

        $req = AmbulanceRequest::create($data);

        return back()->with('success', 'Ambulance request created.');
    }

    public function show(AmbulanceRequest $request)
    {
        $request->load('patient', 'trip.ambulance', 'trip.driver', 'trip.paramedic');
        return view('backend.ambulance.requests.show', compact('request'));
    }

    // Optional: cancel
    public function destroy(AmbulanceRequest $request)
    {
        if ($request->status !== 'NEW') {
            return back()->with('error', 'Only NEW requests can be cancelled.');
        }
        $request->update(['status' => 'CANCELLED']);
        return back()->with('success', 'Request cancelled.');
    }
}
