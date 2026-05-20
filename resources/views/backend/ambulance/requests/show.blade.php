@extends('backend.layouts.master')

@section('title', 'Request Details')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Request #{{ $request->id }}</h1>
            <div class="text-muted">Status: <span class="badge bg-info">{{ $request->status }}</span></div>
        </div>
        <a href="{{ route('amb.requests.index') }}" class="btn btn-light">Back</a>
    </div>

    @if(session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger mt-3">{{ session('error') }}</div> @endif

    <div class="row mt-4 g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Request Info</b></div>
                <div class="card-body">
                    <div><b>Source:</b> {{ $request->source }}</div>
                    <div><b>Type:</b> {{ $request->request_type }}</div>
                    <div><b>Priority:</b> {{ $request->priority }}</div>
                    <div><b>Condition:</b> {{ $request->patient_condition }}</div>
                    <div class="mt-2"><b>Pickup:</b> {{ $request->pickup_location }}</div>
                    <div><b>Drop:</b> {{ $request->drop_location ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><b>Patient</b></div>
                <div class="card-body">
                    @if($request->patient)
                        <div><b>Name:</b> {{ $request->patient->patient_name }}</div>
                        <div><b>Mobile:</b> {{ $request->patient->mobileno ?? '' }}</div>
                    @else
                        <div class="text-muted">Unknown patient</div>
                        <div><b>Temp ID:</b> {{ $request->temp_patient_id ?? '-' }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b>Trip</b>
                    @if($request->status === 'NEW')
                        <a class="btn btn-success btn-sm" href="{{ route('amb.trips.assignForm', $request) }}">
                            <i class="fa-solid fa-truck-medical me-1"></i> Assign Ambulance
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if($request->trip)
                        <div class="row">
                            <div class="col-md-3"><b>Trip ID:</b> {{ $request->trip->id }}</div>
                            <div class="col-md-3"><b>Status:</b> <span class="badge bg-primary">{{ $request->trip->status }}</span></div>
                            <div class="col-md-3"><b>Ambulance:</b> {{ $request->trip->ambulance?->reg_no }} ({{ $request->trip->ambulance?->type }})</div>
                            <div class="col-md-3"><b>Driver:</b> {{ $request->trip->driver?->name }}</div>
                        </div>

                        <hr>

                        <form method="POST" action="{{ route('amb.trips.updateStatus', $request->trip) }}" class="row g-2">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Update Trip Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="EN_ROUTE_PICKUP">EN_ROUTE_PICKUP</option>
                                    <option value="PATIENT_ONBOARD">PATIENT_ONBOARD</option>
                                    <option value="EN_ROUTE_HOSPITAL">EN_ROUTE_HOSPITAL</option>
                                    <option value="COMPLETED">COMPLETED</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delay Reason (optional)</label>
                                <input type="text" name="delay_reason" class="form-control" placeholder="Traffic / Breakdown / Other">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100">Update</button>
                            </div>
                        </form>
                    @else
                        <div class="text-muted">No trip assigned yet.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
