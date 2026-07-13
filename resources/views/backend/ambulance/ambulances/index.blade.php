@extends('backend.layouts.master')

@section('title', 'Ambulances')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Ambulances</h1>
        <a href="{{ route('amb.ambulances.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Ambulance
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-body p-0">
            <table class="table display table-row-rounded mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reg No</th>
                        <th>Type</th>
                        <th>Ownership</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Fitness Expiry</th>
                        <th>Insurance Expiry</th>
                        <th>Equipment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ambulances as $ambulance)
                    <tr>
                        <td><strong>{{ $ambulance->reg_no }}</strong></td>
                        <td>
                            <span class="badge bg-{{ match($ambulance->type) {
                                'ICU','ALS' => 'danger',
                                'NEONATAL'  => 'warning',
                                'EMERGENCY' => 'orange',
                                default     => 'secondary'
                            } }}">{{ $ambulance->type }}</span>
                        </td>
                        <td>{{ $ambulance->ownership }}</td>
                        <td>{{ $ambulance->vendor?->vendor_name ?? '—' }}</td>
                        <td>
                            @if($ambulance->status === 'AVAILABLE')
                                <span class="badge bg-success">Available</span>
                            @elseif($ambulance->status === 'ON_TRIP')
                                <span class="badge bg-primary">On Trip</span>
                            @else
                                <span class="badge bg-danger">Maintenance</span>
                            @endif
                        </td>
                        <td>
                            @if($ambulance->fitness_expiry)
                                @if($ambulance->isFitnessExpired())
                                    <span class="badge bg-danger">Expired {{ $ambulance->fitness_expiry->format('d M Y') }}</span>
                                @elseif($ambulance->isExpiringSoon('fitness_expiry'))
                                    <span class="badge bg-warning text-dark">{{ $ambulance->fitness_expiry->format('d M Y') }}</span>
                                @else
                                    <span class="text-success small">{{ $ambulance->fitness_expiry->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($ambulance->insurance_expiry)
                                @if($ambulance->isInsuranceExpired())
                                    <span class="badge bg-danger">Expired {{ $ambulance->insurance_expiry->format('d M Y') }}</span>
                                @elseif($ambulance->isExpiringSoon('insurance_expiry'))
                                    <span class="badge bg-warning text-dark">{{ $ambulance->insurance_expiry->format('d M Y') }}</span>
                                @else
                                    <span class="text-success small">{{ $ambulance->insurance_expiry->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $ambulance->equipment_count > 0 ? 'info' : 'secondary' }}">
                                {{ $ambulance->equipment_count }} item(s)
                            </span>
                            @if($ambulance->requiresEquipmentCheck() && $ambulance->equipment_count === 0)
                                <span class="badge bg-danger ms-1" title="AMB-BR-004: Equipment required">!</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('amb.ambulances.show', $ambulance) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('amb.ambulances.edit', $ambulance) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if($ambulance->status !== 'ON_TRIP')
                            <form action="{{ route('amb.ambulances.destroy', $ambulance) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this ambulance?')">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No ambulances found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
