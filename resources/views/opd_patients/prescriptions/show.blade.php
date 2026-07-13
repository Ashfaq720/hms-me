@extends('backend.layouts.master')
@section('title', 'View Prescription - Ipd')

@push('styles')
    <style>
        .rx-page {
            background: #f0f0f0;
            min-height: 100vh;
            padding: 24px 0;
        }

        /* ── Header ── */
        .rx-header {
            background: #213f5c;
            color: #fff;
            border-radius: 12px;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rx-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .rx-logo {
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

        .rx-hospital-name {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            margin: 0;
        }

        .rx-hospital-sub {
            font-size: 13px;
            color: #ccc;
            margin: 2px 0 0;
        }

        .rx-doctor-info {
            text-align: right;
        }

        .rx-doctor-name {
            font-size: 16px;
            color: #fff;
            font-weight: 700;
        }

        .rx-doctor-desg {
            font-size: 12px;
            color: #ccc;
        }

        /* ── Patient Info Bar ── */
        .rx-patient-bar {
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

        .rx-patient-bar .rx-field label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: .5px;
        }

        .rx-patient-bar .rx-field p {
            font-size: 15px;
            font-weight: 600;
            margin: 2px 0 0;
        }

        /* ── Cards ── */
        .rx-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            border-left: 4px solid #f5c518;
            margin-top: 16px;
        }

        .rx-card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rx-card-title i {
            font-size: 20px;
            color: #666;
        }

        /* ── Symptom / Test Items ── */
        .rx-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── Test items ── */
        .rx-test-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rx-test-item label {
            font-size: 14px;
            margin: 0;
        }

        /* ── Rx Notes ── */
        .rx-notes-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            margin-top: 16px;
            position: relative;
        }

        .rx-notes-card .rx-watermark {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 64px;
            color: #f0f0f0;
        }

        .rx-notes-title {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .rx-notes-title i {
            color: #666;
        }

        /* ── Tx Medicine List ── */
        .rx-tx-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 16px 20px;
            min-height: 100px;
        }

        .rx-medicine-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .rx-medicine-row:last-child {
            border-bottom: none;
        }

        .rx-medicine-num {
            font-weight: 700;
            color: #666;
            min-width: 30px;
        }

        /* ── Footer Buttons ── */
        .rx-footer {
            display: flex;
            justify-content: end;
            gap: 16px;
            margin-top: 28px;
            padding-bottom: 20px;
        }

        .rx-badge {
            display: inline-block;
            background: #f5c518;
            color: #333;
            font-weight: 700;
            font-size: 12px;
            padding: 2px 10px;
            border-radius: 6px;
        }
    </style>
@endpush

