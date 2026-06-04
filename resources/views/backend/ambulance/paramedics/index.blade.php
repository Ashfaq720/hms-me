@extends('backend.layouts.master')

@section('title', 'Paramedics')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Paramedics</h1>
        <a href="{{ route('amb.paramedics.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Paramedic
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
                        <th>Certification</th>
                        <th>Skill Level</th>
                        <th>Cert Expiry</th>
                        <th>Shift</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paramedics as $paramedic)
                    <tr>
                        <td><strong>{{ $paramedic->name }}</strong></td>
                        <td>{{ $paramedic->phone ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $paramedic->certification === 'ACLS' ? 'danger' : 'secondary' }}">
                                {{ $paramedic->certification }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $paramedic->skill_level === 'ADVANCED' ? 'primary' : 'secondary' }}">
                                {{ $paramedic->skill_level }}
                            </span>
                        </td>
                        <td>
                            @if($paramedic->cert_expiry)
                                @if($paramedic->isCertExpired())
                                    <span class="badge bg-danger">Expired {{ $paramedic->cert_expiry->format('d M Y') }}</span>
                                @elseif($paramedic->isCertExpiringSoon())
                                    <span class="badge bg-warning text-dark">{{ $paramedic->cert_expiry->format('d M Y') }}</span>
                                @else
                                    <span class="text-success small">{{ $paramedic->cert_expiry->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $paramedic->shift ?? '—' }}</td>
                        <td>
                            @if($paramedic->availability === 'AVAILABLE')
                                <span class="badge bg-success">Available</span>
                            @elseif($paramedic->availability === 'ASSIGNED')
                                <span class="badge bg-primary">Assigned</span>
                            @else
                                <span class="badge bg-secondary">Off Duty</span>
                            @endif
                        </td>
                        <td>
                            @if($paramedic->status === 'ACTIVE')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('amb.paramedics.show', $paramedic) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('amb.paramedics.edit', $paramedic) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if($paramedic->availability !== 'ASSIGNED')
                            <form action="{{ route('amb.paramedics.destroy', $paramedic) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this paramedic?')">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No paramedics found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
