@extends('backend.layouts.master')

@section('title', 'ICU Orders — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Doctor Orders</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        {{-- Create order --}}
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">New Order</h6>
                <form method="POST" action="{{ route('icu.admissions.orders.store', $admission->id) }}" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Doctor <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select form-select-sm" required>
                            <option value="">--</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Type <span class="text-danger">*</span></label>
                        <select name="order_type" class="form-select form-select-sm" required>
                            @foreach (['Medication', 'Lab', 'Radiology', 'Procedure', 'NursingCare', 'DietFluid', 'Monitoring'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select form-select-sm" required>
                            @foreach (['Routine', 'Urgent', 'STAT'] as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Title <span class="text-danger">*</span></label>
                        <input type="text" name="order_title" class="form-control form-control-sm"
                            placeholder="e.g. Inj. Meropenem 1 gm IV" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Frequency</label>
                        <input type="text" name="frequency" class="form-control form-control-sm" placeholder="8 hourly">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Duration</label>
                        <input type="text" name="duration" class="form-control form-control-sm" placeholder="5 days">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Start Time</label>
                        <input type="datetime-local" name="start_time"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="requires_doctor_ack" value="0">
                            <input class="form-check-input" type="checkbox" id="rda" name="requires_doctor_ack" value="1">
                            <label class="form-check-label small" for="rda">Requires doctor ack on completion</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Create</button>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small">Details</label>
                        <textarea name="order_details" class="form-control form-control-sm" rows="2"
                            placeholder="Dose, route, full instructions"></textarea>
                    </div>
                </form>
            </div>
        </div>

        {{-- Queue --}}
        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Order Queue</h6>
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:80px;">Priority</th>
                            <th style="width:90px;">Type</th>
                            <th>Title / Details</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:110px;">Doctor</th>
                            <th style="width:140px;">Start</th>
                            <th style="width:280px;" class="text-end pe-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            @php
                                $pColor = match ($o->priority) {
                                    'STAT'   => 'danger',
                                    'Urgent' => 'warning',
                                    default  => 'secondary',
                                };
                                $sColor = match ($o->status) {
                                    'Ordered'      => 'secondary',
                                    'Acknowledged' => 'primary',
                                    'InProgress'   => 'warning',
                                    'Completed'    => 'success',
                                    'Cancelled'    => 'danger',
                                    'Modified'     => 'info',
                                    default        => 'dark',
                                };
                            @endphp
                            <tr>
                                <td class="ps-2"><span class="badge bg-{{ $pColor }}">{{ $o->priority }}</span></td>
                                <td>{{ $o->order_type }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $o->order_title }}</div>
                                    @if ($o->order_details)
                                        <small class="text-muted">{{ $o->order_details }}</small>
                                    @endif
                                    @if ($o->frequency || $o->duration)
                                        <div class="small text-muted">
                                            {{ $o->frequency }} {{ $o->duration ? '× ' . $o->duration : '' }}
                                        </div>
                                    @endif
                                    @if ($o->requires_doctor_ack)
                                        <span class="badge bg-info-subtle text-info">Doctor ack required</span>
                                        @if ($o->doctor_acknowledged_at)
                                            <span class="badge bg-success">Acked
                                                {{ $o->doctor_acknowledged_at->format('Y-m-d H:i') }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $sColor }}">{{ $o->status }}</span></td>
                                <td>{{ $o->doctor?->name }}</td>
                                <td><small>{{ $o->start_time?->format('Y-m-d H:i') }}</small></td>
                                <td class="text-end pe-2">
                                    @if ($o->status === 'Ordered')
                                        <form method="POST"
                                            action="{{ route('icu.admissions.orders.acknowledge', [$admission->id, $o->id]) }}"
                                            class="d-inline">@csrf<button class="btn btn-sm btn-outline-primary">Ack</button></form>
                                    @endif
                                    @if (in_array($o->status, ['Ordered', 'Acknowledged', 'OnHold']))
                                        <form method="POST"
                                            action="{{ route('icu.admissions.orders.start', [$admission->id, $o->id]) }}"
                                            class="d-inline">@csrf<button class="btn btn-sm btn-outline-warning">Start</button></form>
                                    @endif
                                    @if (in_array($o->status, ['Acknowledged', 'InProgress']))
                                        <form method="POST"
                                            action="{{ route('icu.admissions.orders.complete', [$admission->id, $o->id]) }}"
                                            class="d-inline">@csrf<button class="btn btn-sm btn-outline-success">Complete</button></form>
                                    @endif
                                    @if ($o->requires_doctor_ack && $o->status === 'Completed' && ! $o->doctor_acknowledged_at)
                                        <form method="POST"
                                            action="{{ route('icu.admissions.orders.doctor-ack', [$admission->id, $o->id]) }}"
                                            class="d-inline">@csrf<button class="btn btn-sm btn-info">Doctor Ack</button></form>
                                    @endif
                                    @if (! in_array($o->status, ['Completed', 'Cancelled']))
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse"
                                            data-bs-target="#cancel-{{ $o->id }}">Cancel</button>
                                    @endif
                                </td>
                            </tr>
                            <tr class="collapse" id="cancel-{{ $o->id }}">
                                <td colspan="7">
                                    <form method="POST"
                                        action="{{ route('icu.admissions.orders.cancel', [$admission->id, $o->id]) }}"
                                        class="row g-2">
                                        @csrf
                                        <div class="col-md-9">
                                            <input type="text" name="reason" class="form-control form-control-sm"
                                                placeholder="Cancellation reason" required>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-danger btn-sm w-100">Confirm Cancel</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
