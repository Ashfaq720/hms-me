@extends('backend.layouts.master')

@section('title', 'Paramedic — {{ $paramedic->name }}')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Paramedic — {{ $paramedic->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.paramedics.edit', $paramedic) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('amb.paramedics.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-header"><strong>Paramedic Details</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold">{{ $paramedic->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">National ID</div>
                    <div>{{ $paramedic->nid }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Phone</div>
                    <div>{{ $paramedic->phone ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Certification</div>
                    <div>
                        <span class="badge bg-{{ $paramedic->certification === 'ACLS' ? 'danger' : 'secondary' }}">
                            {{ $paramedic->certification === 'ACLS' ? 'Advanced Cardiac Life Support (ACLS)' : 'Basic Life Support (BLS)' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Skill Level</div>
                    <div>
                        <span class="badge bg-{{ $paramedic->skill_level === 'ADVANCED' ? 'primary' : 'secondary' }}">
                            {{ ucfirst(strtolower($paramedic->skill_level)) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Certification Expiry</div>
                    <div>
                        @if($paramedic->cert_expiry)
                            @if($paramedic->isCertExpired())
                                <span class="badge bg-danger">Expired {{ $paramedic->cert_expiry->format('d M Y') }}</span>
                            @elseif($paramedic->isCertExpiringSoon())
                                <span class="badge bg-warning text-dark">{{ $paramedic->cert_expiry->format('d M Y') }} (expiring soon)</span>
                            @else
                                <span class="text-success">{{ $paramedic->cert_expiry->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Shift</div>
                    <div>{{ $paramedic->shift ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Availability</div>
                    <div>
                        @if($paramedic->availability === 'AVAILABLE')
                            <span class="badge bg-success">Available</span>
                        @elseif($paramedic->availability === 'ASSIGNED')
                            <span class="badge bg-primary">Assigned</span>
                        @else
                            <span class="badge bg-secondary">Off Duty</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div>
                        @if($paramedic->status === 'ACTIVE')
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
            @if($paramedic->trips->isNotEmpty())
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
                    @foreach($paramedic->trips as $trip)
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
