@extends('backend.layouts.master')

@section('title', 'Front Desk')

@section('content')
    <div class="container-fluid py-3">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Top Header --}}
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h1 class="app-page-title mb-0">Front Desk</h1>
                <div class="text-muted small mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">

                {{-- Global Patient Search --}}
                <div class="fd-search-wrap" id="fdSearchWrap">
                    <i class="bi bi-search fd-search-icon"></i>
                    <input type="text" id="fdPatientSearch" class="fd-search-input"
                        placeholder="Search by patient name / ID" autocomplete="off">
                    <ul class="fd-search-dropdown" id="fdSearchDropdown"></ul>
                </div>

                <button class="btn btn-outline-secondary btn-sm px-3" type="button"
                    data-bs-toggle="modal" data-bs-target="#hcCheckinModal">
                    <i class="bi bi-qr-code-scan me-1"></i> Scan QR
                </button>

                <button class="btn btn-outline-success btn-sm" type="button" onclick="window.location.href='tel:'">
                    <i class="bi bi-telephone-fill"></i>
                </button>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="fd-toolbar mb-4">
            <div class="fd-toolbar-inner">
                <span class="fd-toolbar-label">Registration</span>

                <a class="fd-action-btn fd-action-primary"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('front_desk.registration.create') }}"
                    data-title="Patient Onboarding">
                    <i class="bi bi-person-plus-fill"></i><span>New Patient</span>
                </a>
                <a class="fd-action-btn fd-action-success"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('front_desk.quickreg.create') }}"
                    data-title="Quick Registration">
                    <i class="bi bi-lightning-charge-fill"></i><span>Quick Reg</span>
                </a>
                <a class="fd-action-btn fd-action-warning"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('front_desk.walkintoken.create') }}"
                    data-title="Walk-in Token">
                    <i class="bi bi-ticket-perforated-fill"></i><span>Walk-in Token</span>
                </a>
                <a class="fd-action-btn fd-action-danger"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('front_desk.er_registration') }}"
                    data-title="ER Register">
                    <i class="bi bi-activity"></i><span>ER Register</span>
                </a>

                <div class="fd-toolbar-divider"></div>
                <span class="fd-toolbar-label">Tools</span>

                <a class="fd-action-btn fd-action-info"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('front_desk.visitor.create') }}"
                    data-title="Add Visitor">
                    <i class="bi bi-person-walking"></i><span>Add Visitor</span>
                </a>
                <button type="button" class="fd-action-btn fd-action-dark"
                    data-bs-toggle="modal" data-bs-target="#hcCheckinModal">
                    <i class="bi bi-credit-card-fill"></i><span>HC Check-in</span>
                </button>
                <a class="fd-action-btn fd-action-amber"
                    data-size="xl" data-ajax-popup="true"
                    data-url="{{ route('amb.requests.create') }}"
                    data-title="Add Ambulance">
                    <i class="bi bi-truck-front-fill"></i><span>Ambulance</span>
                </a>
            </div>
        </div>

        {{-- Stats Row --}}
        @php
            $totalPatients = $opdPatients->count() + $ipdPatients->count() + $erPatients->count();
            $newAmb = $ambulanceRequests->where('status','NEW')->count();
        @endphp
        <div class="row g-3 mb-4">

            {{-- Today's Registrations --}}
            <div class="col-6 col-lg d-flex">
                <div class="stat-card stat-card-blue w-100">
                    <div class="stat-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $totalPatients }}</div>
                        <div class="stat-label">Today's Patients</div>
                        <div class="stat-chips mt-2">
                            <span class="stat-chip stat-chip-blue">{{ $opdPatients->count() }} OPD</span>
                            <span class="stat-chip stat-chip-green">{{ $ipdPatients->count() }} IPD</span>
                            <span class="stat-chip stat-chip-red">{{ $erPatients->count() }} ER</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Appointments --}}
            <div class="col-6 col-lg d-flex">
                <div class="stat-card stat-card-purple w-100">
                    <div class="stat-icon"><i class="bi bi-calendar2-check-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $appointments->count() }}</div>
                        <div class="stat-label">Appointments</div>
                        <div class="stat-chips mt-2">
                            @if($inConsultationCount)
                                <span class="stat-chip stat-chip-purple">{{ $inConsultationCount }} in consult</span>
                            @else
                                <span class="stat-chip" style="background:#f3f4f6;color:#6b7280;">Today's schedule</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ER Critical --}}
            <div class="col-6 col-lg d-flex">
                <div class="stat-card stat-card-red w-100">
                    <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $ercriticalCount }}</div>
                        <div class="stat-label">ER Critical</div>
                        <div class="stat-chips mt-2">
                            <span class="stat-chip stat-chip-red">Active today</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ambulance --}}
            <div class="col-6 col-lg d-flex">
                <div class="stat-card stat-card-amber w-100">
                    <div class="stat-icon"><i class="bi bi-truck-front-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $ambulanceRequests->count() }}</div>
                        <div class="stat-label">Ambulance</div>
                        <div class="stat-chips mt-2">
                            @if($newAmb)
                                <span class="stat-chip stat-chip-orange">{{ $newAmb }} pending</span>
                            @else
                                <span class="stat-chip" style="background:#f3f4f6;color:#6b7280;">Requests today</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visitors --}}
            <div class="col-6 col-lg d-flex">
                <div class="stat-card stat-card-teal w-100">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $todayVisitorCount }}</div>
                        <div class="stat-label">Visitors</div>
                        <div class="stat-chips mt-2">
                            <span class="stat-chip" style="background:#ccfbf1;color:#0f766e;">Checked in today</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Registered Patients --}}
        <div class="card border-0 shadow-sm mb-4 reg-patients-card">
            <div class="card-header bg-white border-bottom px-3 pt-3 pb-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="reg-header-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 mb-0">Today's Registered Patients</div>
                            <div class="text-muted small">{{ now()->format('l, d F Y') }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Total:</span>
                        <span class="fw-bold">{{ $opdPatients->count() + $ipdPatients->count() + $erPatients->count() }}</span>
                    </div>
                </div>
                <ul class="nav reg-tab-nav border-bottom-0 gap-1" id="regPatientsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="reg-tab-btn active" id="tab-opd-btn" data-bs-toggle="tab" data-bs-target="#tab-opd" role="tab" aria-selected="true">
                            <span class="reg-tab-icon reg-tab-icon-opd"><i class="bi bi-clipboard2-pulse"></i></span>
                            <span class="reg-tab-label">OPD</span>
                            <span class="reg-tab-count reg-tab-count-opd">{{ $opdPatients->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="reg-tab-btn" id="tab-ipd-btn" data-bs-toggle="tab" data-bs-target="#tab-ipd" role="tab" aria-selected="false">
                            <span class="reg-tab-icon reg-tab-icon-ipd"><i class="bi bi-hospital"></i></span>
                            <span class="reg-tab-label">IPD</span>
                            <span class="reg-tab-count reg-tab-count-ipd">{{ $ipdPatients->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="reg-tab-btn" id="tab-er-btn" data-bs-toggle="tab" data-bs-target="#tab-er" role="tab" aria-selected="false">
                            <span class="reg-tab-icon reg-tab-icon-er"><i class="bi bi-activity"></i></span>
                            <span class="reg-tab-label">ER</span>
                            <span class="reg-tab-count reg-tab-count-er">{{ $erPatients->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content">

                    {{-- OPD Tab --}}
                    <div class="tab-pane fade show active" id="tab-opd" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table reg-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width:44px;">#</th>
                                        <th>Token No</th>
                                        <th>Patient Name</th>
                                        <th>MRN</th>
                                        <th>Contact</th>
                                        <th>Doctor</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($opdPatients as $i => $opd)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                            <td>
                                                <span class="fw-semibold text-primary font-monospace">{{ $opd->token_no ?? '—' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="reg-avatar reg-avatar-opd">
                                                        {{ strtoupper(substr($opd->patient->patient_name ?? 'P', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ $opd->patient->patient_name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td><span class="text-muted font-monospace small">{{ $opd->patient->mrn ?? '—' }}</span></td>
                                            <td class="text-muted">{{ $opd->patient->mobileno ?? '—' }}</td>
                                            <td>{{ $opd->doctor->name ?? '—' }}</td>
                                            <td><span class="text-muted small">{{ $opd->department->name ?? '—' }}</span></td>
                                            <td>
                                                @php
                                                    $sc = match($opd->status) {
                                                        'Registered' => 'primary',
                                                        'Pending'    => 'warning',
                                                        default      => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge rounded-pill text-bg-{{ $sc }}-subtle border border-{{ $sc }}-subtle text-{{ $sc }}">
                                                    {{ $opd->status ?? 'Registered' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    @if ($opd->token_no)
                                                        <a href="{{ route('front_desk.walkintoken.pdf', $opd->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            title="Print Token">
                                                            <i class="bi bi-ticket-perforated"></i>
                                                        </a>
                                                    @endif
                                                    @if ($opd->patient)
                                                        <a href="{{ route('health-card.show', $opd->patient->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-dark"
                                                            title="Print Health Card">
                                                            <i class="bi bi-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('opd-patients.edit', $opd->id) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('opd-patients.show', $opd->id) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="reg-empty-state">
                                                    <i class="bi bi-clipboard2-pulse reg-empty-icon text-primary"></i>
                                                    <div class="fw-semibold mt-2">No OPD patients today</div>
                                                    <div class="text-muted small">Outpatient registrations will appear here</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- IPD Tab --}}
                    <div class="tab-pane fade" id="tab-ipd" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table reg-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width:44px;">#</th>
                                        <th>IPD No</th>
                                        <th>Patient Name</th>
                                        <th>MRN</th>
                                        <th>Contact</th>
                                        <th>Doctor</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ipdPatients as $i => $ipd)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                            <td>
                                                <span class="fw-semibold text-success font-monospace">{{ $ipd->ipd_no ?? '—' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="reg-avatar reg-avatar-ipd">
                                                        {{ strtoupper(substr($ipd->patient->patient_name ?? 'P', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ $ipd->patient->patient_name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td><span class="text-muted font-monospace small">{{ $ipd->patient->mrn ?? '—' }}</span></td>
                                            <td class="text-muted">{{ $ipd->patient->mobileno ?? '—' }}</td>
                                            <td>{{ $ipd->doctor->name ?? '—' }}</td>
                                            <td><span class="text-muted small">{{ $ipd->department->name ?? '—' }}</span></td>
                                            <td>
                                                @php
                                                    $sc = match($ipd->status) {
                                                        'Admitted'   => 'success',
                                                        'Discharged' => 'secondary',
                                                        default      => 'primary',
                                                    };
                                                @endphp
                                                <span class="badge rounded-pill text-bg-{{ $sc }}-subtle border border-{{ $sc }}-subtle text-{{ $sc }}">
                                                    {{ $ipd->status ?? 'Admitted' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    @if ($ipd->patient)
                                                        <a href="{{ route('health-card.show', $ipd->patient->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-dark"
                                                            title="Print Health Card">
                                                            <i class="bi bi-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('ipd-patients.edit', $ipd->id) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('ipd-patients.show', $ipd->id) }}"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if (!in_array($ipd->status, ['Discharged', 'Cancelled']))
                                                        <form method="POST"
                                                            action="{{ route('ipd-patients.convert-to-opd', $ipd->id) }}"
                                                            onsubmit="return confirm('Move {{ $ipd->patient->patient_name ?? 'this patient' }} from IPD to OPD?\n\nThis will cancel the IPD record and release the bed.')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-info"
                                                                title="Convert to OPD">
                                                                <i class="bi bi-arrow-left-right"></i> OPD
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="reg-empty-state">
                                                    <i class="bi bi-hospital reg-empty-icon text-success"></i>
                                                    <div class="fw-semibold mt-2">No IPD admissions today</div>
                                                    <div class="text-muted small">Inpatient admissions will appear here</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ER Tab --}}
                    <div class="tab-pane fade" id="tab-er" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table reg-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width:44px;">#</th>
                                        <th>Patient Name</th>
                                        <th>MRN</th>
                                        <th>Contact</th>
                                        <th>Arrival Time</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($erPatients as $i => $er)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="reg-avatar reg-avatar-er">
                                                        {{ strtoupper(substr($er->patient->patient_name ?? $er->name ?? 'P', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ $er->patient->patient_name ?? $er->name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td><span class="text-muted font-monospace small">{{ $er->patient->mrn ?? '—' }}</span></td>
                                            <td class="text-muted">{{ $er->patient->mobileno ?? $er->contact_no ?? '—' }}</td>
                                            <td>
                                                @if($er->arrival_time)
                                                    <div class="fw-medium">{{ \Carbon\Carbon::parse($er->arrival_time)->format('h:i A') }}</div>
                                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($er->arrival_time)->format('d M') }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $pc = match($er->priority ?? '') {
                                                        'CRITICAL' => 'danger',
                                                        'HIGH'     => 'warning',
                                                        default    => 'success',
                                                    };
                                                @endphp
                                                <span class="badge rounded-pill text-bg-{{ $pc }}-subtle border border-{{ $pc }}-subtle text-{{ $pc }}">
                                                    <i class="bi bi-circle-fill me-1" style="font-size:6px;vertical-align:middle;"></i>{{ $er->priority ?? 'NORMAL' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $sc = match($er->status ?? '') {
                                                        'ACTIVE'     => 'primary',
                                                        'ADMITTED'   => 'success',
                                                        'DISCHARGED' => 'secondary',
                                                        'CANCELLED'  => 'danger',
                                                        default      => 'warning',
                                                    };
                                                @endphp
                                                <span class="badge rounded-pill text-bg-{{ $sc }}-subtle border border-{{ $sc }}-subtle text-{{ $sc }}">
                                                    {{ $er->status ?? 'PENDING' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ route('front_desk.er_registration.edit', $er->id) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if ($er->patient)
                                                        <a href="{{ route('health-card.show', $er->patient->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-dark"
                                                            title="Print Health Card">
                                                            <i class="bi bi-credit-card"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="reg-empty-state">
                                                    <i class="bi bi-activity reg-empty-icon text-danger"></i>
                                                    <div class="fw-semibold mt-2">No ER patients today</div>
                                                    <div class="text-muted small">Emergency registrations will appear here</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Message Inbox --}}
        {{-- <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-chat-left-dots"></i>
                        </span>
                        <div class="fw-semibold">Message Inbox</div>
                    </div>

                    <div class="input-group w-100 w-md-auto inbox-search">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search here">
                    </div>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 280px;">Message</th>
                                <th class="text-muted" style="min-width: 120px;">Time</th>
                                <th class="text-end" style="min-width: 320px;">Reply</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $messages = [
                                    ['text' => 'Hey any one there!', 'time' => '1 min ago'],
                                    ['text' => 'Is doctor available...?', 'time' => '1 min ago'],
                                    ['text' => 'Is ambulance available...?', 'time' => '1 min ago'],
                                    ['text' => 'Hey any one there!', 'time' => '1 min ago'],
                                ];
                            @endphp

                            @foreach ($messages as $m)
                                <tr>
                                    <td class="fw-medium">{{ $m['text'] }}</td>
                                    <td class="text-muted">{{ $m['time'] }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                            <button class="btn btn-primary btn-sm px-3 w-100 w-sm-auto">
                                                <i class="bi bi-globe2 me-1"></i> In-App
                                            </button>
                                            <button class="btn btn-success btn-sm px-3 w-100 w-sm-auto">
                                                <i class="bi bi-chat-dots me-1"></i> SMS
                                            </button>
                                            <button class="btn btn-success-subtle btn-sm px-3 border w-100 w-sm-auto">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                            <button class="btn btn-primary-subtle btn-sm px-3 border w-100 w-sm-auto">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($messages) === 0)
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No messages found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}

        {{-- Ambulance Requests --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-truck"></i>
                        </span>
                        <div class="fw-semibold">Today's Ambulance Requests</div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                            {{ $ambulanceRequests->count() }}
                        </span>
                    </div>
                    <a href="{{ route('amb.requests.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table reg-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:44px;">#</th>
                                <th>Patient Name</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Pickup Location</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ambulanceRequests as $i => $req)
                                @php
                                    $pc = match($req->priority ?? '') {
                                        'CRITICAL' => 'danger',
                                        'HIGH'     => 'warning',
                                        'LOW'      => 'secondary',
                                        default    => 'success',
                                    };
                                    $sc = match($req->status ?? '') {
                                        'ASSIGNED'  => 'primary',
                                        'COMPLETED' => 'success',
                                        'CANCELLED' => 'danger',
                                        default     => 'warning',
                                    };
                                    $tc = match($req->request_type ?? '') {
                                        'EMERGENCY' => 'danger',
                                        'TRANSFER'  => 'primary',
                                        'SCHEDULED' => 'success',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="reg-avatar" style="background:#fef3c7;color:#92400e;">
                                                {{ strtoupper(substr($req->patient->patient_name ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $req->patient->patient_name ?? '—' }}</div>
                                                @if($req->patient?->mrn)
                                                    <div class="text-muted font-monospace" style="font-size:11px;">{{ $req->patient->mrn }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $req->contact_no ?? $req->patient?->mobileno ?? '—' }}</td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-{{ $tc }}-subtle border border-{{ $tc }}-subtle text-{{ $tc }}">
                                            {{ $req->request_type ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-{{ $pc }}-subtle border border-{{ $pc }}-subtle text-{{ $pc }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size:6px;vertical-align:middle;"></i>{{ $req->priority ?? 'NORMAL' }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $req->pick_up_location ?? '—' }}</td>
                                    <td>
                                        @if($req->date)
                                            <div class="fw-medium">{{ \Carbon\Carbon::parse($req->date)->format('d M') }}</div>
                                            <div class="text-muted small">{{ $req->time ? \Carbon\Carbon::parse($req->time)->format('h:i A') : '—' }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-{{ $sc }}-subtle border border-{{ $sc }}-subtle text-{{ $sc }}">
                                            {{ $req->status ?? 'NEW' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('amb.requests.show', $req->id) }}"
                                            class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($req->status === 'NEW')
                                            <a href="{{ route('amb.trips.assignForm', $req->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Dispatch">
                                                <i class="bi bi-send"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="reg-empty-state">
                                            <i class="bi bi-truck reg-empty-icon text-warning"></i>
                                            <div class="fw-semibold mt-2">No ambulance requests today</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Appointment Info --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <i class="bi bi-calendar2-week"></i>
                        </span>
                        <div class="fw-semibold">Today's Appointments</div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                            {{ $appointments->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table reg-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:44px;">#</th>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Doctor</th>
                                <th>Time Slot</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $i => $appt)
                                @php
                                    $vs = $appt->visit_status ?? 'booked';
                                    $statusColor = match($vs) {
                                        'booked'          => 'primary',
                                        'checked_in'      => 'info',
                                        'waiting'         => 'warning',
                                        'in_consultation' => 'success',
                                        'completed'       => 'success',
                                        'closed'          => 'secondary',
                                        'cancelled'       => 'danger',
                                        'referred'        => 'warning',
                                        'converted_to_er' => 'danger',
                                        default           => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="reg-avatar reg-avatar-opd">
                                                {{ strtoupper(substr($appt->patient->patient_name ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $appt->patient->patient_name ?? '—' }}</div>
                                                @if($appt->patient?->mrn)
                                                    <div class="text-muted font-monospace" style="font-size:11px;">{{ $appt->patient->mrn }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted small">{{ $appt->patient->mobileno ?? '—' }}</td>
                                    <td>{{ $appt->doctorRelation->name ?? '—' }}</td>
                                    <td>
                                        @if($appt->slot_time_from && $appt->slot_time_to)
                                            <span class="badge bg-light text-dark border font-monospace">
                                                {{ \Carbon\Carbon::parse($appt->slot_time_from)->format('h:i A') }}
                                                – {{ \Carbon\Carbon::parse($appt->slot_time_to)->format('h:i A') }}
                                            </span>
                                        @elseif($appt->time)
                                            <span class="badge bg-light text-dark border font-monospace">
                                                {{ \Carbon\Carbon::parse($appt->time)->format('h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small text-uppercase">{{ $appt->source ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-{{ $statusColor }}-subtle border border-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                            {{ $appt->visit_status_label }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary appt-view-btn"
                                                title="View Details"
                                                data-patient="{{ $appt->patient->patient_name ?? '—' }}"
                                                data-mrn="{{ $appt->patient->mrn ?? '—' }}"
                                                data-contact="{{ $appt->patient->mobileno ?? '—' }}"
                                                data-doctor="{{ $appt->doctorRelation->name ?? '—' }}"
                                                data-date="{{ $appt->date ? \Carbon\Carbon::parse($appt->date)->format('d M Y') : '—' }}"
                                                data-slot="{{ $appt->slot_time_from ? \Carbon\Carbon::parse($appt->slot_time_from)->format('h:i A').' – '.\Carbon\Carbon::parse($appt->slot_time_to)->format('h:i A') : ($appt->time ? \Carbon\Carbon::parse($appt->time)->format('h:i A') : '—') }}"
                                                data-source="{{ $appt->source ?? '—' }}"
                                                data-status="{{ $appt->visit_status_label }}"
                                                data-status-color="{{ $statusColor }}"
                                                data-priority="{{ $appt->priority ?? '—' }}"
                                                data-message="{{ $appt->message ?? '' }}"
                                                data-vital-url="{{ route('front_desk.vitals.create') }}?patient_id={{ $appt->patient_id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="reg-empty-state">
                                            <i class="bi bi-calendar2-week reg-empty-icon text-primary"></i>
                                            <div class="fw-semibold mt-2">No appointments today</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Emergency + No-Show Appointments --}}
        <div class="row g-3 mt-1">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </span>
                                <div class="fw-semibold">Today's Emergency</div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                    {{ $erPatients->count() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table reg-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width:44px;">#</th>
                                        <th>Patient Name</th>
                                        <th>Age</th>
                                        <th>Arrival Time</th>
                                        <th>Discount</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($erPatients as $i => $er)
                                        @php
                                            $pc = match($er->priority ?? '') {
                                                'CRITICAL' => 'danger',
                                                'HIGH'     => 'warning',
                                                default    => 'success',
                                            };
                                            $sc = match($er->status ?? '') {
                                                'ACTIVE'     => 'primary',
                                                'ADMITTED'   => 'success',
                                                'DISCHARGED' => 'secondary',
                                                'CANCELLED'  => 'danger',
                                                default      => 'warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="reg-avatar reg-avatar-er">
                                                        {{ strtoupper(substr($er->patient->patient_name ?? $er->name ?? 'P', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $er->patient->patient_name ?? $er->name ?? '—' }}</div>
                                                        @if($er->patient?->mrn)
                                                            <div class="text-muted font-monospace" style="font-size:11px;">{{ $er->patient->mrn }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-muted">{{ $er->age ?? '—' }}</td>
                                            <td>
                                                @if($er->arrival_time)
                                                    <div class="fw-medium">{{ \Carbon\Carbon::parse($er->arrival_time)->format('h:i A') }}</div>
                                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($er->arrival_time)->format('d M') }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $er->discount_type ?? '—' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-{{ $pc }}-subtle border border-{{ $pc }}-subtle text-{{ $pc }}">
                                                    <i class="bi bi-circle-fill me-1" style="font-size:6px;vertical-align:middle;"></i>{{ $er->priority ?? 'NORMAL' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-{{ $sc }}-subtle border border-{{ $sc }}-subtle text-{{ $sc }}">
                                                    {{ $er->status ?? 'PENDING' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger er-view-btn"
                                                        title="View Details"
                                                        data-patient="{{ $er->patient->patient_name ?? $er->name ?? '—' }}"
                                                        data-mrn="{{ $er->patient->mrn ?? '—' }}"
                                                        data-contact="{{ $er->patient->mobileno ?? $er->contact_no ?? '—' }}"
                                                        data-age="{{ $er->age ?? '—' }}"
                                                        data-gender="{{ $er->gender ?? '—' }}"
                                                        data-blood="{{ $er->blood_group ?? '—' }}"
                                                        data-arrival="{{ $er->arrival_time ? \Carbon\Carbon::parse($er->arrival_time)->format('d M Y, h:i A') : '—' }}"
                                                        data-priority="{{ $er->priority ?? 'NORMAL' }}"
                                                        data-priority-color="{{ $pc }}"
                                                        data-status="{{ $er->status ?? 'PENDING' }}"
                                                        data-status-color="{{ $sc }}"
                                                        data-discount="{{ $er->discount_type ?? '—' }}"
                                                        data-third-party="{{ $er->third_party_name ?? '' }}"
                                                        data-description="{{ $er->description ?? '' }}"
                                                        data-case="{{ $er->case_id ?? '—' }}"
                                                        data-edit-url="{{ route('front_desk.er_registration.edit', $er->id) }}">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="{{ route('front_desk.er_registration.edit', $er->id) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="reg-empty-state">
                                                    <i class="bi bi-activity reg-empty-icon text-danger"></i>
                                                    <div class="fw-semibold mt-2">No ER patients today</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark-subtle text-dark border border-dark-subtle">
                                    <i class="bi bi-person-x"></i>
                                </span>
                                <div class="fw-semibold">No-Show Appointments</div>
                                @if($noShowAppointments->count())
                                    <span class="badge bg-dark-subtle text-dark border rounded-pill">{{ $noShowAppointments->count() }}</span>
                                @endif
                            </div>
                            <span class="text-muted" style="font-size:10px;">Last 7 days</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table reg-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Patient</th>
                                        <th>Doctor</th>
                                        <th class="text-end pe-3">Date / Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($noShowAppointments as $appt)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-medium">{{ $appt->patient->patient_name ?? '—' }}</div>
                                                <div class="text-muted small">{{ $appt->patient->mobileno ?? '' }}</div>
                                            </td>
                                            <td class="text-muted small">{{ $appt->doctorRelation->name ?? '—' }}</td>
                                            <td class="text-end pe-3">
                                                @php
                                                    $apptDate = \Carbon\Carbon::parse($appt->date);
                                                    $isToday = $apptDate->isToday();
                                                    $slotTime = $appt->slot_time_from
                                                        ? \Carbon\Carbon::parse($appt->slot_time_from)->format('h:i A')
                                                        : ($appt->time ? \Carbon\Carbon::parse($appt->time)->format('h:i A') : null);
                                                @endphp
                                                <div>
                                                    <span class="badge rounded-pill {{ $isToday ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-dark-subtle text-dark border' }} font-monospace" style="font-size:10px;">
                                                        {{ $isToday ? 'Today' : $apptDate->format('d M') }}
                                                    </span>
                                                </div>
                                                @if($slotTime)
                                                    <div class="text-muted" style="font-size:11px;">{{ $slotTime }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted small">
                                                <i class="bi bi-check2-circle text-success fs-4 d-block mb-2 opacity-50"></i>
                                                No no-shows in the last 7 days
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Visitor List --}}
        <div class="card border-0 shadow-sm mt-4" id="visitorListCard">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-people"></i>
                        </span>
                        <div class="fw-semibold">Today's Visitor List</div>
                        <span id="visitorCountBadge" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">0</span>
                    </div>
                    <button type="button" id="visitorRefreshBtn" class="btn btn-sm btn-outline-secondary" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body pt-2 p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:44px;">#</th>
                                <th>Code</th>
                                <th>Visit Date</th>
                                <th>Visit Time</th>
                                <th>Patient Name</th>
                                <th>Visitor Name</th>
                                <th>Contact</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th class="text-end pe-3">Qty</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="visitorListBody">
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Loading…
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    {{-- Health Card Check-in Modal --}}
    @include('patients._health_card_checkin_modal')

    {{-- Appointment Detail Modal --}}
    <div class="modal fade" id="apptDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Appointment Details</h5>
                        <div class="text-muted small mt-1" id="adm-date-line"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">

                    {{-- Patient Row --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:#f8fafc;">
                        <div class="detail-avatar detail-avatar-blue" id="adm-avatar"></div>
                        <div>
                            <div class="fw-bold fs-6" id="adm-patient"></div>
                            <div class="text-muted small font-monospace" id="adm-mrn"></div>
                            <div class="text-muted small" id="adm-contact"></div>
                        </div>
                        <div class="ms-auto text-end">
                            <span class="badge rounded-pill" id="adm-status-badge"></span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-person-badge me-1"></i>Doctor</div>
                                <div class="detail-value" id="adm-doctor"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-calendar3 me-1"></i>Date</div>
                                <div class="detail-value" id="adm-appt-date"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-clock me-1"></i>Time Slot</div>
                                <div class="detail-value font-monospace" id="adm-slot"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-globe2 me-1"></i>Source</div>
                                <div class="detail-value text-uppercase" id="adm-source"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-flag me-1"></i>Priority</div>
                                <div class="detail-value" id="adm-priority"></div>
                            </div>
                        </div>
                    </div>

                    <div id="adm-message-wrap" class="detail-field mb-3" style="display:none;">
                        <div class="detail-label"><i class="bi bi-chat-text me-1"></i>Notes / Message</div>
                        <div class="detail-value" id="adm-message" style="white-space:pre-line;"></div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <a href="#" id="adm-vital-btn" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-heart-pulse me-1"></i>Vital Check
                    </a>
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ER Patient Detail Modal --}}
    <div class="modal fade" id="erDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0 pt-4 px-4" style="background:#fff1f2;border-radius:12px 12px 0 0;">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-danger">Emergency Details</h5>
                        <div class="text-muted small mt-1" id="erd-arrival-line"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">

                    {{-- Patient Row --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:#fff1f2;">
                        <div class="detail-avatar detail-avatar-red" id="erd-avatar"></div>
                        <div>
                            <div class="fw-bold fs-6" id="erd-patient"></div>
                            <div class="text-muted small font-monospace" id="erd-mrn"></div>
                            <div class="text-muted small" id="erd-contact"></div>
                        </div>
                        <div class="ms-auto text-end d-flex flex-column gap-1 align-items-end">
                            <span class="badge rounded-pill" id="erd-priority-badge"></span>
                            <span class="badge rounded-pill" id="erd-status-badge"></span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-calendar-date me-1"></i>Age</div>
                                <div class="detail-value" id="erd-age"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-gender-ambiguous me-1"></i>Gender</div>
                                <div class="detail-value" id="erd-gender"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-droplet me-1"></i>Blood Group</div>
                                <div class="detail-value fw-semibold text-danger" id="erd-blood"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-clock-history me-1"></i>Arrival</div>
                                <div class="detail-value" id="erd-arrival"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-credit-card me-1"></i>Discount</div>
                                <div class="detail-value" id="erd-discount"></div>
                            </div>
                        </div>
                        <div id="erd-third-party-wrap" class="col-12" style="display:none;">
                            <div class="detail-field">
                                <div class="detail-label"><i class="bi bi-building me-1"></i>Third Party</div>
                                <div class="detail-value" id="erd-third-party"></div>
                            </div>
                        </div>
                    </div>

                    <div id="erd-desc-wrap" class="detail-field mb-3" style="display:none;">
                        <div class="detail-label"><i class="bi bi-file-medical me-1"></i>Chief Complaint</div>
                        <div class="detail-value" id="erd-description" style="white-space:pre-line;"></div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <a href="#" id="erd-edit-btn" class="btn btn-warning btn-sm px-4">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <button type="button" class="btn btn-light btn-sm px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if (session('view_token'))
        <script>
            window.open("{{ session('view_token') }}", "_blank");
        </script>
    @endif
    <style>
        /* ── Global patient search ─────────────────────────────── */
        .fd-search-wrap {
            position: relative;
            flex: 1 1 220px;
            max-width: 360px;
        }

        .fd-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
            pointer-events: none;
        }

        .fd-search-input {
            width: 100%;
            padding: 7px 12px 7px 34px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13.5px;
            background: #f9fafb;
            color: #111827;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .fd-search-input:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }

        .fd-search-input::placeholder { color: #b0b7c3; }

        .fd-search-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            list-style: none;
            margin: 0;
            padding: 4px 0;
            z-index: 1055;
            max-height: 320px;
            overflow-y: auto;
            display: none;
        }

        .fd-search-dropdown.open { display: block; }

        .fd-search-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            cursor: pointer;
            transition: background .12s;
            text-decoration: none;
        }

        .fd-search-item:hover { background: #f5f3ff; }

        .fd-search-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ede9fe;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .fd-search-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fd-search-meta {
            font-size: 11px;
            color: #9ca3af;
            white-space: nowrap;
        }

        .fd-search-empty {
            padding: 14px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }

        .fd-search-loading {
            padding: 12px 14px;
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Quick Actions toolbar ─────────────────────────────── */
        .fd-toolbar {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 10px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .fd-toolbar::-webkit-scrollbar { display: none; }

        .fd-toolbar-inner {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: max-content;
        }

        .fd-toolbar-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #b0b7c3;
            padding: 0 6px;
            white-space: nowrap;
            user-select: none;
        }

        .fd-toolbar-divider {
            width: 1px;
            height: 26px;
            background: #e5e7eb;
            flex-shrink: 0;
            margin: 0 6px;
        }

        .fd-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 13px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: 1.5px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: background .13s, box-shadow .13s, transform .1s;
            white-space: nowrap;
            line-height: 1.4;
        }
        .fd-action-btn i { font-size: 13px; }
        .fd-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,.10); text-decoration: none; }
        .fd-action-btn:active { transform: translateY(0); }

        .fd-action-primary { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .fd-action-primary:hover { background: #dbeafe; color: #1d4ed8; }

        .fd-action-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .fd-action-success:hover { background: #dcfce7; color: #15803d; }

        .fd-action-warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }
        .fd-action-warning:hover { background: #fef3c7; color: #b45309; }

        .fd-action-danger { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
        .fd-action-danger:hover { background: #ffe4e6; color: #be123c; }

        .fd-action-info { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
        .fd-action-info:hover { background: #e0f2fe; color: #0369a1; }

        .fd-action-dark { background: #f9fafb; color: #374151; border-color: #e5e7eb; }
        .fd-action-dark:hover { background: #f3f4f6; color: #111827; }

        .fd-action-amber { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
        .fd-action-amber:hover { background: #ffedd5; color: #c2410c; }

        /* ── Stat cards ────────────────────────────────────────── */
        .stat-card {
            border-radius: 16px;
            padding: 20px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1.5px solid transparent;
            min-height: 110px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-body { flex: 1; min-width: 0; }

        .stat-value {
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.03em;
        }

        .stat-label {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        /* Chip badges inside stat cards — single row, no wrap */
        .stat-chips {
            display: flex;
            flex-wrap: nowrap;
            gap: 4px;
            overflow: hidden;
        }
        .stat-chip {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .stat-chip-blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-chip-green  { background: #dcfce7; color: #15803d; }
        .stat-chip-red    { background: #fee2e2; color: #b91c1c; }
        .stat-chip-orange { background: #ffedd5; color: #c2410c; }
        .stat-chip-purple { background: #ede9fe; color: #7c3aed; }

        /* Colour tokens */
        .stat-card-blue   { background: #eff6ff; border-color: #bfdbfe; }
        .stat-card-blue   .stat-icon  { background: #dbeafe; color: #1d4ed8; }
        .stat-card-blue   .stat-value { color: #1e40af; }

        .stat-card-purple { background: #f5f3ff; border-color: #ddd6fe; }
        .stat-card-purple .stat-icon  { background: #ede9fe; color: #7c3aed; }
        .stat-card-purple .stat-value { color: #6d28d9; }

        .stat-card-red    { background: #fff1f2; border-color: #fecdd3; }
        .stat-card-red    .stat-icon  { background: #fee2e2; color: #dc2626; }
        .stat-card-red    .stat-value { color: #be123c; }

        .stat-card-amber  { background: #fff7ed; border-color: #fed7aa; }
        .stat-card-amber  .stat-icon  { background: #ffedd5; color: #c2410c; }
        .stat-card-amber  .stat-value { color: #c2410c; }

        .stat-card-teal   { background: #f0fdfa; border-color: #99f6e4; }
        .stat-card-teal   .stat-icon  { background: #ccfbf1; color: #0f766e; }
        .stat-card-teal   .stat-value { color: #0f766e; }

        /* ── Detail modal helpers ──────────────────────────────── */
        .detail-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            flex-shrink: 0;
        }
        .detail-avatar-blue { background: #dbeafe; color: #1d4ed8; }
        .detail-avatar-red  { background: #fee2e2; color: #dc2626; }

        .detail-field {
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
        }
        .detail-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }

        /* ── Existing styles below ─────────────────────────────── */
        .reg-dropdown {
            width: 100%;
            max-width: 140px;
            flex-shrink: 0;
        }

        .min-w-0 {
            min-width: 0;
        }

        /* Purple button */
        .btn-purple {
            background: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background: #5a32a3;
            border-color: #5a32a3;
            color: #fff;
        }

        .bg-purple-subtle {
            background: rgba(111, 66, 193, .12) !important;
        }

        .border-purple-subtle {
            border-color: rgba(111, 66, 193, .25) !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .discharge-btn {
            background: #ffc107;
            /* bootstrap warning */
            border: 0;
            border-radius: 12px;
            padding: 16px 18px;
            /* height */
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
        }

        .discharge-btn:hover {
            background: #ffb300;
        }

        .token-pill {
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            line-height: 1;
        }

        .token-pill span {
            opacity: .95;
        }

        .token-pill strong {
            font-weight: 900;
        }

        .token-pill-green {
            background: #43f26c;
            color: #0b3d17;
        }

        .token-pill-blue {
            background: #5b86ff;
            color: #ffffff;
        }

        /* waiting queue inner box */
        .wq-box {
            border: 1px solid #eef0f4;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .wq-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
        }

        .wq-count {
            font-size: 42px;
            font-weight: 900;
            color: #ff7a00;
            /* orange like screenshot */
            line-height: 1;
        }

        /* discharge button like screenshot */
        .discharge-btn {
            background: #ffc107;
            border: 0;
            border-radius: 12px;
            padding: 16px 18px;
            font-weight: 800;
            font-size: 18px;
            color: #111827;
            box-shadow: 0 10px 20px rgba(16, 24, 40, .10);
        }

        .discharge-btn:hover {
            background: #ffb300;
        }

        /* ── Registered Patients card ───────────────────────── */
        .reg-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #e8f0fe;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* Tab nav */
        .reg-tab-nav {
            display: flex;
            flex-wrap: nowrap;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .reg-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
            position: relative;
            top: 1px;
        }

        .reg-tab-btn:hover:not(.active) {
            background: #f9fafb;
            color: #374151;
            border-color: #e5e7eb;
        }

        /* OPD active */
        .reg-tab-btn.active#tab-opd-btn {
            background: #fff;
            border-color: #dee2e6;
            color: #0d6efd;
            border-bottom-color: #fff;
        }

        /* IPD active */
        .reg-tab-btn.active#tab-ipd-btn {
            background: #fff;
            border-color: #dee2e6;
            color: #198754;
            border-bottom-color: #fff;
        }

        /* ER active */
        .reg-tab-btn.active#tab-er-btn {
            background: #fff;
            border-color: #dee2e6;
            color: #dc3545;
            border-bottom-color: #fff;
        }

        /* Tab icon circles */
        .reg-tab-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }

        .reg-tab-icon-opd  { background: #dbeafe; color: #1d4ed8; }
        .reg-tab-icon-ipd  { background: #dcfce7; color: #15803d; }
        .reg-tab-icon-er   { background: #fee2e2; color: #b91c1c; }

        .reg-tab-btn.active .reg-tab-icon-opd { background: #1d4ed8; color: #fff; }
        .reg-tab-btn.active .reg-tab-icon-ipd { background: #15803d; color: #fff; }
        .reg-tab-btn.active .reg-tab-icon-er  { background: #b91c1c; color: #fff; }

        /* Tab count badges */
        .reg-tab-count {
            font-size: 11px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            line-height: 1.6;
        }

        .reg-tab-count-opd { background: #dbeafe; color: #1d4ed8; }
        .reg-tab-count-ipd { background: #dcfce7; color: #15803d; }
        .reg-tab-count-er  { background: #fee2e2; color: #b91c1c; }

        .reg-tab-btn.active .reg-tab-count-opd { background: #1d4ed8; color: #fff; }
        .reg-tab-btn.active .reg-tab-count-ipd { background: #15803d; color: #fff; }
        .reg-tab-btn.active .reg-tab-count-er  { background: #b91c1c; color: #fff; }

        /* Table */
        .reg-table thead th {
            background: #f8fafc;
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e5e7eb;
            padding-top: 10px;
            padding-bottom: 10px;
            white-space: nowrap;
        }

        .reg-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .reg-table tbody tr:last-child {
            border-bottom: none;
        }

        .reg-table tbody tr:hover td {
            background: #fafbff;
        }

        /* Avatar initials */
        .reg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .reg-avatar-opd { background: #dbeafe; color: #1d4ed8; }
        .reg-avatar-ipd { background: #dcfce7; color: #15803d; }
        .reg-avatar-er  { background: #fee2e2; color: #b91c1c; }

        /* Empty state */
        .reg-empty-state { display: flex; flex-direction: column; align-items: center; }
        .reg-empty-icon  { font-size: 2.5rem; opacity: .35; }

        /* Desktop widths, Mobile auto-full */
        @media (min-width: 768px) {
            .search-w {
                width: 360px;
            }

            .inbox-search {
                width: 320px;
            }

            .w-md-auto {
                width: auto !important;
            }

            .reg-dropdown {
                max-width: 100%;
                width: 100%;
            }

            #registrationTypeBtn {
                width: 100%;
            }
        }

        @media (min-width: 576px) {
            .w-sm-auto {
                width: auto !important;
            }

            .wq-count {
                font-size: 36px;
            }

            .discharge-btn {
                font-size: 16px;
                padding: 14px 16px;
            }

            .reg-dropdown {
                max-width: 100%;
                width: 100%;
            }

            #registrationTypeBtn {
                width: 100%;
            }
        }
    </style>
    @push('scripts')
        <script>
        /* ── Global Patient Search ── */
        (function () {
            var input    = document.getElementById('fdPatientSearch');
            var dropdown = document.getElementById('fdSearchDropdown');
            var SEARCH_URL  = "{{ route('front_desk.patients.search') }}";
            var DETAIL_BASE = "{{ url('patients') }}";
            var timer, lastQ = '';

            function open()  { dropdown.classList.add('open'); }
            function close() { dropdown.classList.remove('open'); }

            function showLoading() {
                dropdown.innerHTML = '<li class="fd-search-loading"><span class="spinner-border spinner-border-sm"></span> Searching…</li>';
                open();
            }

            function showEmpty() {
                dropdown.innerHTML = '<li class="fd-search-empty"><i class="bi bi-person-x me-1"></i>No patients found</li>';
                open();
            }

            function render(results) {
                if (!results.length) { showEmpty(); return; }
                dropdown.innerHTML = results.map(function (p) {
                    var parts = p.text.split(' | ');
                    var name  = parts[0] || '—';
                    var phone = parts[1] || '';
                    var mrn   = parts[2] || '';
                    var init  = name.charAt(0).toUpperCase();
                    return '<li>'
                        + '<a class="fd-search-item" href="' + DETAIL_BASE + '/' + p.id + '">'
                        + '<div class="fd-search-avatar">' + init + '</div>'
                        + '<div class="min-w-0">'
                        + '<div class="fd-search-name">' + name + '</div>'
                        + '<div class="fd-search-meta">' + (phone ? phone : '') + (mrn ? ' · ' + mrn : '') + '</div>'
                        + '</div>'
                        + '</a>'
                        + '</li>';
                }).join('');
                open();
            }

            input.addEventListener('input', function () {
                var q = this.value.trim();
                clearTimeout(timer);
                if (q === lastQ) return;
                lastQ = q;
                if (q.length < 2) { close(); return; }
                showLoading();
                timer = setTimeout(function () {
                    $.getJSON(SEARCH_URL, { q: q }, function (data) {
                        render(data.results || []);
                    }).fail(function () { close(); });
                }, 280);
            });

            /* Close on outside click */
            document.addEventListener('click', function (e) {
                if (!document.getElementById('fdSearchWrap').contains(e.target)) close();
            });

            /* Keyboard: Escape closes */
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { close(); input.blur(); }
            });
        }());
        </script>

        <script>
            $(document).on('change', '#commonModal #fdRegForm #patient_id, #commonModal #patient_search', function() {
                const patientId = $(this).val();
                const modal = $('#commonModal');

                const $name = modal.find('#patient_name'); // patient_name input
                const $mobile = modal.find('#patient_contact');
                const $contact_no = modal.find('#contact_no');
                const $registration_type = modal.find('#registration_type');
                const $organization_name = modal.find('#organization_name');
                const $organization_id = modal.find('#organization_id');
                const $organization_api_link = modal.find('#organization_api_link');
                const $discount_type = modal.find('#discount_type');

                // if cleared
                if (!patientId) {
                    $name.prop('readonly', false);
                    $mobile.prop('readonly', false);
                    $organization_name.prop('readonly', false);
                    $organization_id.prop('readonly', false);
                    $organization_api_link.prop('readonly', false);
                    $discount_type.prop('readonly', false);


                    // optional clear:
                    $name.val('');
                    $contact_no.val('');
                    $mobile.val('');
                    $organization_name.val('');
                    $organization_id.val('');
                    $organization_api_link.val('');
                    $discount_type.val('');
                    $registration_type.val('NEW_PATIENT');
                    return;
                }

                // loading state (optional)
                $name.val('Loading...').prop('readonly', true);
                $mobile.val('Loading...').prop('readonly', true);

                $.ajax({
                    url: "{{ route('front_desk.patients.search') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        id: patientId
                    },
                    success: function(res) {
                        console.log(res)
                        // expected: { patient_name: "...", mobileno: "..." }
                        $name.val(res.patient_name || '').prop('readonly', true);
                        $contact_no.val(res.mobileno || '');
                        $mobile.val(res.mobileno || '').prop('readonly', true);
                        $organization_name.val(res.organization_name || '').prop('readonly', true);
                        $organization_id.val(res.organization_id || '').prop('readonly', true);
                        $organization_api_link.val(res.organization_api_link || '').prop('readonly', true);
                        $discount_type.val(res.discount_type || '').prop('readonly', true);
                        $registration_type.val("EXISTING_PATIENT" || '').prop('readonly', true);
                    },

                    error: function(xhr) {
                        let msg = "Failed to fetch patient";
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                        alert(msg);
                        $name.prop('readonly', false);
                        $mobile.prop('readonly', false);
                    }
                });
            });
        </script>

        <script>
            (function() {
                function toggleSection(section, on) {
                    if (!section) return;
                    section.hidden = !on;
                    section.querySelectorAll('input,select,textarea,button').forEach(el => {
                        if (el.type !== 'button') el.disabled = !on;
                    });
                }

                function pad(n) {
                    return String(n).padStart(2, '0');
                }

                function nowDate() {
                    const d = new Date();
                    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
                }

                function nowDateTimeLocal() {
                    const d = new Date();
                    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                }

                function setVisitField(formEl, type) {
                    const wrap = formEl.querySelector('#visitFieldWrap');
                    const label = formEl.querySelector('#visitFieldLabel');
                    const input = formEl.querySelector('#visitFieldInput');
                    const shift_field = formEl.querySelector('#shift_field');

                    if (!wrap || !label || !input) return;

                    if (type === 'OPD') {
                        label.innerHTML = 'Appointment Date <span class="text-danger">*</span>';
                        input.type = 'date';
                        input.name = 'appointment_date';
                        input.value = nowDate();
                        input.required = true;
                    } else if (type === 'Ipd') {
                        label.innerHTML = 'Admission Date <span class="text-danger">*</span>';
                        input.type = 'date';
                        input.name = 'ipd_admission_date';
                        input.value = nowDate();
                        input.required = true;
                    } else if (type === 'ER') {
                        label.innerHTML = 'Arrival Date & Time <span class="text-danger">*</span>';
                        input.type = 'datetime-local';
                        input.name = 'er_arrival_datetime';
                        input.value = nowDateTimeLocal();
                        input.required = true;
                    }
                }

                function applyForForm(formEl) {
                    if (!formEl) return;

                    const typeEl = formEl.querySelector('[name="patient_type"]');
                    if (!typeEl) return;

                    const v = typeEl.value;

                    const ipdBox = formEl.querySelector('#ipdFields');
                    const opdBox = formEl.querySelector('#opdFields');
                    const erBox = formEl.querySelector('#erFields');

                    toggleSection(ipdBox, v === 'Ipd');
                    toggleSection(opdBox, v === 'OPD');
                    toggleSection(erBox, v === 'ER');

                    // Shift + Slot only for OPD
                    const shiftField = formEl.querySelector('#shift_field');
                    const slotField  = formEl.querySelector('#opdSlotField');
                    toggleSection(shiftField, v === 'OPD');
                    toggleSection(slotField, v === 'OPD');
                    if (v !== 'OPD') {
                        const shiftSel = formEl.querySelector('#shift_id');
                        const slotSel  = formEl.querySelector('#slot_time');
                        if (shiftSel) shiftSel.innerHTML = '<option value="">-- Select Shift --</option>';
                        if (slotSel)  slotSel.innerHTML  = '<option value="">-- Select Slot --</option>';
                    }

                    // ✅ Change appointment/admission/arrival field label+type+name
                    setVisitField(formEl, v);
                }

                document.addEventListener('change', function(e) {
                    if (e.target && e.target.name === 'patient_type') {
                        const form = e.target.closest('form');
                        applyForForm(form);
                    }
                });

                document.addEventListener('shown.bs.modal', function(e) {
                    const modal = e.target;
                    modal.querySelectorAll('form').forEach(f => applyForForm(f));
                });

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('form').forEach(f => applyForForm(f));
                });
            })();
        </script>

        <script>
            // OPD: load shifts when doctor changes
            document.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'doctor_id') return;
                var form = e.target.closest('form');
                if (!form) return;
                var ptEl = form.querySelector('[name="patient_type"]');
                if (!ptEl || ptEl.value !== 'OPD') return;

                var doctorId  = e.target.value;
                var shiftSel  = form.querySelector('#shift_id');
                var slotSel   = form.querySelector('#slot_time');
                if (!shiftSel) return;

                shiftSel.innerHTML = '<option value="">-- Select Shift --</option>';
                if (slotSel) slotSel.innerHTML = '<option value="">-- Select Slot --</option>';
                if (!doctorId) return;

                var csrf = (form.querySelector('[name="_token"]') || {}).value || '';
                fetch('{{ route("appointments.get-doctor-shifts") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ doctor_id: doctorId })
                })
                .then(function(r) { return r.json(); })
                .then(function(shifts) {
                    shifts.forEach(function(s) {
                        shiftSel.insertAdjacentHTML('beforeend', '<option value="' + s.id + '">' + s.name + '</option>');
                    });
                });
            });

            // OPD: load slots when shift or appointment date changes
            function fetchOpdSlots(form) {
                var doctorId = (form.querySelector('[name="doctor_id"]') || {}).value;
                var shiftId  = (form.querySelector('#shift_id') || {}).value;
                var date     = (form.querySelector('[name="appointment_date"]') || {}).value;
                var slotSel  = form.querySelector('#slot_time');
                if (!slotSel) return;

                slotSel.innerHTML = '<option value="">-- Select Slot --</option>';
                if (!doctorId || !shiftId || !date) return;

                var csrf = (form.querySelector('[name="_token"]') || {}).value || '';
                fetch('{{ route("appointments.get-slots") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ doctor_id: doctorId, shift_id: shiftId, date: date })
                })
                .then(function(r) { return r.json(); })
                .then(function(slots) {
                    slots.forEach(function(s) {
                        slotSel.insertAdjacentHTML('beforeend', '<option value="' + s.time_from + '|' + s.time_to + '">' + s.time_from + ' - ' + s.time_to + '</option>');
                    });
                });
            }

            document.addEventListener('change', function(e) {
                if (!e.target) return;
                var isShift = e.target.id === 'shift_id';
                var isDate  = e.target.name === 'appointment_date';
                if (!isShift && !isDate) return;
                var form = e.target.closest('form');
                if (!form) return;
                var ptEl = form.querySelector('[name="patient_type"]');
                if (!ptEl || ptEl.value !== 'OPD') return;
                fetchOpdSlots(form);
            });
        </script>

        <script>
        (function () {
            var VISITORS_URL = "{{ route('front_desk.visitor.today') }}";
            var SLIP_BASE    = "{{ url('front_desk/visitor') }}";
            var tbody  = document.getElementById('visitorListBody');
            var badge  = document.getElementById('visitorCountBadge');
            var refreshBtn = document.getElementById('visitorRefreshBtn');

            var typeLabel = { OPD: 'OPD', Ipd: 'IPD', ER: 'ER' };
            var typeColor = { OPD: 'primary', Ipd: 'success', ER: 'danger' };

            function renderVisitors(list) {
                badge.textContent = list.length;
                if (!list.length) {
                    tbody.innerHTML = '<tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-people fs-3 d-block mb-2 opacity-25"></i>No visitors recorded today</td></tr>';
                    return;
                }
                tbody.innerHTML = list.map(function (r, i) {
                    var col     = typeColor[r.patient_type] || 'secondary';
                    var lbl     = typeLabel[r.patient_type] || r.patient_type;
                    var slipUrl = SLIP_BASE + '/' + r.id + '/slip';
                    return '<tr>'
                        + '<td class="ps-3 text-muted small">' + (i + 1) + '</td>'
                        + '<td><span class="fw-semibold font-monospace text-warning">' + (r.visit_code || '—') + '</span></td>'
                        + '<td class="text-muted small">' + (r.visit_date || '—') + '</td>'
                        + '<td>' + (r.visit_time ? '<span class="badge bg-light text-dark border">' + r.visit_time + '</span>' : '<span class="text-muted">—</span>') + '</td>'
                        + '<td class="fw-medium">' + (r.patient_name || '—') + '</td>'
                        + '<td>' + (r.visitor_name || '—') + '</td>'
                        + '<td class="text-muted small">' + (r.contact_no || '—') + '</td>'
                        + '<td class="text-muted small">' + (r.department || '—') + '</td>'
                        + '<td><span class="badge rounded-pill text-bg-' + col + '-subtle border border-' + col + '-subtle text-' + col + '">' + lbl + '</span></td>'
                        + '<td class="text-end pe-3 fw-semibold">' + (r.visitor_qty || 1) + '</td>'
                        + '<td class="text-end pe-3"><a href="' + slipUrl + '" target="_blank" class="btn btn-sm btn-outline-warning" title="Print Pass"><i class="bi bi-printer"></i></a></td>'
                        + '</tr>';
                }).join('');
            }

            function loadVisitors() {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>';
                $.get(VISITORS_URL, function (list) {
                    renderVisitors(list);
                }).fail(function () {
                    tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-danger">Failed to load visitors.</td></tr>';
                });
            }

            refreshBtn.addEventListener('click', loadVisitors);

            // Reload when Add Visitor modal closes after a successful save
            $(document).on('ajaxSuccessCallback', function () { loadVisitors(); });

            // Auto-refresh every 60 seconds
            setInterval(loadVisitors, 60000);

            loadVisitors();
        }());
        </script>

        <script>
        /* ── Appointment Detail Modal ── */
        document.querySelectorAll('.appt-view-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var d = this.dataset;

                document.getElementById('adm-avatar').textContent   = (d.patient || 'P').charAt(0).toUpperCase();
                document.getElementById('adm-patient').textContent   = d.patient;
                document.getElementById('adm-mrn').textContent       = d.mrn !== '—' ? 'MRN: ' + d.mrn : '';
                document.getElementById('adm-contact').textContent   = d.contact;
                document.getElementById('adm-doctor').textContent    = d.doctor;
                document.getElementById('adm-appt-date').textContent = d.date;
                document.getElementById('adm-slot').textContent      = d.slot;
                document.getElementById('adm-source').textContent    = d.source;
                document.getElementById('adm-priority').textContent  = d.priority;
                document.getElementById('adm-date-line').textContent = 'Scheduled: ' + d.date;

                var badge = document.getElementById('adm-status-badge');
                badge.textContent = d.status;
                badge.className = 'badge rounded-pill text-bg-' + d.statusColor + '-subtle border border-' + d.statusColor + '-subtle text-' + d.statusColor;

                var msgWrap = document.getElementById('adm-message-wrap');
                if (d.message && d.message.trim()) {
                    document.getElementById('adm-message').textContent = d.message;
                    msgWrap.style.display = '';
                } else {
                    msgWrap.style.display = 'none';
                }

                document.getElementById('adm-vital-btn').href = d.vitalUrl;

                var modal = new bootstrap.Modal(document.getElementById('apptDetailModal'));
                modal.show();
            });
        });

        /* ── ER Patient Detail Modal ── */
        document.querySelectorAll('.er-view-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var d = this.dataset;

                document.getElementById('erd-avatar').textContent      = (d.patient || 'P').charAt(0).toUpperCase();
                document.getElementById('erd-patient').textContent     = d.patient;
                document.getElementById('erd-mrn').textContent         = d.mrn !== '—' ? 'MRN: ' + d.mrn : '';
                document.getElementById('erd-contact').textContent     = d.contact;
                document.getElementById('erd-age').textContent         = d.age;
                document.getElementById('erd-gender').textContent      = d.gender;
                document.getElementById('erd-blood').textContent       = d.blood !== '—' ? d.blood : '—';
                document.getElementById('erd-arrival').textContent     = d.arrival;
                document.getElementById('erd-arrival-line').textContent = 'Arrived: ' + d.arrival;
                document.getElementById('erd-discount').textContent    = d.discount;

                var prioBadge = document.getElementById('erd-priority-badge');
                prioBadge.textContent = d.priority;
                prioBadge.className = 'badge rounded-pill text-bg-' + d.priorityColor + '-subtle border border-' + d.priorityColor + '-subtle text-' + d.priorityColor;

                var statBadge = document.getElementById('erd-status-badge');
                statBadge.textContent = d.status;
                statBadge.className = 'badge rounded-pill text-bg-' + d.statusColor + '-subtle border border-' + d.statusColor + '-subtle text-' + d.statusColor;

                var tpWrap = document.getElementById('erd-third-party-wrap');
                if (d.thirdParty && d.thirdParty.trim()) {
                    document.getElementById('erd-third-party').textContent = d.thirdParty;
                    tpWrap.style.display = '';
                } else {
                    tpWrap.style.display = 'none';
                }

                var descWrap = document.getElementById('erd-desc-wrap');
                if (d.description && d.description.trim()) {
                    document.getElementById('erd-description').textContent = d.description;
                    descWrap.style.display = '';
                } else {
                    descWrap.style.display = 'none';
                }

                document.getElementById('erd-edit-btn').href = d.editUrl;

                var modal = new bootstrap.Modal(document.getElementById('erDetailModal'));
                modal.show();
            });
        });
        </script>
    @endpush
@endsection
