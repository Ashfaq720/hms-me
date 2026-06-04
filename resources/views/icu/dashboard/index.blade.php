@extends('backend.layouts.master')

@section('title', $icuType ?? null ? $icuType . ' Live Dashboard' : 'ICU/CCU Live Dashboard')

@section('content')
    <style>
        .icu-bed-card {
            position: relative;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding-top: 44px;
            height: 100%;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
            overflow: hidden;
        }

        .icu-bed-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
        }

        .icu-bed-card:hover {
            box-shadow: 0 10px 24px rgba(15, 23, 42, .10);
            transform: translateY(-2px);
        }

        .icu-bed-card__tab {
            position: absolute;
            top: 4px;
            left: -1.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 110px;
            padding: 6px 22px 6px 14px;
            background: #f8fafc;
            border: 1.5px solid #e5e7eb;
            border-top: none;
            border-top-left-radius: 0;
            border-bottom-right-radius: 18px;
            font-weight: 700;
            font-size: .9rem;
            color: #1f2937;
            line-height: 1.2;
            letter-spacing: .02em;
        }

        .icu-bed-card__tab i {
            font-size: .95rem;
            opacity: .8;
        }

        .icu-bed-card__live {
            position: absolute;
            top: 12px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 9px 2px 8px;
            background: rgba(220, 53, 69, .08);
            color: #b91c1c;
            border: 1px solid rgba(220, 53, 69, .25);
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .icu-bed-card__live-dot {
            width: 7px;
            height: 7px;
            background: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, .55);
            animation: icuLivePulse 1.6s ease-out infinite;
        }

        @keyframes icuLivePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, .55);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .icu-bed-card__body {
            padding: 0 16px 16px;
        }

        .icu-bed-card__patient {
            font-size: .98rem;
            color: #0f172a;
        }

        .icu-bed-card__case {
            color: #475569;
            font-weight: 500;
        }

        .icu-bed-card__case:hover {
            color: #0d6efd;
        }

        .icu-bed-card--danger {
            border-color: rgba(220, 53, 69, .45);
        }

        .icu-bed-card--danger::before {
            background: linear-gradient(90deg, #dc3545 0%, #f87171 100%);
        }

        .icu-bed-card--danger .icu-bed-card__tab {
            border-color: rgba(220, 53, 69, .35);
            background: #fef2f3;
            color: #842029;
        }

        .icu-bed-card--warning {
            border-color: rgba(240, 173, 78, .55);
        }

        .icu-bed-card--warning::before {
            background: linear-gradient(90deg, #f0ad4e 0%, #fcd34d 100%);
        }

        .icu-bed-card--warning .icu-bed-card__tab {
            border-color: rgba(240, 173, 78, .45);
            background: #fff8eb;
            color: #7a4d00;
        }

        .icu-bed-card--success .icu-bed-card__tab {
            background: #ecfdf5;
            color: #065f46;
            border-color: rgba(16, 185, 129, .3);
        }

        .icu-vitals {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            padding: 0;
            background: #e5e7eb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .icu-vital {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            padding: 8px 10px;
            background: #fff;
        }

        .icu-vital__label {
            font-size: .68rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .icu-vital__label i {
            font-size: .8rem;
            opacity: .8;
        }

        .icu-vital__value {
            font-weight: 700;
            color: #0f172a;
            font-size: 1rem;
            margin-top: 2px;
        }

        .icu-vital--danger .icu-vital__value {
            color: #b91c1c;
        }

        .icu-vital--warning .icu-vital__value {
            color: #b45309;
        }

        .min-w-0 {
            min-width: 0;
        }

        .icu-code-blue {
            width: 100%;
            padding: 8px;
            background: #fff5f5;
            border: 1px solid rgba(220, 53, 69, .28);
            border-radius: 10px;
        }

        .icu-code-blue__head {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            color: #842029;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .icu-code-blue__head i {
            font-size: .9rem;
        }

        .icu-code-blue__button {
            width: 100%;
            font-weight: 800;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .icu-code-blue__hint {
            color: #842029;
            font-size: .76rem;
            margin-bottom: 7px;
        }

        .code-blue-modal__icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff5f5;
            border: 1px solid rgba(220, 53, 69, .25);
            border-radius: 50%;
            color: #dc3545;
            font-size: 1.3rem;
        }

        .icu-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
        }

        @media (max-width: 1199.98px) {
            .icu-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .icu-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .icu-kpi {
            position: relative;
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 14px;
            padding: 16px 18px 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: box-shadow .2s ease, transform .2s ease;
            min-height: 118px;
        }

        .icu-kpi:hover {
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .icu-kpi__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .icu-kpi__value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            letter-spacing: -.02em;
        }

        .icu-kpi__hint {
            margin-top: 6px;
            font-size: .78rem;
            color: #64748b;
        }

        .icu-kpi__icon {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .icu-kpi__link {
            margin-top: 4px;
            font-size: .82rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .icu-kpi__link:hover {
            text-decoration: underline;
        }

        .icu-kpi--total .icu-kpi__label {
            color: #10b981;
        }

        .icu-kpi--total .icu-kpi__icon {
            background: #e8f7f1;
            color: #10b981;
        }

        .icu-kpi--occupied .icu-kpi__label {
            color: #2563eb;
        }

        .icu-kpi--occupied .icu-kpi__icon {
            background: #e7f0ff;
            color: #2563eb;
        }

        .icu-kpi--available .icu-kpi__label {
            color: #f59e0b;
        }

        .icu-kpi--available .icu-kpi__icon {
            background: #fff3df;
            color: #f59e0b;
        }

        .icu-kpi--vent .icu-kpi__label {
            color: #7c3aed;
        }

        .icu-kpi--vent .icu-kpi__icon {
            background: #efe9ff;
            color: #7c3aed;
        }

        .icu-kpi--critical .icu-kpi__label {
            color: #ef4444;
        }

        .icu-kpi--critical .icu-kpi__icon {
            background: #ffe4ea;
            color: #ef4444;
        }

        .icu-kpi--code .icu-kpi__label {
            color: #dc2626;
        }

        .icu-kpi--code .icu-kpi__value {
            color: #0f172a;
        }

        .icu-infection-card {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .icu-infection-card__title {
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 14px;
        }

        .icu-infection-card__body {
            display: flex;
            align-items: center;
            gap: 26px;
        }

        .icu-donut {
            position: relative;
            width: 130px;
            height: 130px;
            flex-shrink: 0;
            border-radius: 50%;
        }

        .icu-donut::after {
            content: "";
            position: absolute;
            inset: 22px;
            background: #fff;
            border-radius: 50%;
        }

        .icu-donut__center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1;
            line-height: 1.1;
        }

        .icu-donut__center-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
        }

        .icu-donut__center-label {
            font-size: .75rem;
            color: #64748b;
        }

        .icu-infection-legend {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 6px 14px;
            font-size: .92rem;
        }

        .icu-infection-legend__label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #334155;
        }

        .icu-infection-legend__dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .icu-infection-legend__count {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }

        .icu-infection-card__footer {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #eef0f3;
            text-align: center;
        }

        .icu-infection-card__footer a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            font-size: .92rem;
        }

        .icu-infection-card__footer a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <h1 class="app-page-title">{{ $icuType ?? null ? $icuType . ' Live Dashboard' : 'ICU/CCU Live Dashboard' }}
            </h1>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>

        {{-- Patient grid (scoped to current unit, or both if unfiltered) --}}
        @php $units = ($icuType ?? null) ? [$icuType] : ['ICU', 'CCU']; @endphp
        {{-- <ul class="nav nav-tabs mt-3" id="icuUnitTabs" role="tablist">
            @foreach ($units as $i => $u)
                @php $unitCount = $admissions->where('icu_type', $u)->count(); @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $i === 0 ? 'active' : '' }}"
                            id="tab-{{ strtolower($u) }}"
                            data-bs-toggle="tab"
                            data-bs-target="#pane-{{ strtolower($u) }}"
                            type="button" role="tab"
                            aria-controls="pane-{{ strtolower($u) }}"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        {{ $u }} <span class="badge bg-secondary ms-1">{{ $unitCount }}</span>
                    </button>
                </li>
            @endforeach
        </ul> --}}

        <div class="tab-content" id="icuUnitTabsContent">
            @foreach ($units as $i => $u)
                @php
                    $unitAdmissions = $admissions->where('icu_type', $u);
                    $unitTotalBeds = $bedsByUnit[$u] ?? 0;
                    $unitOccupiedBeds = $unitAdmissions->whereNotNull('bed_id')->count();
                    $unitAvailableBeds = max($unitTotalBeds - $unitOccupiedBeds, 0);
                    $unitAdmissionIds = $unitAdmissions->pluck('id');
                    $unitCriticalCount = $openAlerts
                        ->flatten()
                        ->whereIn('icu_admission_id', $unitAdmissionIds)
                        ->where('status', '!=', 'Closed')
                        ->count();
                    $unitCodeBlueCount = $activeCodes->whereIn('icu_admission_id', $unitAdmissionIds)->count();
                    $unitVentilatorCount = $ventilatorUsage->only($unitAdmissionIds->all())->sum();
                    $pct = fn($num, $den) => $den > 0 ? round(($num / $den) * 100, 1) : 0;
                    $occupiedPct = $pct($unitOccupiedBeds, $unitTotalBeds);
                    $availablePct = $pct($unitAvailableBeds, $unitTotalBeds);
                    $ventilatorPct = $pct($unitVentilatorCount, $unitTotalBeds);

                    $infCounts = [
                        'Contact' => $unitAdmissions->where('isolation_type', 'Contact')->count(),
                        'Airborne' => $unitAdmissions->where('isolation_type', 'Airborne')->count(),
                        'Droplet' => $unitAdmissions->where('isolation_type', 'Droplet')->count(),
                        'Protective' => $unitAdmissions->where('isolation_type', 'Standard')->count(),
                    ];
                    $infColors = [
                        'Contact' => '#22c55e',
                        'Airborne' => '#93c5fd',
                        'Droplet' => '#10b981',
                        'Protective' => '#8b5cf6',
                    ];
                    $infTotal = array_sum($infCounts);
                    $donutStops = [];
                    $acc = 0;
                    foreach ($infCounts as $k => $v) {
                        $slicePct = $infTotal > 0 ? round(($v / $infTotal) * 100, 2) : 0;
                        if ($slicePct > 0) {
                            $donutStops[] = "{$infColors[$k]} {$acc}% " . ($acc + $slicePct) . '%';
                            $acc += $slicePct;
                        }
                    }
                    $donutCss =
                        $infTotal > 0
                            ? 'background: conic-gradient(' . implode(', ', $donutStops) . ');'
                            : 'background: #e5e7eb;';
                @endphp
                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="pane-{{ strtolower($u) }}" role="tabpanel"
                    aria-labelledby="tab-{{ strtolower($u) }}">

                    {{-- KPI cards (per unit) --}}
                    <div class="icu-kpi-grid mt-3">
                        <div class="icu-kpi icu-kpi--total">
                            <div class="icu-kpi__label">Total Beds</div>
                            <div class="icu-kpi__value">{{ $unitTotalBeds }}</div>
                            <div class="icu-kpi__hint">100% of capacity</div>
                            <span class="icu-kpi__icon"><i class="bi bi-hospital"></i></span>
                        </div>

                        <div class="icu-kpi icu-kpi--occupied">
                            <div class="icu-kpi__label">Occupied Beds</div>
                            <div class="icu-kpi__value">{{ $unitOccupiedBeds }}</div>
                            <div class="icu-kpi__hint">{{ $occupiedPct }}% Occupied</div>
                            <span class="icu-kpi__icon"><i class="bi bi-person-fill"></i></span>
                        </div>

                        <div class="icu-kpi icu-kpi--available">
                            <div class="icu-kpi__label">Available Beds</div>
                            <div class="icu-kpi__value">{{ $unitAvailableBeds }}</div>
                            <div class="icu-kpi__hint">{{ $availablePct }}% Available</div>
                            <span class="icu-kpi__icon"><i class="bi bi-door-open-fill"></i></span>
                        </div>

                        <div class="icu-kpi icu-kpi--vent">
                            <div class="icu-kpi__label">Ventilators In Use</div>
                            <div class="icu-kpi__value">{{ $unitVentilatorCount }}</div>
                            <div class="icu-kpi__hint">{{ $ventilatorPct }}% of {{ $unitTotalBeds }}</div>
                            <span class="icu-kpi__icon"><i class="bi bi-lungs-fill"></i></span>
                        </div>

                        <div class="icu-kpi icu-kpi--critical">
                            <div class="icu-kpi__label">Critical Alerts</div>
                            <div class="icu-kpi__value">{{ $unitCriticalCount }}</div>
                            <span class="icu-kpi__icon"><i class="bi bi-heart-pulse-fill"></i></span>
                        </div>

                        <div class="icu-kpi icu-kpi--code">
                            <div class="icu-kpi__label">Code Blue Active</div>
                            <div class="icu-kpi__value">{{ $unitCodeBlueCount }}</div>
                            @if ($unitCodeBlueCount > 0)
                                <a href="#pane-{{ strtolower($u) }}" class="icu-kpi__link">View Active Cases</a>
                            @else
                                <div class="icu-kpi__hint">No active events</div>
                            @endif
                            <span class="icu-kpi__icon" style="background:#ffe1e1;color:#dc2626;"><i
                                    class="bi bi-broadcast-pin"></i></span>
                        </div>
                    </div>

                    <div class="row g-3 mt-1 icu-bed-grid">
                        @forelse ($unitAdmissions as $a)
                            @php
                                $v = $latestVitals->get($a->id);
                                $als = $openAlerts->get($a->id, collect());
                                $cb = $activeCodes->get($a->id);
                                $sev = $cb
                                    ? 'Critical'
                                    : $v->severity ??
                                        ($als->where('severity', 'Critical')->count()
                                            ? 'Critical'
                                            : ($als->count()
                                                ? 'Warning'
                                                : 'Normal'));
                                $sevTone = match ($sev) {
                                    'Critical' => 'danger',
                                    'Warning' => 'warning',
                                    default => 'success',
                                };
                            @endphp
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="icu-bed-card icu-bed-card--{{ $sevTone }}">
                                    <div class="icu-bed-card__tab">
                                        <i class="bi bi-hospital"></i>
                                        <span class="icu-bed-card__bed">{{ $a->bed?->name ?? '-' }}</span>
                                    </div>
                                    <div class="icu-bed-card__live" title="Live monitoring">
                                        <span class="icu-bed-card__live-dot"></span>
                                        <span>Live</span>
                                    </div>
                                    <div class="icu-bed-card__body">
                                        <div class="d-flex justify-content-between align-items-start mb-2 mt-1">
                                            <div class="min-w-0 pe-2">
                                                <div class="icu-bed-card__patient fw-semibold text-truncate">
                                                    <i
                                                        class="bi bi-person-fill text-secondary me-1"></i>{{ $a->patient?->patient_name ?? '-' }}
                                                </div>
                                                <a href="{{ route('icu.admissions.show', $a->id) }}"
                                                    class="small text-decoration-none icu-bed-card__case">
                                                    <i class="bi bi-hash"></i>{{ $a->icu_case_id }}
                                                </a>
                                            </div>
                                            @if ($cb)
                                                <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-exclamation-octagon-fill"></i> CODE BLUE
                                                </span>
                                            @else
                                                <span
                                                    class="badge bg-{{ $sevTone }} d-inline-flex align-items-center gap-1">
                                                    @if ($sev === 'Critical')
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    @elseif ($sev === 'Warning')
                                                        <i class="bi bi-exclamation-circle-fill"></i>
                                                    @else
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    @endif
                                                    {{ $sev }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="icu-vitals">
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i
                                                        class="bi bi-heart-pulse-fill text-danger"></i> HR</span>
                                                <span class="icu-vital__value">{{ $v->heart_rate ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-activity text-primary"></i>
                                                    BP</span>
                                                <span
                                                    class="icu-vital__value">{{ $v ? ($v->systolic_bp ?? '-') . '/' . ($v->diastolic_bp ?? '-') : '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-droplet-fill text-info"></i>
                                                    SpO₂</span>
                                                <span class="icu-vital__value">{{ $v->spo2 ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-wind text-secondary"></i>
                                                    RR</span>
                                                <span class="icu-vital__value">{{ $v->respiratory_rate ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i
                                                        class="bi bi-thermometer-half text-warning"></i> Temp</span>
                                                <span class="icu-vital__value">{{ $v->temperature ?? '-' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3">
                                            {{-- <a href="{{ route('icu.admissions.vitals.index', $a->id) }}"
                                                class="btn btn-sm btn-outline-primary flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                                <i class="bi bi-graph-up"></i> Vitals
                                            </a> --}}
                                            @if ($cb)
                                                <a href="{{ route('icu.admissions.emergency.show', [$a->id, $cb->id]) }}"
                                                    class="btn btn-sm btn-danger flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                                    <i class="bi bi-exclamation-octagon"></i> Open Code
                                                </a>
                                            @else
                                                <div class="icu-code-blue flex-fill">
                                                    <div class="icu-code-blue__head">
                                                        <i class="bi bi-exclamation-octagon-fill"></i>
                                                        <span>Emergency activation</span>
                                                    </div>
                                                    <div class="icu-code-blue__hint">Select event type in the activation
                                                        modal.</div>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger icu-code-blue__button d-inline-flex align-items-center justify-content-center gap-1"
                                                        data-bs-toggle="modal" data-bs-target="#codeBlueModal"
                                                        data-action="{{ route('icu.admissions.emergency.activate', $a->id) }}"
                                                        data-bed="{{ $a->bed?->name ?? '-' }}"
                                                        data-case="{{ $a->icu_case_id }}">
                                                        <i class="bi bi-broadcast-pin"></i> CODE BLUE
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center text-muted py-4">
                                        No active {{ $u }} admissions.
                                    </div>
                                </div>
                            </div>
                        @endforelse

                        <div class="row g-3 mt-3">
                            <div class="col-12 col-md-6 col-lg-5">
                                <div class="icu-infection-card">
                                    <div class="icu-infection-card__title">Infection Control</div>
                                    <div class="icu-infection-card__body">
                                        <div class="icu-donut" style="{{ $donutCss }}">
                                            <div class="icu-donut__center">
                                                <span class="icu-donut__center-value">{{ $infTotal }}</span>
                                                <span class="icu-donut__center-label">Isolation</span>
                                            </div>
                                        </div>
                                        <div class="icu-infection-legend">
                                            @foreach ($infCounts as $label => $count)
                                                <span class="icu-infection-legend__label">
                                                    <span class="icu-infection-legend__dot"
                                                        style="background: {{ $infColors[$label] }};"></span>
                                                    {{ $label }}
                                                </span>
                                                <span class="icu-infection-legend__count">{{ $count }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="icu-infection-card__footer">
                                        <a href="{{ route('icu.infection.reports', ['icu_type' => $u]) }}">
                                            View Infection Registry <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="codeBlueModal" tabindex="-1" aria-labelledby="codeBlueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="codeBlueForm" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="code-blue-modal__icon">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </span>
                        <div>
                            <h5 class="modal-title text-danger fw-bold mb-0" id="codeBlueModalLabel">Activate Code Blue
                            </h5>
                            <div class="text-muted small" id="codeBlueModalPatient">Select event type before activation.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Event Type <span class="text-danger">*</span></label>
                    <select name="event_type" id="codeBlueEventType" class="form-select" required>
                        <option value="">Select event type</option>
                        <option value="CardiacArrest">Cardiac arrest</option>
                        <option value="RespiratoryArrest">Respiratory arrest</option>
                        <option value="SevereDesaturation">Severe desaturation</option>
                        <option value="SuddenCollapse">Sudden collapse</option>
                        <option value="Seizure">Seizure</option>
                        <option value="Shock">Shock</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1">
                        <i class="bi bi-broadcast-pin"></i> Activate Code Blue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('codeBlueModal');
            const form = document.getElementById('codeBlueForm');
            const patient = document.getElementById('codeBlueModalPatient');
            const eventType = document.getElementById('codeBlueEventType');

            modal?.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                form.action = trigger?.getAttribute('data-action') || '';
                eventType.value = '';
                patient.textContent =
                    `Bed ${trigger?.getAttribute('data-bed') || '-'} | ${trigger?.getAttribute('data-case') || '-'}`;
            });
        });
    </script>
@endsection
