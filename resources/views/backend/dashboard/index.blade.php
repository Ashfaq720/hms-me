@extends('backend.layouts.master')

@section('title', 'Hospital Management System')

@push('styles')
<style>
    /* ─────────────────────────────────────────────────────────────────
       DASHBOARD DESIGN SYSTEM
       Color tokens, card framework, and section components.
    ───────────────────────────────────────────────────────────────── */
    :root {
        --hms-amber:   #f4b942;
        --hms-mint:    #3ad29f;
        --hms-green:   #28a745;
        --hms-teal:    #0fa57b;
        --hms-pink:    #e83e8c;
        --hms-red:     #dc3545;
        --hms-blue:    #3b82f6;
        --hms-purple:  #a855f7;
        --hms-cyan:    #0dcaf0;
        --hms-orange:  #fd7e14;
        --hms-gray:    #6c757d;

        --hms-border:    #eef0f3;
        --hms-border-2:  #f1f3f5;
        --hms-muted:     #6c757d;
        --hms-ink:       #212529;
        --hms-card-bg:   #ffffff;
        --hms-page-bg:   #f7f8fa;
    }

    .hms-dashboard { background: var(--hms-page-bg); padding-bottom: 1.25rem; }

    /* ── Card framework ── */
    .hms-card {
        background: var(--hms-card-bg);
        border: 1px solid var(--hms-border);
        border-radius: .7rem;
        display: flex; flex-direction: column; height: 100%;
        box-shadow: 0 1px 2px rgba(16,24,40,.04);
        transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
    }
    .hms-card:hover { box-shadow: 0 6px 18px rgba(16,24,40,.06); border-color: #e3e6ea; }
    .hms-card .hms-head {
        padding: .75rem .95rem;
        border-bottom: 1px solid var(--hms-border-2);
        display: flex; justify-content: space-between; align-items: center; gap: .5rem;
        flex-shrink: 0;
    }
    .hms-card .hms-body { padding: .9rem; flex: 1; display: flex; flex-direction: column; }
    .hms-card .hms-head .ttl {
        font-weight: 600; font-size: .94rem; color: var(--hms-ink);
        display: flex; align-items: center; gap: .5rem;
    }
    .hms-card .hms-head .ttl i { font-size: 1.05rem; }
    .hms-card .hms-head .pill {
        font-size: .72rem; color: #495057;
        background: #f4f6f8; border: 1px solid var(--hms-border);
        border-radius: 99px; padding: .25rem .7rem;
        display: inline-flex; align-items: center; gap: .25rem; font-weight: 500;
    }
    .hms-card .hms-head .pill.dark { background: #212529; color: #fff; border-color: #212529; }

    /* Make every Bootstrap col in a row stretch its child to full height */
    .equal-row > [class*="col-"] { display: flex; flex-direction: column; }
    .equal-row > [class*="col-"] > * { flex: 1; }

    /* ── Stat chip (used inside Total Patient) ── */
    .pat-mini {
        display: flex; align-items: center; gap: .6rem;
        background: #fff; border: 1px solid var(--hms-border);
        border-radius: .55rem; padding: .6rem .75rem;
        position: relative; overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .pat-mini::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0;
        width: 3px; background: var(--mini-fg, transparent);
        border-radius: .55rem 0 0 .55rem; opacity: .9;
    }
    .pat-mini:hover {
        transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,.05);
        border-color: var(--mini-fg, #dde2e7);
    }
    .pat-mini .ic {
        width: 36px; height: 36px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.4);
    }
    .pat-mini .val { font-weight: 700; font-size: 1.15rem; line-height: 1.1; color: var(--hms-ink); }
    .pat-mini .lbl { font-size: .68rem; color: var(--hms-muted); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }

    /* ── Stat tile (Appointments + Surgery boxes) — vertical, prominent ── */
    .stat-tile {
        position: relative; overflow: hidden;
        background: linear-gradient(180deg, var(--tile-bg, #f8f9fa) 0%, #ffffff 100%);
        border: 1px solid var(--hms-border); border-radius: .7rem;
        padding: .9rem .6rem .8rem; text-align: center;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%; min-height: 130px;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .stat-tile::after {
        content: ''; position: absolute; left: 0; right: 0; bottom: 0;
        height: 3px; background: var(--tile-fg, #adb5bd); opacity: .85;
    }
    .stat-tile:hover {
        transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,24,40,.07);
        border-color: var(--tile-fg, #dde2e7);
    }
    .stat-tile .tile-ic {
        width: 44px; height: 44px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin-bottom: .55rem;
        background: #fff; box-shadow: 0 2px 6px rgba(16,24,40,.06);
        color: var(--tile-fg);
    }
    .stat-tile .tile-val {
        font-weight: 700; font-size: 1.6rem; line-height: 1; color: var(--hms-ink);
        margin-bottom: .2rem;
    }
    .stat-tile .tile-lbl {
        font-size: .72rem; color: var(--hms-muted);
        text-transform: uppercase; letter-spacing: .05em; font-weight: 600;
    }
    .stat-tile .tile-trend {
        margin-top: .35rem; font-size: .68rem; color: var(--hms-muted);
        display: inline-flex; align-items: center; gap: .2rem;
    }
    .stat-tile.feature { min-height: 156px; }
    .stat-tile.feature .tile-ic { width: 56px; height: 56px; font-size: 1.6rem; }
    .stat-tile.feature .tile-val { font-size: 2rem; }

    /* ── Income card (9 in a row) ── */
    .income-card {
        background: #fff; border: 1px solid var(--hms-border); border-radius: .6rem;
        padding: .8rem .9rem .8rem 1.05rem;
        height: 100%; display: flex; flex-direction: column; justify-content: center;
        position: relative; overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .income-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0;
        width: 4px; background: var(--inc-fg, #adb5bd);
        border-radius: .6rem 0 0 .6rem;
    }
    .income-card:hover {
        transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.06);
        border-color: var(--inc-fg, #dde2e7);
    }
    .income-card .lbl { font-size: .72rem; color: var(--hms-muted); font-weight: 500; }
    .income-card .val {
        font-size: 1.2rem; font-weight: 700; color: var(--hms-ink);
        margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .income-card .val .unit { font-size: .85rem; color: var(--hms-muted); font-weight: 500; margin-left: 2px; }

    /* ── Staff list row ── */
    .staff-row {
        display: flex; align-items: center; gap: .65rem;
        padding: .55rem .9rem; border-bottom: 1px solid var(--hms-border-2);
        transition: background-color .12s ease;
    }
    .staff-row:last-child { border-bottom: 0; }
    .staff-row:hover { background: #fafbfc; }
    .staff-row .num { font-weight: 700; color: var(--hms-ink); font-size: 1rem; min-width: 28px; }
    .staff-row .role { color: var(--hms-muted); font-size: .82rem; flex: 1; }

    /* ── Profit/Loss gauge wrapper ── */
    .gauge-wrap { position: relative; width: 100%; max-width: 230px; margin: 0 auto; }

    /* ── Emergency alert pulse animation ── */
    .alert-pulse { animation: alertPulse 2s ease-in-out infinite; }
    @keyframes alertPulse {
        0%, 100% { transform: scale(1); }
        50%      { transform: scale(1.05); }
    }

    /* ── Bed occupancy table ── */
    .hms-table { font-size: .85rem; }
    .hms-table thead th {
        background: #fafbfc; color: var(--hms-muted); font-weight: 600;
        font-size: .78rem; text-transform: uppercase; letter-spacing: .03em;
        border-bottom: 1px solid var(--hms-border);
    }
    .hms-table tbody tr { transition: background-color .12s ease; }
    .hms-table tbody tr:hover { background: #fafbfc; }

    /* Scrolling container for bed occupancy with sticky header */
    .bed-scroll {
        max-height: 360px;
        overflow-y: auto;
        scrollbar-width: thin;
    }
    .bed-scroll::-webkit-scrollbar { width: 6px; }
    .bed-scroll::-webkit-scrollbar-thumb { background: #dde2e7; border-radius: 3px; }
    .bed-scroll::-webkit-scrollbar-track { background: transparent; }
    .bed-sticky thead th {
        position: sticky; top: 0; z-index: 2;
        background: #fafbfc;
        box-shadow: inset 0 -1px 0 var(--hms-border);
    }
    .bed-view-btn { color: var(--hms-blue); }
    .bed-view-btn:hover { background: #e3eeff; }

    /* ── Weekly calendar grid ── */
    .cal-wrap { overflow-x: auto; }
    .cal-grid {
        display: grid;
        grid-template-columns: 86px repeat(7, minmax(115px, 1fr));
        border-top: 1px solid var(--hms-border-2);
    }
    .cal-head-row { border-top: 0; background: #fafbfc; }
    .cal-time-col {
        padding: .65rem .55rem; color: var(--hms-muted); font-size: .76rem;
        border-right: 1px solid var(--hms-border-2);
        display: flex; align-items: center; gap: .25rem;
    }
    .cal-day-head {
        padding: .65rem .55rem; text-align: center; font-size: .78rem; font-weight: 600;
        color: #495057; border-right: 1px solid var(--hms-border-2);
    }
    .cal-day-head.active { color: var(--hms-blue); background: #eef4ff; }
    .cal-day-head:last-child { border-right: 0; }
    .cal-cell {
        min-height: 64px; padding: .35rem; border-right: 1px solid var(--hms-border-2);
        background: #fff;
    }
    .cal-cell:last-child { border-right: 0; }
    .cal-event {
        background: #fff3c4; color: #5b4a05; border-radius: .35rem;
        padding: .35rem .5rem; margin-bottom: .25rem; font-size: .72rem; line-height: 1.25;
        cursor: pointer; transition: transform .12s ease;
    }
    .cal-event:hover { transform: translateY(-1px); }
    .cal-event .cal-event-time { font-size: .66rem; opacity: .8; }
    .cal-event .cal-event-title { font-weight: 600; }
    .cal-event.cal-green { background: #d6f5e3; color: #0a5d2b; }
    .cal-event.cal-pink  { background: #ffe1ec; color: #7a1f3d; }
    .cal-event.cal-blue  { background: #d6e6ff; color: #0a3c8a; }

    /* Mobile tightening */
    @media (max-width: 991.98px) {
        .pat-mini { padding: .5rem .6rem; gap: .45rem; }
        .pat-mini .val { font-size: 1rem; }
        .pat-mini .ic { width: 30px; height: 30px; font-size: .85rem; }
        .hms-card .hms-head { padding: .65rem .8rem; }
        .income-card .val { font-size: 1.05rem; }
    }
</style>
@endpush

@section('content')
<div class="hms-dashboard">
    <div class="container-fluid py-3">

        {{-- ────────── HEADER ────────── --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h4 class="mb-0 fw-bold">Hospital Management System</h4>
            <div class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:10px; top:8px; color:var(--hms-muted);"></i>
                    <input class="form-control form-control-sm ps-4" placeholder="Search by patient name/ID" style="width:240px;">
                </div>
                <button class="btn btn-sm btn-light border" title="Notifications">
                    <i class="bi bi-bell"></i>
                </button>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 1 — Patient · Appointments · Surgery
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3 equal-row">
            {{-- Total Patients with 8 stats inside --}}
            <div class="col-xl-4 col-md-12">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl"><i class="bi bi-people-fill" style="color:var(--hms-amber);"></i> Total Patient: {{ number_format($totalPatients) }}</div>
                        <span class="pill">Today <i class="bi bi-chevron-down"></i></span>
                    </div>
                    <div class="hms-body">
                        <div class="row g-2 flex-grow-1">
                            @foreach([
                                ['Admitted',    $patientBreakdown['admitted'],    '#e0f7f4', '#0fa57b', 'bi-person-arms-up'],
                                ['IPD',         $patientBreakdown['ipd_today'],   '#e3eeff', '#3b82f6', 'bi-hospital'],
                                ['OPD',         $patientBreakdown['opd_today'],   '#ffe1ec', '#e83e8c', 'bi-stethoscope'],
                                ['Emergency',   $patientBreakdown['emergency'],   '#ffe1e1', '#dc3545', 'bi-heart-pulse-fill'],
                                ['Discharged',  $patientBreakdown['discharged'],  '#dff6e3', '#28a745', 'bi-box-arrow-right'],
                                ['Transferred', $patientBreakdown['transferred'], '#fff1d6', '#f4b942', 'bi-arrow-left-right'],
                                ['Death',       $patientBreakdown['death'],       '#eef0f3', '#6c757d', 'bi-heartbreak'],
                                ['Birth',       $patientBreakdown['birth'],       '#f3e8ff', '#a855f7', 'bi-emoji-smile'],
                            ] as [$lbl, $val, $bg, $fg, $icon])
                                <div class="col-6">
                                    <div class="pat-mini" style="--mini-fg: {{ $fg }};">
                                        <span class="ic" style="background: {{ $bg }}; color: {{ $fg }};"><i class="bi {{ $icon }}"></i></span>
                                        <div>
                                            <div class="val">{{ $val }}</div>
                                            <div class="lbl">{{ $lbl }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Appointments — 3 vertical stat tiles --}}
            <div class="col-xl-4 col-md-6">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl"><i class="bi bi-calendar-check" style="color:var(--hms-green);"></i> Appointments</div>
                        <span class="pill">Today <i class="bi bi-chevron-down"></i></span>
                    </div>
                    <div class="hms-body">
                        @php $apptTotal = max(1, ($appointments['approved'] + $appointments['pending'] + $appointments['declined'])); @endphp
                        <div class="row g-2 flex-grow-1">
                            <div class="col-4">
                                <div class="stat-tile" style="--tile-bg:#eaf8ee; --tile-fg:#28a745;">
                                    <span class="tile-ic"><i class="bi bi-check-circle-fill"></i></span>
                                    <div class="tile-val">{{ $appointments['approved'] }}</div>
                                    <div class="tile-lbl">Approved</div>
                                    <div class="tile-trend"><i class="bi bi-graph-up-arrow"></i> {{ round(($appointments['approved'] / $apptTotal) * 100) }}%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-tile" style="--tile-bg:#fff7e5; --tile-fg:#f4b942;">
                                    <span class="tile-ic"><i class="bi bi-hourglass-split"></i></span>
                                    <div class="tile-val">{{ $appointments['pending'] }}</div>
                                    <div class="tile-lbl">Pending</div>
                                    <div class="tile-trend"><i class="bi bi-dash-circle"></i> {{ round(($appointments['pending'] / $apptTotal) * 100) }}%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-tile" style="--tile-bg:#fdebf2; --tile-fg:#e83e8c;">
                                    <span class="tile-ic"><i class="bi bi-x-circle-fill"></i></span>
                                    <div class="tile-val">{{ $appointments['declined'] }}</div>
                                    <div class="tile-lbl">Declined</div>
                                    <div class="tile-trend"><i class="bi bi-graph-down-arrow"></i> {{ round(($appointments['declined'] / $apptTotal) * 100) }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Surgery — single feature tile --}}
            <div class="col-xl-4 col-md-6">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl"><i class="bi bi-scissors" style="color:var(--hms-amber);"></i> Total Surgery</div>
                        <span class="pill">Today <i class="bi bi-chevron-down"></i></span>
                    </div>
                    <div class="hms-body">
                        <div class="stat-tile feature flex-grow-1" style="--tile-bg:#fff7e5; --tile-fg:#f4b942;">
                            <span class="tile-ic"><i class="bi bi-clipboard2-pulse"></i></span>
                            <div class="tile-val">{{ $surgeryToday }}</div>
                            <div class="tile-lbl">Surgery Today</div>
                            <div class="tile-trend"><i class="bi bi-calendar2-week"></i> Scheduled for today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 2 — Waiting Time chart · Emergency Alert
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3 equal-row">
            <div class="col-xl-8 col-md-8">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl"><i class="bi bi-clock-history" style="color:var(--hms-amber);"></i> Average Patient Waiting Time</div>
                        <span class="pill dark">{{ $avgWaitMinutes }} Minute</span>
                    </div>
                    <div class="hms-body"><canvas id="waitChart" height="70"></canvas></div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4">
                @php $emergencyCount = $patientBreakdown['emergency'] ?? 0; @endphp
                <div class="hms-card text-center {{ $emergencyCount > 0 ? 'alert-pulse' : '' }}"
                     style="background: {{ $emergencyCount > 0 ? '#fff0f0' : '#fafbfc' }}; border-color: {{ $emergencyCount > 0 ? '#dc3545' : '#e9ecef' }};">
                    <div class="hms-body justify-content-center align-items-center">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <i class="bi bi-bell-fill fs-2"
                               style="color: {{ $emergencyCount > 0 ? '#dc3545' : '#cfd4da' }};"></i>
                            <strong style="color: {{ $emergencyCount > 0 ? '#212529' : '#adb5bd' }};">Emergency Alert</strong>
                            @if($emergencyCount > 0)
                                <span class="badge bg-danger">{{ $emergencyCount }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 3 — Income (9 cards) — color-coded by department
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3 equal-row">
            @foreach([
                ['OPD Income',        $incomeByModule['opd'],        '#e83e8c'],
                ['IPD Income',        $incomeByModule['ipd'],        '#3b82f6'],
                ['Pharmacy Income',   $incomeByModule['pharmacy'],   '#0fa57b'],
                ['Pathology Income',  $incomeByModule['pathology'],  '#a855f7'],
                ['Radiology Income',  $incomeByModule['radiology'],  '#f4b942'],
                ['Blood Bank Income', $incomeByModule['blood_bank'], '#dc3545'],
                ['Ambulance Income',  $incomeByModule['ambulance'],  '#fd7e14'],
                ['General Income',    $incomeByModule['general'],    '#0dcaf0'],
                ['Expences',          $incomeByModule['expenses'],   '#6c757d'],
            ] as [$lbl, $val, $fg])
                <div class="col-xl col-lg-3 col-md-4 col-6">
                    <div class="income-card" style="--inc-fg: {{ $fg }};">
                        <div class="lbl">{{ $lbl }}</div>
                        <div class="val">{{ number_format((float) $val) }}<span class="unit">/=</span></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 4 — Yearly Income chart · Monthly Income donut
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3 equal-row">
            <div class="col-lg-7">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl">Yearly Income and Expences</div>
                        <div class="small d-flex gap-3 text-muted">
                            <span><i class="bi bi-circle-fill" style="color:#0d6efd;font-size:.55rem;"></i> Income</span>
                            <span><i class="bi bi-circle-fill" style="color:#3ad29f;font-size:.55rem;"></i> Expences</span>
                        </div>
                    </div>
                    <div class="hms-body"><canvas id="yearlyChart" height="110"></canvas></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hms-card">
                    <div class="hms-head"><div class="ttl">Monthly Income Overview</div></div>
                    <div class="hms-body"><canvas id="incomeDonut" height="220"></canvas></div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 5 — Patients by Specification · Profit/Loss · Staff
        ════════════════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-3 equal-row">
            <div class="col-lg-7">
                <div class="hms-card">
                    <div class="hms-head">
                        <div class="ttl">Patients by Specification</div>
                        <div class="small text-muted">
                            <span class="badge" style="background:var(--hms-amber);">&nbsp;</span> Inpatients
                            <span class="badge ms-2" style="background:var(--hms-mint);">&nbsp;</span> Outpatients
                        </div>
                    </div>
                    <div class="hms-body"><canvas id="specChart" height="280"></canvas></div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="d-flex flex-column gap-3 h-100">
                    {{-- Profit & Loss --}}
                    <div class="hms-card">
                        <div class="hms-head">
                            <div class="ttl">Profit &amp; Loss</div>
                            <span class="pill">Both <i class="bi bi-chevron-down"></i></span>
                        </div>
                        <div class="hms-body text-center">
                            <div class="gauge-wrap"><canvas id="profitGauge" height="120"></canvas></div>
                            <div class="d-flex justify-content-center gap-3 mt-2 small">
                                <span><i class="bi bi-circle-fill" style="color:var(--hms-amber);"></i> Profit {{ $profitPct }}%</span>
                                <span><i class="bi bi-circle-fill" style="color:var(--hms-mint);"></i> Loss {{ $lossPct }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Total Staff --}}
                    <div class="hms-card flex-grow-1">
                        <div class="hms-head"><div class="ttl"><i class="bi bi-people" style="color:var(--hms-mint);"></i> Total Staff: {{ $staff['total'] }}</div></div>
                        <div class="flex-grow-1">
                            @foreach([
                                ['Doctor',       $staff['doctors'],       '#e3eeff', '#3b82f6', 'bi-clipboard2-pulse'],
                                ['Nurse',        $staff['nurses'],        '#ffe1ec', '#e83e8c', 'bi-bandaid'],
                                ['Staff',        $staff['admins'],        '#dff6e3', '#28a745', 'bi-person-workspace'],
                                ['Accountant',   $staff['accountants'],   '#fff1d6', '#f4b942', 'bi-calculator'],
                                ['Pharmacist',   $staff['pharmacists'],   '#e0f7f4', '#0fa57b', 'bi-capsule'],
                                ['Pathologist',  $staff['pathologists'],  '#f3e8ff', '#a855f7', 'bi-eyedropper'],
                                ['Radiologist',  $staff['radiologists'],  '#fdebd0', '#fd7e14', 'bi-broadcast'],
                                ['Gynecologist', $staff['gynecologists'], '#ffe1e1', '#dc3545', 'bi-gender-female'],
                            ] as [$lbl, $val, $bg, $fg, $icon])
                                <div class="staff-row">
                                    <span class="ic rounded-circle d-inline-flex align-items-center justify-content-center"
                                          style="width:30px; height:30px; background:{{ $bg }}; color:{{ $fg }};">
                                        <i class="bi {{ $icon }}"></i>
                                    </span>
                                    <span class="num">{{ $val }}</span>
                                    <span class="role">{{ $lbl }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 6 — Bed Occupancy
        ════════════════════════════════════════════════════════════════ --}}
        <div class="hms-card mb-3">
            <div class="hms-head">
                <div class="ttl">
                    <i class="bi bi-hospital" style="color:var(--hms-mint);"></i>
                    Bed Occupancy
                    <span class="badge bg-light text-dark border ms-2">{{ $bedOccupancy->count() }}</span>
                </div>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:10px; top:8px; color:var(--hms-muted);"></i>
                    <input type="text" class="form-control form-control-sm ps-4" id="bedSearch" placeholder="Search here" style="max-width:220px;">
                </div>
            </div>
            <div class="bed-scroll">
                <table class="table mb-0 align-middle hms-table bed-sticky" id="bedTable">
                    <thead>
                        <tr>
                            <th style="width:34px;"><input type="checkbox" id="bedSelectAll"></th>
                            <th>Bed Group</th>
                            <th>Bed Type</th>
                            <th>Available</th>
                            <th>Occupied</th>
                            <th>Free in next 12 Hours</th>
                            <th style="width:80px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bedOccupancy as $b)
                            @php
                                $total      = (int) ($b->total_beds ?? 0);
                                $available  = (int) $b->available;
                                $occupied   = (int) $b->occupied;
                                $usagePct   = $total > 0 ? round(($occupied / $total) * 100) : 0;
                                $usageCls   = $usagePct >= 80 ? 'danger' : ($usagePct >= 50 ? 'warning' : 'success');
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="bed-row-check"></td>
                                <td class="fw-semibold text-uppercase small">{{ $b->bed_group }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $b->bed_type }}</span></td>
                                <td><span class="text-success fw-semibold">{{ $available }}</span></td>
                                <td><span class="text-danger fw-semibold">{{ $occupied }}</span></td>
                                <td>{{ $available }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-light border-0 bed-view-btn"
                                            data-bs-toggle="modal" data-bs-target="#bedDetailModal"
                                            data-group="{{ $b->bed_group }}"
                                            data-type="{{ $b->bed_type }}"
                                            data-available="{{ $available }}"
                                            data-occupied="{{ $occupied }}"
                                            data-total="{{ $total }}"
                                            data-usage="{{ $usagePct }}"
                                            data-usage-cls="{{ $usageCls }}"
                                            title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No bed data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bedOccupancy->count())
                <div class="d-flex flex-wrap justify-content-between align-items-center p-2 border-top small gap-2">
                    <div class="text-muted">Showing all {{ $bedOccupancy->count() }} bed groups · scroll to see more</div>
                </div>
            @endif
        </div>

        {{-- Bed details modal --}}
        <div class="modal fade" id="bedDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-hospital text-success"></i>
                            Bed Group: <span id="bedModalGroup">—</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row mb-3 small">
                            <dt class="col-sm-5 text-muted">Bed Type</dt>
                            <dd class="col-sm-7" id="bedModalType">—</dd>

                            <dt class="col-sm-5 text-muted">Total Beds</dt>
                            <dd class="col-sm-7 fw-semibold" id="bedModalTotal">—</dd>

                            <dt class="col-sm-5 text-muted">Available</dt>
                            <dd class="col-sm-7 text-success fw-semibold" id="bedModalAvailable">—</dd>

                            <dt class="col-sm-5 text-muted">Occupied</dt>
                            <dd class="col-sm-7 text-danger fw-semibold" id="bedModalOccupied">—</dd>

                            <dt class="col-sm-5 text-muted">Utilization</dt>
                            <dd class="col-sm-7 fw-semibold" id="bedModalUsage">—</dd>
                        </dl>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="bedModalBar" role="progressbar" style="width: 0%;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ url('/beds') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right"></i> Manage Beds
                        </a>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             SECTION 7 — Weekly Calendar (time × day grid)
        ════════════════════════════════════════════════════════════════ --}}
        @php
            $startOfWeek = now()->startOfWeek();
            $weekDays    = collect(range(0, 6))->map(fn($i) => $startOfWeek->copy()->addDays($i));
            $timeSlots   = ['8 AM','9 AM','10 AM','11 AM','12 PM','1 PM','2 PM'];
            $eventsByCell = [];
            foreach ($todaysCalendar as $ev) {
                if (! $ev->time) continue;
                try {
                    $t   = \Illuminate\Support\Carbon::parse(($ev->date ?? now()->toDateString()).' '.$ev->time);
                    $dow = $t->dayOfWeekIso - 1;
                    $h   = (int) $t->format('G');
                    $bucket = match (true) {
                        $h < 9  => 0,
                        $h < 10 => 1,
                        $h < 11 => 2,
                        $h < 12 => 3,
                        $h < 13 => 4,
                        $h < 14 => 5,
                        default => 6,
                    };
                    $eventsByCell[$dow][$bucket][] = $ev;
                } catch (\Throwable $e) { /* skip */ }
            }
        @endphp
        <div class="hms-card mb-3">
            <div class="hms-head">
                <div class="ttl">{{ now()->format('F Y') }}</div>
                <span class="pill"><i class="bi bi-calendar3"></i> {{ $startOfWeek->format('d M') }} – {{ $startOfWeek->copy()->endOfWeek()->format('d M Y') }}</span>
            </div>
            <div class="hms-body p-0">
                <div class="cal-wrap">
                    <div class="cal-grid cal-head-row">
                        <div class="cal-time-col">
                            <button class="btn btn-sm btn-light border"><i class="bi bi-chevron-left"></i></button>
                            <button class="btn btn-sm btn-light border"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        @foreach($weekDays as $day)
                            <div class="cal-day-head {{ $day->isToday() ? 'active' : '' }}">
                                <div class="dow">{{ strtoupper($day->format('D')) }} {{ $day->format('d') }}</div>
                            </div>
                        @endforeach
                    </div>

                    @foreach($timeSlots as $tIdx => $slot)
                        <div class="cal-grid">
                            <div class="cal-time-col"><div class="small fw-semibold">{{ $slot }}</div></div>
                            @for($d = 0; $d < 7; $d++)
                                <div class="cal-cell">
                                    @if(isset($eventsByCell[$d][$tIdx]))
                                        @foreach($eventsByCell[$d][$tIdx] as $ev)
                                            @php
                                                $cls = match (strtolower((string) ($ev->status ?? ''))) {
                                                    'approved'             => 'cal-green',
                                                    'declined','cancelled' => 'cal-pink',
                                                    default                => '',
                                                };
                                            @endphp
                                            <div class="cal-event {{ $cls }}">
                                                <div class="cal-event-time">{{ $ev->time }}</div>
                                                <div class="cal-event-title">{{ $ev->patient_name ?? 'Appointment' }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="{{ asset('backend/assets/libs/chartjs/chart.js') }}"></script>
<script>
(function () {
    /* ── Waiting time mini line (amber) ── */
    const wait = document.getElementById('waitChart');
    if (wait) new Chart(wait, {
        type: 'line',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [{
                data: [22, 18, 25, 30, 27, 24, {{ $avgWaitMinutes ?: 0 }}],
                borderColor: '#f4b942', backgroundColor: 'rgba(244,185,66,.18)',
                borderWidth: 2, tension: .4, fill: true, pointRadius: 0,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { intersect: false } },
            scales: { x: { display: false }, y: { display: false } },
        }
    });

    /* ── Yearly income/expense (blue + mint) ── */
    const yearlyData = @json($monthlySeries);
    const yc = document.getElementById('yearlyChart');
    if (yc) new Chart(yc, {
        type: 'line',
        data: {
            labels: yearlyData.map(r => r.label),
            datasets: [
                { label: 'Income',   data: yearlyData.map(r => r.income),  borderColor: '#0d6efd', tension: .4, borderWidth: 2, pointRadius: 0, fill: false },
                { label: 'Expences', data: yearlyData.map(r => r.expense), borderColor: '#3ad29f', tension: .4, borderWidth: 2, pointRadius: 0, fill: false },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#f1f3f5' }, ticks: { callback: v => v >= 1000 ? (v/1000) + 'K' : v } }
            },
            interaction: { mode: 'index', intersect: false },
        }
    });

    /* ── Monthly income overview (rainbow half-donut) ── */
    const inc = @json($incomeByModule);
    const incEntries = Object.entries(inc).filter(([k,v]) => v > 0);
    const labels = incEntries.map(([k]) => k.replace('_',' ').replace(/\b\w/g, c => c.toUpperCase()));
    const totals = incEntries.map(([,v]) => v);
    const colors = ['#e74c3c','#f39c12','#f4d03f','#27ae60','#16a085','#1abc9c','#3498db','#2c3e50','#9b59b6'];
    const dn = document.getElementById('incomeDonut');
    if (dn && totals.length) new Chart(dn, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: totals, backgroundColor: colors.slice(0, totals.length), borderWidth: 2, borderColor: '#fff' }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '60%', rotation: -90, circumference: 180,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } } },
        }
    });

    /* ── Patients by specialization (horizontal stacked) ── */
    const sp = @json($bySpec->toArray());
    const sc = document.getElementById('specChart');
    if (sc && sp.length) new Chart(sc, {
        type: 'bar',
        data: {
            labels: sp.map(r => r.spec),
            datasets: [
                { label: 'Inpatients',  data: sp.map(r => r.inpatients),  backgroundColor: '#f4b942', barThickness: 14, borderRadius: 4 },
                { label: 'Outpatients', data: sp.map(r => r.outpatients), backgroundColor: '#3ad29f', barThickness: 14, borderRadius: 4 },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { color: '#f1f3f5' } },
                y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } }
            },
            plugins: { legend: { display: false } },
        }
    });

    /* ── Profit / loss gauge (half-doughnut) ── */
    const pg = document.getElementById('profitGauge');
    if (pg) new Chart(pg, {
        type: 'doughnut',
        data: {
            labels: ['Profit', 'Loss'],
            datasets: [{
                data: [{{ $profitPct }}, {{ $lossPct }}],
                backgroundColor: ['#f4b942', '#3ad29f'],
                borderWidth: 2, borderColor: '#fff',
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            rotation: -90, circumference: 180,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.parsed + '%' } },
            }
        }
    });

    /* ── Bed table search ── */
    const bs = document.getElementById('bedSearch');
    if (bs) bs.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#bedTable tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    /* ── Bed select-all toggle ── */
    const selAll = document.getElementById('bedSelectAll');
    if (selAll) selAll.addEventListener('change', function () {
        document.querySelectorAll('#bedTable tbody .bed-row-check').forEach(c => {
            if (c.closest('tr').style.display !== 'none') c.checked = this.checked;
        });
    });

    /* ── Bed details modal (Action button) ── */
    document.querySelectorAll('.bed-view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;
            const usage = parseInt(d.usage || '0', 10);
            const cls   = d.usageCls || 'success';

            document.getElementById('bedModalGroup').textContent     = d.group || '—';
            document.getElementById('bedModalType').textContent      = d.type || '—';
            document.getElementById('bedModalTotal').textContent     = d.total || '0';
            document.getElementById('bedModalAvailable').textContent = d.available || '0';
            document.getElementById('bedModalOccupied').textContent  = d.occupied || '0';
            document.getElementById('bedModalUsage').textContent     = usage + '%';

            const bar = document.getElementById('bedModalBar');
            bar.style.width = usage + '%';
            bar.className = 'progress-bar bg-' + cls;
            bar.setAttribute('aria-valuenow', String(usage));
        });
    });
})();
</script>
@endpush
@endsection