@section('content')
    <div class="rx-page">
        <div class="container">

            {{-- Back button --}}
            <div class="mb-2">
                <a href="{{ route('opd-patients.show', $opdPatient->id) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            {{-- ══════════ HEADER ══════════ --}}
            <div class="rx-header">
                <div class="rx-header-left">
                    <div class="rx-logo"><i class="bi bi-hospital"></i></div>
                    <div>
                        <p class="rx-hospital-name">{{ setting('company_name') }}</p>
                        @if ($prescription->doctor && $prescription->doctor->department)
                            <p class="rx-hospital-sub">{{ $prescription->doctor->department->name ?? '' }}</p>
                        @endif
                        <p class="rx-hospital-sub">{{ setting('company_address') }} &bull;
                            {{ setting('company_phone') }}</p>
                    </div>
                </div>
                <div class="rx-doctor-info">
                    @if ($prescription->doctor)
                        <p class="rx-doctor-name">{{ $prescription->doctor->name }}</p>
                        <p class="rx-doctor-desg">{{ $prescription->doctor?->designation?->name ?? '' }}</p>
                        <p class="rx-doctor-desg">Reg. No: {{ $prescription->doctor?->registration_no ?? '-' }}</p>
                    @else
                        <p class="rx-doctor-name">N/A</p>
                    @endif
                </div>
            </div>

            {{-- ══════════ PATIENT INFO BAR ══════════ --}}
            <div class="rx-patient-bar">
                <div class="rx-field">
                    <label>Ipd No</label>
                    <p>{{ $opdPatient->case_id ?? '-' }}</p>
                </div>
                <div class="rx-field">
                    <label>Pt Name</label>
                    <p>{{ $opdPatient->patient->patient_name ?? '-' }}</p>
                </div>
                <div class="rx-field">
                    <label>Age / Gender</label>
                    <p>
                        @if ($opdPatient->patient->dob)
                            {{ calculateAgeFromDob($opdPatient->patient?->dob) ?? '' }}
                        @else
                            N/A
                        @endif
                        / {{ ucfirst($opdPatient->patient->gender ?? '-') }}
                    </p>
                </div>
                <div class="rx-field">
                    <label>Contact</label>
                    <p>{{ $opdPatient->patient->mobileno ?? '-' }}</p>
                </div>
                <div class="rx-field">
                    <label>Date</label>
                    <p>{{ $prescription->date ? $prescription->date->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div class="rx-field">
                    <label>Prescription No</label>
                    <p><span class="rx-badge">{{ $prescription->prescription_no }}</span></p>
                </div>
            </div>

            {{-- ══════════ MAIN CONTENT (2 columns) ══════════ --}}
            <div class="row mt-0">

                {{-- LEFT COLUMN --}}
                <div class="col-lg-5">
                    {{-- Symptoms Card --}}
                    <div class="rx-card">
                        <div class="rx-card-title"><i class="bi bi-virus"></i> Symptoms</div>
                        @if ($prescription->symptoms->count())
                            @foreach ($prescription->symptoms as $ps)
                                <div class="rx-item">
                                    <span>{{ $ps->symptom->name ?? 'N/A' }}</span>
                                    @if ($ps->note)
                                        <small class="text-muted">{{ $ps->note }}</small>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small">No symptoms recorded.</p>
                        @endif
                    </div>

                    {{-- Lab Investigations Card --}}
                    <div class="rx-card">
                        <div class="rx-card-title"><i class="bi bi-clipboard2-pulse"></i> Lab Investigations</div>
                        @if ($prescription->labInvestigations->count())
                            @foreach ($prescription->labInvestigations as $pl)
                                <div class="rx-test-item" style="flex-wrap: wrap;">
                                    <label>{{ $pl->labInvestigation->name ?? 'N/A' }}</label>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    @if ($pl->note)
                                        <small class="text-muted" style="width: 100%; margin-top: 4px;">Note: {{ $pl->note }}</small>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small">No lab investigations recorded.</p>
                        @endif
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-7">
                    {{-- Rx Diagnosis & Clinical Notes --}}
                    <div class="rx-notes-card">
                        <span class="rx-watermark"><i class="bi bi-clipboard2-check"></i></span>
                        <div class="rx-notes-title"><i class="bi bi-journal-medical"></i> Rx Diagnosis &amp; Clinical Notes</div>
                        <p style="font-size: 15px; font-weight: 600; min-height: 40px;">{{ $prescription->findings ?? 'N/A' }}</p>

                        <hr class="my-3">

                        <div class="rx-notes-title"><i class="bi bi-journal-medical"></i> Tx</div>
                        <div class="rx-tx-area">
                            @if ($prescription->medicines->count())
                                @foreach ($prescription->medicines as $pm)
                                    @php
                                        $display = $pm->medicine->medicine_name;
                                        if ($pm->dosage && $pm->dosage !== '0 + 0 + 0 + 0') {
                                            $display .= ' --- [ ' . str_replace('+', '-', $pm->dosage) . ' ]';
                                        }
                                        if ($pm->frequency) $display .= ' --- ' . $pm->frequency;
                                        if ($pm->duration) $display .= ' --- ' . $pm->duration;
                                        if ($pm->note) $display .= ' --- ' . $pm->note;
                                    @endphp
                                    <div class="rx-medicine-row">
                                        <span class="rx-medicine-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}.</span>
                                        <span>{{ $display }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted small">No medicines recorded.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Advice / Next Visit --}}
                    <div class="rx-notes-card">
                        <div class="rx-notes-title"><i class="bi bi-chat-square-text"></i> Advice</div>
                        <p style="font-size: 14px; min-height: 30px;">{{ $prescription->advice ?? 'N/A' }}</p>
                        <hr class="my-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold" style="white-space: nowrap;">Next Visit:</span>
                            <span>{{ $prescription->next_visit ? $prescription->next_visit->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════ FOOTER BUTTONS ══════════ --}}
            <div class="rx-footer">
                <a href="{{ route('opd-patients.show', $opdPatient->id) }}" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-left"></i> Back to Patient
                </a>
                <a href="{{ route('opd-patients.prescriptions.pdf', [$opdPatient->id, $prescription->id]) }}" target="_blank" class="btn btn-warning px-4 fw-bold">
                    <i class="bi bi-printer"></i> Print / Download PDF
                </a>
            </div>
        </div>
    </div>
@endsection
