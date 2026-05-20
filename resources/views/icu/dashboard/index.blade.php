@extends('backend.layouts.master')

@section('title', ($icuType ?? null) ? $icuType . ' Live Dashboard' : 'ICU/CCU Live Dashboard')

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
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
        }
        .icu-bed-card:hover { box-shadow: 0 10px 24px rgba(15, 23, 42, .10); transform: translateY(-2px); }
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
        .icu-bed-card__tab i { font-size: .95rem; opacity: .8; }
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
            width: 7px; height: 7px;
            background: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(220, 53, 69, .55);
            animation: icuLivePulse 1.6s ease-out infinite;
        }
        @keyframes icuLivePulse {
            0%   { box-shadow: 0 0 0 0 rgba(220, 53, 69, .55); }
            70%  { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        .icu-bed-card__body { padding: 0 16px 16px; }
        .icu-bed-card__patient { font-size: .98rem; color: #0f172a; }
        .icu-bed-card__case { color: #475569; font-weight: 500; }
        .icu-bed-card__case:hover { color: #0d6efd; }

        .icu-bed-card--danger  { border-color: rgba(220, 53, 69, .45); }
        .icu-bed-card--danger::before  { background: linear-gradient(90deg, #dc3545 0%, #f87171 100%); }
        .icu-bed-card--danger  .icu-bed-card__tab { border-color: rgba(220, 53, 69, .35); background: #fef2f3; color: #842029; }

        .icu-bed-card--warning { border-color: rgba(240, 173, 78, .55); }
        .icu-bed-card--warning::before { background: linear-gradient(90deg, #f0ad4e 0%, #fcd34d 100%); }
        .icu-bed-card--warning .icu-bed-card__tab { border-color: rgba(240, 173, 78, .45); background: #fff8eb; color: #7a4d00; }

        .icu-bed-card--success .icu-bed-card__tab { background: #ecfdf5; color: #065f46; border-color: rgba(16, 185, 129, .3); }

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
        .icu-vital__label i { font-size: .8rem; opacity: .8; }
        .icu-vital__value { font-weight: 700; color: #0f172a; font-size: 1rem; margin-top: 2px; }
        .icu-vital--danger .icu-vital__value { color: #b91c1c; }
        .icu-vital--warning .icu-vital__value { color: #b45309; }
        .min-w-0 { min-width: 0; }
    </style>

    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <h1 class="app-page-title">{{ ($icuType ?? null) ? $icuType . ' Live Dashboard' : 'ICU/CCU Live Dashboard' }}</h1>
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
                    $unitAdmissions     = $admissions->where('icu_type', $u);
                    $unitTotalBeds      = $bedsByUnit[$u] ?? 0;
                    $unitOccupiedBeds   = $unitAdmissions->whereNotNull('bed_id')->count();
                    $unitAdmissionIds   = $unitAdmissions->pluck('id');
                    $unitCriticalCount  = $openAlerts->flatten()
                        ->whereIn('icu_admission_id', $unitAdmissionIds)
                        ->where('severity', 'Critical')
                        ->count();
                @endphp
                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                     id="pane-{{ strtolower($u) }}"
                     role="tabpanel"
                     aria-labelledby="tab-{{ strtolower($u) }}">

                    {{-- KPI cards (per unit) --}}
                    <div class="row g-2 mt-2">
                        <div class="col-md-3"><div class="card"><div class="card-body py-2">
                            <div class="text-muted small">Total {{ $u }} Beds</div>
                            <div class="fs-4 fw-semibold">{{ $unitTotalBeds }}</div>
                        </div></div></div>
                        <div class="col-md-3"><div class="card"><div class="card-body py-2">
                            <div class="text-muted small">Occupied</div>
                            <div class="fs-4 fw-semibold">{{ $unitOccupiedBeds }} / {{ $unitTotalBeds }}</div>
                        </div></div></div>
                        <div class="col-md-3"><div class="card border-warning-subtle"><div class="card-body py-2">
                            <div class="text-muted small">Active Admissions</div>
                            <div class="fs-4 fw-semibold">{{ $unitAdmissions->count() }}</div>
                        </div></div></div>
                        <div class="col-md-3"><div class="card border-danger-subtle"><div class="card-body py-2">
                            <div class="text-muted small">Open Critical Alerts</div>
                            <div class="fs-4 fw-semibold text-danger">{{ $unitCriticalCount }}</div>
                        </div></div></div>
                    </div>

                    <div class="row g-3 mt-1 icu-bed-grid">
                        @forelse ($unitAdmissions as $a)
                            @php
                                $v   = $latestVitals->get($a->id);
                                $als = $openAlerts->get($a->id, collect());
                                $cb  = $activeCodes->get($a->id);
                                $sev = $cb ? 'Critical' : ($v->severity ?? ($als->where('severity', 'Critical')->count() ? 'Critical' : ($als->count() ? 'Warning' : 'Normal')));
                                $sevTone = match ($sev) {
                                    'Critical' => 'danger',
                                    'Warning'  => 'warning',
                                    default    => 'success',
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
                                                    <i class="bi bi-person-fill text-secondary me-1"></i>{{ $a->patient?->patient_name ?? '-' }}
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
                                                <span class="badge bg-{{ $sevTone }} d-inline-flex align-items-center gap-1">
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
                                                <span class="icu-vital__label"><i class="bi bi-heart-pulse-fill text-danger"></i> HR</span>
                                                <span class="icu-vital__value">{{ $v->heart_rate ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-activity text-primary"></i> BP</span>
                                                <span class="icu-vital__value">{{ $v ? ($v->systolic_bp ?? '-') . '/' . ($v->diastolic_bp ?? '-') : '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-droplet-fill text-info"></i> SpO₂</span>
                                                <span class="icu-vital__value">{{ $v->spo2 ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-wind text-secondary"></i> RR</span>
                                                <span class="icu-vital__value">{{ $v->respiratory_rate ?? '-' }}</span>
                                            </div>
                                            <div class="icu-vital">
                                                <span class="icu-vital__label"><i class="bi bi-thermometer-half text-warning"></i> Temp</span>
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
                                                <form method="POST"
                                                    action="{{ route('icu.admissions.emergency.activate', $a->id) }}"
                                                    class="d-inline flex-fill"
                                                    onsubmit="return confirm('Activate Code Blue for {{ $a->bed?->name ?? $a->icu_case_id }}?');">
                                                    @csrf
                                                    <input type="hidden" name="event_type" value="CardiacArrest">
                                                    <button class="btn btn-sm btn-danger w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                                        <i class="bi bi-exclamation-octagon-fill"></i> CODE BLUE
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card"><div class="card-body text-center text-muted py-4">
                                    No active {{ $u }} admissions.
                                </div></div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
