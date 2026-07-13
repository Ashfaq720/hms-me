@extends('backend.layouts.master')

@section('title', 'Code Blue — ' . $event->event_no)

@php
    $statusColor = match ($event->status) {
        'Activated', 'TeamNotified' => 'danger',
        'ResponseStarted', 'InProgress' => 'warning',
        'Stabilized' => 'success',
        'Closed' => 'secondary',
        default => 'danger',
    };
    $outcomeColor = match ($event->outcome) {
        'Stabilized' => 'success',
        'TransferredToOT', 'TransferredToHigherCare', 'Referred' => 'info',
        'Expired' => 'dark',
        default => 'secondary',
    };
    $responseDelta = $event->first_response_at ? $event->activated_at->diffInSeconds($event->first_response_at) : null;
    $arrivalDelta = $event->doctor_arrival_at ? $event->activated_at->diffInSeconds($event->doctor_arrival_at) : null;
    $isOpen = $event->status !== 'Closed';
    $patient = $event->patient ?? $admission->patient;
    $responseTimeText =
        $responseDelta !== null ? sprintf('%d min %02d sec', intdiv($responseDelta, 60), $responseDelta % 60) : null;
@endphp

@section('content')
    <div class="container codeblue-report">
        {{-- ========= HEADER ========= --}}
        <div class="d-flex flex-wrap gap-3 align-items-start justify-content-between mb-3">
            <div>
                <h1 class="cb-title mb-0">Code Blue Report Overview</h1>
                <div class="text-muted small">Emergency Response Summary</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-download"></i> Download PDF
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Report
                </button>
                <a href="{{ route('icu.admissions.show', $admission->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Admission
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ========= PATIENT OVERVIEW CARD ========= --}}
        <div class="card cb-card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Photo + name --}}
                    <div class="col-lg-3 text-center cb-divider-end">
                        <div class="cb-photo-wrap">
                            <div class="cb-photo">
                                @if (!empty($patient?->image))
                                    <img src="{{ asset('storage/' . $patient->image) }}"
                                        alt="{{ $patient->patient_name ?? 'Patient' }}">
                                @else
                                    <i class="bi bi-person-fill"></i>
                                @endif
                            </div>
                            {{-- <span class="cb-photo-badge"><i class="bi bi-heart-pulse-fill"></i> ICU CASE</span> --}}
                        </div>
                        <div class="fw-bold mt-3">{{ $patient->patient_name ?? '—' }}</div>
                        <div class="text-muted small">{{ $patient->mrn ?? '—' }}</div>
                        <div class="text-muted small">
                            Age: {{ calculateAgeFromDob($patient->dob) ?? '' }} @if (!empty($patient?->gender))
                                · {{ $patient->gender }}
                            @endif
                        </div>
                        <button type="button" class="btn btn-warning btn-sm mt-2 px-3 text-white fw-semibold">
                            <i class="bi bi-credit-card-2-front"></i> Health Card
                        </button>
                    </div>

                    {{-- Patient & Admission details --}}
                    <div class="col-lg-6">
                        <h6 class="fw-bold mb-3">Patient &amp; Admission</h6>
                        <div class="row g-3 small">
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Mobile</div>
                                <div class="fw-semibold">{{ $patient->mobileno ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Guardian</div>
                                <div class="fw-semibold">{{ $patient->guardian_name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Blood Group</div>
                                <div class="fw-semibold">{{ $patient->blood_group ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Allergies</div>
                                <div class="fw-semibold text-danger">{{ $patient->allergies ?? '' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Admission Time</div>
                                <div class="fw-semibold">{{ $admission->ipdPatient?->admission_date?->format('d M Y h:i A') ?? '—' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Admission Type</div>
                                <div class="fw-semibold">{{ $admission->admission_type ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Source</div>
                                <div class="fw-semibold">{{ $admission->ipdPatient?->source ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="text-muted">Discharge Time</div>
                                <div class="fw-semibold">{{ $admission->ipdPatient?->discharge_date?->format('d M Y h:i A') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right column: status / bed / doctor / chips --}}
                    <div class="col-lg-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success-subtle text-success px-2 py-1">Admitted</span>
                            <span class="text-info fw-semibold small">{{ $admission->icu_case_id }}</span>
                        </div>
                        <div class="cb-info-pill mb-2">
                            <i class="bi bi-hospital text-primary me-2"></i>
                            <div>
                                <div class="cb-info-label">BED</div>
                                <div class="fw-semibold text-primary">{{ $admission->ipdPatient?->bedAllocations?->sortByDesc('id')->first()?->bed?->name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="cb-info-pill mb-2">
                            <i class="bi bi-person-badge text-primary me-2"></i>
                            <div>
                                <div class="cb-info-label">REFERRING DOCTOR</div>
                                <div class="fw-semibold text-primary">{{ $admission->referringDoctor->name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-1 mb-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-danger flex-fill cb-chip"><i
                                    class="bi bi-telephone"></i> Contact</button>
                            @if ($admission->ventilator_required || $ventilatorCount > 0)
                                <button type="button" class="btn btn-sm btn-outline-danger flex-fill cb-chip">
                                    <i class="bi bi-lungs"></i> Ventilator
                                    @if ($ventilatorCount > 0)
                                        <span class="badge bg-danger ms-1">{{ $ventilatorCount }}</span>
                                    @endif
                                </button>
                            @endif
                            @if ($admission->monitor_required || $monitorCount > 0)
                                <button type="button" class="btn btn-sm btn-outline-primary flex-fill cb-chip">
                                    <i class="bi bi-display"></i> Monitor
                                    @if ($monitorCount > 0)
                                        <span class="badge bg-primary ms-1">{{ $monitorCount }}</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                        @if ($criticalAlertCount > 0)
                            <button type="button" class="btn btn-danger w-100 fw-semibold">
                                <i class="bi bi-exclamation-octagon-fill"></i> {{ $criticalAlertCount }} Critical
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= PATIENT DETAILS STRIP ========= --}}
        <div class="card cb-card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4 d-flex align-items-center gap-3">
                        <div class="cb-icon-circle bg-info-subtle text-info"><i class="bi bi-person"></i></div>
                        <div>
                            <div class="cb-info-label">PATIENT DETAILS</div>
                            <div class="fw-bold">{{ $patient->patient_name ?? '—' }}</div>
                            <div class="text-muted small">
                                {{ calculateAgeFromDob($patient->dob) ?? '' }} / @if (!empty($patient?->gender)) / {{ $patient->gender }}@endif
                            </div>
                            <div class="text-muted small">{{ $patient->mrn ?? '' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Admission Date</div>
                        <div class="fw-semibold">{{ $admission->ipdPatient?->admission_date?->format('d M Y') ?? '—' }}
                        </div>
                        <div class="text-muted small mt-2">Location</div>
                        <div class="fw-semibold">{{ $admission->ipdPatient?->bedAllocations?->sortByDesc('id')->first()?->bed?->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Primary Diagnosis</div>
                        <div class="fw-semibold">{{ $admission->primary_diagnosis ?? $event->event_type }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="cb-mini-pill">
                            <div class="cb-info-label">ICU CASE ID</div>
                            <div class="fw-semibold text-info">{{ $admission->icu_case_id }}</div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="cb-mini-pill">
                            <div class="cb-info-label">BED NO</div>
                            <div class="fw-semibold text-info">{{ $admission->ipdPatient?->bedAllocations?->sortByDesc('id')->first()?->bed?->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= EVENT / ACTIVATION / RESPONSE STRIP ========= --}}
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="card cb-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="cb-icon-circle bg-danger-subtle text-danger"><i class="bi bi-heart-pulse"></i></div>
                        <div>
                            <div class="cb-info-label">EVENT TYPE</div>
                            <div class="fw-bold">
                                {{ $event->event_type }}
                                <span class="badge bg-danger ms-1">CODE BLUE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cb-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="cb-icon-circle bg-warning-subtle text-warning"><i class="bi bi-stopwatch"></i></div>
                        <div>
                            <div class="cb-info-label">ACTIVATION TIME</div>
                            <div class="fw-bold">
                                {{ $event->activated_at?->format('d M Y') }}
                                <span class="text-danger">{{ $event->activated_at?->format('h:i:s A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cb-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="cb-icon-circle bg-primary-subtle text-primary"><i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <div class="cb-info-label">RESPONSE TIME</div>
                            <div class="fw-bold">{{ $responseTimeText ?? '—' }}</div>
                            <div class="text-muted small">
                                ({{ $event->first_response_at?->format('h:i:s A') ?? '—' }})
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= 3-COLUMN: TIMELINE / ACTIONS TABLE / TEAM ========= --}}
        <div class="row g-3 mb-3">
            {{-- CPR / Action Timeline --}}
            <div class="col-lg-4">
                <div class="card cb-card h-100">
                    <div class="card-body">
                        <div class="cb-section-title">CPR / ACTION TIMELINE</div>
                        <ul class="cb-timeline">
                            @forelse ($event->actions as $a)
                                <li>
                                    <span class="cb-time">{{ $a->action_time?->format('h:i:s A') }}</span>
                                    <span class="cb-action">{{ $a->action_name }}</span>
                                </li>
                            @empty
                                <li class="text-muted small">No actions recorded yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Medication / Procedure Actions --}}
            <div class="col-lg-5">
                <div class="card cb-card h-100">
                    <div class="card-body p-0">
                        <div class="cb-section-title px-3 pt-3">MEDICATION / PROCEDURE ACTIONS</div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead class="cb-thead">
                                    <tr>
                                        <th class="ps-3">TIME</th>
                                        <th>MEDICATION / PROCEDURE</th>
                                        <th>DOSE / DETAILS</th>
                                        <th class="pe-3">BY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($event->actions as $a)
                                        <tr>
                                            <td class="ps-3 small">{{ $a->action_time?->format('h:i:s A') }}</td>
                                            <td class="small fw-semibold">{{ $a->action_name }}</td>
                                            <td class="small">{{ $a->remarks ?? '—' }}</td>
                                            <td class="pe-3 small">{{ $a->performedBy->name ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted small py-3">No actions
                                                recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Team Members Involved --}}
            <div class="col-lg-3">
                <div class="card cb-card h-100">
                    <div class="card-body">
                        <div class="cb-section-title">TEAM MEMBERS INVOLVED</div>
                        @php
                            $teamMembers = $event->actions
                                ->map(fn($a) => $a->performedBy)
                                ->filter()
                                ->unique('id')
                                ->values();
                        @endphp
                        @forelse ($teamMembers as $member)
                            @php
                                $type = $member->type ?? 'staff';
                                $roleMap = [
                                    'doctor' => ['color' => 'primary', 'label' => 'Doctor'],
                                    'nurse' => ['color' => 'success', 'label' => 'Nurse'],
                                    'therapist' => ['color' => 'purple', 'label' => 'Therapist'],
                                    'pharmacist' => ['color' => 'warning', 'label' => 'Pharmacist'],
                                ];
                                $role = $roleMap[strtolower($type)] ?? [
                                    'color' => 'secondary',
                                    'label' => ucfirst(str_replace('_', ' ', $type)),
                                ];
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cb-avatar cb-avatar-{{ $role['color'] }}">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <div class="small fw-semibold">{{ $member->name }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $role['label'] }}</div>
                                    </div>
                                </div>
                                <span class="badge cb-badge-{{ $role['color'] }}">{{ $role['label'] }}</span>
                            </div>
                        @empty
                            <div class="text-muted small">No team members recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= BOTTOM 4-CARD STRIP ========= --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="card cb-card cb-outcome h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <div class="cb-info-label">OUTCOME</div>
                                <div class="fw-bold">{{ $event->outcome ?? '—' }}</div>
                                <div class="text-muted small">
                                    {{ $event->final_remarks ? \Illuminate\Support\Str::limit($event->final_remarks, 80) : 'Patient status updated.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cb-card cb-note h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-file-medical text-warning fs-5"></i>
                            <div>
                                <div class="cb-info-label">DOCTOR FINAL NOTE</div>
                                <div class="small text-pre">{{ $event->final_remarks ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cb-card cb-generated h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2">
                            <div class="cb-avatar cb-avatar-primary"><i class="bi bi-person-fill"></i></div>
                            <div>
                                <div class="cb-info-label">GENERATED BY</div>
                                <div class="fw-bold">{{ $event->activatedBy->name ?? '—' }}</div>
                                {{-- <div class="text-muted small">Intensivist</div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card cb-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-calendar-event text-primary fs-5"></i>
                            <div>
                                <div class="cb-info-label">REPORT DETAILS</div>
                                <div class="small mt-1">
                                    <span class="text-muted">Generated Time</span><br>
                                    <b>{{ now()->format('d M Y, h:i A') }}</b>
                                </div>
                                <div class="small mt-1">
                                    <span class="text-muted">Generated By</span><br>
                                    <b>{{ $event->activatedBy->name ?? '—' }}</b>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========= PRESERVED: QUICK STAMPS (when open) ========= --}}
        @if ($isOpen)
            <div class="card cb-card mb-3 d-print-none">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="text-muted small me-2"><i class="bi bi-stopwatch"></i> Quick Stamps:</span>
                        @if (!$event->first_response_at)
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.first-response', [$admission->id, $event->id]) }}"
                                class="m-0 d-flex flex-wrap gap-1 align-items-center">
                                @csrf
                                <select name="performed_by" class="form-select form-select-sm" style="width:220px;"
                                    required>
                                    <option value="">Doctor / Nurse</option>
                                    @foreach ($staffUsers as $user)
                                        <option value="{{ $user->id }}" @selected(auth()->id() === $user->id)>
                                            {{ $user->name }}{{ $user->type ? ' (' . ucfirst(str_replace('_', ' ', $user->type)) . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-warning">
                                    <i class="bi bi-person-fill-check"></i> Mark First Response
                                </button>
                            </form>
                        @endif
                        @if (!$event->doctor_arrival_at)
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.doctor-arrival', [$admission->id, $event->id]) }}"
                                class="m-0 d-flex flex-wrap gap-1 align-items-center">
                                @csrf
                                <select name="performed_by" class="form-select form-select-sm" style="width:220px;"
                                    required>
                                    <option value="">Doctor / Nurse</option>
                                    @foreach ($staffUsers as $user)
                                        <option value="{{ $user->id }}" @selected(auth()->id() === $user->id)>
                                            {{ $user->name }}{{ $user->type ? ' (' . ucfirst(str_replace('_', ' ', $user->type)) . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-info">
                                    <i class="bi bi-hospital"></i> Mark Doctor Arrival
                                </button>
                            </form>
                        @endif
                        @if ($event->status !== 'Stabilized' && $event->status !== 'Closed')
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.stabilize', [$admission->id, $event->id]) }}"
                                class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-heart-pulse"></i> Mark Stabilized
                                </button>
                            </form>
                        @endif
                        @if ($event->first_response_at && $event->doctor_arrival_at && $event->status === 'Stabilized')
                            <span class="text-muted small fst-italic">All stamps recorded — close the event when
                                ready.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3 d-print-none">
                {{-- Add Action --}}
                <div class="col-lg-7">
                    <div class="card cb-card h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-plus-circle"></i> Record Action</h6>
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.add-action', [$admission->id, $event->id]) }}"
                                class="row g-2">
                                @csrf
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Action</label>
                                    <select name="action_name" class="form-select form-select-sm" required>
                                        <option value="">-- select action --</option>
                                        @foreach (['CPR started', 'Oxygen support increased', 'Patient intubated', 'Medication administered', 'Defibrillation performed', 'Vitals reassessed', 'Patient shifted to ventilator', 'Other'] as $a)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Time</label>
                                    <input type="datetime-local" name="action_time"
                                        value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Doctor / Nurse <span
                                            class="text-danger">*</span></label>
                                    <select name="performed_by" class="form-select form-select-sm" required>
                                        <option value="">-- select staff --</option>
                                        @foreach ($staffUsers as $user)
                                            <option value="{{ $user->id }}" @selected(auth()->id() === $user->id)>
                                                {{ $user->name }}{{ $user->type ? ' (' . ucfirst(str_replace('_', ' ', $user->type)) . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Remarks</label>
                                    <input type="text" name="remarks" class="form-control form-control-sm"
                                        placeholder="Optional remarks">
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="bi bi-check2"></i> Record Action
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Close Event --}}
                <div class="col-lg-5">
                    <div class="card cb-card border-success-subtle h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success"><i class="bi bi-check2-circle"></i> Close Event</h6>
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.close', [$admission->id, $event->id]) }}"
                                class="row g-2">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label small mb-1">Outcome <span
                                            class="text-danger">*</span></label>
                                    <select name="outcome" class="form-select form-select-sm" required>
                                        @foreach (['Stabilized', 'TransferredToOT', 'TransferredToHigherCare', 'Expired', 'Referred'] as $o)
                                            <option value="{{ $o }}">{{ $o }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Final Remarks</label>
                                    <textarea name="final_remarks" class="form-control form-control-sm" rows="3"
                                        placeholder="Final remarks / doctor note"></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-success btn-sm">
                                        <i class="bi bi-lock"></i> Close Event
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========= PRESERVED: NOTIFICATIONS ========= --}}
        {{-- <div class="card cb-card mb-3 d-print-none">
            <div class="card-body p-0">
                <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bell"></i> Notifications
                    </h6>
                    <span class="badge bg-light text-dark border">{{ $event->notifications->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Role</th>
                                <th>Channel</th>
                                <th>Sent</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($event->notifications as $n)
                                <tr>
                                    <td class="ps-3">{{ $n->role }}</td>
                                    <td>{{ $n->notification_type }}</td>
                                    <td><small>{{ $n->sent_at?->format('Y-m-d H:i:s') }}</small></td>
                                    <td class="pe-3">
                                        @php
                                            $nColor = match ($n->status) {
                                                'Sent', 'Delivered' => 'success',
                                                'Failed' => 'danger',
                                                'Pending' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $nColor }}">{{ $n->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No notifications recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}
    </div>

    <style>
        .codeblue-report .cb-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
        }

        .codeblue-report .cb-card {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
        }

        .codeblue-report .cb-info-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 600;
        }

        .codeblue-report .cb-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 12px;
        }

        .codeblue-report .cb-thead th {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .codeblue-report .cb-divider-end {
            border-right: 1px solid #e5e7eb;
        }

        @media (max-width: 992px) {
            .codeblue-report .cb-divider-end {
                border-right: 0;
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 1rem;
            }
        }

        .codeblue-report .cb-icon-circle {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .6rem;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .codeblue-report .cb-photo-wrap {
            position: relative;
            display: inline-block;
        }

        .codeblue-report .cb-photo {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            color: #9ca3af;
            border: 3px solid #fff;
            box-shadow: 0 0 0 1px #e5e7eb;
            overflow: hidden;
        }

        .codeblue-report .cb-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .codeblue-report .cb-photo-badge {
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 12px;
            white-space: nowrap;
            font-weight: 600;
        }

        .codeblue-report .cb-info-pill {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: .5rem;
            background: #eff6ff;
        }

        .codeblue-report .cb-mini-pill {
            background: #eff6ff;
            padding: 8px 10px;
            border-radius: .5rem;
            text-align: center;
        }

        .codeblue-report .cb-chip {
            font-size: 11px;
            padding: 4px 6px;
        }

        .codeblue-report .cb-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .codeblue-report .cb-avatar-primary {
            background: #dbeafe;
            color: #2563eb;
        }

        .codeblue-report .cb-avatar-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .codeblue-report .cb-avatar-warning {
            background: #fef3c7;
            color: #d97706;
        }

        .codeblue-report .cb-avatar-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .codeblue-report .cb-avatar-secondary {
            background: #e5e7eb;
            color: #4b5563;
        }

        .codeblue-report .cb-badge-primary {
            background: #2563eb;
            color: #fff;
        }

        .codeblue-report .cb-badge-success {
            background: #16a34a;
            color: #fff;
        }

        .codeblue-report .cb-badge-warning {
            background: #f59e0b;
            color: #fff;
        }

        .codeblue-report .cb-badge-purple {
            background: #7c3aed;
            color: #fff;
        }

        .codeblue-report .cb-badge-secondary {
            background: #6b7280;
            color: #fff;
        }

        .codeblue-report .cb-timeline {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .codeblue-report .cb-timeline li {
            position: relative;
            padding-left: 20px;
            padding-bottom: 12px;
        }

        .codeblue-report .cb-timeline li::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 6px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #3b82f6;
            box-shadow: 0 0 0 2px #fff, 0 0 0 3px #93c5fd;
        }

        .codeblue-report .cb-timeline li:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 8px;
            top: 16px;
            bottom: 0;
            width: 1px;
            background: #e5e7eb;
        }

        .codeblue-report .cb-timeline .cb-time {
            display: inline-block;
            font-size: 11px;
            color: #6b7280;
            min-width: 85px;
        }

        .codeblue-report .cb-timeline .cb-action {
            font-size: 12px;
            font-weight: 500;
            color: #111827;
        }

        .codeblue-report .cb-outcome {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .codeblue-report .cb-note {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .codeblue-report .cb-generated {
            background: #f5f3ff;
            border-color: #ddd6fe;
        }

        .text-pre {
            white-space: pre-line;
        }

        @media print {
            .d-print-none {
                display: none !important;
            }
        }
    </style>
@endsection
