@extends('backend.layouts.master')
@section('title', 'NICU Admissions')

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-clipboard2-heart text-info"></i> NICU Admissions</h4>
            <small class="text-muted">Neonatal Intensive Care · {{ $stats['admitted'] }} active · {{ $stats['critical'] }} critical</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nicu.dashboard') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-grid"></i> Dashboard</a>
            <a href="{{ route('ipd-patients.create') }}?nicu=1" class="btn btn-sm btn-info">
                <i class="bi bi-plus-lg"></i> New NICU Admission
            </a>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success py-2">{{ session('success') }}</div> @endif

    {{-- KPI tiles --}}
    <div class="row g-2 mb-3">
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-primary"><i class="bi bi-clipboard2-heart"></i> Total</small>
                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-success"><i class="bi bi-bed"></i> Admitted</small>
                    <h4 class="mb-0">{{ $stats['admitted'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Critical</small>
                    <h4 class="mb-0">{{ $stats['critical'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-warning"><i class="bi bi-stopwatch"></i> Preterm</small>
                    <h4 class="mb-0">{{ $stats['preterm'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-info"><i class="bi bi-graph-down"></i> Low Birth Wt</small>
                    <h4 class="mb-0">{{ $stats['lbw'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm bg-secondary bg-opacity-10">
                <div class="card-body py-2 px-3">
                    <small class="text-secondary"><i class="bi bi-box-arrow-right"></i> Discharged</small>
                    <h4 class="mb-0">{{ $stats['discharged'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3 d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label small mb-1 text-muted">Search</label>
                <input name="search" value="{{ request('search') }}" placeholder="🔍 Baby ID / Name" class="form-control form-control-sm" style="width:180px;">
            </div>
            <div>
                <label class="form-label small mb-1 text-muted">Status</label>
                <select name="status" class="form-select form-select-sm" style="width:140px;">
                    <option value="">All</option>
                    @foreach (['admitted', 'discharged', 'transferred', 'expired'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small mb-1 text-muted">Risk Flag</label>
                <select name="risk" class="form-select form-select-sm" style="width:160px;">
                    <option value="">All</option>
                    <option value="critical" @selected(request('risk') === 'critical')>🔴 Critical only</option>
                    <option value="preterm"  @selected(request('risk') === 'preterm')>🟡 Preterm only</option>
                    <option value="lbw"      @selected(request('risk') === 'lbw')>🟡 Low Birth Weight</option>
                </select>
            </div>
            <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['search', 'status', 'risk']))
                <a href="{{ route('nicu.admissions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Clear</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:40px;">#</th>
                        <th>Baby ID</th>
                        <th>Baby Name</th>
                        <th>Mother</th>
                        <th>Source</th>
                        <th>Birth wt / GA</th>
                        <th>APGAR (1·5·10)</th>
                        <th>Risk Flags</th>
                        <th>Status</th>
                        <th>Admitted</th>
                        <th class="text-end pe-3" style="width:200px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($admissions as $a)
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('nicu.admissions.show', $a->id) }}" class="text-decoration-none">
                                <strong class="text-info">{{ $a->baby_id }}</strong>
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ optional($a->patient)->patient_name ?: '—' }}</div>
                            @if (optional($a->patient)->gender)
                                <small class="text-muted">{{ $a->patient->gender }}</small>
                            @endif
                        </td>
                        <td><small>{{ optional($a->mother)->patient_name ?: '—' }}</small></td>
                        <td><span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $a->source ?: '—' }}</span></td>
                        <td>
                            {{ $a->birth_weight_g ? number_format($a->birth_weight_g) . 'g' : '—' }}
                            <br><small class="text-muted">{{ $a->gestational_age_weeks ? $a->gestational_age_weeks.'w' : '—' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $a->apgar_1min ?? '?' }} · {{ $a->apgar_5min ?? '?' }} · {{ $a->apgar_10min ?? '?' }}
                            </span>
                        </td>
                        <td>
                            @if ($a->is_critical)
                                <span class="badge bg-danger" title="Critical case">CRIT</span>
                            @endif
                            @if ($a->is_preterm)
                                <span class="badge bg-warning text-dark" title="Preterm">PT</span>
                            @endif
                            @if ($a->is_low_birth_weight)
                                <span class="badge bg-warning text-dark" title="Low birth weight">LBW</span>
                            @endif
                            @if (! $a->is_critical && ! $a->is_preterm && ! $a->is_low_birth_weight)
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $col = ['admitted' => 'success', 'discharged' => 'secondary', 'transferred' => 'info', 'expired' => 'dark'][$a->status] ?? 'warning';
                            @endphp
                            <span class="badge bg-{{ $col }}">{{ ucfirst($a->status) }}</span>
                        </td>
                        <td><small>{{ optional($a->admission_time)->diffForHumans() ?? '—' }}</small></td>
                        <td class="text-end pe-3 text-nowrap">
                            <a href="{{ route('nicu.admissions.show', $a->id) }}" class="btn btn-sm btn-outline-primary" title="View admission">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Quick actions">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item small" href="{{ route('nicu.vitals.index') }}?admission_id={{ $a->id }}"><i class="bi bi-activity text-danger"></i> Vitals</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.growth.index') }}?admission_id={{ $a->id }}"><i class="bi bi-graph-up text-success"></i> Growth</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.feeding.index') }}?admission_id={{ $a->id }}"><i class="bi bi-cup-hot text-warning"></i> Feeding</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.medications.index') }}?admission_id={{ $a->id }}"><i class="bi bi-capsule text-info"></i> Medications</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.procedures.index') }}?admission_id={{ $a->id }}"><i class="bi bi-bandaid text-primary"></i> Procedures</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.infections.index') }}?admission_id={{ $a->id }}"><i class="bi bi-shield-exclamation text-danger"></i> Infections</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.consents.index') }}?admission_id={{ $a->id }}"><i class="bi bi-file-text"></i> Consents</a></li>
                                    <li><a class="dropdown-item small" href="{{ route('nicu.resources.index') }}?admission_id={{ $a->id }}"><i class="bi bi-plug"></i> Resources</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-5">
                        <i class="bi bi-clipboard2-heart display-4"></i>
                        <p class="mt-2 mb-0">No NICU admissions yet</p>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($admissions->hasPages())
            <div class="card-footer bg-white">{{ $admissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
