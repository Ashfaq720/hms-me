@extends('backend.layouts.master')

@section('title', 'Trip #' . $trip->id)

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Trip #{{ $trip->id }}</h1>
        <div class="d-flex gap-2 flex-wrap">
            @if($trip->status !== 'COMPLETED')
                <a href="{{ route('amb.trips.edit', $trip) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Edit Trip
                </a>
                <a href="{{ route('amb.trips.reassign', $trip) }}" class="btn btn-outline-warning">
                    <i class="bi bi-arrow-repeat me-1"></i> Reassign
                </a>
                <a href="{{ route('amb.trips.clinical_notes.create', $trip) }}" class="btn btn-info">
                    <i class="fi fi-rr-stethoscope me-1"></i> Add Clinical Notes
                </a>
            @endif
            <a href="{{ route('amb.requests.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="row mt-4 g-4">
        {{-- Left column: Trip details + Timestamps --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Trip Details</strong>
                    @php
                        $statusColors = [
                            'ASSIGNED'            => 'secondary',
                            'EN_ROUTE_PICKUP'     => 'primary',
                            'ARRIVED_AT_PICKUP'   => 'info',
                            'PATIENT_ONBOARD'     => 'warning',
                            'EN_ROUTE_HOSPITAL'   => 'primary',
                            'ARRIVED_AT_HOSPITAL' => 'info',
                            'COMPLETED'           => 'success',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$trip->status] ?? 'secondary' }} fs-6">
                        {{ str_replace('_', ' ', $trip->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Patient</th><td>{{ $trip->request->patient?->patient_name ?? ($trip->request->temp_patient_id ?? 'Unknown') }}</td></tr>
                        <tr><th>Request Type</th><td>{{ $trip->request->request_type }}</td></tr>
                        <tr><th>Priority</th><td>{{ $trip->request->priority }}</td></tr>
                        <tr><th>Case Tag</th><td>{{ $trip->request->case_tag ?? '—' }}</td></tr>
                        <tr><th>Pickup</th><td>{{ $trip->request->pick_up_location }}</td></tr>
                        <tr><th>Drop</th><td>{{ $trip->request->drop_location ?? '—' }}</td></tr>
                        <tr><th>Destination Hospital</th><td>{{ $trip->request->destination_hospital ?? '—' }}</td></tr>
                        <tr><th>Ambulance</th><td>{{ $trip->ambulance->reg_no }} ({{ $trip->ambulance->type }})</td></tr>
                        <tr><th>Driver</th><td>{{ $trip->driver->name }}</td></tr>
                        <tr><th>Paramedic</th><td>{{ $trip->paramedic?->name ?? '—' }}</td></tr>
                        @if($trip->vendor)<tr><th>Vendor</th><td>{{ $trip->vendor->vendor_name }}</td></tr>@endif
                        <tr><th>ETA</th><td>{{ $trip->eta_minutes ? $trip->eta_minutes . ' min' : '—' }}</td></tr>
                        <tr><th>Distance</th><td>{{ $trip->distance_km ? $trip->distance_km . ' km' : '—' }}</td></tr>
                        <tr><th>Duration</th><td>{{ $trip->duration_minutes ? $trip->duration_minutes . ' min' : '—' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="card mt-3">
                <div class="card-header"><strong>Timestamps</strong></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th>Assigned</th><td>{{ $trip->created_at?->format('d M Y H:i') }}</td></tr>
                        <tr><th>En-route Start</th><td>{{ $trip->started_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Arrived at Pickup</th><td>{{ $trip->pickup_arrival_time?->format('d M Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Patient Onboard</th><td>{{ $trip->patient_onboard_time?->format('d M Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Arrived at Hospital</th><td>{{ $trip->hospital_arrival_time?->format('d M Y H:i') ?? '—' }}</td></tr>
                        <tr><th>Completed</th><td>{{ $trip->completed_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-md-7">
            {{-- Advance Status --}}
            @if($nextStatus)
            <div class="card mb-3">
                <div class="card-header"><strong>Advance Status</strong></div>
                <div class="card-body">
                    <form action="{{ route('amb.trips.updateStatus', $trip) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-end">
                        @csrf
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <div>
                            <label class="form-label">Delay Reason (optional)</label>
                            <input type="text" name="delay_reason" class="form-control" placeholder="Traffic, route issue...">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Mark as: {{ str_replace('_', ' ', $nextStatus) }}
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Clinical Notes --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Clinical Notes ({{ $trip->clinicalNotes->count() }})</strong>
                    @if($trip->status !== 'COMPLETED')
                        <a href="{{ route('amb.trips.clinical_notes.create', $trip) }}" class="btn btn-sm btn-outline-info">+ Add</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @forelse($trip->clinicalNotes as $note)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">
                                By {{ $note->recorder?->name ?? 'System' }} — {{ $note->recorded_at?->format('d M Y H:i') }}
                            </small>
                        </div>
                        <div class="row row-cols-3 row-cols-md-4 g-2 mb-2">
                            @if($note->bp)<div><span class="text-muted small">BP:</span> {{ $note->bp }}</div>@endif
                            @if($note->pulse)<div><span class="text-muted small">Pulse:</span> {{ $note->pulse }} bpm</div>@endif
                            @if($note->spo2)<div><span class="text-muted small">SpO₂:</span> {{ $note->spo2 }}%</div>@endif
                            @if($note->temperature)<div><span class="text-muted small">Temp:</span> {{ $note->temperature }}°F</div>@endif
                        </div>
                        <div class="mb-1">
                            @if($note->oxygen_given)<span class="badge bg-info me-1">Oxygen Given</span>@endif
                            @if($note->ventilator_used)<span class="badge bg-warning me-1">Ventilator Used</span>@endif
                            @if($note->emergency_intervention !== 'NONE')
                                <span class="badge bg-danger">{{ $note->emergency_intervention }}</span>
                            @endif
                        </div>
                        @if($note->clinical_notes)
                            <p class="mb-0 small">{{ $note->clinical_notes }}</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-center text-muted py-3 mb-0">No clinical notes yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Status History --}}
            <div class="card mb-3">
                <div class="card-header"><strong>Status History</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>From</th><th>To</th><th>Delay Reason</th><th>By</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            @forelse($trip->statusLogs as $log)
                            <tr>
                                <td>{{ $log->from_status ? str_replace('_', ' ', $log->from_status) : '—' }}</td>
                                <td><strong>{{ str_replace('_', ' ', $log->to_status) }}</strong></td>
                                <td>{{ $log->delay_reason ?? '—' }}</td>
                                <td>{{ $log->changedBy?->name ?? '—' }}</td>
                                <td>{{ $log->changed_at?->format('d M H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">No log entries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Assignment Audit --}}
            @if($trip->assignmentAudits->count())
            <div class="card mb-3">
                <div class="card-header"><strong>Assignment Audit</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Event</th><th>Override</th><th>Reason</th><th>By</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            @foreach($trip->assignmentAudits as $audit)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $audit->event_type === 'ASSIGNED' ? 'success' : ($audit->event_type === 'REASSIGNED' ? 'warning' : 'danger') }}">
                                        {{ $audit->event_type }}
                                    </span>
                                </td>
                                <td>{{ $audit->override_flag ? 'Yes' : 'No' }}</td>
                                <td>{{ $audit->reason ?? '—' }}</td>
                                <td>{{ optional($audit->changedBy)->name ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($audit->changed_at)->format('d M H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Trip Edit Audit --}}
            @if($trip->editAudits->count())
            <div class="card">
                <div class="card-header"><strong>Edit History</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Field</th><th>Old</th><th>New</th><th>Reason</th><th>By</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            @foreach($trip->editAudits as $edit)
                            <tr>
                                <td><code>{{ $edit->edited_field }}</code></td>
                                <td class="text-muted">{{ $edit->old_value ?? '—' }}</td>
                                <td>{{ $edit->new_value ?? '—' }}</td>
                                <td>{{ $edit->edit_reason ?? '—' }}</td>
                                <td>{{ optional($edit->editor)->name ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($edit->edited_at)->format('d M H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
