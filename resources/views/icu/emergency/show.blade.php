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
    $responseDelta = $event->first_response_at
        ? $event->activated_at->diffInSeconds($event->first_response_at) : null;
    $arrivalDelta = $event->doctor_arrival_at
        ? $event->activated_at->diffInSeconds($event->doctor_arrival_at) : null;
    $isOpen = $event->status !== 'Closed';
@endphp

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title text-danger mb-1">
                    <i class="bi bi-exclamation-octagon"></i> CODE BLUE
                    <span class="badge bg-{{ $statusColor }} ms-2 align-middle" style="font-size:.55em;">
                        {{ $event->status }}
                    </span>
                </h1>
                <div class="text-muted small">
                    <span class="fw-semibold">{{ $event->event_no }}</span>
                    <span class="mx-1">·</span>
                    {{ $admission->icu_case_id }}
                    <span class="mx-1">·</span>
                    {{ $event->patient?->patient_name ?? $admission->patient?->patient_name ?? '-' }}
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Admission
            </a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2 mb-0">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2 mb-0">{{ session('error') }}</div>   @endif

        {{-- KPI strip: key timestamps at a glance --}}
        <div class="row g-2 mt-2">
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100"><div class="card-body py-2">
                    <div class="text-muted small">Event Type</div>
                    <div class="fw-semibold">{{ $event->event_type }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100"><div class="card-body py-2">
                    <div class="text-muted small">Activated</div>
                    <div class="fw-semibold">{{ $event->activated_at?->format('Y-m-d H:i:s') ?? '-' }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 {{ $event->first_response_at ? '' : 'border-warning-subtle' }}">
                    <div class="card-body py-2">
                        <div class="text-muted small">First Response</div>
                        <div class="fw-semibold">
                            {{ $event->first_response_at?->format('H:i:s') ?? '—' }}
                            @if ($responseDelta !== null)
                                <small class="text-muted ms-1">+{{ $responseDelta }}s</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 {{ $event->doctor_arrival_at ? '' : 'border-warning-subtle' }}">
                    <div class="card-body py-2">
                        <div class="text-muted small">Doctor Arrival</div>
                        <div class="fw-semibold">
                            {{ $event->doctor_arrival_at?->format('H:i:s') ?? '—' }}
                            @if ($arrivalDelta !== null)
                                <small class="text-muted ms-1">+{{ $arrivalDelta }}s</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            {{-- Left: Event details --}}
            <div class="col-lg-5">
                <div class="card border-danger-subtle h-100">
                    <div class="card-body">
                        <h6 class="card-title">Event Details</h6>
                        <table class="table table-sm mb-0">
                            <tr><th style="width:160px;">Status</th>
                                <td><span class="badge bg-{{ $statusColor }}">{{ $event->status }}</span></td>
                            </tr>
                            <tr><th>Type</th><td>{{ $event->event_type }}</td></tr>
                            <tr><th>Activated</th><td>{{ $event->activated_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
                            <tr><th>Team Notified</th><td>{{ $event->team_notified_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
                            <tr><th>First Response</th><td>
                                {{ $event->first_response_at?->format('Y-m-d H:i:s') ?? '-' }}
                                @if ($responseDelta !== null)
                                    <small class="text-muted">(+{{ $responseDelta }}s)</small>
                                @endif
                            </td></tr>
                            <tr><th>Doctor Arrival</th><td>
                                {{ $event->doctor_arrival_at?->format('Y-m-d H:i:s') ?? '-' }}
                                @if ($arrivalDelta !== null)
                                    <small class="text-muted">(+{{ $arrivalDelta }}s)</small>
                                @endif
                            </td></tr>
                            <tr><th>Outcome</th>
                                <td>
                                    @if ($event->outcome)
                                        <span class="badge bg-{{ $outcomeColor }}">{{ $event->outcome }}</span>
                                    @else - @endif
                                </td>
                            </tr>
                            <tr><th>Closed At</th><td>{{ $event->closed_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
                            @if ($event->final_remarks)
                                <tr><th>Final Remarks</th><td><div class="text-pre">{{ $event->final_remarks }}</div></td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Action Timeline (centerpiece) --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body p-0 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-list-check"></i> Action Timeline
                            </h6>
                            <span class="badge bg-light text-dark border">{{ $event->actions->count() }} actions</span>
                        </div>
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width:170px;">Time</th>
                                        <th>Action</th>
                                        <th>Remarks</th>
                                        <th style="width:90px;" class="pe-3">By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($event->actions as $a)
                                        <tr>
                                            <td class="ps-3"><small>{{ $a->action_time?->format('Y-m-d H:i:s') }}</small></td>
                                            <td class="fw-semibold">{{ $a->action_name }}</td>
                                            <td><small class="text-muted">{{ $a->remarks ?? '-' }}</small></td>
                                            <td class="pe-3"><small>#{{ $a->performed_by ?? '-' }}</small></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-clock-history"></i> No actions recorded yet.
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($isOpen)
            {{-- Quick stamps: visible while event is open --}}
            <div class="card mt-3">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="text-muted small me-2"><i class="bi bi-stopwatch"></i> Quick Stamps:</span>
                        @if (! $event->first_response_at)
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.first-response', [$admission->id, $event->id]) }}"
                                class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-warning">
                                    <i class="bi bi-person-fill-check"></i> Mark First Response
                                </button>
                            </form>
                        @endif
                        @if (! $event->doctor_arrival_at)
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.doctor-arrival', [$admission->id, $event->id]) }}"
                                class="m-0">
                                @csrf
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
                            <span class="text-muted small fst-italic">All stamps recorded — close the event when ready.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-0">
                {{-- Add Action --}}
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-plus-circle"></i> Record Action</h6>
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.add-action', [$admission->id, $event->id]) }}"
                                class="row g-2">
                                @csrf
                                <div class="col-md-7">
                                    <label class="form-label small mb-1">Action</label>
                                    <select name="action_name" class="form-select form-select-sm" required>
                                        <option value="">-- select action --</option>
                                        @foreach (['CPR started', 'Oxygen support increased', 'Patient intubated', 'Medication administered', 'Defibrillation performed', 'Vitals reassessed', 'Patient shifted to ventilator', 'Other'] as $a)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Time</label>
                                    <input type="datetime-local" name="action_time"
                                        value="{{ now()->format('Y-m-d\TH:i') }}"
                                        class="form-control form-control-sm">
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
                    <div class="card border-success-subtle h-100">
                        <div class="card-body">
                            <h6 class="card-title text-success"><i class="bi bi-check2-circle"></i> Close Event</h6>
                            <form method="POST"
                                action="{{ route('icu.admissions.emergency.close', [$admission->id, $event->id]) }}"
                                class="row g-2">
                                @csrf
                                <div class="col-12">
                                    <label class="form-label small mb-1">Outcome <span class="text-danger">*</span></label>
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

        {{-- Notifications --}}
        <div class="card mt-3 mb-3">
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
                                <tr><td colspan="4" class="text-center text-muted py-3">No notifications recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>.text-pre{white-space:pre-line}</style>
@endsection
