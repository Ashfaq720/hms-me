@extends('backend.layouts.master')

@php
    $unitLabel = in_array($icuType ?? null, ['ICU', 'CCU'], true) ? $icuType : 'ICU';
@endphp

@section('title', "New {$unitLabel} Admission")

@section('content')
    <style>
        .icu-adm-page {
            background: #f4f6fb;
            padding: 8px 0;
        }

        .icu-adm-page .container {
            max-width: 1480px;
        }

        /* Top header card */
        .icu-adm-topbar {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .icu-adm-topbar h1 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -.01em;
        }

        .icu-adm-topbar small {
            color: #64748b;
            font-size: .78rem;
        }

        .icu-adm-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .icu-adm-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 12px;
            font-size: .8rem;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .icu-adm-pill i {
            color: #2563eb;
        }

        .icu-adm-pill--time {
            color: #0f172a;
            font-weight: 700;
        }

        .icu-adm-pill--time .pill-sub {
            color: #64748b;
            font-weight: 500;
            font-size: .72rem;
        }

        /* Section cards */
        .icu-adm-card {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            padding: 16px 18px;
            margin-bottom: 14px;
        }

        .icu-adm-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .icu-adm-card__title {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .06em;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }

        .icu-adm-card__title .num {
            color: #2563eb;
            margin-right: 6px;
        }

        .icu-adm-card__badge {
            font-size: .72rem;
            color: #059669;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .icu-adm-card__badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
        }

        /* Search */
        .icu-adm-search {
            position: relative;
            margin-bottom: 14px;
        }

        .icu-adm-search input {
            padding-left: 36px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            height: 40px;
        }

        .icu-adm-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Patient info panel */
        .icu-pat-card {
            padding: 16px;
        }

        .icu-pat-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .icu-pat-head__title {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            color: #64748b;
            text-transform: uppercase;
            margin: 0;
        }

        .icu-pat-body {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }

        .icu-pat-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #e0e7ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 1.4rem;
        }

        .icu-pat-meta {
            flex: 1;
            min-width: 0;
        }

        .icu-pat-meta__risk {
            font-size: .68rem;
            font-weight: 700;
            background: #fee2e2;
            color: #dc2626;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 4px;
            letter-spacing: .04em;
        }

        .icu-pat-meta__name {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .icu-pat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            row-gap: 10px;
            column-gap: 10px;
            margin-bottom: 12px;
        }

        .icu-pat-grid__label {
            font-size: .7rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 2px;
            letter-spacing: .04em;
        }

        .icu-pat-grid__value {
            font-size: .85rem;
            color: #0f172a;
            font-weight: 700;
        }

        .icu-pat-alerts {
            display: flex;
            gap: 8px;
            margin: 10px 0;
            flex-wrap: wrap;
        }

        .icu-pat-alert {
            flex: 1;
            min-width: 0;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: .72rem;
        }

        .icu-pat-alert--danger {
            background: #fef2f2;
            border: 1px solid #fee2e2;
        }

        .icu-pat-alert--warn {
            background: #fff7ed;
            border: 1px solid #fed7aa;
        }

        .icu-pat-alert__title {
            font-weight: 700;
            font-size: .68rem;
            letter-spacing: .04em;
        }

        .icu-pat-alert--danger .icu-pat-alert__title {
            color: #dc2626;
        }

        .icu-pat-alert--warn .icu-pat-alert__title {
            color: #ea580c;
        }

        .icu-pat-alert__value {
            color: #0f172a;
            font-weight: 600;
            margin-top: 2px;
        }

        .icu-pat-section+.icu-pat-section {
            margin-top: 10px;
        }

        .icu-pat-section__label {
            font-size: .7rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: .04em;
            margin-bottom: 2px;
        }

        .icu-pat-section__value {
            font-size: .85rem;
            color: #0f172a;
            font-weight: 700;
        }

        /* Option card group (Source / ICU Type) */
        .icu-opt-grid {
            display: grid;
            gap: 10px;
        }

        .icu-opt-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .icu-opt-grid.cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .icu-opt-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 10px;
            cursor: pointer;
            transition: all .15s ease;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .icu-opt-card:hover {
            border-color: #93c5fd;
        }

        .icu-opt-card input[type=radio] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .icu-opt-card__icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .icu-opt-card__title {
            font-size: .85rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .icu-opt-card__sub {
            font-size: .7rem;
            color: #64748b;
            line-height: 1.1;
            margin-top: 2px;
        }

        .icu-opt-card.is-checked {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
        }

        .icu-opt-card.is-checked .icu-opt-card__icon {
            background: #2563eb;
            color: #fff;
        }

        /* ICU type variants */
        .icu-opt-card[data-variant="ccu"] .icu-opt-card__icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .icu-opt-card[data-variant="ccu"].is-checked {
            border-color: #dc2626;
            background: #fef2f2;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .08);
        }

        .icu-opt-card[data-variant="ccu"].is-checked .icu-opt-card__icon {
            background: #dc2626;
            color: #fff;
        }

        .icu-opt-card[data-variant="nicu"] .icu-opt-card__icon {
            background: #f0fdf4;
            color: #16a34a;
        }

        .icu-opt-card[data-variant="nicu"].is-checked {
            border-color: #16a34a;
            background: #f0fdf4;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .08);
        }

        .icu-opt-card[data-variant="nicu"].is-checked .icu-opt-card__icon {
            background: #16a34a;
            color: #fff;
        }

        .icu-opt-card[data-variant="picu"] .icu-opt-card__icon {
            background: #fff7ed;
            color: #ea580c;
        }

        .icu-opt-card[data-variant="picu"].is-checked {
            border-color: #ea580c;
            background: #fff7ed;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, .08);
        }

        .icu-opt-card[data-variant="picu"].is-checked .icu-opt-card__icon {
            background: #ea580c;
            color: #fff;
        }

        /* Form fields */
        .icu-adm-form .form-label {
            font-size: .75rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            letter-spacing: .02em;
        }

        .icu-adm-form .form-control,
        .icu-adm-form .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: .85rem;
        }

        .icu-adm-form .form-control:focus,
        .icu-adm-form .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
        }

        .icu-section-divider {
            font-size: .75rem;
            font-weight: 700;
            color: #0f172a;
            margin: 8px 0 6px;
        }

        /* Switch */
        .icu-switch-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 24px;
        }

        .icu-switch-row .form-check-label {
            font-size: .82rem;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
        }

        /* Bed table */
        .icu-bed-table {
            width: 100%;
            font-size: .8rem;
        }

        .icu-bed-table th {
            color: #94a3b8;
            font-weight: 700;
            font-size: .68rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 6px 4px;
            border-bottom: 1px solid #f1f5f9;
        }

        .icu-bed-row {
            display: grid;
            grid-template-columns: 50px 60px 50px 1fr auto;
            align-items: center;
            gap: 8px;
            padding: 8px 6px;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .icu-bed-row:hover {
            background: #f8fafc;
        }

        .icu-bed-row.is-checked {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .icu-bed-row__name {
            font-weight: 700;
            color: #0f172a;
            font-size: .82rem;
        }

        .icu-bed-row__type {
            color: #64748b;
            font-size: .75rem;
        }

        .icu-bed-row__avail {
            color: #16a34a;
            font-size: .75rem;
            font-weight: 700;
        }

        .icu-bed-row__feat {
            color: #64748b;
            font-size: .72rem;
        }

        /* Equipment list */
        .icu-eq-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 4px;
            border-bottom: 1px solid #f1f5f9;
        }

        .icu-eq-row:last-child {
            border-bottom: 0;
        }

        .icu-eq-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icu-eq-meta {
            flex: 1;
            min-width: 0;
        }

        .icu-eq-meta__name {
            font-size: .85rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.15;
        }

        .icu-eq-meta__model {
            font-size: .72rem;
            color: #94a3b8;
        }

        .icu-eq-status {
            font-size: .72rem;
            color: #16a34a;
            font-weight: 700;
        }

        .icu-eq-asset {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: .75rem;
            font-weight: 600;
            color: #0f172a;
            background: #fff;
        }

        /* Resource availability cards */
        .icu-res-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        @media (max-width: 991.98px) {
            .icu-res-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .icu-res-grid {
                grid-template-columns: 1fr;
            }
        }

        .icu-res-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .icu-res-card__icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icu-res-card__name {
            font-size: .85rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .icu-res-card__status {
            font-size: .72rem;
            font-weight: 600;
            line-height: 1.1;
            margin-top: 2px;
        }

        .icu-res-card__dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .65rem;
            margin-left: auto;
        }

        .icu-res-card.is-ok {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .icu-res-card.is-ok .icu-res-card__icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .icu-res-card.is-ok .icu-res-card__status {
            color: #16a34a;
        }

        .icu-res-card.is-ok .icu-res-card__dot {
            background: #16a34a;
        }

        .icu-res-card.is-warn {
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .icu-res-card.is-warn .icu-res-card__icon {
            background: #ffedd5;
            color: #ea580c;
        }

        .icu-res-card.is-warn .icu-res-card__status {
            color: #ea580c;
        }

        .icu-res-card.is-warn .icu-res-card__dot {
            background: #ea580c;
        }

        .icu-res-note {
            margin-top: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 12px;
            color: #166534;
            font-size: .78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Summary */
        .icu-sum-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        @media (max-width: 991.98px) {
            .icu-sum-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 575.98px) {
            .icu-sum-grid {
                grid-template-columns: 1fr;
            }
        }

        .icu-sum-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: .82rem;
            font-weight: 700;
            width: fit-content;
        }

        .icu-sum-status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
        }

        /* Footer */
        .icu-adm-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 14px;
            padding: 12px 18px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            margin-top: 14px;
        }

        /* Override card */
        .icu-override-card {
            padding: 16px 18px;
        }

        .icu-override-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .icu-override-head .form-check {
            padding-left: 2.4em;
            margin: 0;
        }

        .icu-override-head .form-check-label {
            font-weight: 700;
            color: #0f172a;
            font-size: .82rem;
        }

        @media (max-width: 1199.98px) {

            .icu-opt-grid.cols-3,
            .icu-opt-grid.cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <div class="icu-adm-page">
        <div class="container">

            {{-- Top header bar --}}
            <div class="icu-adm-topbar">
                <div>
                    <h1>New {{ strtoupper($unitLabel) }} ADMISSION</h1>
                </div>
                <div class="icu-adm-pills">
                    <span class="icu-adm-pill icu-adm-pill--time" id="icu-adm-clock">
                        <span class="pill-time">--:-- --</span>
                        <span class="pill-sub" id="icu-adm-date">{{ now()->format('d M Y') }}</span>
                    </span>
                    <a href="{{ route('icu.admissions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('icu.admissions.store') }}" class="icu-adm-form" id="icu-adm-form">
                @csrf

                <div class="row g-3">

                    {{-- ===== LEFT COLUMN: PATIENT INFO ===== --}}
                    <div class="col-xl-3 col-lg-12">
                        {{-- <div class="icu-adm-search">
                            <i class="bi bi-search"></i>
                            <input type="text" id="patient_search" class="form-control"
                                placeholder="Search by Patient Name, UHID, Bed No., etc.">
                        </div> --}}

                        <div class="icu-adm-card icu-pat-card">
                            <div class="icu-pat-head">
                                <h6 class="icu-pat-head__title">PATIENT INFORMATION</h6>
                                {{-- <a href="#" class="text-muted small"><i class="bi bi-pencil-square"></i></a> --}}
                            </div>

                            <label class="form-label">Select Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select mb-3" required>
                                <option value="">-- Select Patient --</option>
                                @foreach ($patients as $p)
                                    <option value="{{ $p->id }}" data-name="{{ $p->patient_name }}"
                                        data-mrn="{{ $p->mrn ?? '' }}" data-gender="{{ $p->gender ?? '' }}"
                                        data-dob="{{ $p->dob ?? '' }}" data-blood="{{ $p->blood_group ?? '' }}"
                                        data-mobile="{{ $p->mobileno ?? '' }}"
                                        data-allergy="{{ $p->known_allergies ?? '' }}"
                                        {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->patient_name }} ({{ $p->mrn ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div id="patient_card_body" style="display:none;">
                                <div class="icu-pat-body">
                                    <div class="icu-pat-avatar"><i class="bi bi-person-fill"></i></div>
                                    <div class="icu-pat-meta">
                                        {{-- <span class="icu-pat-meta__risk">High Risk</span> --}}
                                        <h5 class="icu-pat-meta__name" data-field="name">-</h5>
                                    </div>
                                </div>

                                <div class="icu-pat-grid">
                                    <div>
                                        <div class="icu-pat-grid__label">MRN No.</div>
                                        <div class="icu-pat-grid__value" data-field="mrn">-</div>
                                    </div>
                                    <div>
                                        <div class="icu-pat-grid__label">&nbsp;</div>
                                        <div class="icu-pat-grid__value" data-field="agegender">-</div>
                                    </div>
                                    <div>
                                        <div class="icu-pat-grid__label">&nbsp;</div>
                                        <div class="icu-pat-grid__value" data-field="dob">-</div>
                                    </div>
                                    <div>
                                        <div class="icu-pat-grid__label">Blood Group</div>
                                        <div class="icu-pat-grid__value" data-field="blood">-</div>
                                    </div>
                                    <div>
                                        <div class="icu-pat-grid__label">Mobile</div>
                                        <div class="icu-pat-grid__value" data-field="mobile">-</div>
                                    </div>
                                </div>

                                <div class="icu-pat-alerts">
                                    <div class="icu-pat-alert icu-pat-alert--danger">
                                        <div class="icu-pat-alert__title"><i class="bi bi-exclamation-triangle-fill"></i>
                                            Allergy</div>
                                        <div class="icu-pat-alert__value" data-field="allergy"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== MIDDLE COLUMN: ADMISSION DETAILS ===== --}}
                    <div class="col-xl-5 col-lg-12">
                        <div class="icu-adm-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">1.</span> ADMISSION DETAILS</h6>
                            </div>

                            {{-- Admission Source --}}
                            <label class="form-label">Admission Source</label>
                            <div class="icu-opt-grid cols-3 mb-3" data-group="source">
                                @php
                                    $sources = [
                                        'ER' => [
                                            'title' => 'ER',
                                            'sub' => 'From Emergency',
                                            'icon' => 'bi-truck-front-fill',
                                        ],
                                        'OPD' => [
                                            'title' => 'OPD',
                                            'sub' => 'From Out Patient',
                                            'icon' => 'bi-clipboard2-pulse',
                                        ],
                                        'Ipd' => [
                                            'title' => 'IPD',
                                            'sub' => 'From In Patient',
                                            'icon' => 'bi-arrow-left-right',
                                        ],
                                    ];
                                    $currentSource = old('source_type', 'ER');
                                @endphp
                                @foreach ($sources as $key => $s)
                                    <label class="icu-opt-card {{ $currentSource === $key ? 'is-checked' : '' }}">
                                        <input type="radio" name="source_type" value="{{ $key }}"
                                            {{ $currentSource === $key ? 'checked' : '' }}>
                                        <span class="icu-opt-card__icon"><i class="bi {{ $s['icon'] }}"></i></span>
                                        <span>
                                            <span class="icu-opt-card__title">{{ $s['title'] }}</span>
                                            <span class="icu-opt-card__sub d-block">{{ $s['sub'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Source Reference</label>
                                    <div class="input-group">
                                        <input type="number" name="source_id" value="{{ old('source_id') }}"
                                            class="form-control" placeholder="Source ID #">
                                        <span class="input-group-text bg-white"><i
                                                class="bi bi-search text-muted"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Admission Date & Time</label>
                                    <input type="datetime-local" name="admission_time"
                                        value="{{ old('admission_time', now()->format('Y-m-d\TH:i')) }}"
                                        class="form-control" required>
                                </div>
                            </div>

                            {{-- ICU Type --}}
                            <label class="form-label">ICU Type <span class="text-danger">*</span></label>
                            <div class="icu-opt-grid cols-2 mb-3" data-group="icu_type">
                                @php
                                    $icuTypes = [
                                        'ICU' => [
                                            'title' => 'ICU',
                                            'sub' => 'General ICU',
                                            'icon' => 'bi-hospital',
                                            'variant' => 'icu',
                                        ],
                                        'CCU' => [
                                            'title' => 'CCU',
                                            'sub' => 'Cardiac Care Unit',
                                            'icon' => 'bi-heart-pulse-fill',
                                            'variant' => 'ccu',
                                        ],
                                        // 'NICU' => [
                                        //     'title' => 'NICU',
                                        //     'sub' => 'Neonatal ICU',
                                        //     'icon' => 'bi-emoji-smile',
                                        //     'variant' => 'nicu',
                                        // ],
                                        // 'PICU' => [
                                        //     'title' => 'PICU',
                                        //     'sub' => 'Pediatric ICU',
                                        //     'icon' => 'bi-people-fill',
                                        //     'variant' => 'picu',
                                        // ],
                                    ];
                                    $currentIcu = old('icu_type', $icuType ?? 'ICU');
                                    $icuTypeKey = (string) ($icuType ?? '');
                                    if ($icuTypeKey !== '' && isset($icuTypes[$icuTypeKey])) {
                                        $icuTypes = [$icuTypeKey => $icuTypes[$icuTypeKey]];
                                    }
                                @endphp
                                @foreach ($icuTypes as $key => $t)
                                    <label class="icu-opt-card {{ $currentIcu === $key ? 'is-checked' : '' }}"
                                        data-variant="{{ $t['variant'] }}">
                                        <input type="radio" name="icu_type" value="{{ $key }}"
                                            id="icu_type_{{ $key }}"
                                            {{ $currentIcu === $key ? 'checked' : '' }}>
                                        <span class="icu-opt-card__icon"><i class="bi {{ $t['icon'] }}"></i></span>
                                        <span>
                                            <span class="icu-opt-card__title">{{ $t['title'] }}</span>
                                            <span class="icu-opt-card__sub d-block">{{ $t['sub'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Clinical & Admission --}}
                            <div class="icu-section-divider">Clinical & Admission Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Referring Doctor <span class="text-danger">*</span></label>
                                    <select name="referring_doctor_id" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($doctors as $d)
                                            <option value="{{ $d->id }}"
                                                {{ old('referring_doctor_id') == $d->id ? 'selected' : '' }}>
                                                {{ $d->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Admission Type</label>
                                    <select name="admission_type" class="form-select">
                                        @foreach (['Emergency', 'Planned', 'Transfer'] as $t)
                                            <option value="{{ $t }}"
                                                {{ old('admission_type', 'Emergency') === $t ? 'selected' : '' }}>
                                                {{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Admission Diagnosis <span
                                            class="text-danger">*</span></label>
                                    <textarea type="text" name="admission_diagnosis" class="form-control" placeholder="Type here...." rows="3"
                                        required>{{ old('admission_diagnosis') }}</textarea>
                                    @error('admission_diagnosis')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Isolation Type</label>
                                    <select name="isolation_type" id="isolation_type" class="form-select">
                                        @foreach (['None', 'Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                                            <option value="{{ $t }}"
                                                {{ old('isolation_type', 'None') === $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="icu-switch-row">
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="ventilator_required" value="0">
                                            <input class="form-check-input" type="checkbox" id="vent_req"
                                                name="ventilator_required" value="1"
                                                {{ old('ventilator_required') ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label" for="vent_req">
                                            Ventilator Required <span class="text-danger">*</span>
                                            <div class="text-success small fw-bold" id="vent_state_label">Yes</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="icu-switch-row">
                                        <div class="form-check form-switch m-0">
                                            <input type="hidden" name="monitor_required" value="0">
                                            <input class="form-check-input" type="checkbox" id="mon_req"
                                                name="monitor_required" value="1"
                                                {{ old('monitor_required') ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label" for="mon_req">
                                            Monitor Required <span class="text-danger">*</span>
                                            <div class="text-success small fw-bold" id="mon_state_label">Yes</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"
                                        placeholder="">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Resource Availability Check --}}
                        {{-- <div class="icu-adm-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">2.</span> RESOURCE AVAILABILITY CHECK
                                </h6>
                            </div>
                            <div class="icu-res-grid">
                                <div class="icu-res-card is-ok">
                                    <span class="icu-res-card__icon"><i class="bi bi-hospital"></i></span>
                                    <div>
                                        <div class="icu-res-card__name">ICU Bed</div>
                                        <div class="icu-res-card__status">Available</div>
                                    </div>
                                    <span class="icu-res-card__dot"><i class="bi bi-check"></i></span>
                                </div>
                                <div class="icu-res-card is-ok">
                                    <span class="icu-res-card__icon"><i class="bi bi-lungs-fill"></i></span>
                                    <div>
                                        <div class="icu-res-card__name">Ventilator</div>
                                        <div class="icu-res-card__status">Available</div>
                                    </div>
                                    <span class="icu-res-card__dot"><i class="bi bi-check"></i></span>
                                </div>
                                <div class="icu-res-card is-ok">
                                    <span class="icu-res-card__icon"><i class="bi bi-display"></i></span>
                                    <div>
                                        <div class="icu-res-card__name">Monitor</div>
                                        <div class="icu-res-card__status">Available</div>
                                    </div>
                                    <span class="icu-res-card__dot"><i class="bi bi-check"></i></span>
                                </div>
                                <div class="icu-res-card is-warn">
                                    <span class="icu-res-card__icon"><i class="bi bi-shield-exclamation"></i></span>
                                    <div>
                                        <div class="icu-res-card__name">Isolation Bed</div>
                                        <div class="icu-res-card__status">Limited</div>
                                    </div>
                                    <span class="icu-res-card__dot"><i class="bi bi-exclamation"></i></span>
                                </div>
                            </div>
                            <div class="icu-res-note">
                                <i class="bi bi-check-circle-fill"></i> All required resources are available for admission.
                            </div>
                        </div> --}}
                    </div>

                    {{-- ===== RIGHT COLUMN: BED + EQUIPMENT + OVERRIDE ===== --}}
                    <div class="col-xl-4 col-lg-12">

                        {{-- Auto Bed Suggestion --}}
                        <div class="icu-adm-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">2.</span> AUTO BED SUGGESTION</h6>
                                <span class="icu-adm-card__badge"><span class="dot"></span> Auto Refreshed</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-muted small fw-semibold">Other Available Beds (<span
                                        id="bed_count">0</span>)</div>
                                <button type="button" id="check_avail" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>

                            <div id="bed_list">
                                <div class="text-muted small py-3 text-center">Pick an ICU type and click Refresh to
                                    see available beds.</div>
                            </div>
                            @error('bed_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <button type="button" class="btn btn-primary w-100 mt-3" id="confirm_bed_btn">
                                Confirm Selected Bed
                            </button>
                        </div>

                        {{-- Equipment Reservation --}}
                        {{-- <div class="icu-adm-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">4.</span> EQUIPMENT RESERVATION</h6>
                            </div>

                            @php
                                $equipment = [
                                    [
                                        'name' => 'Ventilator',
                                        'model' => 'Model: Philips V60',
                                        'icon' => 'bi-lungs-fill',
                                        'asset' => 'VENT-12',
                                    ],
                                    [
                                        'name' => 'Patient Monitor',
                                        'model' => 'Model: Mindray uMEC12',
                                        'icon' => 'bi-display',
                                        'asset' => 'MON-08',
                                    ],
                                    [
                                        'name' => 'Syringe Pump',
                                        'model' => 'Model: Perfusor Space',
                                        'icon' => 'bi-droplet-fill',
                                        'asset' => 'SP-15',
                                    ],
                                    [
                                        'name' => 'Infusion Pump',
                                        'model' => 'Model: B. Braun Infusomat',
                                        'icon' => 'bi-droplet-half',
                                        'asset' => 'IP-21',
                                    ],
                                ];
                            @endphp
                            @foreach ($equipment as $i => $eq)
                                <div class="icu-eq-row">
                                    <span class="icu-eq-icon"><i class="bi {{ $eq['icon'] }}"></i></span>
                                    <div class="icu-eq-meta">
                                        <div class="icu-eq-meta__name">{{ $eq['name'] }}</div>
                                        <div class="icu-eq-meta__model">{{ $eq['model'] }}</div>
                                    </div>
                                    <div class="icu-eq-status">Available</div>
                                    <select name="equipment[{{ $i }}]"
                                        class="icu-eq-asset form-select form-select-sm" style="width:auto;">
                                        <option value="{{ $eq['asset'] }}">{{ $eq['asset'] }}</option>
                                    </select>
                                </div>
                            @endforeach
                        </div> --}}

                        {{-- Emergency Override --}}
                        <div class="icu-adm-card icu-override-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">3.</span> EMERGENCY OVERRIDE</h6>
                            </div>
                            <div class="icu-override-head">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="override" value="0">
                                    <input class="form-check-input" type="checkbox" id="override" name="override"
                                        value="1" {{ old('override') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="override">Use Emergency Override</label>
                                </div>
                            </div>

                            <div id="override_block" class="row g-3 mt-1"
                                style="{{ old('override') ? '' : 'display:none;' }}">
                                <div class="col-md-12">
                                    <label class="form-label">Resource Issue <span class="text-danger">*</span></label>
                                    <select name="override_resource_issue" class="form-select">
                                        @foreach (['NoBed', 'NoVentilator', 'NoMonitor', 'NoIsolationBed', 'Other'] as $r)
                                            <option value="{{ $r }}"
                                                {{ old('override_resource_issue') === $r ? 'selected' : '' }}>
                                                {{ $r }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Approved By (User ID) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="override_approved_by"
                                        value="{{ old('override_approved_by') }}" class="form-control"
                                        placeholder="ICU Consultant user ID">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Temporary Bed (Optional)</label>
                                    <input type="number" name="override_temporary_bed_id"
                                        value="{{ old('override_temporary_bed_id') }}" class="form-control"
                                        placeholder="Bed ID">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Override Reason</label>
                                    <textarea name="override_reason" class="form-control" rows="2" placeholder="Why override needed">{{ old('override_reason') }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="override_remarks" class="form-control" rows="2"
                                        placeholder="Patient in severe respiratory distress. Requires ventilator support and close monitoring.">{{ old('override_remarks') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== ADMISSION SUMMARY (full width) ===== --}}
                    <div class="col-12">
                        <div class="icu-adm-card">
                            <div class="icu-adm-card__head">
                                <h6 class="icu-adm-card__title"><span class="num">4.</span> ADMISSION SUMMARY</h6>
                            </div>
                            <div class="icu-sum-grid">
                                <div>
                                    <label class="form-label">ICU Case ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                            value="ICU-{{ now()->format('Ymd') }}-0001" readonly>
                                        <span class="input-group-text bg-white"><i
                                                class="bi bi-arrow-clockwise text-muted"></i></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Admission Date & Time</label>
                                    <input type="text" class="form-control"
                                        value="{{ now()->format('d-M-Y h:i A') }}" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Assigned Bed</label>
                                    <input type="text" class="form-control" id="assigned_bed_display" value="—"
                                        readonly>
                                </div>
                                <div>
                                    <label class="form-label">Status</label>
                                    <div class="icu-sum-status mt-1"><span class="dot"></span> Ready to Admit</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer buttons --}}
                <div class="icu-adm-footer">
                    <a href="{{ route('icu.admissions.index') }}" class="btn btn-link text-secondary">Cancel</a>
                    <div class="d-flex gap-2">
                        {{-- <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                            Save as Draft
                        </button> --}}
                        <button type="submit" name="action" value="confirm" class="btn btn-primary">
                            <i class="bi bi-heart-pulse"></i> Confirm {{ $unitLabel }} Admission
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        (function() {
            /* ===== Clock ===== */
            function tick() {
                var d = new Date();
                var hh = d.getHours(),
                    mm = String(d.getMinutes()).padStart(2, '0'),
                    ss = String(d.getSeconds()).padStart(2, '0'),
                    ampm = hh >= 12 ? 'PM' : 'AM';
                hh = ((hh + 11) % 12 + 1);
                var t = String(hh).padStart(2, '0') + ':' + mm + ':' + ss + ' ' + ampm;
                var el = document.querySelector('#icu-adm-clock .pill-time');
                if (el) el.textContent = t;
            }
            setInterval(tick, 1000);
            tick();

            /* ===== Option card radio toggles ===== */
            document.querySelectorAll('.icu-opt-grid').forEach(function(grid) {
                grid.addEventListener('change', function(e) {
                    if (e.target.matches('input[type=radio]')) {
                        grid.querySelectorAll('.icu-opt-card').forEach(function(c) {
                            c.classList.remove('is-checked');
                        });
                        var card = e.target.closest('.icu-opt-card');
                        if (card) card.classList.add('is-checked');
                    }
                });
            });

            /* ===== Bed row toggle ===== */
            function wireBedRows() {
                document.querySelectorAll('#bed_list .icu-bed-row').forEach(function(row) {
                    row.addEventListener('change', function() {
                        document.querySelectorAll('#bed_list .icu-bed-row').forEach(function(r) {
                            r.classList.remove('is-checked');
                        });
                        row.classList.add('is-checked');
                        var name = row.querySelector('.icu-bed-row__name')?.textContent || '—';
                        var disp = document.getElementById('assigned_bed_display');
                        if (disp) disp.value = name;
                    });
                });
            }
            wireBedRows();

            /* ===== Override toggle ===== */
            var overrideToggle = document.getElementById('override');
            var overrideBlock = document.getElementById('override_block');
            overrideToggle.addEventListener('change', function() {
                overrideBlock.style.display = overrideToggle.checked ? '' : 'none';
            });

            /* ===== Ventilator label ===== */
            var vent = document.getElementById('vent_req');
            var mon = document.getElementById('mon_req');
            var ventLabel = document.getElementById('vent_state_label');
            var monLabel = document.getElementById('mon_state_label');

            function syncVent() {
                if (!ventLabel) return;
                ventLabel.textContent = vent.checked ? 'Yes' : 'No';
                ventLabel.className = vent.checked ? 'text-success small fw-bold' : 'text-muted small fw-bold';
            }
            vent.addEventListener('change', syncVent);
            syncVent();

            function syncMon() {
                if (!monLabel) return;
                monLabel.textContent = mon.checked ? 'Yes' : 'No';
                monLabel.className = mon.checked ? 'text-success small fw-bold' : 'text-muted small fw-bold';
            }
            mon.addEventListener('change', syncMon);
            syncMon();

            /* ===== Patient card binding ===== */
            var patientSel = document.getElementById('patient_id');
            var patientBody = document.getElementById('patient_card_body');

            function setField(name, value) {
                var el = patientBody.querySelector('[data-field="' + name + '"]');
                if (el) el.textContent = value || '-';
            }

            function calcAge(dob) {
                if (!dob) return '';
                var d = new Date(dob);
                if (isNaN(d.getTime())) return '';
                var today = new Date();
                var age = today.getFullYear() - d.getFullYear();
                var m = today.getMonth() - d.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
                return age;
            }

            function fmtDob(dob) {
                if (!dob) return '';
                var d = new Date(dob);
                if (isNaN(d.getTime())) return dob;
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return String(d.getDate()).padStart(2, '0') + '-' + months[d.getMonth()] + '-' + d.getFullYear();
            }

            function syncPatient() {
                var opt = patientSel.options[patientSel.selectedIndex];
                if (!opt || !opt.value) {
                    patientBody.style.display = 'none';
                    return;
                }
                patientBody.style.display = '';
                var name = opt.dataset.name || opt.textContent.trim();
                var mrn = opt.dataset.mrn || '';
                var gender = opt.dataset.gender || '';
                var dob = opt.dataset.dob || '';
                var blood = opt.dataset.blood || '';
                var mobile = opt.dataset.mobile || '';
                var allergy = opt.dataset.allergy || '';
                setField('name', name);
                setField('mrn', mrn);
                setField('agegender', (calcAge(dob) ? calcAge(dob) + ' Y' : '') + (gender ? ' / ' + (gender.charAt(0)
                    .toUpperCase()) : ''));
                setField('dob', fmtDob(dob));
                setField('blood', blood);
                setField('mobile', mobile);
                setField('allergy', allergy);
            }
            patientSel.addEventListener('change', syncPatient);
            syncPatient();

            /* ===== Patient search ===== */
            var search = document.getElementById('patient_search');
            if (search) {
                search.addEventListener('input', function() {
                    var q = search.value.toLowerCase();
                    var found = false;
                    for (var i = 0; i < patientSel.options.length; i++) {
                        var o = patientSel.options[i];
                        if (!o.value) continue;
                        var hay = (o.textContent + ' ' + (o.dataset.mrn || '') + ' ' + (o.dataset.mobile || ''))
                            .toLowerCase();
                        if (hay.indexOf(q) !== -1) {
                            patientSel.value = o.value;
                            found = true;
                            break;
                        }
                    }
                    if (found) syncPatient();
                });
            }

            /* ===== Fetch available beds ===== */
            var btn = document.getElementById('check_avail');
            btn.addEventListener('click', async function() {
                var icuType = document.querySelector('input[name=icu_type]:checked')?.value || '';
                var iso = document.getElementById('isolation_type').value;
                var needVent = document.getElementById('vent_req').checked ? 1 : 0;
                var needMon = document.getElementById('mon_req').checked ? 1 : 0;
                if (!icuType) {
                    alert('Pick an ICU type first.');
                    return;
                }

                var url = '{{ route('icu.beds.available') }}' +
                    '?icu_type=' + encodeURIComponent(icuType) +
                    '&isolation_type=' + encodeURIComponent(iso) +
                    '&ventilator_required=' + needVent +
                    '&monitoring_required=' + needMon;
                try {
                    var res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    var json = await res.json();
                    var list = document.getElementById('bed_list');
                    list.innerHTML = '';
                    (json.beds || []).forEach(function(b) {
                        var row = document.createElement('label');
                        row.className = 'icu-bed-row';
                        row.innerHTML =
                            '<input type="radio" name="bed_id" value="' + b.id +
                            '" class="visually-hidden">' +
                            '<span class="icu-bed-row__name">' + b.name + '</span>' +
                            '<span class="icu-bed-row__type">' + icuType + '</span>' +
                            '<span class="icu-bed-row__feat">' + (b.bed_type || '-') + '</span>' +
                            '<span class="icu-bed-row__feat">৳ ' + b.rent + '</span>' +
                            '<span class="icu-bed-row__avail">Available</span>';
                        list.appendChild(row);
                    });
                    document.getElementById('bed_count').textContent = (json.beds || []).length;
                    if (!(json.beds || []).length) {
                        list.innerHTML =
                            '<div class="text-muted small py-3 text-center">No beds match. Use Emergency Override below.</div>';
                    }
                    wireBedRows();
                } catch (e) {
                    alert('Could not fetch beds: ' + e.message);
                }
            });

            /* ===== Confirm bed = scroll to submit ===== */
            var confirmBtn = document.getElementById('confirm_bed_btn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    var checked = document.querySelector('input[name=bed_id]:checked');
                    if (!checked) {
                        alert('Select a bed first.');
                        return;
                    }
                    var disp = document.getElementById('assigned_bed_display');
                    var row = checked.closest('.icu-bed-row');
                    if (disp && row) disp.value = row.querySelector('.icu-bed-row__name').textContent;
                    document.querySelector('.icu-adm-footer').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                });
            }
        })();
    </script>
@endsection
