@extends('backend.layouts.master')
@section('title', 'View Operation - Ipd')

@push('styles')
    <style>
        .op-page {
            background: #f0f0f0;
            min-height: 100vh;
            padding: 24px 0;
        }

        /* ── Header ── */
        .op-header {
            background: #213f5c;
            color: #fff;
            border-radius: 12px;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .op-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .op-logo {
            width: 60px;
            height: 60px;
            background: #f5c518;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #2d2d2d;
        }

        .op-hospital-name {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            margin: 0;
        }

        .op-hospital-sub {
            font-size: 13px;
            color: #ccc;
            margin: 2px 0 0;
        }

        .op-status-area {
            text-align: right;
        }

        /* ── Patient Info Bar ── */
        .op-patient-bar {
            background: #e8e8e8;
            border-radius: 10px;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .op-patient-bar .op-field label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: .5px;
        }

        .op-patient-bar .op-field p {
            font-size: 15px;
            font-weight: 600;
            margin: 2px 0 0;
        }

        /* ── Cards ── */
        .op-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            border-left: 4px solid #0d6efd;
            margin-top: 16px;
        }

        .op-card.op-card-warning {
            border-left-color: #f5c518;
        }

        .op-card.op-card-success {
            border-left-color: #198754;
        }

        .op-card.op-card-info {
            border-left-color: #0dcaf0;
        }

        .op-card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .op-card-title i {
            font-size: 20px;
            color: #666;
        }

        /* ── Detail Row ── */
        .op-detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .op-detail-row .op-label {
            font-weight: 600;
            color: #555;
            min-width: 160px;
        }

        .op-detail-row .op-value {
            text-align: right;
            color: #222;
        }

        /* ── Checklist ── */
        .op-checklist {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .op-check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            min-width: 140px;
        }

        /* ── Footer ── */
        .op-footer {
            display: flex;
            justify-content: end;
            gap: 16px;
            margin-top: 28px;
            padding-bottom: 20px;
        }

        .op-badge {
            display: inline-block;
            font-weight: 700;
            font-size: 14px;
            padding: 4px 14px;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
    <div class="op-page">
        <div class="container">

            {{-- Back button --}}
            <div class="mb-2">
                <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            {{-- ══════════ HEADER ══════════ --}}
            <div class="op-header">
                <div class="op-header-left">
                    <div class="op-logo"><i class="bi bi-hospital"></i></div>
                    <div>
                        <p class="op-hospital-name">{{ setting('company_name') }}</p>
                        <p class="op-hospital-sub">{{ setting('company_address') }} &bull; {{ setting('company_phone') }}</p>
                    </div>
                </div>
                <div class="op-status-area">
                    <p class="mb-1 text-white-50 small text-uppercase">Operation Status</p>
                    @if ($operation->status === 'Completed')
                        <span class="op-badge bg-success text-white">{{ $operation->status }}</span>
                    @elseif($operation->status === 'In Progress')
                        <span class="op-badge bg-warning text-dark">{{ $operation->status }}</span>
                    @elseif($operation->status === 'Cancelled')
                        <span class="op-badge bg-danger text-white">{{ $operation->status }}</span>
                    @else
                        <span class="op-badge bg-info text-white">{{ $operation->status }}</span>
                    @endif
                </div>
            </div>

            {{-- ══════════ PATIENT INFO BAR ══════════ --}}
            <div class="op-patient-bar">
                <div class="op-field">
                    <label>Ipd No</label>
                    <p>{{ $ipdPatient->ipd_no ?? '-' }}</p>
                </div>
                <div class="op-field">
                    <label>Patient Name</label>
                    <p>{{ $ipdPatient->patient->patient_name ?? '-' }}</p>
                </div>
                <div class="op-field">
                    <label>Age / Gender</label>
                    <p>
                        @if ($ipdPatient->patient->dob)
                            {{ calculateAgeFromDob($ipdPatient->patient->dob) }}
                        @else
                            N/A
                        @endif
                        / {{ ucfirst($ipdPatient->patient->gender ?? '-') }}
                    </p>
                </div>
                <div class="op-field">
                    <label>Contact</label>
                    <p>{{ $ipdPatient->patient->mobileno ?? '-' }}</p>
                </div>
                <div class="op-field">
                    <label>Operation Date</label>
                    <p>{{ $operation->date ? $operation->date->format('d M, Y') : 'N/A' }}</p>
                </div>
                <div class="op-field">
                    <label>Case ID</label>
                    <p>{{ $operation->case_id ?? '-' }}</p>
                </div>
            </div>

            {{-- ══════════ MAIN CONTENT ══════════ --}}
            <div class="row mt-0">

                {{-- LEFT COLUMN --}}
                <div class="col-lg-6">

                    {{-- Operation Details --}}
                    <div class="op-card">
                        <div class="op-card-title"><i class="bi bi-clipboard2-pulse"></i> Operation Details</div>

                        <div class="op-detail-row">
                            <span class="op-label">Operation Type</span>
                            <span class="op-value">{{ $operation->operationType->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Operation</span>
                            <span class="op-value">{{ $operation->operation->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Procedure</span>
                            <span class="op-value">{{ $operation->operationProcedure->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Operation Theatre</span>
                            <span class="op-value">{{ $operation->operationTheatre->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">OT Technician</span>
                            <span class="op-value">{{ $operation->ot_technician ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Schedule & Timing --}}
                    <div class="op-card op-card-warning">
                        <div class="op-card-title"><i class="bi bi-clock-history"></i> Schedule & Timing</div>

                        <div class="op-detail-row">
                            <span class="op-label">Date</span>
                            <span class="op-value">{{ $operation->date ? $operation->date->format('d M, Y') : '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Start Time</span>
                            <span class="op-value">{{ $operation->start_datetime ? $operation->start_datetime->format('d M, Y h:i A') : '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">End Time</span>
                            <span class="op-value">{{ $operation->end_datetime ? $operation->end_datetime->format('d M, Y h:i A') : '-' }}</span>
                        </div>
                        @if ($operation->start_datetime && $operation->end_datetime)
                            <div class="op-detail-row">
                                <span class="op-label">Duration</span>
                                <span class="op-value">
                                    {{ $operation->start_datetime->diff($operation->end_datetime)->format('%Hh %Im') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Pre-Op Checklist --}}
                    <div class="op-card op-card-info">
                        <div class="op-card-title"><i class="bi bi-check2-square"></i> Pre-Op Checklist</div>

                        <div class="op-checklist">
                            <div class="op-check-item">
                                @if ($operation->pre_op)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                @endif
                                <span>Pre-Op</span>
                            </div>
                            <div class="op-check-item">
                                @if ($operation->vitals)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                @endif
                                <span>Vitals</span>
                            </div>
                            <div class="op-check-item">
                                @if ($operation->consent)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                @endif
                                <span>Consent</span>
                            </div>
                            <div class="op-check-item">
                                @if ($operation->equipment)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                @endif
                                <span>Equipment</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-6">

                    {{-- Surgical Team --}}
                    <div class="op-card op-card-success">
                        <div class="op-card-title"><i class="bi bi-people-fill"></i> Surgical Team</div>

                        <div class="op-detail-row">
                            <span class="op-label">Assign Doctor</span>
                            <span class="op-value">{{ $operation->assignDoctor->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Assistant Doctor</span>
                            <span class="op-value">{{ $operation->assistantDoctor->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Main Surgeon</span>
                            <span class="op-value fw-bold">{{ $operation->mainSurgeon->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">Anesthesiologist</span>
                            <span class="op-value">{{ $operation->anesthesiologist->name ?? '-' }}</span>
                        </div>
                        <div class="op-detail-row">
                            <span class="op-label">OT Technician</span>
                            <span class="op-value">{{ $operation->ot_technician ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Diagnosis --}}
                    <div class="op-card">
                        <div class="op-card-title"><i class="bi bi-journal-medical"></i> Diagnosis</div>
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 14px 18px; min-height: 60px; font-size: 14px;">
                            {{ $operation->diagnosis ?? 'No diagnosis recorded.' }}
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div class="op-card op-card-warning">
                        <div class="op-card-title"><i class="bi bi-chat-square-text"></i> Remarks</div>
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 14px 18px; min-height: 60px; font-size: 14px;">
                            {{ $operation->remarks ?? 'No remarks.' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════ FOOTER BUTTONS ══════════ --}}
            <div class="op-footer">
                <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-left"></i> Back to Patient
                </a>
            </div>
        </div>
    </div>
@endsection
