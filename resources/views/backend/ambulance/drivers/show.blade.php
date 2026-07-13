@extends('backend.layouts.master')

@section('title', 'Driver — {{ $driver->name }}')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Driver — {{ $driver->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.drivers.edit', $driver) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('amb.drivers.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-header"><strong>Driver Details</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold">{{ $driver->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">National ID</div>
                    <div>{{ $driver->nid }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Phone</div>
                    <div>{{ $driver->phone ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">License Number</div>
                    <div>{{ $driver->license_number ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">License Type</div>
                    <div>{{ $driver->license_type ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">License Expiry</div>
                    <div>
                        @if($driver->license_expiry)
                            @if($driver->isLicenseExpired())
                                <span class="badge bg-danger">Expired {{ $driver->license_expiry->format('d M Y') }}</span>
                            @elseif($driver->isLicenseExpiringSoon())
                                <span class="badge bg-warning text-dark">{{ $driver->license_expiry->format('d M Y') }} (expiring soon)</span>
                            @else
                                <span class="text-success">{{ $driver->license_expiry->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Shift</div>
                    <div>{{ $driver->shift ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Availability</div>
                    <div>
                        @if($driver->availability === 'AVAILABLE')
                            <span class="badge bg-success">Available</span>
                        @elseif($driver->availability === 'ASSIGNED')
                            <span class="badge bg-primary">Assigned</span>
                        @else
                            <span class="badge bg-secondary">Off Duty</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div>
                        @if($driver->status === 'ACTIVE')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Trips --}}
    <div class="card mt-4">
        <div class="card-header"><strong>Recent Trips</strong></div>
        <div class="card-body p-0">
            @if($driver->trips->isNotEmpty())
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ambulance</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Dispatched</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($driver->trips as $trip)
                    <tr>
                        <td>{{ $trip->id }}</td>
                        <td>{{ $trip->ambulance?->reg_no ?? '—' }}</td>
                        <td>{{ $trip->request?->patient_name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $trip->status) }}</span></td>
                        <td>{{ $trip->started_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td><a href="{{ route('amb.trips.show', $trip) }}" class="btn btn-xs btn-info">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="p-3 text-muted">No trips recorded.</div>
            @endif
        </div>
    </div>
</div>
@endsection
