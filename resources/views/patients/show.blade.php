@extends('backend.layouts.master')

@section('title', 'Patient: ' . $patient->patient_name)

@section('content')
    <div class="container">

        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title mb-0">Patient Details</h1>
                <div class="text-muted small mt-1">{{ $patient->mrn }} &middot; {{ $patient->health_card_no }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                </a>
                <a href="{{ route('health-card.show', $patient) }}" class="btn btn-success btn-sm" target="_blank">
                    <i class="fa-solid fa-id-card me-1"></i> Health Card
                </a>
                <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible mt-3">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ══════════════════════════════════════
             PROFILE HEADER
        ══════════════════════════════════════ --}}
        @php
            $words    = array_filter(explode(' ', $patient->patient_name));
            $initials = strtoupper(
                substr($words[array_key_first($words)] ?? '', 0, 1) .
                substr($words[array_key_last($words)]  ?? '', 0, 1)
            );
            $palette = ['#4361ee','#3a86ff','#06d6a0','#8338ec','#fb5607','#e07a5f','#3d405b','#2d6a4f'];
            $bgColor = $palette[abs(crc32($patient->patient_name)) % count($palette)];
        @endphp

        <div class="card mt-4 mb-3 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-4 align-items-start">

                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        @if($patient->image)
                            <img src="{{ asset('storage/' . $patient->image) }}"
                                 style="width:90px;height:90px;object-fit:cover;border-radius:16px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center text-white fw-bold rounded-3"
                                 style="width:90px;height:90px;background:{{ $bgColor }};font-size:30px;letter-spacing:1px">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>

                    {{-- Core Identity --}}
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h3 class="mb-0 fw-bold">{{ $patient->patient_name }}</h3>
                            @if($patient->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            @if($patient->is_ipd)
                                <span class="badge bg-warning text-dark">IPD</span>
                            @endif
                            @if($patient->is_dead)
                                <span class="badge bg-dark">Deceased</span>
                            @endif
                        </div>

                        <div class="text-muted mb-2" style="font-size:13px">
                            <span class="fw-semibold text-dark">{{ $patient->mrn }}</span>
                            @if($patient->health_card_no)
                                &middot; {{ $patient->health_card_no }}
                            @endif
                            @if($patient->patient_type)
                                &middot; <span class="text-primary fw-semibold">{{ $patient->patient_type }}</span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-3 mb-2" style="font-size:13.5px">
                            @if($patient->gender)
                                @php $gIcon = match(strtolower($patient->gender)) { 'male' => 'fa-mars', 'female' => 'fa-venus', default => 'fa-genderless' }; @endphp
                                <span><i class="fa-solid {{ $gIcon }} text-muted me-1"></i>{{ ucfirst($patient->gender) }}</span>
                            @endif
                            @if($patient->blood_group)
                                <span><i class="fa-solid fa-droplet text-danger me-1"></i>{{ $patient->blood_group }}</span>
                            @endif
                            @if($patient->dob)
                                <span><i class="fa-solid fa-cake-candles text-muted me-1"></i>{{ $patient->dob->format('d M Y') }} ({{ $patient->dob->age }}y)</span>
                            @endif
                            @if($patient->marital_status)
                                <span class="text-muted">{{ ucfirst($patient->marital_status) }}</span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-3" style="font-size:13.5px">
                            @if($patient->mobileno)
                                <span><i class="fa-solid fa-phone text-muted me-1"></i>{{ $patient->mobileno }}</span>
                            @endif
                            @if($patient->email)
                                <span><i class="fa-solid fa-envelope text-muted me-1"></i>{{ $patient->email }}</span>
                            @endif
                            @if($patient->address)
                                <span class="text-muted"><i class="fa-solid fa-location-dot text-muted me-1"></i>{{ $patient->address }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                        @php
                            $quickStats = [
                                ['label' => 'OPD',      'count' => $patient->opdPatients->count(),          'color' => '#4361ee'],
                                ['label' => 'IPD',      'count' => $patient->ipdPatients->count(),          'color' => '#0dcaf0'],
                                ['label' => 'ER',       'count' => $patient->erPatients->count(),           'color' => '#dc3545'],
                                ['label' => 'Rx',       'count' => $patient->prescriptions->count(),        'color' => '#06d6a0'],
                                ['label' => 'Pharmacy', 'count' => $patient->pharmacyTransactions->count(), 'color' => '#fb5607'],
                                ['label' => 'Lab',      'count' => $patient->labOrders->count(),            'color' => '#6c757d'],
                            ];
                        @endphp
                        @foreach($quickStats as $s)
                            <div class="text-center rounded-3 px-3 py-2" style="min-width:62px;background:{{ $s['color'] }}18;border:1px solid {{ $s['color'] }}30">
                                <div class="fw-bold fs-5 lh-1" style="color:{{ $s['color'] }}">{{ $s['count'] }}</div>
                                <div class="mt-1 text-muted" style="font-size:10.5px">{{ $s['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             TABBED CONTENT
        ══════════════════════════════════════ --}}
        <div class="card overflow-hidden">

            {{-- Tab Nav --}}
            <div class="card-header border-0 pb-0 bg-white">
                <ul class="nav nav-tabs custom-tabs mb-0" id="patientDetailTabs" role="tablist">

                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button">
                            <i class="fa-solid fa-table-cells-large me-1"></i>Overview
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-opd" type="button">
                            <i class="fa-solid fa-stethoscope me-1"></i>OPD
                            @if($patient->opdPatients->count())
                                <span class="badge bg-primary ms-1">{{ $patient->opdPatients->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ipd" type="button">
                            <i class="fa-solid fa-bed me-1"></i>IPD
                            @if($patient->ipdPatients->count())
                                <span class="badge bg-info text-dark ms-1">{{ $patient->ipdPatients->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-er" type="button">
                            <i class="fa-solid fa-truck-medical me-1"></i>ER
                            @if($patient->erPatients->count())
                                <span class="badge bg-danger ms-1">{{ $patient->erPatients->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-appointments" type="button">
                            <i class="fa-solid fa-calendar-check me-1"></i>Appointments
                            @if($patient->appointments->count())
                                <span class="badge bg-secondary ms-1">{{ $patient->appointments->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-prescriptions" type="button">
                            <i class="fa-solid fa-prescription me-1"></i>Prescriptions
                            @if($patient->prescriptions->count())
                                <span class="badge bg-success ms-1">{{ $patient->prescriptions->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pharmacy" type="button">
                            <i class="fa-solid fa-pills me-1"></i>Pharmacy
                            @if($patient->pharmacyTransactions->count())
                                <span class="badge bg-warning text-dark ms-1">{{ $patient->pharmacyTransactions->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lab" type="button">
                            <i class="fa-solid fa-flask me-1"></i>Lab / Radiology
                            @if($patient->labOrders->count())
                                <span class="badge bg-secondary ms-1">{{ $patient->labOrders->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-billing" type="button">
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i>Billing
                            @if($patient->transactions->count())
                                <span class="badge bg-dark ms-1">{{ $patient->transactions->count() }}</span>
                            @endif
                        </button>
                    </li>

                </ul>
            </div>

            {{-- Tab Content --}}
            <div class="card-body p-4">
                <div class="tab-content">

                    {{-- ═══════════════ OVERVIEW ═══════════════ --}}
                    <div class="tab-pane fade show active" id="tab-overview">
                        <div class="row g-4">

                            {{-- Personal Info --}}
                            <div class="col-lg-6">
                                <p class="text-uppercase fw-bold text-muted mb-3" style="font-size:11px;letter-spacing:.8px">
                                    <i class="fa-solid fa-circle-user me-1"></i> Personal Information
                                </p>
                                @php
                                    $personalFields = [
                                        ['Full Name',       $patient->patient_name],
                                        ['Gender',          ucfirst($patient->gender ?? '')],
                                        ['Date of Birth',   $patient->dob ? $patient->dob->format('d M Y') . ' — ' . $patient->dob->age . ' yrs' : null],
                                        ['Marital Status',  ucfirst($patient->marital_status ?? '')],
                                        ['Blood Group',     $patient->blood_group],
                                        ['Mobile',          $patient->mobileno],
                                        ['Email',           $patient->email],
                                        ['Address',         $patient->address],
                                        ['Guardian',        $patient->guardian_name],
                                    ];
                                @endphp
                                <div class="d-flex flex-column gap-0">
                                    @foreach($personalFields as [$label, $value])
                                        @if(!empty($value))
                                            <div class="d-flex gap-2 py-2 border-bottom">
                                                <span class="text-muted flex-shrink-0" style="width:140px;font-size:13px">{{ $label }}</span>
                                                <span class="fw-semibold" style="font-size:13.5px">{{ $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            {{-- Medical & Admin Info --}}
                            <div class="col-lg-6">
                                <p class="text-uppercase fw-bold text-muted mb-3" style="font-size:11px;letter-spacing:.8px">
                                    <i class="fa-solid fa-notes-medical me-1"></i> Medical & Administrative
                                </p>
                                @php
                                    $medFields = [
                                        ['MRN',                $patient->mrn],
                                        ['Health Card No',     $patient->health_card_no],
                                        ['Patient Type',       $patient->patient_type],
                                        ['Identification No',  $patient->identification_number],
                                        ['Organization',       $patient->organization_name],
                                        ['Insurance',          $patient->insurance],
                                        ['Insurance Validity', $patient->insurance_validity?->format('d M Y')],
                                        ['Known Allergies',    $patient->known_allergies],
                                        ['Notes',              $patient->note],
                                    ];
                                @endphp
                                <div class="d-flex flex-column gap-0">
                                    @foreach($medFields as [$label, $value])
                                        @if(!empty($value))
                                            <div class="d-flex gap-2 py-2 border-bottom">
                                                <span class="text-muted flex-shrink-0" style="width:140px;font-size:13px">{{ $label }}</span>
                                                <span class="fw-semibold" style="font-size:13.5px">{{ $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Status Flags --}}
                                <div class="d-flex gap-2 flex-wrap mt-4">
                                    @foreach([['Active', $patient->is_active, 'success'], ['IPD', $patient->is_ipd, 'warning'], ['Deceased', $patient->is_dead, 'dark']] as [$lbl, $val, $col])
                                        <div class="border rounded-3 px-3 py-2 text-center {{ $val ? "border-{$col} bg-{$col} bg-opacity-10" : '' }}"
                                             style="min-width:80px">
                                            <div class="fw-bold {{ $val ? "text-{$col}" : 'text-muted' }}">{{ $val ? 'Yes' : 'No' }}</div>
                                            <div class="text-muted" style="font-size:11px">{{ $lbl }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Medical History --}}
                            @if($patient->histories->isNotEmpty())
                                <div class="col-12">
                                    <p class="text-uppercase fw-bold text-muted mb-3" style="font-size:11px;letter-spacing:.8px">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Medical History
                                    </p>
                                    <div class="row g-3">
                                        @foreach($patient->histories->groupBy('history_type') as $type => $entries)
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <span class="badge {{ $entries->first()->type_badge }} mb-2">
                                                        {{ $entries->first()->type_label }}
                                                    </span>
                                                    @foreach($entries as $h)
                                                        <div class="small text-muted">• {{ $h->description }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- ═══════════════ OPD ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-opd">
                        @if($patient->opdPatients->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-stethoscope', 'message' => 'No OPD visits recorded for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Case ID</th>
                                            <th>Doctor</th>
                                            <th>Department</th>
                                            <th>Visit Type</th>
                                            <th>Chief Complaint</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->opdPatients as $opd)
                                            <tr>
                                                <td class="text-nowrap fw-semibold">{{ $opd->date?->format('d M Y') ?? '—' }}</td>
                                                <td><small class="text-muted">{{ $opd->case_id }}</small></td>
                                                <td>{{ $opd->doctor?->name ?? '—' }}</td>
                                                <td>{{ $opd->department?->name ?? '—' }}</td>
                                                <td>
                                                    @if($opd->visit_type)
                                                        <span class="badge {{ $opd->visit_type_badge }}">{{ $opd->visit_type_label }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-muted small" title="{{ $opd->chief_complaint }}">
                                                        {{ Str::limit($opd->chief_complaint, 45) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $opd->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ ucfirst($opd->status ?? '—') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('opd-patients.show', $opd) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ IPD ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-ipd">
                        @if($patient->ipdPatients->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-bed', 'message' => 'No IPD admissions recorded for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>IPD No</th>
                                            <th>Admission</th>
                                            <th>Discharge</th>
                                            <th>Duration</th>
                                            <th>Doctor</th>
                                            <th>Department</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->ipdPatients as $ipd)
                                            @php
                                                $end      = $ipd->discharge_date ?? now();
                                                $duration = $ipd->admission_date
                                                    ? $ipd->admission_date->diffInDays($end) . 'd' . ($ipd->discharge_date ? '' : ' (ongoing)')
                                                    : '—';
                                                $statusClass = match(strtolower($ipd->status ?? '')) {
                                                    'admitted', 'active' => 'bg-success',
                                                    'discharged'         => 'bg-secondary',
                                                    'transferred'        => 'bg-warning text-dark',
                                                    default              => 'bg-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td><span class="fw-semibold text-primary">{{ $ipd->ipd_no }}</span></td>
                                                <td class="text-nowrap">{{ $ipd->admission_date?->format('d M Y') ?? '—' }}</td>
                                                <td class="text-nowrap text-muted">{{ $ipd->discharge_date?->format('d M Y') ?? '—' }}</td>
                                                <td><small class="text-muted">{{ $duration }}</small></td>
                                                <td>{{ $ipd->doctor?->name ?? '—' }}</td>
                                                <td>{{ $ipd->department?->name ?? '—' }}</td>
                                                <td>
                                                    @if($ipd->admission_type)
                                                        <span class="badge bg-info text-dark">{{ ucfirst($ipd->admission_type) }}</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge {{ $statusClass }}">{{ ucfirst($ipd->status ?? '—') }}</span></td>
                                                <td>
                                                    <a href="{{ route('ipd-patients.show', $ipd) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ ER ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-er">
                        @if($patient->erPatients->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-truck-medical', 'message' => 'No ER visits recorded for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Arrival Time</th>
                                            <th>Priority</th>
                                            <th>Doctor</th>
                                            <th>Department</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->erPatients as $er)
                                            @php
                                                $priClass = match($er->priority ?? '') {
                                                    'CRITICAL' => 'bg-danger',
                                                    'HIGH'     => 'bg-warning text-dark',
                                                    default    => 'bg-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="text-nowrap fw-semibold">{{ $er->arrival_time?->format('d M Y H:i') ?? '—' }}</td>
                                                <td><span class="badge {{ $priClass }}">{{ $er->priority ?? '—' }}</span></td>
                                                <td>{{ $er->doctor?->name ?? '—' }}</td>
                                                <td>{{ $er->department?->name ?? '—' }}</td>
                                                <td><span class="text-muted small">{{ Str::limit($er->description, 55) }}</span></td>
                                                <td><span class="badge bg-info text-dark">{{ $er->status ?? '—' }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ APPOINTMENTS ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-appointments">
                        @if($patient->appointments->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-calendar-check', 'message' => 'No appointments found for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Doctor</th>
                                            <th>Appointment</th>
                                            <th>Visit Status</th>
                                            <th>Source</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->appointments as $appt)
                                            <tr>
                                                <td class="text-nowrap fw-semibold">{{ $appt->date?->format('d M Y') ?? '—' }}</td>
                                                <td class="text-muted text-nowrap">{{ $appt->time ?? '—' }}</td>
                                                <td>{{ $appt->doctorRelation?->name ?? '—' }}</td>
                                                <td>
                                                    @if($appt->appointment_status)
                                                        <span class="badge bg-primary">{{ ucfirst($appt->appointment_status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($appt->visit_status)
                                                        <span class="badge {{ $appt->visit_status_badge }}">{{ $appt->visit_status_label }}</span>
                                                    @endif
                                                </td>
                                                <td><small class="text-muted">{{ ucfirst($appt->source ?? '') }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ PRESCRIPTIONS ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-prescriptions">
                        @if($patient->prescriptions->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-prescription', 'message' => 'No prescriptions found for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rx No</th>
                                            <th>Date</th>
                                            <th>Doctor</th>
                                            <th>Type</th>
                                            <th>ICD-10</th>
                                            <th>Findings</th>
                                            <th>Next Visit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->prescriptions as $rx)
                                            <tr>
                                                <td><span class="fw-semibold text-success">{{ $rx->prescription_no }}</span></td>
                                                <td class="text-nowrap">{{ $rx->date?->format('d M Y') ?? '—' }}</td>
                                                <td>{{ $rx->doctor?->name ?? '—' }}</td>
                                                <td>
                                                    @if($rx->type)
                                                        <span class="badge {{ $rx->type === 'opd' ? 'bg-primary' : 'bg-info text-dark' }}">
                                                            {{ strtoupper($rx->type) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($rx->icd10_code)
                                                        <span class="badge bg-secondary">{{ $rx->icd10_code }}</span>
                                                        <div class="small text-muted mt-1">{{ Str::limit($rx->icd10_description, 30) }}</div>
                                                    @endif
                                                </td>
                                                <td><span class="text-muted small">{{ Str::limit($rx->findings, 50) }}</span></td>
                                                <td class="text-nowrap"><small class="text-muted">{{ $rx->next_visit?->format('d M Y') }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ PHARMACY ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-pharmacy">
                        @if($patient->pharmacyTransactions->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-pills', 'message' => 'No pharmacy records found for this patient.'])
                        @else
                            @php
                                $phTotal    = $patient->pharmacyTransactions->sum('total_amount');
                                $phDiscount = $patient->pharmacyTransactions->sum('discount_amount');
                                $phPaid     = $patient->pharmacyTransactions->sum('paid_amount');
                            @endphp
                            <div class="row g-3 mb-4">
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Total Billed</div>
                                        <div class="fw-bold fs-5">{{ number_format($phTotal, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Discount</div>
                                        <div class="fw-bold fs-5 text-warning">{{ number_format($phDiscount, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Total Paid</div>
                                        <div class="fw-bold fs-5 text-success">{{ number_format($phPaid, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Transaction No</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Drugs</th>
                                            <th>Total</th>
                                            <th>Discount</th>
                                            <th>Paid</th>
                                            <th>Method</th>
                                            <th>Payment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->pharmacyTransactions as $pt)
                                            <tr>
                                                <td><span class="fw-semibold">{{ $pt->transaction_no }}</span></td>
                                                <td class="text-muted text-nowrap small">{{ $pt->created_at?->format('d M Y') }}</td>
                                                <td><span class="badge {{ $pt->type_badge_class }}">{{ $pt->type_label }}</span></td>
                                                <td>{{ $pt->drug_count }}</td>
                                                <td>{{ number_format($pt->total_amount, 2) }}</td>
                                                <td class="text-warning">{{ number_format($pt->discount_amount, 2) }}</td>
                                                <td class="text-success fw-semibold">{{ number_format($pt->paid_amount, 2) }}</td>
                                                <td><small class="text-muted">{{ ucfirst($pt->payment_method ?? '') }}</small></td>
                                                <td>
                                                    <span class="badge {{ $pt->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ ucfirst($pt->payment_status ?? '—') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ LAB / RADIOLOGY ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-lab">
                        @if($patient->labOrders->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-flask', 'message' => 'No lab or radiology orders found for this patient.'])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order No</th>
                                            <th>Date & Time</th>
                                            <th>Type</th>
                                            <th>Doctor</th>
                                            <th>Priority</th>
                                            <th>Source</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->labOrders as $lab)
                                            @php
                                                $pClass = match(strtolower($lab->priority ?? '')) {
                                                    'urgent', 'stat' => 'bg-danger',
                                                    'routine'        => 'bg-secondary',
                                                    default          => 'bg-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td><span class="fw-semibold">{{ $lab->order_number }}</span></td>
                                                <td class="text-nowrap">{{ $lab->datetime?->format('d M Y H:i') ?? '—' }}</td>
                                                <td>
                                                    <span class="badge {{ $lab->type === 'pathology' ? 'bg-danger' : 'bg-info text-dark' }}">
                                                        {{ ucfirst($lab->type ?? '—') }}
                                                    </span>
                                                </td>
                                                <td>{{ $lab->doctor?->name ?? '—' }}</td>
                                                <td>
                                                    @if($lab->priority)
                                                        <span class="badge {{ $pClass }}">{{ ucfirst($lab->priority) }}</span>
                                                    @endif
                                                </td>
                                                <td><small class="text-muted">{{ ucfirst($lab->source ?? '') }}</small></td>
                                                <td><span class="text-muted small">{{ Str::limit($lab->remarks, 40) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ═══════════════ BILLING ═══════════════ --}}
                    <div class="tab-pane fade" id="tab-billing">
                        @if($patient->transactions->isEmpty())
                            @include('patients._empty_state', ['icon' => 'fa-file-invoice-dollar', 'message' => 'No billing records found for this patient.'])
                        @else
                            @php
                                $billTotal = $patient->transactions->sum('net_amount');
                                $billPaid  = $patient->transactions->where('status', 'paid')->sum('net_amount');
                                $billDue   = $billTotal - $billPaid;
                            @endphp
                            <div class="row g-3 mb-4">
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Total Billed</div>
                                        <div class="fw-bold fs-5">{{ number_format($billTotal, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Total Paid</div>
                                        <div class="fw-bold fs-5 text-success">{{ number_format($billPaid, 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="border rounded-3 p-3 bg-light text-center">
                                        <div class="text-muted small mb-1">Balance Due</div>
                                        <div class="fw-bold fs-5 {{ $billDue > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($billDue, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Section</th>
                                            <th>Amount</th>
                                            <th>Net Amount</th>
                                            <th>Payment Via</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patient->transactions as $txn)
                                            @php
                                                $txnClass = match($txn->status ?? '') {
                                                    'paid'    => 'bg-success',
                                                    'partial' => 'bg-warning text-dark',
                                                    default   => 'bg-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td><span class="fw-semibold">{{ $txn->invoice_no }}</span></td>
                                                <td class="text-nowrap">{{ $txn->payment_date?->format('d M Y') ?? '—' }}</td>
                                                <td><small class="text-muted">{{ ucfirst($txn->type ?? '') }}</small></td>
                                                <td><small class="text-muted">{{ ucfirst($txn->section ?? '') }}</small></td>
                                                <td>{{ number_format($txn->amount ?? 0, 2) }}</td>
                                                <td class="fw-semibold">{{ number_format($txn->net_amount ?? 0, 2) }}</td>
                                                <td><small class="text-muted">{{ ucfirst($txn->payment_via ?? '') }}</small></td>
                                                <td><span class="badge {{ $txnClass }}">{{ ucfirst($txn->status ?? '—') }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>{{-- end tab-content --}}
            </div>{{-- end card-body --}}
        </div>{{-- end main card --}}

        {{-- Danger Zone --}}
        <div class="card mt-3 border-danger border-opacity-25">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-semibold text-danger mb-1">Delete Patient</div>
                    <small class="text-muted">Permanently removes the patient and all linked records. This cannot be undone.</small>
                </div>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}"
                      onsubmit="return confirm('Permanently delete {{ addslashes($patient->patient_name) }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-trash me-1"></i> Delete Patient
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection
