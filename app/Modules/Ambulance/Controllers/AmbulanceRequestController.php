<?php

namespace App\Modules\Ambulance\Controllers;

use App\Models\Patient;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Models\Driver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $drivers    = Driver::orderBy('name')->get(['id', 'name', 'phone']);
        $ambulances = Ambulance::orderBy('reg_no')->get(['id', 'reg_no', 'type', 'status']);

        return view('backend.ambulance.requests.create', compact('drivers', 'ambulances'));
    }

    public function store(Request $r)
    {
        $isNew = $r->input('patient_mode') !== 'existing';

        $r->validate([
            'patient_id'         => $isNew ? 'nullable|integer' : 'required|integer|exists:patients,id',
            'patient_mode'       => 'nullable|string',
            'contact_no'         => 'required|string|max:20',
            'patient_name'       => $isNew ? 'required|string|max:255' : 'nullable|string|max:255',
            'gender'             => 'nullable|in:Male,Female,Other',
            'source'             => 'nullable|in:ER_DESK,OPD,IPD,CALL_CENTER,REFERRAL',
            'request_type'       => 'required|in:NORMAL,EMERGENCY,TRANSFER,SCHEDULED',
            'priority'           => 'required|in:LOW,HIGH,CRITICAL,NORMAL',
            'patient_condition'  => 'nullable|in:CRITICAL,STABLE',
            'case_tag'           => 'nullable|in:TRAUMA,STROKE,CARDIAC,RESPIRATORY,OTHER',
            'requested_by_name'  => 'nullable|string|max:255',
            'date'               => 'required|date',
            'time'               => 'required|date_format:H:i',
            'pick_up_location'   => 'required|string|max:255',
            'drop_location'          => 'nullable|string|max:255',
            'destination_hospital'   => 'nullable|string|max:255',
            'ambulance_id'           => 'nullable|integer|exists:amb_ambulances,id',
            'driver_id'          => 'nullable|integer|exists:amb_drivers,id',
        ]);

        DB::beginTransaction();
        try {
            $patientId = $r->input('patient_id');

            if ($isNew) {
                $patient               = new Patient();
                $patient->patient_name = $r->input('patient_name');
                $patient->mobileno     = $r->input('contact_no');
                $patient->gender       = $r->input('gender') ?: null;
                $patient->created_by   = auth()->id();
                $patient->save();
                $patientId = $patient->id;
            }

            AmbulanceRequest::create([
                'patient_id'        => $patientId,
                'contact_no'        => $r->input('contact_no'),
                'source'            => $r->input('source', 'ER_DESK'),
                'request_type'      => $r->input('request_type'),
                'priority'          => $r->input('priority'),
                'patient_condition' => $r->input('patient_condition', 'STABLE'),
                'case_tag'          => $r->input('case_tag') ?: null,
                'requested_by_name' => $r->input('requested_by_name') ?: null,
                'date'              => $r->input('date'),
                'time'              => $r->input('time'),
                'pick_up_location'  => $r->input('pick_up_location'),
                'drop_location'        => $r->input('drop_location') ?: null,
                'destination_hospital' => $r->input('destination_hospital') ?: null,
                'ambulance_id'      => $r->input('ambulance_id') ?: null,
                'driver_id'         => $r->input('driver_id') ?: null,
                'status'            => 'NEW',
                'created_by'        => auth()->id() ?? null,
            ]);

            DB::commit();
            return back()->with('success', 'Ambulance request created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create request: ' . $e->getMessage())->withInput();
        }
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
