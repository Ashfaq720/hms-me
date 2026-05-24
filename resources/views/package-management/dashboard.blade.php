@extends('backend.layouts.master')
@section('title','Package Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-box-seam me-2"></i>Package Management</h1>
            <small class="text-muted">Overview of all service packages, assignments, and pending approvals.</small>
        </div>
        <div class="d-flex gap-2">
            @can('service_packages_create')
                <a href="{{ route('setup.service-packages.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> New Package
                </a>
            @endcan
            @can('service_packages_access')
                <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-collection me-1"></i> Package Master
                </a>
            @endcan
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('setup.service-packages.index') }}" class="text-decoration-none">
                <div class="card border-start border-4 border-primary h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Total Packages</div>
                        <div class="h3 mb-0 text-primary">{{ $kpis['total_packages'] }}</div>
                        <div class="small text-muted mt-1">{{ $kpis['active_packages'] }} active</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('package-assignments.index') }}" class="text-decoration-none">
                <div class="card border-start border-4 border-info h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Total Assignments</div>
                        <div class="h3 mb-0 text-info">{{ $kpis['total_assignments'] }}</div>
                        <div class="small text-muted mt-1">{{ $kpis['active_assignments'] }} in use</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('package-management.pending') }}" class="text-decoration-none">
                <div class="card border-start border-4 border-warning h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Pending Approval</div>
                        <div class="h3 mb-0 text-warning">{{ $kpis['pending_approval'] }}</div>
                        <div class="small text-muted mt-1">
                            @if($kpis['pending_approval'] > 0)
                                <i class="bi bi-shield-exclamation"></i> needs action
                            @else
                                clear
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">Billable Revenue (snapshot)</div>
                    <div class="h3 mb-0 text-success">৳{{ number_format((float) $billableTotal, 0) }}</div>
                    <div class="small text-muted mt-1">{{ $kpis['completed'] }} completed · {{ $kpis['cancelled'] }} cancelled</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-trophy me-1"></i> Top Packages by Usage</strong>
                    <a href="{{ route('setup.service-packages.index') }}" class="small">All →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Code</th><th>Name</th><th>Type</th><th class="text-end">Used</th><th class="text-end">Price</th></tr>
                        </thead>
                        <tbody>
                            @forelse($topPackages as $p)
                                <tr>
                                    <td><a href="{{ route('setup.service-packages.show', $p) }}">{{ $p->code }}</a></td>
                                    <td>{{ $p->name }}</td>
                                    <td><span class="badge bg-info">{{ $p->package_type }}</span></td>
                                    <td class="text-end"><strong>{{ $p->applications_count }}</strong></td>
                                    <td class="text-end">৳{{ number_format((float) $p->base_price, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No package usage yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-clock-history me-1"></i> Recent Assignments</strong>
                    <a href="{{ route('package-assignments.index') }}" class="small">All →</a>
                </div>
                <ul class="list-group list-group-flush" style="max-height: 360px; overflow-y: auto;">
                    @forelse($recentAssignments as $a)
                        <li class="list-group-item d-flex justify-content-between align-items-start small">
                            <div class="flex-grow-1">
                                <strong>{{ optional($a->package)->code }}</strong> —
                                {{ optional($a->package)->name }}
                                <span class="badge {{ $a->status_badge_class }} ms-1">{{ $a->status }}</span>
                                <div class="text-muted">
                                    Patient: {{ optional(optional($a->ipdAdmission)->patient)->patient_name ?? '—' }}
                                    @if($a->appliedBy) · by {{ $a->appliedBy->name }}@endif
                                </div>
                            </div>
                            <div class="text-end text-muted">
                                <div>৳{{ number_format($a->effectivePrice(), 0) }}</div>
                                <div class="small">{{ optional($a->applied_at)->diffForHumans() }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">No assignments yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
