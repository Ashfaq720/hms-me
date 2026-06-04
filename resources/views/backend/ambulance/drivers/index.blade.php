@extends('backend.layouts.master')

@section('title', 'Drivers')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Drivers</h1>
        <a href="{{ route('amb.drivers.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Driver
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
                        <th>Name</th>
                        <th>Phone</th>
                        <th>License No</th>
                        <th>License Expiry</th>
                        <th>Shift</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr>
                        <td><strong>{{ $driver->name }}</strong></td>
                        <td>{{ $driver->phone ?? '—' }}</td>
                        <td>{{ $driver->license_number ?? '—' }}</td>
                        <td>
                            @if($driver->license_expiry)
                                @if($driver->isLicenseExpired())
                                    <span class="badge bg-danger">Expired {{ $driver->license_expiry->format('d M Y') }}</span>
                                @elseif($driver->isLicenseExpiringSoon())
                                    <span class="badge bg-warning text-dark">{{ $driver->license_expiry->format('d M Y') }}</span>
                                @else
                                    <span class="text-success small">{{ $driver->license_expiry->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $driver->shift ?? '—' }}</td>
                        <td>
                            @if($driver->availability === 'AVAILABLE')
                                <span class="badge bg-success">Available</span>
                            @elseif($driver->availability === 'ASSIGNED')
                                <span class="badge bg-primary">Assigned</span>
                            @else
                                <span class="badge bg-secondary">Off Duty</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->status === 'ACTIVE')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('amb.drivers.show', $driver) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('amb.drivers.edit', $driver) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if($driver->availability !== 'ASSIGNED')
                            <form action="{{ route('amb.drivers.destroy', $driver) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this driver?')">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No drivers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
