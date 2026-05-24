@extends('backend.layouts.master')

@section('title', 'NICU Admissions')

@section('content')
<div class="container-fluid">

    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">
                <i class="bi bi-emoji-smile text-primary"></i> NICU Admissions
            </h1>
            <div class="text-muted small">Newborn intensive care — admissions, risk flags, beds.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('nicu.admissions.index') }}" class="btn btn-outline-secondary btn-sm{{ ! request('status') ? ' active' : '' }}">All</a>
            @foreach(\App\Models\NicuAdmission::STATUSES as $st)
                <a href="{{ route('nicu.admissions.index', ['status' => $st]) }}"
                   class="btn btn-outline-secondary btn-sm{{ request('status') === $st ? ' active' : '' }}">
                    {{ $st }}
                </a>
            @endforeach
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Filters --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-0">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-sm"
                           placeholder="Admission no / baby / mother name…">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\NicuAdmission::STATUSES as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('nicu.admissions.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </div>
    </form>

    {{-- KPI strip --}}
    @php
        $totalActive = \App\Models\NicuAdmission::where('status', \App\Models\NicuAdmission::STATUS_ADMITTED)->count();
        $critical    = \App\Models\NicuAdmission::where('is_critical', 1)
                            ->whereIn('status', [\App\Models\NicuAdmission::STATUS_ADMITTED, \App\Models\NicuAdmission::STATUS_IN_PROGRESS])
                            ->count();
        $lbw         = \App\Models\NicuAdmission::where('is_low_birth_weight', 1)
                            ->whereIn('status', [\App\Models\NicuAdmission::STATUS_ADMITTED, \App\Models\NicuAdmission::STATUS_IN_PROGRESS])
                            ->count();
        $preterm     = \App\Models\NicuAdmission::where('is_preterm', 1)
                            ->whereIn('status', [\App\Models\NicuAdmission::STATUS_ADMITTED, \App\Models\NicuAdmission::STATUS_IN_PROGRESS])
                            ->count();
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="card border-info"><div class="card-body py-2">
            <div class="text-muted small">Active Babies</div>
            <div class="fs-4 fw-semibold text-info">{{ $totalActive }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card border-danger"><div class="card-body py-2">
            <div class="text-muted small">Critical (APGAR &lt; 7)</div>
            <div class="fs-4 fw-semibold text-danger">{{ $critical }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card border-warning"><div class="card-body py-2">
            <div class="text-muted small">Low Birth Weight</div>
            <div class="fs-4 fw-semibold text-warning">{{ $lbw }}</div>
        </div></div></div>
        <div class="col-md-3"><div class="card border-warning"><div class="card-body py-2">
            <div class="text-muted small">Preterm (&lt; 37 wk)</div>
            <div class="fs-4 fw-semibold text-warning">{{ $preterm }}</div>
        </div></div></div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px;">Adm No</th>
                            <th>Baby</th>
                            <th>Mother</th>
                            <th style="width:90px;">Source</th>
                            <th style="width:90px;">Bed</th>
                            <th style="width:130px;">Birth</th>
                            <th>Risk</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:80px;" class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $adm)
                            <tr>
                                <td>
                                    <a href="{{ route('nicu.admissions.show', $adm) }}" class="fw-semibold">
                                        {{ $adm->admission_no }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $adm->baby?->patient_name ?? '—' }}</div>
                                    <small class="text-muted">{{ $adm->baby?->gender }}</small>
                                </td>
                                <td>{{ $adm->mother?->patient_name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $adm->source_type }}</span>
                                </td>
                                <td>
                                    @if($adm->bed)
                                        <span class="badge bg-info">{{ $adm->bed->name ?? ('#'.$adm->bed_id) }}</span>
                                    @else
                                        <span class="text-muted small">No bed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        @if($adm->birth_weight_grams)
                                            <i class="bi bi-droplet text-muted"></i>
                                            {{ rtrim(rtrim((string) $adm->birth_weight_grams, '0'), '.') }}g<br>
                                        @endif
                                        @if($adm->gestational_age_weeks)
                                            {{ $adm->gestational_age_weeks }}w
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @foreach($adm->riskBadges() as $rb)
                                        @php [$label, $cls] = $rb; @endphp
                                        <span class="badge {{ $cls }}">{{ $label }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge {{ $adm->status_badge_class }}">{{ $adm->status }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('nicu.admissions.show', $adm) }}"
                                       class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No NICU admissions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($admissions->hasPages())
            <div class="card-footer bg-white">
                {{ $admissions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
