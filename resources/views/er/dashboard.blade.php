@extends('backend.layouts.master')
@section('title', 'ER Live Dashboard')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="fas fa-heartbeat text-danger"></i> ER Live Dashboard</h4>
            <small class="text-muted">Real-time emergency department operations</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('er.board') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-kanban"></i> Tracking Board</a>
            <a href="{{ route('front_desk.er_registration') }}" class="btn btn-sm btn-danger"><i class="bi bi-plus-lg"></i> New ER Registration</a>
            <a href="{{ route('amb.er.incoming') }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-ambulance"></i> Incoming Ambulances</a>
        </div>
    </div>

    {{-- 10 KPI tiles --}}
    <div class="row g-2 mb-3">
        @php $tiles = [
            ['Today Total', $kpi['today_total'], 'primary', 'people'],
            ['Critical', $kpi['critical'], 'danger', 'exclamation-triangle'],
            ['Waiting', $kpi['waiting'], 'warning', 'hourglass-split'],
            ['Under Assessment', $kpi['under_assessment'], 'info', 'clipboard-pulse'],
            ['In Treatment', $kpi['in_treatment'], 'primary', 'capsule-pill'],
            ['Observation', $kpi['observation'], 'info', 'eye'],
            ['Avg Wait (min)', $kpi['avg_wait_min'], 'secondary', 'stopwatch'],
            ['Beds Free', $kpi['beds_free'] . ' / ' . $kpi['beds_total'], 'success', 'house-check'],
            ['Pending Transfers', $kpi['pending_transfers'], 'warning', 'arrow-left-right'],
        ]; @endphp
        @foreach ($tiles as [$label, $value, $colour, $icon])
            <div class="col-md col-6">
                <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--bs-{{ $colour }}) !important;">
                    <div class="card-body py-2 px-3">
                        <small class="text-{{ $colour }}"><i class="bi bi-{{ $icon }}"></i> {{ $label }}</small>
                        <h4 class="mb-0 mt-1">{{ $value }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Triage breakdown --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Triage Breakdown (Today)</h6>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                @php $triageMap = [
                    'RED'    => ['danger',  'Critical', 'exclamation-octagon'],
                    'ORANGE' => ['warning', 'Very Urgent', 'exclamation-triangle'],
                    'YELLOW' => ['info',    'Urgent', 'info-circle'],
                    'GREEN'  => ['success', 'Stable', 'check-circle'],
                    'BLACK'  => ['dark',    'Deceased', 'x-octagon'],
                ]; @endphp
                @foreach ($triageMap as $level => [$colour, $label, $icon])
                    <div class="col">
                        <div class="card text-white bg-{{ $colour }} border-0 text-center">
                            <div class="card-body py-2">
                                <i class="bi bi-{{ $icon }}"></i>
                                <div class="small">{{ $label }}</div>
                                <h4 class="mb-0">{{ $triageBreakdown[$level] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Active patients queue --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-list-task"></i> Active ER Patients ({{ $activePatients->count() }})</h6>
                    <a href="{{ route('er.board') }}" class="btn btn-sm btn-link p-0">Open Board →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Patient</th><th>Triage</th><th>Priority</th><th>Status</th><th>Doctor</th><th>Arrived</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($activePatients as $p)
                                @php $triage = $p->latestTriage; @endphp
                                <tr>
                                    <td><strong>{{ optional($p->patient)->patient_name ?? '—' }}</strong><br>
                                        <small class="text-muted">{{ optional($p->patient)->mrn ?? 'ER#' . $p->id }}</small></td>
                                    <td>@if ($triage)
                                            <span class="badge bg-{{ \App\Models\Er\ErTriage::levelColour($triage->triage_level) }}">{{ $triage->triage_level }}</span>
                                        @else <span class="text-muted small">Not triaged</span> @endif</td>
                                    <td><span class="badge bg-{{ $p->priorityBadgeClass() }}">{{ $p->priority }}</span></td>
                                    <td><span class="badge bg-{{ $p->statusBadgeClass() }}">{{ str_replace('_', ' ', $p->status) }}</span></td>
                                    <td>{{ optional($p->doctor)->name ?? '—' }}</td>
                                    <td><small>{{ $p->arrival_time?->diffForHumans() ?? '—' }}</small></td>
                                    <td><a href="{{ route('er.show', $p->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-right"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No active ER patients</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent transfers --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-arrow-left-right"></i> Recent Transfers</h6>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentTransfers as $t)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ optional(optional($t->erPatient)->patient)->patient_name ?? '—' }}</strong>
                                <span class="badge bg-info ms-1">→ {{ $t->target }}</span>
                                <br><small class="text-muted">{{ optional(optional($t->targetBed)->bedType)->name }} bed {{ optional($t->targetBed)->name }}</small>
                            </div>
                            <small class="text-muted">{{ $t->requested_at?->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-3">No recent transfers</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
