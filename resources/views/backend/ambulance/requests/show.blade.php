@extends('backend.layouts.master')

@section('title', 'Request #{{ $request->id }}')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Request #{{ $request->id }}</h1>
            <div class="text-muted small">
                @php
                    $scls = match($request->status) {
                        'NEW'       => 'info',
                        'ASSIGNED'  => 'primary',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default     => 'secondary',
                    };
                @endphp
                Status: <span class="badge bg-{{ $scls }}">{{ $request->status }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($request->status === 'NEW')
                <a href="{{ route('amb.trips.assignForm', $request) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-truck-medical me-1"></i> Assign Ambulance
                </a>
                <form method="POST" action="{{ route('amb.requests.destroy', $request) }}"
                    onsubmit="return confirm('Cancel this request?')" class="m-0">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" type="submit">Cancel Request</button>
                </form>
            @endif
            <a href="{{ route('amb.requests.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger mt-3">{{ session('error') }}</div>@endif

    <div class="row mt-4 g-3">
        {{-- Request Info --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><strong>Request Info</strong></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">Source</div>
                            <div>{{ $request->source ?? '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Type</div>
                            <div><span class="badge bg-secondary">{{ $request->request_type }}</span></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Priority</div>
                            <div>
                                <span class="badge bg-{{ match($request->priority) {
                                    'CRITICAL' => 'danger',
                                    'HIGH'     => 'warning',
                                    default    => 'secondary'
                                } }}">{{ $request->priority }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Patient Condition</div>
                            <div>
                                @if($request->patient_condition === 'CRITICAL')
                                    <span class="badge bg-danger">Critical</span>
                                @else
                                    <span class="badge bg-success">Stable</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Case Tag</div>
                            <div>
                                @if($request->case_tag)
                                    <span class="badge bg-info text-dark">{{ $request->case_tag }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Requested By</div>
                            <div>{{ $request->requested_by_name ?? '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Date &amp; Time</div>
                            <div>
                                {{ $request->date?->format('d M Y') ?? '—' }}
                                @if($request->time) at {{ $request->time }} @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Contact</div>
                            <div>{{ $request->contact_no ?? '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Pickup Location</div>
                            <div>{{ $request->pick_up_location }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Drop Location</div>
                            <div>{{ $request->drop_location ?? '—' }}</div>
                        </div>
                        @if($request->destination_hospital)
                        <div class="col-12">
                            <div class="text-muted small">Destination Hospital / ER</div>
                            <div>{{ $request->destination_hospital }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Patient --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header"><strong>Patient</strong></div>
                <div class="card-body">
                    @if($request->patient)
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-muted small">Name</div>
                                <div class="fw-semibold">{{ $request->patient->patient_name }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Mobile</div>
                                <div>{{ $request->patient->mobileno ?? '—' }}</div>
                            </div>
                            @if($request->patient->mrn ?? null)
                            <div class="col-6">
                                <div class="text-muted small">MRN</div>
                                <div>{{ $request->patient->mrn }}</div>
                            </div>
                            @endif
                            @if($request->patient->gender ?? null)
                            <div class="col-6">
                                <div class="text-muted small">Gender</div>
                                <div>{{ $request->patient->gender }}</div>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-muted">Unknown patient</div>
                        @if($request->temp_patient_id)
                            <div class="mt-1 small"><strong>Temp ID:</strong> {{ $request->temp_patient_id }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Trip --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><strong>Trip</strong></div>
                <div class="card-body">
                    @if($request->trip)
                        <div class="row g-2 mb-3">
                            <div class="col-md-2">
                                <div class="text-muted small">Trip #</div>
                                <div>{{ $request->trip->id }}</div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-muted small">Status</div>
                                <div><span class="badge bg-primary">{{ str_replace('_', ' ', $request->trip->status) }}</span></div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small">Ambulance</div>
                                <div>{{ $request->trip->ambulance?->reg_no ?? '—' }}
                                    @if($request->trip->ambulance)<small class="text-muted">({{ $request->trip->ambulance->type }})</small>@endif
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-muted small">Driver</div>
                                <div>{{ $request->trip->driver?->name ?? '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small">Paramedic</div>
                                <div>{{ $request->trip->paramedic?->name ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <a href="{{ route('amb.trips.show', $request->trip) }}" class="btn btn-sm btn-info">
                                View Full Trip Detail
                            </a>
                        </div>

                        @if($request->trip->status !== 'COMPLETED' && $request->trip->status !== 'CANCELLED')
                        <hr>
                        <form method="POST" action="{{ route('amb.trips.updateStatus', $request->trip) }}" class="row g-2">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Advance Trip Status</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    @foreach([
                                        'EN_ROUTE_PICKUP'      => 'En Route to Pickup',
                                        'ARRIVED_AT_PICKUP'    => 'Arrived at Pickup',
                                        'PATIENT_ONBOARD'      => 'Patient Onboard',
                                        'EN_ROUTE_HOSPITAL'    => 'En Route to Hospital',
                                        'ARRIVED_AT_HOSPITAL'  => 'Arrived at Hospital',
                                        'COMPLETED'            => 'Completed',
                                    ] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delay Reason (optional)</label>
                                <input type="text" name="delay_reason" class="form-control form-control-sm"
                                    placeholder="Traffic / Breakdown / Other">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-sm btn-primary w-100">Update</button>
                            </div>
                        </form>
                        @endif
                    @else
                        <div class="text-muted">No trip assigned yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
