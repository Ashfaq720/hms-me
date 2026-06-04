@extends('backend.layouts.master')

@section('title', 'Ambulance Vendors')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Ambulance Vendors</h1>
        <a href="{{ route('amb.vendors.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Vendor
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-body">
            <table class="table display table-row-rounded">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Vendor Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>SLA (min)</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $vendor->vendor_code }}</td>
                        <td>{{ $vendor->vendor_name }}</td>
                        <td>{{ $vendor->contact_person ?? '—' }}</td>
                        <td>{{ $vendor->phone ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $vendor->ambulance_type }}</span></td>
                        <td>{{ $vendor->sla_response_minutes }}</td>
                        <td>{{ $vendor->performance_score ?? '—' }}</td>
                        <td>
                            @if($vendor->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('amb.vendors.show', $vendor) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('amb.vendors.edit', $vendor) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('amb.vendors.destroy', $vendor) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this vendor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">No vendors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
