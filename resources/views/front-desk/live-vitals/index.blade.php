@extends('backend.layouts.master')

@section('title', 'Live Vitals Room')

@push('styles')
<style>
    .live-dot {
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-dot 1.4s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.5); opacity: .6; }
    }

    .stat-card {
        border-left: 4px solid transparent;
        transition: transform .15s ease;
    }

    .stat-card:hover { transform: translateY(-2px); }
    .stat-card-waiting  { border-left-color: #ffc107; }
    .stat-card-done     { border-left-color: #28a745; }
    .stat-card-billed   { border-left-color: #0d6efd; }
    .stat-card-total    { border-left-color: #6c757d; }

    .vital-badge {
        font-size: .72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .queue-row-priority { background: rgba(255, 193, 7, .06) !important; }
    .queue-row-done     { opacity: .6; }

    .scan-input-wrap { position: relative; }
    .scan-input-wrap .scan-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.1rem;
    }
    .scan-input-wrap input { padding-left: 2.4rem; }

    /* Machine fetch indicator */
    .machine-badge {
        font-size: .65rem;
        background: #e8f4ff;
        color: #0d6efd;
        border: 1px solid #b6d7ff;
        border-radius: 20px;
        padding: 2px 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .patient-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
    }
    .avatar-opd  { background: #dbeafe; color: #1d4ed8; }
    .avatar-ipd  { background: #dcfce7; color: #15803d; }
    .avatar-er   { background: #fee2e2; color: #dc2626; }

    /* Vitals input groups */
    #vitalsModal .input-group-text {
        font-size: .75rem;
        min-width: 46px;
        justify-content: center;
        color: #6c757d;
    }

    .refresh-btn { transition: transform .3s; }
    .refresh-btn.spinning { transform: rotate(360deg); }

    .wait-time-chip {
        font-size: .72rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2px 8px;
        color: #64748b;
    }

    /* Scan result card */
    #scanResult .patient-result-card {
        border: 1.5px solid #0d6efd;
        border-radius: 12px;
        padding: 16px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Page Header ──────────────────────────────────────────── --}}
    <div class="d-flex align-items-start align-items-md-center justify-content-between gap-2 flex-wrap flex-md-nowrap mb-4">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1 class="app-page-title mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse text-danger"></i>
                    Live Vitals Room
                    <span class="live-dot ms-1" title="Live queue"></span>
                </h1>
                <div class="text-muted small mt-1">
                    {{ now()->format('l, d F Y') }}
                    &bull; Last refreshed: <span id="lastRefreshed">{{ now()->format('h:i:s A') }}</span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap justify-content-md-end">
            <button class="btn btn-outline-secondary btn-sm refresh-btn" id="refreshBtn" title="Refresh queue">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#scanModal">
                <i class="bi bi-qr-code-scan me-1"></i>Scan Health Card
            </button>
            <button class="btn btn-primary btn-sm" id="manualEntryBtn"
                data-bs-toggle="modal" data-bs-target="#vitalsModal">
                <i class="bi bi-plus-lg me-1"></i>Manual Entry
            </button>
        </div>
    </div>

    {{-- ── Stats Row ────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card stat-card-waiting h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning-subtle p-3">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="display-6 fw-bold lh-1">{{ $stats['waiting'] }}</div>
                        <div class="text-muted small mt-1">Waiting for Vitals</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card stat-card-done h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle p-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="display-6 fw-bold lh-1">{{ $stats['done'] }}</div>
                        <div class="text-muted small mt-1">Vitals Done Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card stat-card-billed h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle p-3">
                        <i class="bi bi-receipt-cutoff text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="display-6 fw-bold lh-1">{{ $stats['billed'] }}</div>
                        <div class="text-muted small mt-1">Billing Cleared</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm stat-card stat-card-total h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-secondary-subtle p-3">
                        <i class="bi bi-people text-secondary fs-4"></i>
                    </div>
                    <div>
                        <div class="display-6 fw-bold lh-1">{{ $stats['total'] }}</div>
                        <div class="text-muted small mt-1">Total Registered</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Queue Table ──────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">
                        <i class="bi bi-heart-pulse"></i>
                    </span>
                    <div class="fw-semibold">Today's Vitals Queue</div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="input-group input-group-sm" style="max-width:240px">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="queueSearch" class="form-control" placeholder="Filter by name / MRN…">
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm filter-btn active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-warning btn-sm filter-btn" data-filter="pending">Pending</button>
                        <button type="button" class="btn btn-outline-success btn-sm filter-btn" data-filter="done">Done</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="queueTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px">#</th>
                            <th style="min-width:120px">Patient</th>
                            <th>MRN</th>
                            <th>Token</th>
                            <th>Type</th>
                            <th>Doctor</th>
                            <th>Billing</th>
                            <th>Wait</th>
                            <th>Vitals</th>
                            <th class="text-end" style="min-width:130px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($queue as $i => $row)
                            @php
                                $typeColor = match($row->source_type) {
                                    'OPD' => 'primary',
                                    'IPD' => 'success',
                                    'ER'  => 'danger',
                                    default => 'secondary',
                                };
                                $avatarClass = 'avatar-' . strtolower($row->source_type);
                                $initial = strtoupper(substr($row->patient?->patient_name ?? '?', 0, 1));
                            @endphp
                            <tr class="queue-row {{ !$row->vitals_done ? 'queue-row-priority' : 'queue-row-done' }}"
                                data-name="{{ strtolower($row->patient?->patient_name ?? '') }}"
                                data-mrn="{{ strtolower($row->patient?->mrn ?? '') }}"
                                data-status="{{ $row->vitals_done ? 'done' : 'pending' }}">

                                <td class="text-muted">{{ $i + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="patient-avatar {{ $avatarClass }}">{{ $initial }}</div>
                                        <div>
                                            <div class="fw-semibold lh-1">{{ $row->patient?->patient_name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size:.72rem">{{ $row->patient?->gender ?? '' }}
                                                @if($row->patient?->mobileno)
                                                    &bull; {{ $row->patient->mobileno }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="font-monospace text-muted" style="font-size:.8rem">
                                    {{ $row->patient?->mrn ?? '-' }}
                                </td>

                                <td class="fw-semibold">{{ $row->token }}</td>

                                <td>
                                    <span class="vital-badge text-bg-{{ $typeColor }}-subtle border border-{{ $typeColor }}-subtle text-{{ $typeColor }}">
                                        {{ $row->source_type }}
                                    </span>
                                </td>

                                <td class="text-muted" style="font-size:.8rem;max-width:120px">
                                    <span class="text-truncate d-block">{{ $row->doctor?->name ?? '-' }}</span>
                                </td>

                                <td>
                                    @if ($row->billing_ok)
                                        <span class="vital-badge bg-success-subtle border border-success-subtle text-success">
                                            <i class="bi bi-check-lg"></i> Cleared
                                        </span>
                                    @else
                                        <span class="vital-badge bg-warning-subtle border border-warning-subtle text-warning">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="wait-time-chip">
                                        {{ $row->registered_at
                                            ? \Carbon\Carbon::parse($row->registered_at)->diffForHumans(null, true)
                                            : '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if ($row->vitals_done)
                                        <span class="vital-badge bg-success-subtle border border-success-subtle text-success">
                                            <i class="bi bi-check-circle-fill"></i> Done
                                        </span>
                                    @else
                                        <span class="vital-badge bg-warning-subtle border border-warning-subtle text-warning">
                                            <i class="bi bi-hourglass-split"></i> Waiting
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    @if (! $row->vitals_done)
                                        <button type="button"
                                            class="btn btn-primary btn-sm btn-take-vitals"
                                            data-patient-id="{{ $row->patient_id }}"
                                            data-patient-name="{{ $row->patient?->patient_name }}"
                                            data-patient-mrn="{{ $row->patient?->mrn }}"
                                            data-patient-gender="{{ $row->patient?->gender }}"
                                            data-patient-type="{{ $row->source_type }}"
                                            data-source-id="{{ $row->source_id }}"
                                            data-token="{{ $row->token }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#vitalsModal">
                                            <i class="bi bi-activity me-1"></i>Take Vitals
                                        </button>
                                    @else
                                        <span class="text-success small">
                                            <i class="bi bi-check-circle-fill me-1"></i>Recorded
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-40"></i>
                                    <div class="fw-semibold">No patients registered today</div>
                                    <div class="small mt-1">Patients appear here after front desk registration</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Health Card Scan Modal                                     --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scanModalLabel">
                    <i class="bi bi-qr-code-scan me-2 text-primary"></i>Scan / Search Patient
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Health Card No / MRN / Name / Phone</label>
                <div class="scan-input-wrap mb-1">
                    <i class="bi bi-credit-card scan-icon"></i>
                    <input type="text" id="scanInput" class="form-control form-control-lg font-monospace"
                        placeholder="Scan barcode or type here…" autocomplete="off">
                </div>
                <div class="form-text mb-3">
                    Accepts barcode scan (HC-YYYY-NNNNN), MRN, name, or phone number.
                </div>

                <div id="scanSpinner" class="text-center py-3 d-none">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <span class="text-muted">Looking up patient…</span>
                </div>

                <div id="scanResult"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Vitals Entry Modal                                         --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="vitalsModal" tabindex="-1" aria-labelledby="vitalsModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vitalsModalLabel">
                    <i class="bi bi-activity me-2 text-primary"></i>Record Vital Signs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('front_desk.live-vitals.store') }}" method="POST" id="vitalsForm">
                @csrf

                {{-- Hidden context fields --}}
                <input type="hidden" name="patient_id"        id="vPatientId">
                <input type="hidden" name="patient_type"      id="vPatientType" value="OPD">
                <input type="hidden" name="source_id"         id="vSourceId">
                <input type="hidden" name="machine_fetched"   id="vMachineFetched" value="0">
                <input type="hidden" name="machine_device_id" id="vMachineDeviceId" value="">

                <div class="modal-body">

                    {{-- Patient Banner --}}
                    <div id="patientBanner" class="alert alert-primary border-primary border-opacity-25 mb-4 d-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;flex-shrink:0">
                                <i class="bi bi-person fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold fs-6" id="bannerName">—</div>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-1" style="font-size:.8rem;color:#555">
                                    <span>MRN: <strong id="bannerMrn">—</strong></span>
                                    <span>&bull;</span>
                                    <span id="bannerTypeBadge" class="badge rounded-pill bg-primary">OPD</span>
                                    <span>&bull; Token: <strong id="bannerToken">—</strong></span>
                                    <span>&bull; HC: <strong id="bannerHC">—</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Machine Fetch Row --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3 border">
                        <i class="bi bi-cpu text-primary fs-4"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">Auto-Fetch from Device</div>
                            <div class="text-muted" style="font-size:.75rem">Pulls vitals directly from connected measurement machine</div>
                        </div>
                        <div id="machineFetchStatus" class="text-muted small me-2"></div>
                        <button type="button" id="fetchMachineBtn" class="btn btn-outline-info btn-sm flex-shrink-0">
                            <i class="bi bi-cloud-download me-1"></i>Fetch from Machine
                        </button>
                    </div>

                    {{-- Vitals Grid --}}
                    <div class="row g-3">

                        {{-- Row 1: Key vitals --}}
                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Blood Pressure</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-heart-pulse"></i></span>
                                <input type="text" name="blood_pressure" id="vBP"
                                    class="form-control" placeholder="120/80">
                                <span class="input-group-text">mmHg</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Temperature</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-thermometer-half"></i></span>
                                <input type="number" step="0.1" name="temperature" id="vTemp"
                                    class="form-control" placeholder="37.0">
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Heart Rate</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-activity"></i></span>
                                <input type="number" name="heart_rate" id="vHR"
                                    class="form-control" placeholder="72">
                                <span class="input-group-text">bpm</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">SpO2</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-droplet-half"></i></span>
                                <input type="number" name="spo2" id="vSpo2"
                                    class="form-control" placeholder="99" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Respiratory Rate</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-wind"></i></span>
                                <input type="number" name="respiratory_rate" id="vRR"
                                    class="form-control" placeholder="16">
                                <span class="input-group-text">/min</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Weight</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                                <input type="number" step="0.1" name="weight" id="vWeight"
                                    class="form-control" placeholder="65.0">
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Height</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                                <input type="number" step="0.1" name="height" id="vHeight"
                                    class="form-control" placeholder="170">
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-3">
                            <label class="form-label fw-semibold small">Gender</label>
                            <select name="gender" id="vGender" class="form-select form-select-sm">
                                <option value="">—</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-4 col-xl-2">
                            <label class="form-label fw-semibold small">Age</label>
                            <input type="number" name="age" id="vAge"
                                class="form-control form-control-sm" placeholder="Age">
                        </div>

                        <div class="col-6 col-md-4 col-xl-2">
                            <label class="form-label fw-semibold small">Token No</label>
                            <input type="text" name="patient_token" id="vToken"
                                class="form-control form-control-sm" placeholder="Token">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Remarks / Notes</label>
                            <textarea name="remarks" class="form-control form-control-sm"
                                rows="2" placeholder="Any additional notes…"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer justify-content-between">
                    <div id="machineFetchedFlag" class="d-none">
                        <span class="machine-badge">
                            <i class="bi bi-cpu-fill"></i> Machine-fetched data
                        </span>
                    </div>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="saveVitalsBtn">
                            <i class="bi bi-save me-1"></i>Save Vitals
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const LOOKUP_URL  = @json(route('front_desk.live-vitals.patient-lookup'));
    const MACHINE_URL = @json(route('front_desk.live-vitals.fetch-machine'));
    const typeColors  = { OPD: 'primary', IPD: 'success', ER: 'danger' };

    // ── Populate vitals modal ──────────────────────────────────────
    function populateVitalsModal(pid, name, mrn, hc, gender, type, sourceId, token) {
        document.getElementById('vPatientId').value   = pid   || '';
        document.getElementById('vPatientType').value = type  || 'OPD';
        document.getElementById('vSourceId').value    = sourceId || '';
        document.getElementById('vMachineFetched').value   = '0';
        document.getElementById('vMachineDeviceId').value  = '';

        // Banner
        const banner = document.getElementById('patientBanner');
        banner.classList.remove('d-none');
        document.getElementById('bannerName').textContent  = name  || '—';
        document.getElementById('bannerMrn').textContent   = mrn   || '—';
        document.getElementById('bannerHC').textContent    = hc    || '—';
        document.getElementById('bannerToken').textContent = token || '—';

        const badge = document.getElementById('bannerTypeBadge');
        badge.textContent = type || 'OPD';
        badge.className   = 'badge rounded-pill bg-' + (typeColors[type] || 'secondary');

        // Pre-fill gender
        const gSel = document.getElementById('vGender');
        if (gSel && gender) gSel.value = gender;

        // Token (don't show 'ER' literal as token)
        document.getElementById('vToken').value = (token && token !== 'ER') ? token : '';

        // Reset vitals fields & machine state
        ['vBP','vTemp','vHR','vSpo2','vRR','vWeight','vHeight','vAge'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        document.getElementById('machineFetchStatus').innerHTML    = '';
        document.getElementById('machineFetchedFlag').classList.add('d-none');
    }

    // ── "Take Vitals" buttons in queue ────────────────────────────
    document.querySelectorAll('.btn-take-vitals').forEach(function (btn) {
        btn.addEventListener('click', function () {
            populateVitalsModal(
                this.dataset.patientId,
                this.dataset.patientName,
                this.dataset.patientMrn,
                '',                          // HC not in data-attr, blank
                this.dataset.patientGender,
                this.dataset.patientType,
                this.dataset.sourceId,
                this.dataset.token
            );
        });
    });

    // ── Manual Entry button clears the modal ──────────────────────
    document.getElementById('manualEntryBtn').addEventListener('click', function () {
        populateVitalsModal(null, null, null, null, null, 'OPD', null, null);
        document.getElementById('patientBanner').classList.add('d-none');
        document.getElementById('vPatientId').value = '';
    });

    // ── Health Card Scan ──────────────────────────────────────────
    const scanInput   = document.getElementById('scanInput');
    const scanSpinner = document.getElementById('scanSpinner');
    const scanResult  = document.getElementById('scanResult');

    function doScan() {
        const q = scanInput.value.trim();
        if (!q) return;

        scanSpinner.classList.remove('d-none');
        scanResult.innerHTML = '';

        fetch(LOOKUP_URL + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(function (data) {
                scanSpinner.classList.add('d-none');

                if (!data.ok) {
                    scanResult.innerHTML =
                        '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle me-2"></i>'
                        + (data.message || 'Patient not found') + '</div>';
                    return;
                }

                const p     = data.patient;
                const color = typeColors[data.source_type] || 'secondary';

                const vitalsWarning = data.vitals_done
                    ? '<div class="alert alert-warning py-2 mb-0 mt-2 small"><i class="bi bi-exclamation-triangle me-1"></i>Vitals already recorded today for this patient.</div>'
                    : '';

                scanResult.innerHTML = `
                    <div class="patient-result-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-${color}-subtle d-flex align-items-center justify-content-center"
                                style="width:48px;height:48px;flex-shrink:0">
                                <span class="fw-bold text-${color}">${(p.name||'?').charAt(0).toUpperCase()}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6">${p.name}</div>
                                <div class="text-muted small">
                                    MRN: <strong>${p.mrn}</strong> &bull;
                                    HC: <strong>${p.health_card_no}</strong> &bull;
                                    ${p.gender || ''} &bull; ${p.mobileno || ''}
                                </div>
                                <div class="mt-1">
                                    <span class="badge rounded-pill bg-${color}-subtle text-${color} border border-${color}-subtle">${data.source_type || 'Unknown'}</span>
                                    ${data.token ? `<span class="badge rounded-pill bg-secondary ms-1">Token: ${data.token}</span>` : ''}
                                    ${p.blood_group ? `<span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle ms-1">${p.blood_group}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        ${vitalsWarning}
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary w-100" id="scanTakeVitalsBtn">
                                <i class="bi bi-activity me-2"></i>Take Vitals Now
                            </button>
                        </div>
                    </div>
                `;

                document.getElementById('scanTakeVitalsBtn').addEventListener('click', function () {
                    populateVitalsModal(
                        p.id, p.name, p.mrn, p.health_card_no,
                        p.gender, data.source_type, data.source_id, data.token
                    );
                    bootstrap.Modal.getInstance(document.getElementById('scanModal')).hide();
                    new bootstrap.Modal(document.getElementById('vitalsModal')).show();
                });
            })
            .catch(function () {
                scanSpinner.classList.add('d-none');
                scanResult.innerHTML =
                    '<div class="alert alert-danger mb-0">Network error. Please try again.</div>';
            });
    }

    document.getElementById('scanModal')
        .addEventListener('shown.bs.modal', () => scanInput.focus());

    document.getElementById('scanModal')
        .addEventListener('hidden.bs.modal', function () {
            scanInput.value  = '';
            scanResult.innerHTML = '';
            scanSpinner.classList.add('d-none');
        });

    scanInput.addEventListener('keydown', e => { if (e.key === 'Enter') doScan(); });
    scanInput.addEventListener('input', function () {
        // Auto-search when input looks like a barcode scan (≥8 chars entered fast)
        if (this.value.length >= 8) {
            clearTimeout(this._scanTimer);
            this._scanTimer = setTimeout(doScan, 350);
        }
    });

    // Explicit search button (dynamically created — event delegation)
    document.getElementById('scanModal').addEventListener('click', function (e) {
        if (e.target && e.target.id === 'scanSearchBtn') doScan();
    });

    // ── Fetch from Machine ────────────────────────────────────────
    document.getElementById('fetchMachineBtn').addEventListener('click', function () {
        const btn      = this;
        const statusEl = document.getElementById('machineFetchStatus');
        const flagEl   = document.getElementById('machineFetchedFlag');

        btn.disabled = true;
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-info me-1"></span>Connecting…';

        fetch(MACHINE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ device_id: 'DEVICE-01' }),
        })
        .then(r => r.json())
        .then(function (data) {
            btn.disabled = false;

            if (!data.ok) {
                statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Device not responding</span>';
                return;
            }

            const v = data.vitals;
            if (v.blood_pressure)   document.getElementById('vBP').value     = v.blood_pressure;
            if (v.temperature)      document.getElementById('vTemp').value   = v.temperature;
            if (v.heart_rate)       document.getElementById('vHR').value     = v.heart_rate;
            if (v.spo2)             document.getElementById('vSpo2').value   = v.spo2;
            if (v.respiratory_rate) document.getElementById('vRR').value     = v.respiratory_rate;
            if (v.weight)           document.getElementById('vWeight').value = v.weight;

            document.getElementById('vMachineFetched').value   = '1';
            document.getElementById('vMachineDeviceId').value  = data.device_id || 'DEVICE-01';

            statusEl.innerHTML =
                `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Fetched at ${data.fetched_at}</span>`;
            flagEl.classList.remove('d-none');
        })
        .catch(function () {
            btn.disabled = false;
            statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Device error</span>';
        });
    });

    // ── Queue filter & search ─────────────────────────────────────
    const rows       = document.querySelectorAll('#queueTable tbody .queue-row');
    const searchBox  = document.getElementById('queueSearch');
    let   activeFilter = 'all';

    function applyFilter() {
        const q = searchBox.value.toLowerCase();
        rows.forEach(function (row) {
            const matchText   = row.dataset.name.includes(q) || row.dataset.mrn.includes(q);
            const matchStatus = activeFilter === 'all' || row.dataset.status === activeFilter;
            row.style.display = (matchText && matchStatus) ? '' : 'none';
        });
    }

    searchBox.addEventListener('input', applyFilter);

    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            applyFilter();
        });
    });

    // ── Auto-refresh every 60 s ───────────────────────────────────
    let refreshTimer = setInterval(() => location.reload(), 60000);

    document.getElementById('refreshBtn').addEventListener('click', function () {
        this.classList.add('spinning');
        clearInterval(refreshTimer);
        location.reload();
    });

    // ── Reset vitals modal on close ───────────────────────────────
    document.getElementById('vitalsModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('vitalsForm').reset();
        document.getElementById('patientBanner').classList.add('d-none');
        document.getElementById('machineFetchStatus').innerHTML = '';
        document.getElementById('machineFetchedFlag').classList.add('d-none');
        document.getElementById('vMachineFetched').value = '0';
    });

})();
</script>
@endpush
@endsection
