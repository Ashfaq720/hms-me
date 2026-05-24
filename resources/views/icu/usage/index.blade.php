@extends('backend.layouts.master')

@section('title', 'ICU Equipment — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Equipment & Usage</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                    <span class="badge bg-danger-subtle text-danger ms-2">{{ $admission->icu_type }}</span>
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back to Admission</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        {{-- Active usage --}}
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title mb-2">Currently In Use</h6>
                @php $active = $admission->equipmentUsageLogs->where('status', 'InUse'); @endphp
                @if ($active->isEmpty())
                    <div class="text-muted small">No equipment currently in use.</div>
                @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:160px;">Equipment</th>
                                <th style="width:130px;">Type</th>
                                <th style="width:170px;">Started</th>
                                <th style="width:110px;">Rate</th>
                                <th>Running estimate</th>
                                <th style="width:240px;" class="text-end pe-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($active as $u)
                                @php $est = $u->computeAmount(); @endphp
                                <tr>
                                    <td>{{ $u->equipment?->equipment_name }}<br>
                                        <small class="text-muted">{{ $u->equipment?->equipment_code }}</small>
                                    </td>
                                    <td>{{ $u->equipment_type }}</td>
                                    <td><small>{{ $u->start_time?->format('Y-m-d H:i') }}</small></td>
                                    <td>৳ {{ number_format($u->charge_rate, 2) }}/{{ strtolower($u->billing_unit) }}</td>
                                    <td>
                                        <small class="text-muted">{{ $est['minutes'] }} min →</small>
                                        <strong>৳ {{ number_format($est['amount'], 2) }}</strong>
                                        <small class="text-muted">({{ $est['units'] }} {{ strtolower($u->billing_unit) }})</small>
                                    </td>
                                    <td class="text-end pe-2">
                                        {{-- Remove --}}
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse"
                                            data-bs-target="#remove-{{ $u->id }}">Stop</button>
                                        {{-- Change --}}
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse"
                                            data-bs-target="#change-{{ $u->id }}">Change</button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="remove-{{ $u->id }}">
                                    <td colspan="6">
                                        <form method="POST"
                                            action="{{ route('icu.admissions.usage.remove', [$admission->id, $u->id]) }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-md-3">
                                                <label class="form-label small">End Time</label>
                                                <input type="datetime-local" name="end_time"
                                                    value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label small">Reason <span class="text-danger">*</span></label>
                                                <input type="text" name="remove_reason" class="form-control form-control-sm"
                                                    placeholder="Why is this equipment being removed?" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-danger btn-sm w-100">Confirm Stop</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="collapse" id="change-{{ $u->id }}">
                                    <td colspan="6">
                                        <form method="POST"
                                            action="{{ route('icu.admissions.usage.change', [$admission->id, $u->id]) }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-md-3">
                                                <label class="form-label small">New Equipment</label>
                                                <select name="new_equipment_id" class="form-select form-select-sm" required>
                                                    <option value="">--</option>
                                                    @foreach ($availableEquipment->where('equipment_type', $u->equipment_type) as $e)
                                                        <option value="{{ $e->id }}">
                                                            {{ $e->equipment_code }} — {{ $e->equipment_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Change Time</label>
                                                <input type="datetime-local" name="change_time"
                                                    value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Reason <span class="text-danger">*</span></label>
                                                <input type="text" name="change_reason" class="form-control form-control-sm"
                                                    placeholder="Malfunction / upgrade / etc." required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-warning btn-sm w-100">Confirm Change</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Assign new --}}
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title mb-2">Assign Equipment</h6>
                <form method="POST" action="{{ route('icu.admissions.usage.assign', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small">Equipment <span class="text-danger">*</span></label>
                        <select name="equipment_id" class="form-select form-select-sm" required>
                            <option value="">-- Pick available equipment --</option>
                            @foreach ($availableEquipment as $e)
                                <option value="{{ $e->id }}">
                                    [{{ $e->equipment_type }}] {{ $e->equipment_code }} — {{ $e->equipment_name }}
                                    (৳ {{ number_format($e->charge_rate, 2) }}/{{ strtolower($e->charge_type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Start Time</label>
                        <input type="datetime-local" name="start_time"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Assign</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- History --}}
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title mb-2">Closed Usage</h6>
                @php $closed = $admission->equipmentUsageLogs->where('status', 'Closed'); @endphp
                @if ($closed->isEmpty())
                    <div class="text-muted small">No closed usage yet.</div>
                @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipment</th>
                                <th style="width:170px;">Start</th>
                                <th style="width:170px;">End</th>
                                <th style="width:100px;">Duration</th>
                                <th style="width:120px;">Rate</th>
                                <th style="width:120px;">Total</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($closed as $u)
                                <tr>
                                    <td>{{ $u->equipment?->equipment_name }}<br>
                                        <small class="text-muted">{{ $u->equipment?->equipment_code }}</small>
                                    </td>
                                    <td><small>{{ $u->start_time?->format('Y-m-d H:i') }}</small></td>
                                    <td><small>{{ $u->end_time?->format('Y-m-d H:i') }}</small></td>
                                    <td>{{ $u->duration_minutes }} min</td>
                                    <td>৳ {{ number_format($u->charge_rate, 2) }}/{{ strtolower($u->billing_unit) }}</td>
                                    <td><strong>৳ {{ number_format($u->total_amount, 2) }}</strong></td>
                                    <td><small>{{ $u->remove_reason }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Change log --}}
        @if ($admission->equipmentChangeLogs->isNotEmpty())
            <div class="card mt-2">
                <div class="card-body">
                    <h6 class="card-title mb-2">Change Audit</h6>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:170px;">Time</th>
                                <th>Old → New</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admission->equipmentChangeLogs as $c)
                                <tr>
                                    <td><small>{{ $c->changed_at?->format('Y-m-d H:i') }}</small></td>
                                    <td>
                                        <small class="text-muted">{{ $c->oldEquipment?->equipment_code ?? '-' }}</small>
                                        →
                                        <small class="fw-semibold">{{ $c->newEquipment?->equipment_code ?? '-' }}</small>
                                    </td>
                                    <td>{{ $c->change_reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
