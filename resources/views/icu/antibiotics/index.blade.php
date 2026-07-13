@extends('backend.layouts.master')

@section('title', 'Antibiotics — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Antibiotic Stewardship</h1>
                <div class="text-muted">{{ $admission->icu_case_id }}</div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Add Antibiotic</h6>
                <form method="POST" action="{{ route('icu.admissions.antibiotics.store', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Antibiotic <span class="text-danger">*</span></label>
                        <input type="text" name="antibiotic_name" class="form-control form-control-sm"
                            placeholder="e.g. Inj. Meropenem" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Dose</label>
                        <input type="text" name="dose" class="form-control form-control-sm" placeholder="1 gm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Route</label>
                        <select name="route" class="form-select form-select-sm">
                            @foreach (['IV', 'PO', 'IM', 'SC', 'Topical', 'Other'] as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Frequency</label>
                        <input type="text" name="frequency" class="form-control form-control-sm" placeholder="8 hourly">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Indication</label>
                        <input type="text" name="indication" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" value="{{ now()->toDateString() }}"
                            class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Stop Date</label>
                        <input type="date" name="stop_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Culture Report ID</label>
                        <input type="number" name="culture_report_id" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_restricted" value="0">
                            <input class="form-check-input" type="checkbox" id="restricted" name="is_restricted"
                                value="1">
                            <label class="form-check-label small" for="restricted">Restricted</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Save Antibiotic</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Antibiotic History</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2">Antibiotic</th>
                            <th style="width:80px;">Dose</th>
                            <th style="width:60px;">Route</th>
                            <th>Indication</th>
                            <th style="width:100px;">Start</th>
                            <th style="width:100px;">Stop</th>
                            <th style="width:90px;">Days</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:130px;" class="text-end pe-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $l)
                            @php
                                $days = $l->durationDays();
                                $longRun = $l->status === 'Active' && $days > \App\Http\Controllers\ICU\IcuAntibioticController::LONG_USE_DAYS;
                            @endphp
                            <tr class="{{ $longRun ? 'table-warning' : '' }}">
                                <td class="ps-2">
                                    <div class="fw-semibold">{{ $l->antibiotic_name }}</div>
                                    @if ($l->is_restricted)
                                        <span class="badge bg-danger-subtle text-danger">Restricted</span>
                                    @endif
                                </td>
                                <td>{{ $l->dose ?? '-' }}</td>
                                <td>{{ $l->route ?? '-' }}</td>
                                <td><small>{{ $l->indication ?? '-' }}</small></td>
                                <td><small>{{ $l->start_date?->format('Y-m-d') }}</small></td>
                                <td><small>{{ $l->stop_date?->format('Y-m-d') ?? '-' }}</small></td>
                                <td>
                                    {{ $days }}
                                    @if ($longRun) <span class="badge bg-warning">Long</span> @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $l->status === 'Active' ? 'primary' : 'secondary' }}">
                                        {{ $l->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-2">
                                    @if ($l->status === 'Active')
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                            data-bs-target="#stop-{{ $l->id }}">Stop</button>
                                    @endif
                                </td>
                            </tr>
                            @if ($l->status === 'Active')
                                <tr class="collapse" id="stop-{{ $l->id }}">
                                    <td colspan="9">
                                        <form method="POST"
                                            action="{{ route('icu.admissions.antibiotics.stop', [$admission->id, $l->id]) }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-md-3">
                                                <input type="date" name="stop_date" value="{{ now()->toDateString() }}"
                                                    class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="text" name="remarks" class="form-control form-control-sm"
                                                    placeholder="Reason for stopping">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-secondary btn-sm w-100">Stop Now</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No antibiotics recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
