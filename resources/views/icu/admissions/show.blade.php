@extends('backend.layouts.master')

@php
    $unitLabel = $admission->icu_type ?: 'ICU';
@endphp

@section('title', $unitLabel . ' Admission ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">{{ $unitLabel }} Admission</h1>
                <div class="text-muted">
                    <span class="fw-semibold">{{ $admission->icu_case_id }}</span>
                    <span class="badge bg-danger-subtle text-danger ms-2">{{ $admission->icu_type }}</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('icu.admissions.vitals.index', $admission->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-activity"></i> Vitals
                </a>
                <a href="{{ route('icu.admissions.alerts.index', $admission->id) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-bell"></i> Alerts
                </a>
                <a href="{{ route('icu.admissions.usage.index', $admission->id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-gear"></i> Equipment
                </a>
                <a href="{{ route('icu.admissions.orders.index', $admission->id) }}" class="btn btn-info btn-sm">
                    <i class="bi bi-clipboard-pulse"></i> Doctor Orders
                </a>
                <a href="{{ route('icu.admissions.pathology-orders.index', $admission->id) }}"
                    class="btn btn-primary btn-sm">
                    <i class="bi bi-clipboard2-pulse"></i> Pathology Orders
                </a>
                <a href="{{ route('icu.admissions.radiology-orders.index', $admission->id) }}"
                    class="btn btn-primary btn-sm">
                    <i class="bi bi-clipboard2-pulse"></i> Radiology Orders
                </a>
                <a href="{{ route('icu.admissions.medicine-orders.index', $admission->id) }}"
                    class="btn btn-secondary btn-sm">
                    <i class="bi bi-prescription2"></i> Medicine Order
                </a>
                <a href="{{ route('icu.admissions.procedure-orders.index', $admission->id) }}"
                    class="btn btn-secondary btn-sm">
                    <i class="bi bi-clipboard2-check"></i> Procedure Order
                </a>
                <a href="{{ route('icu.admissions.nursing-notes.index', $admission->id) }}" class="btn btn-info btn-sm">
                    <i class="bi bi-pencil-square"></i> Nurse Notes
                </a>
                <a href="{{ route('icu.admissions.intake-output.index', $admission->id) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-droplet"></i> I/O
                </a>
                <a href="{{ route('icu.admissions.infection.index', $admission->id) }}" class="btn btn-dark btn-sm">
                    <i class="bi bi-shield-exclamation"></i> Infection
                </a>
                <a href="{{ route('icu.admissions.billing.mode', $admission->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-seam"></i> Package
                </a>
                <a href="{{ route('icu.admissions.billing.preview', $admission->id) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-receipt"></i> Charge
                </a>
                @if (in_array($admission->status, ['Approved', 'Admitted']))
                    <a href="{{ route('icu.admissions.transfer.create', $admission->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left-right"></i> Transfer
                    </a>
                    <a href="{{ route('icu.admissions.discharge.create', $admission->id) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Discharge
                    </a>
                    <a href="{{ route('icu.admissions.mortality.create', $admission->id) }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-x-octagon"></i> Mortality
                    </a>
                @else
                    @if ($admission->dischargeSummary)
                        <a href="{{ route('icu.admissions.discharge.summary', $admission->id) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-file-medical"></i> Discharge Summary
                        </a>
                    @endif
                    @if ($admission->mortalityAudit)
                        <a href="{{ route('icu.admissions.mortality.show', $admission->id) }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-file-earmark-medical"></i> Mortality Audit
                        </a>
                    @endif
                @endif
                <button type="button"
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#codeBlueModal">
                    <i class="bi bi-exclamation-octagon"></i> CODE BLUE
                </button>
                <a href="{{ route('icu.admissions.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif

        @php
            $statusClassMap = [
                'Approved'   => 'bg-success-subtle text-success border-success-subtle',
                'Admitted'   => 'bg-success-subtle text-success border-success-subtle',
                'Discharged' => 'bg-primary-subtle text-primary border-primary-subtle',
                'Transferred'=> 'bg-info-subtle text-info border-info-subtle',
                'Deceased'   => 'bg-dark-subtle text-dark border-dark-subtle',
                'Cancelled'  => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            ];
            $statusClass = $statusClassMap[$admission->status] ?? 'bg-secondary-subtle text-secondary border-secondary-subtle';

            $openAlerts = $admission->alerts->whereIn('status', ['Active', 'Acknowledged']);
            $criticalAlerts = $openAlerts->where('severity', 'Critical');
        @endphp

        <div class="p-2 mt-3" style="font-size: 0.82rem;">

            {{-- Patient Header Card --}}
            <div class="row g-2 mb-3">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <div class="row g-2 align-items-start">

                                {{-- Photo + Name --}}
                                <div class="col-md-3 text-center">
                                    @if ($admission->patient?->image)
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#icuPatientImageModal">
                                            <img src="{{ asset('storage/' . $admission->patient->image) }}"
                                                class="rounded-4 border shadow-sm object-fit-cover" role="button"
                                                width="100" height="110" alt="Patient Image">
                                        </a>
                                    @else
                                        <div class="rounded-4 border d-flex align-items-center justify-content-center bg-light mx-auto"
                                            style="width:100px; height:110px;">
                                            <i class="bi bi-person fs-1 text-secondary"></i>
                                        </div>
                                    @endif

                                    <div class="mt-2">
                                        <span class="badge rounded-pill bg-danger px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-activity me-1"></i>{{ $unitLabel }} CASE
                                        </span>
                                    </div>

                                    <h6 class="fw-bold mt-1 mb-0">
                                        {{ $admission->patient?->patient_name ?? '-' }}
                                    </h6>

                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $admission->patient?->mrn ?? '' }}
                                        <br>
                                        Age: {{ calculateAgeFromDob($admission->patient?->dob) ?? '-' }}
                                        @if ($admission->patient?->gender)
                                            · {{ $admission->patient->gender }}
                                        @endif
                                    </div>

                                    @if ($admission->patient?->id)
                                        <div class="mt-2">
                                            <a href="{{ route('health-card.show', $admission->patient->id) }}" target="_blank"
                                                class="btn btn-warning btn-sm w-100 fw-semibold rounded-3">
                                                <i class="bi bi-printer me-1"></i> Health Card
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                {{-- Patient + Admission details --}}
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <h6 class="fw-bold mb-0">Patient & Admission</h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge {{ $statusClass }} border px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                {{ $admission->status }}
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill"
                                                style="font-size: 0.7rem;">
                                                {{ $admission->icu_case_id }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row g-1" style="font-size: 0.78rem;">
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Mobile</div>
                                            <div class="fw-semibold">{{ $admission->patient?->mobileno ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Guardian</div>
                                            <div class="fw-semibold">{{ $admission->patient?->guardian_name ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Blood Group</div>
                                            <div class="fw-semibold">{{ $admission->patient?->blood_group ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Allergies</div>
                                            <div class="fw-semibold {{ !empty($admission->patient?->known_allergies) ? 'text-danger' : '' }}">
                                                {{ $admission->patient?->known_allergies ?: 'None reported' }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Admission Time</div>
                                            <div class="fw-semibold">{{ format_datetime($admission->admission_time) ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Admission Type</div>
                                            <div class="fw-semibold">{{ $admission->admission_type ?? '-' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Source</div>
                                            <div class="fw-semibold">
                                                {{ $admission->source_type ?? '-' }}
                                                @if ($admission->source_id)
                                                    <small class="text-muted">#{{ $admission->source_id }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted mb-0">Discharge Time</div>
                                            <div class="fw-semibold">{{ format_datetime($admission->discharge_time) ?? '—' }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bed + Doctor + flags --}}
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light rounded-3 mb-2">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center"
                                                    style="width:40px; height:40px;">
                                                    <i class="bi bi-hospital fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small text-uppercase fw-semibold">Bed</div>
                                                    <div class="fw-bold text-primary">
                                                        {{ $admission->bed?->name ?? 'Not Assigned' }}
                                                        @if ($admission->bed?->bedType?->name)
                                                            <small class="text-muted fw-normal">[{{ $admission->bed->bedType->name }}]</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 bg-light rounded-3 mb-2">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center"
                                                    style="width:40px; height:40px;">
                                                    <i class="bi bi-person-vcard fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small text-uppercase fw-semibold">Referring Doctor</div>
                                                    <div class="fw-bold text-info">{{ $admission->referringDoctor?->name ?? 'Not Assigned' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($admission->isolation_type && strtolower($admission->isolation_type) !== 'none')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-shield-exclamation me-1"></i>{{ $admission->isolation_type }}
                                            </span>
                                        @endif
                                        @if ($admission->ventilator_required)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-lungs me-1"></i>Ventilator
                                            </span>
                                        @endif
                                        @if ($admission->monitor_required)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-display me-1"></i>Monitor
                                            </span>
                                        @endif
                                        @if ($criticalAlerts->isNotEmpty())
                                            <span class="badge bg-danger text-white rounded-pill px-2 py-1">
                                                <i class="bi bi-exclamation-octagon me-1"></i>{{ $criticalAlerts->count() }} Critical
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Latest Vitals --}}
            <div class="row g-2 mb-3">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0">Latest Vitals</h6>
                                    <span class="text-muted small">({{ $vitalCount }} total)</span>
                                </div>
                                @if ($latestVital)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-clock me-1"></i>{{ $latestVital->recorded_at?->format('d M Y, h:i A') }}
                                    </span>
                                @endif
                            </div>

                            @if ($latestVital)
                                @php
                                    $sys = (int) ($latestVital->systolic_bp ?? 0);
                                    $dia = (int) ($latestVital->diastolic_bp ?? 0);
                                    if ($sys > 140 || $dia > 90) {
                                        $bpStatus = 'HIGH'; $bpBadge = 'bg-danger-subtle text-danger'; $bpBorder = 'border-danger';
                                    } elseif ($sys && $dia && ($sys < 90 || $dia < 60)) {
                                        $bpStatus = 'LOW'; $bpBadge = 'bg-warning-subtle text-warning'; $bpBorder = 'border-warning';
                                    } elseif ($sys && $dia) {
                                        $bpStatus = 'NORMAL'; $bpBadge = 'bg-success-subtle text-success'; $bpBorder = '';
                                    } else {
                                        $bpStatus = 'N/A'; $bpBadge = 'bg-secondary-subtle text-secondary'; $bpBorder = '';
                                    }

                                    $pulse = (int) $latestVital->heart_rate;
                                    if ($pulse > 100) {
                                        $pulseStatus = 'HIGH'; $pulseBadge = 'bg-danger-subtle text-danger'; $pulseBorder = 'border-danger';
                                    } elseif ($pulse > 0 && $pulse < 60) {
                                        $pulseStatus = 'LOW'; $pulseBadge = 'bg-warning-subtle text-warning'; $pulseBorder = 'border-warning';
                                    } elseif ($pulse) {
                                        $pulseStatus = 'NORMAL'; $pulseBadge = 'bg-success-subtle text-success'; $pulseBorder = '';
                                    } else {
                                        $pulseStatus = 'N/A'; $pulseBadge = 'bg-secondary-subtle text-secondary'; $pulseBorder = '';
                                    }

                                    $temp = (float) $latestVital->temperature;
                                    if ($temp > 99.5) {
                                        $tempStatus = 'HIGH'; $tempBadge = 'bg-danger-subtle text-danger'; $tempBorder = 'border-danger';
                                    } elseif ($temp > 0 && $temp < 96.8) {
                                        $tempStatus = 'LOW'; $tempBadge = 'bg-warning-subtle text-warning'; $tempBorder = 'border-warning';
                                    } elseif ($temp) {
                                        $tempStatus = 'NORMAL'; $tempBadge = 'bg-success-subtle text-success'; $tempBorder = '';
                                    } else {
                                        $tempStatus = 'N/A'; $tempBadge = 'bg-secondary-subtle text-secondary'; $tempBorder = '';
                                    }

                                    $spo2 = (float) $latestVital->spo2;
                                    if ($spo2 >= 95) {
                                        $spo2Status = 'NORMAL'; $spo2Badge = 'bg-success-subtle text-success'; $spo2Border = '';
                                    } elseif ($spo2 >= 90) {
                                        $spo2Status = 'LOW'; $spo2Badge = 'bg-warning-subtle text-warning'; $spo2Border = 'border-warning';
                                    } elseif ($spo2 > 0) {
                                        $spo2Status = 'CRITICAL'; $spo2Badge = 'bg-danger text-white'; $spo2Border = 'border-danger';
                                    } else {
                                        $spo2Status = 'N/A'; $spo2Badge = 'bg-secondary-subtle text-secondary'; $spo2Border = '';
                                    }

                                    $respRate = (int) $latestVital->respiratory_rate;
                                    if ($respRate > 20) {
                                        $respStatus = 'HIGH'; $respBadge = 'bg-danger-subtle text-danger'; $respBorder = 'border-danger';
                                    } elseif ($respRate > 0 && $respRate < 12) {
                                        $respStatus = 'LOW'; $respBadge = 'bg-warning-subtle text-warning'; $respBorder = 'border-warning';
                                    } elseif ($respRate) {
                                        $respStatus = 'NORMAL'; $respBadge = 'bg-success-subtle text-success'; $respBorder = '';
                                    } else {
                                        $respStatus = 'N/A'; $respBadge = 'bg-secondary-subtle text-secondary'; $respBorder = '';
                                    }

                                    $sevBadge = match (strtolower((string) $latestVital->severity)) {
                                        'critical' => 'bg-danger text-white',
                                        'warning' => 'bg-warning-subtle text-warning',
                                        'normal' => 'bg-success-subtle text-success',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                @endphp

                                <div class="row g-2">
                                    <div class="col-md-2 col-6">
                                        <div class="border {{ $bpBorder }} rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">BP (mmHg)</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">{{ $sys ?: '-' }}/{{ $dia ?: '-' }}</h6>
                                                <span class="badge rounded-pill {{ $bpBadge }}">{{ $bpStatus }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <div class="border {{ $pulseBorder }} rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">HEART RATE</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">{{ $pulse ?: '-' }} <small class="fw-normal text-muted">bpm</small></h6>
                                                <span class="badge rounded-pill {{ $pulseBadge }}">{{ $pulseStatus }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <div class="border {{ $spo2Border }} rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">SPO2</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">{{ $spo2 ? rtrim(rtrim(number_format($spo2,2), '0'), '.') : '-' }} <small class="fw-normal text-muted">%</small></h6>
                                                <span class="badge rounded-pill {{ $spo2Badge }}">{{ $spo2Status }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <div class="border {{ $tempBorder }} rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">TEMP</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">{{ $temp ?: '-' }} <small class="fw-normal text-muted">°F</small></h6>
                                                <span class="badge rounded-pill {{ $tempBadge }}">{{ $tempStatus }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <div class="border {{ $respBorder }} rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">RESP RATE</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0">{{ $respRate ?: '-' }} <small class="fw-normal text-muted">/min</small></h6>
                                                <span class="badge rounded-pill {{ $respBadge }}">{{ $respStatus }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2 col-6">
                                        <div class="border rounded-3 p-2 h-100">
                                            <div class="small text-muted fw-semibold mb-1">SEVERITY</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0 text-uppercase" style="font-size:0.85rem;">{{ $latestVital->severity ?: '-' }}</h6>
                                                <span class="badge rounded-pill {{ $sevBadge }}">{{ strtoupper($latestVital->source_type ?? 'MANUAL') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($latestVital->remarks)
                                    <div class="mt-2 small text-muted"><i class="bi bi-chat-left-text me-1"></i>{{ $latestVital->remarks }}</div>
                                @endif
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-heart-pulse fs-4 d-block mb-1"></i>
                                    No vital records found.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clinical & Resource + I/O 24h + Active Alerts --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="bi bi-clipboard2-pulse me-1"></i> Diagnosis & Clinical
                            </h6>
                            <div class="mb-2">
                                <div class="text-muted small">Admission Diagnosis</div>
                                <div class="fw-semibold">{{ $admission->admission_diagnosis ?: '—' }}</div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted small">Isolation</div>
                                    <div class="fw-semibold">{{ $admission->isolation_type ?: '—' }}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small">Ventilator</div>
                                    <div class="fw-semibold">{{ $admission->ventilator_required ? 'Yes' : 'No' }}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small">Monitor</div>
                                    <div class="fw-semibold">{{ $admission->monitor_required ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>
                            @if ($admission->remarks)
                                <hr class="my-2">
                                <div class="text-muted small">Remarks</div>
                                <div class="small">{{ $admission->remarks }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <h6 class="fw-bold mb-2"><i class="bi bi-droplet-half me-1"></i> 24h I/O</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Intake</span>
                                <span class="fw-bold text-success">{{ number_format($intake24h) }} <small class="fw-normal text-muted">ml</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Output</span>
                                <span class="fw-bold text-info">{{ number_format($output24h) }} <small class="fw-normal text-muted">ml</small></span>
                            </div>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small">Balance</span>
                                @php $bal = $intake24h - $output24h; @endphp
                                <span class="fw-bold {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($bal >= 0 ? '+' : '') . number_format($bal) }} <small class="fw-normal text-muted">ml</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-bell me-1"></i> Active Alerts</h6>
                            @if ($openAlerts->isEmpty())
                                <div class="text-muted small fst-italic">No active alerts.</div>
                            @else
                                @foreach ($openAlerts->take(3) as $al)
                                    @php
                                        $sevColor = match (strtolower((string) $al->severity)) {
                                            'critical' => 'danger',
                                            'warning' => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div class="small">
                                            <span class="badge bg-{{ $sevColor }}-subtle text-{{ $sevColor }} border border-{{ $sevColor }}-subtle">
                                                {{ strtoupper($al->severity ?? 'INFO') }}
                                            </span>
                                            <span class="fw-semibold ms-1">{{ $al->vital_type ?? $al->alert_type ?? '-' }}</span>
                                            @if ($al->observed_value)
                                                <span class="text-muted">({{ $al->observed_value }})</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                @if ($openAlerts->count() > 3)
                                    <a href="{{ route('icu.admissions.alerts.index', $admission->id) }}"
                                        class="small text-decoration-none">
                                        +{{ $openAlerts->count() - 3 }} more →
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Patient Timeline (latest 5) --}}
            @php
                $tlEvents = collect();

                if ($admission->admission_time) {
                    $tlEvents->push(['date' => $admission->admission_time, 'color' => 'primary', 'title' => 'ICU admitted', 'desc' => $admission->icu_case_id]);
                }
                foreach ($admission->vitalLogs ?? [] as $v) {
                    $tlEvents->push(['date' => $v->recorded_at ?? $v->created_at, 'color' => 'danger', 'title' => 'Vital recorded', 'desc' => trim(collect([
                        $v->systolic_bp && $v->diastolic_bp ? 'BP ' . $v->systolic_bp . '/' . $v->diastolic_bp : null,
                        $v->heart_rate ? 'HR ' . $v->heart_rate : null,
                        $v->spo2 ? 'SpO2 ' . $v->spo2 : null,
                    ])->filter()->implode(' • ')) ?: null]);
                }
                foreach ($admission->nursingNotes ?? [] as $n) {
                    $tlEvents->push(['date' => $n->observation_time ?? $n->created_at, 'color' => 'info', 'title' => 'Nursing note (' . ($n->shift ?? '-') . ')', 'desc' => $n->general_condition ?? $n->remarks ?? null]);
                }
                foreach ($admission->doctorOrders ?? [] as $o) {
                    $tlEvents->push(['date' => $o->start_time ?? $o->created_at, 'color' => 'success', 'title' => 'Order: ' . ($o->order_title ?? $o->order_type ?? '-'), 'desc' => $o->priority ? 'Priority: ' . $o->priority : null]);
                }
                foreach ($admission->alerts ?? [] as $a) {
                    $tlEvents->push(['date' => $a->created_at, 'color' => 'warning', 'title' => 'Alert: ' . ($a->vital_type ?? $a->alert_type ?? '-'), 'desc' => $a->message ?? ($a->observed_value ? 'Value: ' . $a->observed_value : null)]);
                }
                foreach ($admission->transfers ?? [] as $t) {
                    $tlEvents->push(['date' => $t->transfer_time ?? $t->created_at, 'color' => 'secondary', 'title' => 'Transfer: ' . ($t->from_unit ?? '-') . ' → ' . ($t->to_unit ?? '-'), 'desc' => $t->transfer_reason ?? null]);
                }
                foreach ($admission->emergencyEvents ?? [] as $e) {
                    $tlEvents->push(['date' => $e->event_time ?? $e->created_at, 'color' => 'dark', 'title' => 'Emergency: ' . ($e->event_type ?? '-'), 'desc' => $e->event_details ?? null]);
                }
                foreach ($admission->pathologyOrders ?? [] as $po) {
                    $tlEvents->push(['date' => $po->datetime ?? $po->created_at, 'color' => 'warning', 'title' => 'Pathology order', 'desc' => $po->order_number ?? null]);
                }
                foreach ($admission->radiologyOrders ?? [] as $ro) {
                    $tlEvents->push(['date' => $ro->datetime ?? $ro->created_at, 'color' => 'warning', 'title' => 'Radiology order', 'desc' => $ro->order_number ?? null]);
                }
                if ($admission->discharge_time) {
                    $tlEvents->push(['date' => $admission->discharge_time, 'color' => 'success', 'title' => 'Discharged', 'desc' => $admission->outcome ?? null]);
                }

                $tlEvents = $tlEvents->filter(fn($e) => !empty($e['date']))->sortByDesc(fn($e) => $e['date'])->take(5)->values();

                $iconMap = [
                    'primary'   => 'bi-person-badge',
                    'secondary' => 'bi-arrow-left-right',
                    'danger'    => 'bi-heart-pulse-fill',
                    'info'      => 'bi-journal-medical',
                    'success'   => 'bi-clipboard-pulse',
                    'warning'   => 'bi-bell',
                    'dark'      => 'bi-exclamation-octagon',
                ];
            @endphp

            <div class="row g-2 mb-3">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                                    style="width:32px; height:32px;">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                                <div>
                                    <h6 class="fw-bold mb-0">Patient Timeline</h6>
                                    <small class="text-muted">Latest 5 events</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-3 px-3">
                            @if ($tlEvents->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inboxes fs-3 d-block mb-1 opacity-50"></i>
                                    No timeline events yet.
                                </div>
                            @else
                                <ul class="list-unstyled mb-0 position-relative ps-4 pt-2"
                                    style="border-left: 2px dashed #e5e7eb; margin-left: 14px;">
                                    @foreach ($tlEvents as $event)
                                        @php $icon = $iconMap[$event['color']] ?? 'bi-circle-fill'; @endphp
                                        <li class="position-relative mb-3">
                                            <span class="position-absolute d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $event['color'] }}-subtle text-{{ $event['color'] }} border border-2 border-white shadow-sm"
                                                style="width:32px; height:32px; left:-32px; top:-2px;">
                                                <i class="bi {{ $icon }}"></i>
                                            </span>
                                            <div class="bg-light rounded-3 px-3 py-2">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                                    <div class="fw-semibold small text-dark">{{ $event['title'] }}</div>
                                                    <span class="badge bg-white text-muted border" style="font-weight:500;">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        {{ \Illuminate\Support\Carbon::parse($event['date'])->format('d M Y, h:i A') }}
                                                    </span>
                                                </div>
                                                @if (!empty($event['desc']))
                                                    <div class="text-muted small mb-0">{{ $event['desc'] }}</div>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Equipment + Active Infections --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><i class="bi bi-gear me-1"></i> Active Equipment</h6>
                                <a href="{{ route('icu.admissions.usage.index', $admission->id) }}" class="small text-decoration-none">Manage →</a>
                            </div>
                            @if ($admission->activeEquipmentUsage->isEmpty())
                                <div class="text-muted small fst-italic">No equipment in use.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Equipment</th>
                                                <th>Type</th>
                                                <th>Start</th>
                                                <th class="text-end">Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($admission->activeEquipmentUsage as $eu)
                                                <tr>
                                                    <td class="fw-semibold">{{ $eu->equipment?->name ?? '-' }}</td>
                                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $eu->equipment_type }}</span></td>
                                                    <td class="small">{{ format_datetime($eu->start_time) }}</td>
                                                    <td class="text-end">{{ number_format((float) $eu->charge_rate, 2) }}/{{ $eu->billing_unit }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-shield-exclamation me-1"></i> Active Infections</h6>
                                <a href="{{ route('icu.admissions.infection.index', $admission->id) }}" class="small text-decoration-none">Manage →</a>
                            </div>
                            @if ($admission->activeInfections->isEmpty())
                                <div class="text-muted small fst-italic">No active infections.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Infection</th>
                                                <th>Organism</th>
                                                <th>Status</th>
                                                <th>Detected</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($admission->activeInfections as $inf)
                                                <tr>
                                                    <td class="fw-semibold">{{ $inf->infection_name ?? '-' }}</td>
                                                    <td>{{ $inf->organism ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                            {{ $inf->infection_status ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="small">{{ format_datetime($inf->first_detected_at, 'd M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Doctor Orders + Nursing Notes (latest 5 each) --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><i class="bi bi-clipboard-pulse me-1"></i> Doctor Orders</h6>
                                <a href="{{ route('icu.admissions.orders.index', $admission->id) }}" class="small text-decoration-none">View all →</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Doctor</th>
                                            <th>When</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($admission->doctorOrders->take(5) as $o)
                                            @php
                                                $prClass = match (strtoupper((string) $o->priority)) {
                                                    'STAT' => 'bg-danger',
                                                    'URGENT' => 'bg-warning',
                                                    'ROUTINE' => 'bg-success',
                                                    default => 'bg-secondary',
                                                };
                                                $stClass = match (strtolower((string) $o->status)) {
                                                    'completed', 'done' => 'bg-success-subtle text-success',
                                                    'inprogress' => 'bg-info-subtle text-info',
                                                    'cancelled' => 'bg-secondary-subtle text-secondary',
                                                    'onhold' => 'bg-warning-subtle text-warning',
                                                    default => 'bg-primary-subtle text-primary',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $o->order_title ?? $o->order_type ?? '-' }}</td>
                                                <td><span class="badge {{ $prClass }}">{{ $o->priority ?? '-' }}</span></td>
                                                <td><span class="badge {{ $stClass }}">{{ $o->status ?? '-' }}</span></td>
                                                <td class="small">{{ $o->doctor?->name ?? '-' }}</td>
                                                <td class="small">{{ format_datetime($o->start_time, 'd M, h:i A') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2">No orders.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><i class="bi bi-pencil-square me-1"></i> Nursing Notes</h6>
                                <a href="{{ route('icu.admissions.nursing-notes.index', $admission->id) }}" class="small text-decoration-none">View all →</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>When</th>
                                            <th>Shift</th>
                                            <th>Consciousness</th>
                                            <th>Pain</th>
                                            <th>Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($admission->nursingNotes->take(5) as $n)
                                            <tr>
                                                <td class="small">{{ format_datetime($n->observation_time, 'd M, h:i A') }}</td>
                                                <td><span class="badge bg-info-subtle text-info">{{ $n->shift ?? '-' }}</span></td>
                                                <td>{{ $n->consciousness_level ?? '-' }}</td>
                                                <td>{{ $n->pain_score ?? '-' }}/10</td>
                                                <td>{{ $n->general_condition ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2">No notes.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transfers --}}
            @if ($admission->transfers->isNotEmpty())
                <div class="row g-2 mb-3">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-2">
                                <h6 class="fw-bold mb-2"><i class="bi bi-arrow-left-right me-1"></i> Transfer History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Type</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($admission->transfers as $t)
                                                <tr>
                                                    <td class="small">{{ format_datetime($t->transfer_time) }}</td>
                                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $t->transfer_type }}</span></td>
                                                    <td>{{ $t->from_unit ?? '-' }} @if ($t->fromBed) <small class="text-muted">({{ $t->fromBed->name }})</small>@endif</td>
                                                    <td>{{ $t->to_unit ?? '-' }} @if ($t->toBed) <small class="text-muted">({{ $t->toBed->name }})</small>@endif</td>
                                                    <td class="small">{{ $t->transfer_reason ?? '-' }}</td>
                                                    <td><span class="badge bg-info-subtle text-info">{{ $t->status }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Discharge / Mortality Summary --}}
            @if ($admission->dischargeSummary || $admission->mortalityAudit)
                <div class="row g-2 mb-3">
                    @if ($admission->dischargeSummary)
                        <div class="{{ $admission->mortalityAudit ? 'col-md-6' : 'col-md-12' }}">
                            <div class="card border-0 shadow-sm rounded-3 h-100 border-success-subtle">
                                <div class="card-body p-2">
                                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-file-medical me-1"></i> Discharge Summary</h6>
                                    <div class="row g-2 small">
                                        <div class="col-12">
                                            <div class="text-muted">Final Diagnosis</div>
                                            <div class="fw-semibold">{{ $admission->dischargeSummary->final_diagnosis ?: '—' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted">Condition at Discharge</div>
                                            <div class="fw-semibold">{{ $admission->dischargeSummary->condition_at_discharge ?: '—' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted">Follow-up Advice</div>
                                            <div>{{ $admission->dischargeSummary->followup_advice ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('icu.admissions.discharge.summary', $admission->id) }}"
                                            class="btn btn-sm btn-outline-success rounded-pill">
                                            <i class="bi bi-eye me-1"></i> Full Summary
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($admission->mortalityAudit)
                        <div class="{{ $admission->dischargeSummary ? 'col-md-6' : 'col-md-12' }}">
                            <div class="card border-0 shadow-sm rounded-3 h-100 border-dark-subtle">
                                <div class="card-body p-2">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-medical me-1"></i> Mortality Audit</h6>
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <div class="text-muted">Death Time</div>
                                            <div class="fw-semibold">{{ format_datetime($admission->mortalityAudit->death_time) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted">Audit Status</div>
                                            <div class="fw-semibold">{{ $admission->mortalityAudit->audit_status ?: '—' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted">Final Diagnosis</div>
                                            <div class="fw-semibold">{{ $admission->mortalityAudit->final_diagnosis ?: '—' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted">Cause of Death</div>
                                            <div class="fw-semibold">{{ $admission->mortalityAudit->cause_of_death ?: '—' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-muted">Preventability</div>
                                            <div>{{ $admission->mortalityAudit->preventability ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('icu.admissions.mortality.show', $admission->id) }}"
                                            class="btn btn-sm btn-outline-dark rounded-pill">
                                            <i class="bi bi-eye me-1"></i> Full Audit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Emergency Overrides --}}
            @if ($admission->overrides->isNotEmpty())
                <div class="row g-2 mb-3">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm rounded-3 border-danger-subtle">
                            <div class="card-body p-2">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-octagon me-1"></i> Emergency Override Audit</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Issue</th>
                                                <th>Reason</th>
                                                <th>Approved By</th>
                                                <th>Temp Bed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($admission->overrides as $o)
                                                <tr>
                                                    <td class="small">{{ format_datetime($o->override_time) }}</td>
                                                    <td>{{ $o->resource_issue }}</td>
                                                    <td>{{ $o->override_reason }}</td>
                                                    <td>#{{ $o->approved_by }}</td>
                                                    <td>{{ $o->temporaryBed?->name ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Patient Image Modal --}}
    @if ($admission->patient?->image)
        <div class="modal fade" id="icuPatientImageModal" tabindex="-1" aria-label="Patient Image" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-3 overflow-hidden">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">{{ $admission->patient?->patient_name ?? 'Patient' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <img src="{{ asset('storage/' . $admission->patient->image) }}"
                            class="img-fluid rounded-4 shadow" alt="Patient Image">
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="codeBlueModal" tabindex="-1" aria-labelledby="codeBlueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST"
                action="{{ route('icu.admissions.emergency.activate', $admission->id) }}"
                class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="code-blue-modal__icon">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </span>
                        <div>
                            <h5 class="modal-title text-danger fw-bold mb-0" id="codeBlueModalLabel">Activate Code Blue</h5>
                            <div class="text-muted small">
                                {{ $admission->bed?->name ? 'Bed ' . $admission->bed->name . ' | ' : '' }}{{ $admission->icu_case_id }}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Event Type <span class="text-danger">*</span></label>
                    <select name="event_type" class="form-select" required>
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
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1">
                        <i class="bi bi-broadcast-pin"></i> Activate Code Blue
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
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
    </style>
@endsection
