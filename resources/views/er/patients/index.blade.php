@extends('backend.layouts.master')

@section('title', 'ER Patient Tracking')

@section('content')
<div class="container-fluid">

    {{-- Tab strip --}}
    <ul class="nav nav-pills mb-3 flex-wrap gap-1">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('er.dashboard') ? 'active' : '' }}"
               href="{{ route('er.dashboard') }}">
                <i class="bi bi-speedometer2 me-1"></i> Live Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('er.patients.*') ? 'active' : '' }}"
               href="{{ route('er.patients.index') }}">
                <i class="bi bi-clipboard2-pulse me-1"></i> Tracking Board
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('amb.er.incoming') ? 'active' : '' }}"
               href="{{ route('amb.er.incoming') }}">
                <i class="bi bi-truck me-1"></i> Incoming Ambulances
            </a>
        </li>
        <li class="nav-item ms-auto">
            <a class="nav-link btn-sm" href="{{ route('front_desk.er_registration') }}">
                <i class="bi bi-plus-lg me-1"></i> Register ER Patient
            </a>
        </li>
    </ul>

    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">ER Tracking Board</h1>
            <div class="text-muted small">All ER patients — filter by triage, status, search by name/MRN.</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif

    {{-- Quick view chips --}}
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('er.patients.index') }}"
           class="btn btn-sm btn-outline-secondary{{ ! request('view') ? ' active' : '' }}">All</a>
        <a href="{{ route('er.patients.index', ['view' => 'today']) }}"
           class="btn btn-sm btn-outline-primary{{ request('view') === 'today' ? ' active' : '' }}">Today</a>
        <a href="{{ route('er.patients.index', ['view' => 'active']) }}"
           class="btn btn-sm btn-outline-info{{ request('view') === 'active' ? ' active' : '' }}">Active (on floor)</a>
        <a href="{{ route('er.patients.index', ['priority' => 'CRITICAL', 'view' => 'active']) }}"
           class="btn btn-sm btn-outline-danger{{ request('priority') === 'CRITICAL' ? ' active' : '' }}">🔴 Critical</a>
        <a href="{{ route('er.patients.index', ['priority' => 'HIGH', 'view' => 'active']) }}"
           class="btn btn-sm btn-outline-warning{{ request('priority') === 'HIGH' ? ' active' : '' }}">🟠 Urgent</a>
        <a href="{{ route('er.patients.index', ['priority' => 'NORMAL', 'view' => 'active']) }}"
           class="btn btn-sm btn-outline-success{{ request('priority') === 'NORMAL' ? ' active' : '' }}">🟢 Stable</a>
    </div>

    {{-- Filter form --}}
    <form method="GET" class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-0">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-sm" placeholder="Patient name / MRN / mobile">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\FrontDesk\ErPatient::STATUSES as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Triage</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\FrontDesk\ErPatient::PRIORITIES as $pri)
                            <option value="{{ $pri }}" @selected(request('priority') === $pri)>{{ $pri }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('er.patients.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-x-circle"></i></a>
                </div>
            </div>
        </div>
    </form>

    {{-- Tracking table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:8px;"></th>
                            <th>Patient</th>
                            <th>Triage</th>
                            <th>Status</th>
                            <th>Arrival</th>
                            <th>Wait</th>
                            <th>Doctor</th>
                            <th>Description</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $p)
                            <tr>
                                <td style="background-color:var(--bs-{{ $p->triage_color }})"></td>
                                <td>
                                    <a href="{{ route('er.patients.show', $p) }}" class="fw-semibold text-decoration-none">
                                        {{ $p->patient?->patient_name ?? '#'.$p->id }}
                                    </a>
                                    <div class="small text-muted">
                                        {{ $p->patient?->mrn }}
                                        @if($p->patient?->gender) · {{ $p->patient->gender }} @endif
                                        @if($p->age) · {{ $p->age }}y @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $p->triage_color }}">{{ $p->priority }}</span>
                                </td>
                                <td><span class="badge {{ $p->status_badge_class }}">{{ $p->status }}</span></td>
                                <td class="small">
                                    {{ optional($p->arrival_time)?->format('Y-m-d') }}<br>
                                    <span class="text-muted">{{ optional($p->arrival_time)?->format('H:i') }}</span>
                                </td>
                                <td>
                                    @if(in_array($p->status, \App\Models\FrontDesk\ErPatient::STATUSES_ACTIVE, true))
                                        <span class="badge bg-warning text-dark">{{ $p->waiting_minutes }}m</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="small">{{ $p->doctor?->name ?? '—' }}</td>
                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit($p->description, 50) }}</td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('er.patients.show', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-heart-pulse fs-2 d-block mb-2 opacity-50"></i>
                                No ER patients match the filters.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($patients->hasPages())
            <div class="card-footer bg-white">{{ $patients->links() }}</div>
        @endif
    </div>
</div>
@endsection
