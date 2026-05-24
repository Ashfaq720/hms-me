@extends('backend.layouts.master')
@section('title','Service Packages')

@section('content')
<div class="container-fluid">

    {{-- Hero --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-box-seam text-primary me-1"></i> Service Packages</h1>
            <small class="text-muted">Bundled packages — wired to <strong>Charge master</strong> for billing and <strong>Bed types</strong> for bed-wise pricing.</small>
        </div>
        @can('service_packages_create')
            <a href="{{ route('setup.service-packages.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Package
            </a>
        @endcan
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- KPI strip --}}
    @php
        $totalPackages = $packages->total();
        $activeCount   = $packages->getCollection()->where('status', 'Active')->count();
        $totalRevenue  = $packages->getCollection()->sum('revenue_posted');
        $totalApps     = $packages->getCollection()->sum('applications_count');
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6"><div class="card border-primary h-100"><div class="card-body py-2">
            <div class="small text-muted">Total Packages</div>
            <div class="fs-4 fw-bold text-primary">{{ $totalPackages }}</div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="card border-success h-100"><div class="card-body py-2">
            <div class="small text-muted">Active (this page)</div>
            <div class="fs-4 fw-bold text-success">{{ $activeCount }}</div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="card border-info h-100"><div class="card-body py-2">
            <div class="small text-muted">Applications</div>
            <div class="fs-4 fw-bold text-info">{{ $totalApps }}</div>
        </div></div></div>
        <div class="col-md-3 col-6"><div class="card border-warning h-100"><div class="card-body py-2">
            <div class="small text-muted">Revenue Posted</div>
            <div class="fs-4 fw-bold text-warning">৳{{ number_format((float) $totalRevenue, 0) }}</div>
        </div></div></div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                       placeholder="Code or name">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Type</label>
                <select name="package_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected(request('package_type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Department</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" @selected((int) request('department_id') === $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="Active"   @selected(request('status') === 'Active')>Active</option>
                    <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code / Name</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th>Bed Type</th>
                            <th class="text-end">Base Price</th>
                            <th>Charges</th>
                            <th>Bed Variants</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('setup.service-packages.show', $p) }}" class="fw-semibold text-decoration-none">
                                        {{ $p->name }}
                                    </a>
                                    <div class="small text-muted"><code>{{ $p->code }}</code>
                                        @if($p->admission_type)
                                            · <span class="badge bg-light text-dark border" style="font-size:9px;">{{ $p->admission_type }}</span>
                                        @endif
                                        @if($p->duration_days)
                                            · {{ $p->duration_days }}d
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge bg-info">{{ $p->package_type }}</span></td>
                                <td class="small">{{ optional($p->department)->name ?? '—' }}</td>
                                <td>
                                    @if($p->bedType)
                                        <span class="badge bg-light text-dark border">{{ $p->bedType->name }}</span>
                                        @if($p->bedType->is_icu)
                                            <span class="badge bg-danger" style="font-size:9px;">{{ $p->bedType->icu_type ?? 'ICU' }}</span>
                                        @endif
                                    @else <span class="text-muted small">—</span> @endif
                                </td>
                                <td class="text-end"><strong>৳{{ number_format((float) $p->base_price, 0) }}</strong></td>
                                <td>
                                    @php
                                        $totalItems = (int) $p->items_count;
                                        $chargedItems = (int) $p->items_with_charge_count;
                                    @endphp
                                    <span class="badge bg-{{ $chargedItems ? 'success-subtle text-success' : 'light text-dark' }} border" title="Items linked to Charge master">
                                        <i class="bi bi-receipt"></i> {{ $chargedItems }}/{{ $totalItems }}
                                    </span>
                                </td>
                                <td>
                                    @if((int) $p->bed_prices_count > 0)
                                        <span class="badge bg-primary-subtle text-primary border" title="Bed-type price variants">
                                            <i class="bi bi-hospital"></i> {{ $p->bed_prices_count }}
                                        </span>
                                    @else <span class="text-muted small">—</span> @endif
                                </td>
                                <td>
                                    @if((int) $p->applications_count > 0)
                                        <span class="badge bg-info-subtle text-dark border" title="IPD attachments">
                                            <i class="bi bi-people"></i> {{ $p->applications_count }}
                                        </span>
                                    @endif
                                    @if((float) $p->revenue_posted > 0)
                                        <div class="small text-success mt-1">৳{{ number_format((float) $p->revenue_posted, 0) }}</div>
                                    @endif
                                    @if((int) $p->applications_count === 0 && (float) $p->revenue_posted === 0)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td><span class="badge {{ $p->status_badge_class }}">{{ $p->status }}</span></td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('setup.service-packages.show', $p) }}"
                                       class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                                    @can('service_packages_edit')
                                        <a href="{{ route('setup.service-packages.edit', $p) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('service_packages_delete')
                                        <form action="{{ route('setup.service-packages.destroy', $p) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Archive this package?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Archive"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-50"></i>
                                No packages found. Create one to get started.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $packages->links() }}</div>
</div>
@endsection
