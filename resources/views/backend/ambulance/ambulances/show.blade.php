@extends('backend.layouts.master')

@section('title', 'Ambulance — {{ $ambulance->reg_no }}')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Ambulance — {{ $ambulance->reg_no }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('amb.ambulances.edit', $ambulance) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('amb.ambulances.index') }}" class="btn btn-light">Back to List</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    {{-- Main Info --}}
    <div class="card mt-4">
        <div class="card-header"><strong>Ambulance Details</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Registration Number</div>
                    <div class="fw-semibold">{{ $ambulance->reg_no }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Type</div>
                    <div>
                        <span class="badge bg-{{ match($ambulance->type) {
                            'ICU','ALS' => 'danger',
                            'NEONATAL'  => 'warning',
                            'EMERGENCY' => 'orange',
                            default     => 'secondary'
                        } }}">{{ $ambulance->type }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div>
                        @if($ambulance->status === 'AVAILABLE')
                            <span class="badge bg-success">Available</span>
                        @elseif($ambulance->status === 'ON_TRIP')
                            <span class="badge bg-primary">On Trip</span>
                        @else
                            <span class="badge bg-danger">Maintenance</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Ownership</div>
                    <div>{{ $ambulance->ownership }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Vendor</div>
                    <div>
                        @if($ambulance->vendor)
                            <a href="{{ route('amb.vendors.show', $ambulance->vendor) }}">{{ $ambulance->vendor->vendor_name }}</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Oxygen Capacity</div>
                    <div>{{ $ambulance->oxygen_capacity ?? '—' }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Stretcher Capacity</div>
                    <div>{{ $ambulance->stretcher_capacity }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Attendant Seats</div>
                    <div>{{ $ambulance->attendants_capacity }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Fitness Expiry</div>
                    <div>
                        @if($ambulance->fitness_expiry)
                            @if($ambulance->isFitnessExpired())
                                <span class="badge bg-danger">Expired {{ $ambulance->fitness_expiry->format('d M Y') }}</span>
                            @elseif($ambulance->isExpiringSoon('fitness_expiry'))
                                <span class="badge bg-warning text-dark">{{ $ambulance->fitness_expiry->format('d M Y') }} (expiring soon)</span>
                            @else
                                <span class="text-success">{{ $ambulance->fitness_expiry->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Insurance Expiry</div>
                    <div>
                        @if($ambulance->insurance_expiry)
                            @if($ambulance->isInsuranceExpired())
                                <span class="badge bg-danger">Expired {{ $ambulance->insurance_expiry->format('d M Y') }}</span>
                            @elseif($ambulance->isExpiringSoon('insurance_expiry'))
                                <span class="badge bg-warning text-dark">{{ $ambulance->insurance_expiry->format('d M Y') }} (expiring soon)</span>
                            @else
                                <span class="text-success">{{ $ambulance->insurance_expiry->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">License Validity</div>
                    <div>
                        @if($ambulance->license_validity)
                            @if($ambulance->license_validity->isPast())
                                <span class="badge bg-danger">Expired {{ $ambulance->license_validity->format('d M Y') }}</span>
                            @elseif($ambulance->isExpiringSoon('license_validity'))
                                <span class="badge bg-warning text-dark">{{ $ambulance->license_validity->format('d M Y') }} (expiring soon)</span>
                            @else
                                <span class="text-success">{{ $ambulance->license_validity->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Equipment (AMB-BR-004) --}}
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Equipment</strong>
            @if($ambulance->requiresEquipmentCheck() && $ambulance->equipment->isEmpty())
                <span class="badge bg-danger">AMB-BR-004: Equipment required for {{ $ambulance->type }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($ambulance->equipment->isNotEmpty())
            <table class="table table-sm mb-3">
                <thead class="table-light">
                    <tr>
                        <th>Equipment</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Condition</th>
                        <th>Last Checked</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ambulance->equipment as $item)
                    <tr>
                        <td>{{ $item->equipment_name }}</td>
                        <td>{{ $item->equipment_type ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            <span class="badge bg-{{ match($item->condition) {
                                'GOOD'          => 'success',
                                'NEEDS_SERVICE' => 'warning',
                                default         => 'danger'
                            } }}">{{ str_replace('_', ' ', $item->condition) }}</span>
                        </td>
                        <td>{{ $item->last_checked?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <form action="{{ route('amb.ambulances.equipment.destroy', [$ambulance, $item]) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this equipment?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <p class="text-muted mb-3">No equipment mapped.</p>
            @endif

            {{-- Add Equipment --}}
            <form action="{{ route('amb.ambulances.equipment.store', $ambulance) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Equipment Name <span class="text-danger">*</span></label>
                        <input type="text" name="equipment_name" class="form-control form-control-sm @error('equipment_name') is-invalid @enderror"
                            value="{{ old('equipment_name') }}" required>
                        @error('equipment_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <input type="text" name="equipment_type" class="form-control form-control-sm"
                            value="{{ old('equipment_type') }}" placeholder="e.g. Monitor">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                            value="{{ old('quantity', 1) }}" min="1" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Condition <span class="text-danger">*</span></label>
                        <select name="condition" class="form-select form-select-sm @error('condition') is-invalid @enderror" required>
                            <option value="GOOD"          @selected(old('condition','GOOD') == 'GOOD')>Good</option>
                            <option value="NEEDS_SERVICE" @selected(old('condition') == 'NEEDS_SERVICE')>Needs Service</option>
                            <option value="OUT_OF_ORDER"  @selected(old('condition') == 'OUT_OF_ORDER')>Out of Order</option>
                        </select>
                        @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Last Checked</label>
                        <input type="date" name="last_checked" class="form-control form-control-sm"
                            value="{{ old('last_checked') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Add Equipment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Recent Trips --}}
    <div class="card mt-4">
        <div class="card-header"><strong>Recent Trips</strong></div>
        <div class="card-body p-0">
            @if($ambulance->trips->isNotEmpty())
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Dispatched</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ambulance->trips as $trip)
                    <tr>
                        <td>{{ $trip->id }}</td>
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
