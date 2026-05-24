@extends('backend.layouts.master')

@section('title', 'OT Dashboard')

@push('styles')
<style>
    .ot-dash .kpi-icon { font-size: 1.5rem; }
    @media (min-width: 768px) { .ot-dash .kpi-icon { font-size: 2rem; } }
    .ot-dash .kpi-value { font-size: 1.25rem; font-weight: 600; line-height: 1.1; }
    @media (min-width: 768px) { .ot-dash .kpi-value { font-size: 1.75rem; } }
    .ot-dash .kpi-label { font-size: .7rem; }
    @media (min-width: 768px) { .ot-dash .kpi-label { font-size: .8rem; } }
    .ot-dash .table { font-size: .85rem; }
    .ot-dash .table .col-nowrap { white-space: nowrap; }
    .ot-dash .empty-panel { padding: 1.25rem; text-align: center; color: #6c757d; }
    @media (max-width: 575.98px) {
        .ot-dash .app-page-title { font-size: 1.1rem; }
        .ot-dash .btn { font-size: .85rem; padding: .35rem .6rem; }
        .ot-dash .table { font-size: .75rem; }
    }
    .ot-dash .kpi-link {
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        cursor: pointer;
    }
    .ot-dash .kpi-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .85rem rgba(0,0,0,.08);
        background-color: #f8f9fa;
        text-decoration: none;
    }
    .ot-dash .kpi-link:focus { outline: 2px solid #0d6efd; outline-offset: 2px; }
</style>
@endpush

@section('content')
<div class="container-fluid ot-dash">
    <div class="app-page-head d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="app-page-title mb-0">OT Management Dashboard</h1>
                <span class="live-pill">Live</span>
            </div>
            <small class="text-muted d-none d-sm-block">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Auto-refresh every 5 min · {{ now()->format('D, d M Y H:i') }}
            </small>
            <small class="text-muted d-block d-sm-none">
                <i class="bi bi-arrow-clockwise"></i> {{ now()->format('d M, H:i') }}
            </small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ot.surgery-requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i><span class="d-none d-md-inline">New Surgery Request</span><span class="d-inline d-md-none">New</span>
            </a>
            <a href="{{ route('ot.emergency.create') }}" class="btn btn-danger">
                <i class="bi bi-exclamation-triangle me-1"></i><span class="d-none d-md-inline">Emergency OT</span><span class="d-inline d-md-none">Emergency</span>
            </a>
            <a href="{{ route('ot.schedules.calendar') }}" class="btn btn-info">
                <i class="bi bi-calendar3 me-1"></i><span class="d-none d-md-inline">Calendar</span>
            </a>
        </div>
    </div>

    {{-- KPI Cards (each clickable to drill into the corresponding list) --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['Today Total', $stats['today_total'], 'primary', 'bi-calendar-day',
                    route('ot.schedules.index', ['date' => now()->toDateString()])],
                ['Running Now', $stats['running'], 'warning', 'bi-activity',
                    route('ot.intra-op.index')],
                ['Delayed', $stats['delayed'], 'danger', 'bi-clock-history',
                    route('ot.schedules.index', ['date' => now()->toDateString(), 'status' => 'Scheduled'])],
                ['Emergency', $stats['emergency'], 'danger', 'bi-exclamation-triangle',
                    route('ot.emergency.index')],
                ['Pending Pre-Op', $stats['pending_pre_op'], 'info', 'bi-check2-square',
                    route('ot.pre-op.index', ['date' => now()->toDateString()])],
                ['Waiting Transfer', $stats['waiting_transfer'], 'info', 'bi-arrow-left-right',
                    route('ot.transfers.index')],
                ['In PACU', $stats['in_pacu'], 'success', 'bi-bandaid',
                    route('ot.pacu.index')],
                ['Pending Requests', $stats['pending_requests'], 'secondary', 'bi-inbox',
                    route('ot.surgery-requests.index', ['status' => 'Submitted'])],
            ];
        @endphp
        @foreach($cards as [$label, $value, $color, $icon, $href])
            <div class="col-6 col-md-3 col-xl">
                <a href="{{ $href }}" class="card kpi-link border-start border-4 border-{{ $color }} h-100 text-decoration-none text-dark"
                   title="View {{ strtolower($label) }} list">
                    <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                        <div class="text-truncate">
                            <div class="text-muted kpi-label text-truncate">{{ $label }}</div>
                            <div class="kpi-value mb-0">{{ $value }}</div>
                        </div>
                        <i class="bi {{ $icon }} text-{{ $color }} kpi-icon flex-shrink-0"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- FR-03: OT Room Status (full width) --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-door-closed me-1"></i> OT Room Status</strong>
            <a href="{{ route('ot.rooms.status') }}" class="small">Detailed view</a>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @forelse($rooms as $room)
                    @php [$color, $label] = $roomStates->badge($room->status); @endphp
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="border rounded p-2 text-center h-100">
                            <div class="fw-semibold">{{ $room->name }}</div>
                            <div class="small text-muted">{{ $room->code }}</div>
                            <span class="badge bg-{{ $color }} text-uppercase small">{{ $label }}</span>
                            @if($room->is_emergency)
                                <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle"></i> Emergency OT</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted text-center">
                        No rooms configured. <a href="{{ route('ot.setup.rooms.create') }}">Add one</a>.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- FR-05: Emergency cases banner --}}
    @if($emergencyCases->count() > 0)
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between">
                <strong><i class="bi bi-exclamation-triangle me-1"></i> Emergency Cases ({{ $emergencyCases->count() }})</strong>
                <small>Highest Priority</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="d-none d-sm-table-cell">Source</th>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell">Surgery</th>
                            <th class="d-none d-lg-table-cell">Surgeon</th>
                            <th class="d-none d-md-table-cell">OT Room</th>
                            <th class="d-none d-lg-table-cell">Approval</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emergencyCases as $s)
                            <tr class="table-danger">
                                <td class="d-none d-sm-table-cell"><span class="badge bg-danger">{{ $s->surgeryRequest?->encounter_type ?? '—' }}</span></td>
                                <td>
                                    {{ optional($s->surgeryRequest?->patient)->patient_name }}
                                    <div class="small text-muted d-md-none">{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell">{{ optional($s->surgeryRequest?->primarySurgeon)->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ optional($s->room)->name }}</td>
                                <td class="d-none d-lg-table-cell">{{ $s->approved_at ? '✓ Approved' : '⏳ Pending' }}</td>
                                <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                                <td><a href="{{ route('ot.schedules.show', $s->id) }}" class="btn btn-sm btn-light">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- FR-02: Running Surgeries — full-width section (live ops view) --}}
    <div class="card mb-3">
        <div class="card-header text-warning d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-activity me-1"></i> Running Surgeries ({{ $runningSurgeries->count() }})</strong>
            <a href="{{ route('ot.intra-op.index') }}" class="small">View all</a>
        </div>
        @if($runningSurgeries->count() === 0)
            <div class="empty-panel">
                <i class="bi bi-pause-circle fs-4 d-block mb-2 opacity-50"></i>
                No surgeries are currently running.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell">Surgery</th>
                            <th class="d-none d-sm-table-cell">Room</th>
                            <th class="d-none d-lg-table-cell col-nowrap">Started</th>
                            <th class="col-nowrap">Duration</th>
                            <th class="d-none d-md-table-cell col-nowrap">Expected End</th>
                            <th class="d-none d-sm-table-cell">Anesthesia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($runningSurgeries as $s)
                            @php
                                $startedAt = $s->actual_start;
                                $expected = $dash->expectedEndTime($s);
                                $duration = $dash->runningDurationMinutes($s);
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('ot.intra-op.show', $s->id) }}">{{ optional($s->surgeryRequest?->patient)->patient_name }}</a>
                                    <div class="small text-muted d-md-none">{{ optional($s->surgeryRequest?->surgeryType)->name }}</div>
                                </td>
                                <td class="small d-none d-md-table-cell">{{ optional($s->surgeryRequest?->surgeryType)->name }}</td>
                                <td class="small d-none d-sm-table-cell">{{ optional($s->room)->name }}</td>
                                <td class="small d-none d-lg-table-cell col-nowrap">{{ $startedAt?->format('H:i') }}</td>
                                <td class="col-nowrap">
                                    @if($startedAt)
                                        <span class="badge bg-warning text-dark" data-running-since="{{ $startedAt->toIso8601String() }}">{{ $duration }} min</span>
                                    @else — @endif
                                </td>
                                <td class="small d-none d-md-table-cell col-nowrap">{{ $expected?->format('H:i') ?? '—' }}</td>
                                <td class="d-none d-sm-table-cell"><span class="badge bg-info">{{ $dash->anesthesiaStatus($s) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- FR-07: Post-Op Recovery — full-width section --}}
    <div class="card mb-3">
        <div class="card-header text-success d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bandaid me-1"></i> Post-Op Recovery ({{ $pacuPatients->count() }})</strong>
            <a href="{{ route('ot.pacu.index') }}" class="small">View all</a>
        </div>
        @if($pacuPatients->count() === 0)
            <div class="empty-panel">
                <i class="bi bi-bandaid fs-4 d-block mb-2 opacity-50"></i>
                PACU is empty — no patients in recovery.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell col-nowrap">Bed</th>
                            <th class="d-none d-sm-table-cell col-nowrap">In Recovery</th>
                            <th class="col-nowrap">Aldrete</th>
                            <th class="d-none d-md-table-cell">Latest Vitals</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pacuPatients as $p)
                            @php $vitals = collect($p->vitals_log ?? [])->last(); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('ot.pacu.show', $p->surgery_schedule_id) }}" class="fw-semibold">
                                        {{ optional($p->schedule?->surgeryRequest?->patient)->patient_name }}
                                    </a>
                                    <div class="small text-muted d-md-none">Bed {{ $p->bed_no ?? '—' }} · {{ $p->admitted_at?->diffForHumans() }}</div>
                                </td>
                                <td class="d-none d-md-table-cell col-nowrap">{{ $p->bed_no ?? '—' }}</td>
                                <td class="small d-none d-sm-table-cell col-nowrap">{{ $p->admitted_at?->diffForHumans() }}</td>
                                <td class="col-nowrap"><strong>{{ $p->aldrete_score ?? '—' }}</strong>/10</td>
                                <td class="small d-none d-md-table-cell">
                                    @if($vitals)
                                        BP {{ $vitals['bp'] ?? '—' }} · Pulse {{ $vitals['pulse'] ?? '—' }} · SpO₂ {{ $vitals['spo2'] ?? '—' }} · Pain <strong>{{ $vitals['pain_score'] ?? '—' }}</strong>
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if($p->discharge_destination)
                                            <span class="badge bg-info">→ {{ $p->discharge_destination }}</span>
                                        @endif
                                        @if(($p->aldrete_score ?? 0) >= 8)
                                            <span class="badge bg-success">Ready</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Observation</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            {{-- FR-01: Today's Surgeries --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-calendar-day me-1"></i> Today's Surgeries</strong>
                    <a href="{{ route('ot.schedules.index', ['date' => now()->toDateString()]) }}" class="small">View all</a>
                </div>
                @if($todaySchedules->count() === 0)
                    <div class="empty-panel">
                        <i class="bi bi-calendar-x fs-4 d-block mb-2 opacity-50"></i>
                        No surgeries scheduled today.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-nowrap">Time</th>
                                    <th>Patient / Ref</th>
                                    <th class="d-none d-md-table-cell">Surgery</th>
                                    <th class="d-none d-lg-table-cell">Surgeon</th>
                                    <th class="d-none d-md-table-cell">Room</th>
                                    <th class="d-none d-sm-table-cell">Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedules as $s)
                                    <tr @class(['table-danger' => $s->emergency_fast_track])>
                                        <td class="small col-nowrap">{{ $s->scheduled_start?->format('H:i') }}</td>
                                        <td>
                                            <a href="{{ route('ot.schedules.show', $s->id) }}">
                                                {{ optional($s->surgeryRequest?->patient)->patient_name }}
                                            </a>
                                            <div class="small text-muted">{{ $s->surgeryRequest?->encounter_type }} · {{ optional($s->surgeryRequest?->patient)->mrn }}</div>
                                            <div class="small text-muted d-md-none">{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</div>
                                        </td>
                                        <td class="small d-none d-md-table-cell">{{ optional($s->surgeryRequest?->surgeryType)->name ?? '—' }}</td>
                                        <td class="small d-none d-lg-table-cell">{{ optional($s->surgeryRequest?->primarySurgeon)->name ?? '—' }}</td>
                                        <td class="small d-none d-md-table-cell">{{ optional($s->room)->name ?? '—' }}</td>
                                        <td class="d-none d-sm-table-cell"><span class="badge bg-light text-dark border">{{ $s->surgeryRequest?->priority }}</span></td>
                                        <td><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- FR-04: Delayed --}}
            @if($delayedCases->count() > 0)
                <div class="card mb-3 border-danger">
                    <div class="card-header text-danger d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-clock-history me-1"></i> Delayed Cases ({{ $delayedCases->count() }})</strong>
                        <a href="{{ route('ot.schedules.index', ['date' => now()->toDateString()]) }}" class="small">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th><th>Patient</th>
                                    <th class="d-none d-sm-table-cell">Room</th>
                                    <th class="d-none d-md-table-cell">Status</th>
                                    <th>Delay Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delayedCases as $s)
                                    <tr>
                                        <td class="small">{{ $s->scheduled_start?->format('H:i') }}</td>
                                        <td>
                                            <a href="{{ route('ot.schedules.show', $s->id) }}">{{ optional($s->surgeryRequest?->patient)->patient_name }}</a>
                                            <div class="small text-muted d-sm-none">{{ optional($s->room)->name }}</div>
                                        </td>
                                        <td class="small d-none d-sm-table-cell">{{ optional($s->room)->name }}</td>
                                        <td class="d-none d-md-table-cell"><span class="badge {{ $s->status_badge_class }}">{{ $s->status }}</span></td>
                                        <td class="small text-danger" style="white-space: normal;">{{ $dash->delayReason($s) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12 col-xl-5">
            {{-- FR-06: Pending Pre-Op --}}
            <div class="card mb-3">
                <div class="card-header text-info d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-check2-square me-1"></i> Pending Pre-Op ({{ $pendingPreOp->count() }})</strong>
                    <a href="{{ route('ot.pre-op.index') }}" class="small">View all</a>
                </div>
                @if($pendingPreOp->count() === 0)
                    <div class="empty-panel">
                        <i class="bi bi-check2-circle fs-4 d-block mb-2 opacity-50"></i>
                        All pre-op checklists are clear.
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($pendingPreOp as $s)
                            @php
                                $missing = $dash->missingPreOpItems($s->preOpChecklist);
                                $pct = $s->preOpChecklist?->completionPercent() ?? 0;
                            @endphp
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('ot.pre-op.show', $s->id) }}" class="fw-semibold">
                                        {{ optional($s->surgeryRequest?->patient)->patient_name }}
                                    </a>
                                    <span class="small text-muted">{{ $s->scheduled_start?->format('H:i') }}</span>
                                </div>
                                <div class="progress my-1" style="height:5px">
                                    <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(array_slice($missing, 0, 5) as $m)
                                        <span class="badge bg-light text-dark border">{{ $m }}</span>
                                    @endforeach
                                    @if(count($missing) > 5)
                                        <span class="badge bg-light text-dark border">+{{ count($missing) - 5 }} more</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- In-transit --}}
            @if($waitingTransfer->count() > 0)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-arrow-left-right me-1"></i> Patients In Transit</strong>
                        <a href="{{ route('ot.transfers.index') }}" class="small">View all</a>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach($waitingTransfer as $t)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ optional($t->schedule?->surgeryRequest?->patient)->patient_name }}</span>
                                <small class="text-muted">{{ strtoupper(str_replace('_',' ',$t->direction)) }} · {{ $t->initiated_at?->diffForHumans() }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>

    {{-- ───────────────────────────────────────────── --}}
    {{-- 🆕 PENDING SURGERY REQUESTS --}}
    {{-- ───────────────────────────────────────────── --}}
    @if(($pendingRequests ?? collect())->count() > 0)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-inbox me-1"></i> Pending Surgery Requests ({{ $pendingRequests->count() }})</strong>
                <a href="{{ route('ot.surgery-requests.index') }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Request No</th>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell">Procedure</th>
                            <th class="d-none d-lg-table-cell">Surgeon</th>
                            <th class="d-none d-sm-table-cell">Priority</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $r)
                            <tr class="{{ $r->is_emergency ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('ot.surgery-requests.show', $r->id) }}" class="fw-semibold">{{ $r->request_no }}</a>
                                    @if($r->is_emergency)<span class="badge bg-danger ms-1">ER</span>@endif
                                </td>
                                <td class="small">{{ optional($r->patient)->patient_name }}</td>
                                <td class="small d-none d-md-table-cell">{{ optional($r->surgeryType)->name ?? '—' }}</td>
                                <td class="small d-none d-lg-table-cell">{{ optional($r->primarySurgeon)->name ?? '—' }}</td>
                                <td class="d-none d-sm-table-cell"><span class="badge bg-light text-dark border">{{ $r->priority }}</span></td>
                                <td><span class="badge {{ $r->status_badge_class }}">{{ $r->status }}</span></td>
                                <td>
                                    <a href="{{ route('ot.surgery-requests.show', $r->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- 🆕 RECENT ACTIVITY (audit feed) --}}
    {{-- ───────────────────────────────────────────── --}}
    @if(($recentActivity ?? collect())->count() > 0)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-activity me-1"></i> Recent Activity ({{ $recentActivity->count() }})</strong>
                <a href="{{ route('ot.reports.audit') }}" class="small">Full audit log →</a>
            </div>
            <ul class="list-group list-group-flush" style="max-height: 360px; overflow-y: auto;">
                @foreach($recentActivity as $log)
                    <li class="list-group-item d-flex justify-content-between align-items-start small">
                        <div class="flex-grow-1">
                            <span class="badge bg-info">{{ $log->action }}</span>
                            <strong class="ms-1">{{ str_replace('_', ' ', $log->entity_type) }}</strong>
                            #{{ $log->entity_id }}
                            @if($log->from_status || $log->to_status)
                                <span class="text-muted ms-1">
                                    {{ $log->from_status ?? '—' }} <i class="bi bi-arrow-right"></i> <strong>{{ $log->to_status ?? '—' }}</strong>
                                </span>
                            @endif
                            @if($log->reason)<div class="text-muted small">{{ \Illuminate\Support\Str::limit($log->reason, 100) }}</div>@endif
                        </div>
                        <div class="text-end text-muted small">
                            <div>{{ optional($log->user)->name ?? '#' . $log->user_id }}</div>
                            <div>{{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- 🆕 WEEK / MONTH PERFORMANCE STATS --}}
    {{-- ───────────────────────────────────────────── --}}
    @if(isset($weekStats))
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-start border-4 border-success h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Surgeries this week</div>
                        <div class="h4 mb-0">{{ $weekStats['surgeries_done_week'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-start border-4 border-primary h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Surgeries this month</div>
                        <div class="h4 mb-0">{{ $weekStats['surgeries_done_month'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-start border-4 border-warning h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Cancelled this week</div>
                        <div class="h4 mb-0">{{ $weekStats['cancelled_week'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-start border-4 border-danger h-100">
                    <div class="card-body p-3">
                        <div class="text-muted small">Emergencies this week</div>
                        <div class="h4 mb-0">{{ $weekStats['emergency_week'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Notifications — full-width section in main body (out of right column) --}}
    @if(($notifications ?? collect())->count() > 0)
        <div class="card mb-3 border-info">
            <div class="card-header bg-info-subtle text-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong><i class="bi bi-bell me-1"></i> Notifications ({{ $notifications->count() }})</strong>
                <div class="d-flex gap-2 align-items-center">
                    <form action="{{ route('ot.notifications.read-all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-info" title="Mark all as read">
                            <i class="bi bi-check2-all"></i> Mark all read
                        </button>
                    </form>
                    <a href="{{ route('ot.reports.audit') }}" class="small">Audit log →</a>
                </div>
            </div>
            <div class="row g-0" style="max-height: 420px; overflow-y: auto;">
                @foreach($notifications as $n)
                    @php
                        $sev = $n->severity ?? 'info';
                        $badgeColor = $sev === 'danger' ? 'danger' : ($sev === 'warning' ? 'warning text-dark' : ($sev === 'success' ? 'success' : 'info'));
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 position-relative">
                        <a href="{{ $n->action_url ?? '#' }}"
                           class="d-block p-3 text-decoration-none text-dark border-bottom border-end h-100"
                           style="transition: background-color .15s ease;"
                           onmouseover="this.style.backgroundColor='#f8f9fc'"
                           onmouseout="this.style.backgroundColor=''">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-{{ $badgeColor }} flex-shrink-0">{{ str_replace('_', ' ', $n->type) }}</span>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="fw-semibold text-truncate" style="padding-right: 1.5rem;">{{ $n->title }}</div>
                                    <div class="small text-muted">{{ $n->body }}</div>
                                    <div class="small text-muted mt-1"><i class="bi bi-clock"></i> {{ $n->created_at?->diffForHumans() }}</div>
                                </div>
                            </div>
                        </a>
                        <form action="{{ route('ot.notifications.read', $n->id) }}" method="POST"
                              class="position-absolute" style="top: .5rem; right: .5rem;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link p-0 text-muted" title="Mark as read">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // FR-02: live timer ticker + scroll-aware auto-refresh
    (function () {
        function tick() {
            document.querySelectorAll('[data-running-since]').forEach(el => {
                const since = new Date(el.dataset.runningSince);
                const mins = Math.max(0, Math.round((Date.now() - since.getTime()) / 60000));
                el.textContent = mins + ' min';
            });
        }
        tick();
        setInterval(tick, 30000);

        // Refresh every 5 minutes — skip if hidden, scrolled past 100px, or recent interaction
        let lastInteraction = Date.now();
        ['scroll','mousedown','keydown','touchstart'].forEach(evt =>
            window.addEventListener(evt, () => lastInteraction = Date.now(), { passive: true })
        );
        setInterval(() => {
            if (document.hidden) return;
            if (window.scrollY > 100) return;
            if (Date.now() - lastInteraction < 30000) return;
            location.reload();
        }, 300000);
    })();
</script>
@endpush
@endsection
