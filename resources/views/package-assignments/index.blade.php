@extends('backend.layouts.master')
@section('title','Package Assignments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">Package Assignments</h1>
            <small class="text-muted">Every service package attached to an IPD admission, across all patients.</small>
        </div>
        @can('service_packages_access')
            <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-collection me-1"></i> Package Master
            </a>
        @endcan
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Patient</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Patient name">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Package</label>
                <select name="service_package_id" class="form-select">
                    <option value="">All</option>
                    @foreach($packages as $p)
                        <option value="{{ $p->id }}" @selected((int) request('service_package_id') === $p->id)>
                            {{ $p->code }} — {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="{{ route('package-assignments.index') }}" class="btn btn-outline-secondary" title="Reset">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Package</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Agreed Price</th>
                        <th>Applied At</th>
                        <th>Approved By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        @php
                            $pkg     = $a->package;
                            $patient = optional($a->ipdAdmission)->patient;
                        @endphp
                        <tr>
                            <td><strong>#{{ $a->id }}</strong></td>
                            <td>
                                @if($patient && $a->ipd_admission_id)
                                    <a href="{{ route('ipd-patients.ipd-patients.show', $a->ipd_admission_id) }}#packages">
                                        {{ $patient->patient_name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ optional($pkg)->code }}</strong>
                                <span class="text-muted small d-block">{{ optional($pkg)->name }}</span>
                            </td>
                            <td><span class="badge bg-info">{{ optional($pkg)->package_type ?? '—' }}</span></td>
                            <td><span class="badge {{ $a->status_badge_class }}">{{ $a->status }}</span></td>
                            <td class="text-end">৳{{ number_format($a->effectivePrice(), 2) }}</td>
                            <td class="small text-muted">{{ optional($a->applied_at)->format('Y-m-d H:i') }}
                                @if($a->appliedBy)<div>by {{ $a->appliedBy->name ?? '#' . $a->applied_by }}</div>@endif
                            </td>
                            <td class="small">
                                @if($a->approver)
                                    {{ $a->approver->name }}
                                    <div class="text-muted">{{ optional($a->approved_at)->format('Y-m-d H:i') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('ipd_packages_approve')
                                    @if($a->status === \App\Models\IpdPatientPackage::STATUS_PENDING_APPROVAL)
                                        <form action="{{ route('setup.ipd-patient-packages.approve', $a->id) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-success" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('ipd_packages_apply')
                                    @if(in_array($a->status, [\App\Models\IpdPatientPackage::STATUS_CONFIRMED, \App\Models\IpdPatientPackage::STATUS_PARTIALLY_USED]))
                                        <form action="{{ route('setup.ipd-patient-packages.complete', $a->id) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-primary" title="Mark Complete">
                                                <i class="bi bi-check2-all"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($a->status === \App\Models\IpdPatientPackage::STATUS_COMPLETED)
                                        <form action="{{ route('setup.ipd-patient-packages.close', $a->id) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="Close">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @if($patient && $a->ipd_admission_id)
                                    <a href="{{ route('ipd-patients.ipd-patients.show', $a->ipd_admission_id) }}#packages"
                                       class="btn btn-sm btn-outline-secondary" title="Open patient">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">
                            No package assignments match these filters.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $assignments->links() }}</div>
</div>
@endsection
