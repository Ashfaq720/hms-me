@extends('backend.layouts.master')

@php
    $unitLabel = request('icu_type') ?: 'ICU / CCU';
@endphp

@section('title', $unitLabel . ' Admissions')

@section('content')
    <style>
        .icu-adm-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 991.98px) {
            .icu-adm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 575.98px) {
            .icu-adm-stats { grid-template-columns: 1fr; }
        }

        .icu-adm-stat {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .icu-adm-stat__icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .icu-adm-stat__body { min-width: 0; line-height: 1.15; }

        .icu-adm-stat__label {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 4px;
        }

        .icu-adm-stat__value {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.01em;
        }

        .icu-adm-stat__hint {
            font-size: .78rem;
            color: #64748b;
            margin-top: 2px;
        }

        .icu-adm-stat--total .icu-adm-stat__icon { background: #e7f0ff; color: #2563eb; }
        .icu-adm-stat--total .icu-adm-stat__label { color: #2563eb; }

        .icu-adm-stat--isolation .icu-adm-stat__icon { background: #fde7eb; color: #dc2626; }
        .icu-adm-stat--isolation .icu-adm-stat__label { color: #dc2626; }

        .icu-adm-stat--vent .icu-adm-stat__icon { background: #e8f7ef; color: #059669; }
        .icu-adm-stat--vent .icu-adm-stat__label { color: #059669; }
        .icu-adm-stat--vent .icu-adm-stat__value { color: #047857; }

        .icu-adm-stat--critical .icu-adm-stat__icon { background: #fde7eb; color: #dc2626; }
        .icu-adm-stat--critical .icu-adm-stat__label { color: #dc2626; }
        .icu-adm-stat--critical .icu-adm-stat__value { color: #b91c1c; }
    </style>

    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">{{ $unitLabel }} Admissions</h1>
            </div>

            <a href="{{ route('icu.admissions.create', request()->filled('icu_type') ? ['icu_type' => request('icu_type')] : []) }}" class="btn btn-primary waves-effect waves-light">
                <i class="bi bi-heart-pulse me-1"></i> New  Admission
            </a>
        </div>

        @php
            $pct = fn($n, $d) => $d > 0 ? number_format(($n / $d) * 100, 2) . '%' : '0%';
            $fmt = fn($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        @endphp

        <div class="icu-adm-stats mt-3 mb-1">
            <div class="icu-adm-stat icu-adm-stat--total">
                <span class="icu-adm-stat__icon"><i class="bi bi-person-plus-fill"></i></span>
                <div class="icu-adm-stat__body">
                    <div class="icu-adm-stat__label">Total Admissions</div>
                    <div class="icu-adm-stat__value">{{ $fmt($totalActive) }}</div>
                    <div class="icu-adm-stat__hint">All Active Patients</div>
                </div>
            </div>

            <div class="icu-adm-stat icu-adm-stat--isolation">
                <span class="icu-adm-stat__icon"><i class="bi bi-emoji-frown-fill"></i></span>
                <div class="icu-adm-stat__body">
                    <div class="icu-adm-stat__label">ISOLATION</div>
                    <div class="icu-adm-stat__value">{{ $fmt($isolationCount) }}</div>
                    <div class="icu-adm-stat__hint">{{ $pct($isolationCount, $totalActive) }}</div>
                </div>
            </div>

            <div class="icu-adm-stat icu-adm-stat--vent">
                <span class="icu-adm-stat__icon"><i class="bi bi-lungs-fill"></i></span>
                <div class="icu-adm-stat__body">
                    <div class="icu-adm-stat__label">On Ventilator</div>
                    <div class="icu-adm-stat__value">{{ $fmt($ventilatorCount) }}</div>
                    <div class="icu-adm-stat__hint">{{ $pct($ventilatorCount, $totalActive) }}</div>
                </div>
            </div>

            <div class="icu-adm-stat icu-adm-stat--critical">
                <span class="icu-adm-stat__icon"><i class="bi bi-power"></i></span>
                <div class="icu-adm-stat__body">
                    <div class="icu-adm-stat__label">Critical Alerts</div>
                    <div class="icu-adm-stat__value">{{ $fmt($criticalCount) }}</div>
                    <div class="icu-adm-stat__hint">{{ $pct($criticalCount, $totalActive) }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="row g-2 mt-1 mb-2">
            {{-- <div class="col-md-3">
                <select name="icu_type" class="form-select form-select-sm">
                    <option value="">All ICU Types</option>
                    @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                        <option value="{{ $t }}" {{ request('icu_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['Requested', 'Approved', 'Admitted', 'Transferred', 'Discharged', 'Cancelled', 'Expired'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('icu.admissions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>--}}
        </form>

        <div class="row mt-1">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title mb-0">Admissions</h6>
                    </div>

                    <div class="card-body px-2 pt-2 pb-0 gradient-layer" style="min-height: 300px;">
                        <table class="table table-sm table-hover display table-row-rounded mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-2" style="width:40px;">SN</th>
                                    <th style="width:110px;">Case ID</th>
                                    <th style="min-width:160px;">Patient</th>
                                    <th style="width:110px;">Mobile</th>
                                    <th style="width:70px;">Gender</th>
                                    <th style="width:55px;">Age</th>
                                    <th style="width:70px;">Type</th>
                                    <th style="width:90px;">Source</th>
                                    <th style="width:80px;">Bed</th>
                                    <th style="width:85px;">Isolation</th>
                                    <th style="width:65px;">Vent</th>
                                    <th style="width:130px;">Admitted</th>
                                    <th style="width:95px;">Status</th>
                                    <th style="width:70px;" class="text-end pe-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admissions as $i => $a)
                                    <tr>
                                        <td class="ps-2 text-muted">{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('icu.admissions.show', $a->id) }}"
                                                class="fw-semibold">{{ $a->icu_case_id }}</a></td>
                                        <td>
                                            <div class="fw-semibold lh-sm">{{ $a->patient?->patient_name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ $a->patient?->mrn ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $a->patient?->mobileno ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $a->patient?->gender ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ calculateAgeFromDob($a->patient?->dob) ?? '' }}</div>
                                        </td>
                                        <td><span class="badge bg-danger-subtle text-danger">{{ $a->icu_type }}</span></td>
                                        <td>{{ $a->source_type }}</td>
                                        <td>{{ $a->bed?->name ?? '-' }}</td>
                                        <td>{{ $a->isolation_type }}</td>
                                        <td>{!! $a->ventilator_required
                                            ? '<span class="badge bg-warning-subtle text-warning">Yes</span>'
                                            : '<span class="text-muted small">No</span>' !!}</td>
                                        <td><small>{{ $a->admission_time?->format('Y-m-d H:i') }}</small></td>
                                        <td>
                                            @php
                                                $color = match ($a->status) {
                                                    'Admitted' => 'success',
                                                    'Approved' => 'primary',
                                                    'Discharged' => 'secondary',
                                                    'Transferred' => 'info',
                                                    'Expired' => 'dark',
                                                    'Cancelled' => 'danger',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $a->status }}</span>
                                        </td>
                                        <td class="text-end pe-2">
                                            <a href="{{ route('icu.admissions.show', $a->id) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center text-muted py-4">No ICU admissions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
