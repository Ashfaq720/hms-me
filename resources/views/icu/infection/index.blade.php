@extends('backend.layouts.master')

@section('title', 'Infection Control — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Infection & Isolation</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('icu.admissions.antibiotics.index', $admission->id) }}"
                    class="btn btn-sm btn-outline-warning">Antibiotics</a>
                <a href="{{ route('icu.admissions.exposure.index', $admission->id) }}"
                    class="btn btn-sm btn-outline-info">Exposure</a>
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        @php
            $bedType   = $admission->bed?->bedType;
            $isIsoBed  = $bedType?->is_isolation_bed;
            $allowed   = $bedType?->allowed_isolation_type;
            $needIso   = $records->where('is_active', true)->where('isolation_type', '!=', 'None')->first();
            $mismatch  = $needIso && (!$isIsoBed || ($allowed && $allowed !== $needIso->isolation_type));
        @endphp

        @if ($mismatch)
            <div class="alert alert-danger mt-2">
                <strong>Isolation mismatch:</strong> Patient requires
                {{ $needIso->isolation_type }} isolation, but bed
                ({{ $admission->bed?->name ?? '-' }})
                {{ $isIsoBed ? "is configured for $allowed isolation" : 'is not an isolation bed' }}.
            </div>
        @endif

        {{-- Add infection record --}}
        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Tag Infection / Isolation</h6>
                <form method="POST" action="{{ route('icu.admissions.infection.store', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small">Status <span class="text-danger">*</span></label>
                        <select name="infection_status" class="form-select form-select-sm" required>
                            @foreach (['Suspected', 'Confirmed', 'RuledOut', 'Resolved'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Infection Name</label>
                        <input type="text" name="infection_name" class="form-control form-control-sm"
                            placeholder="e.g. MRSA bacteremia">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Organism</label>
                        <input type="text" name="organism" class="form-control form-control-sm"
                            placeholder="e.g. Staphylococcus aureus">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Isolation <span class="text-danger">*</span></label>
                        <select name="isolation_type" class="form-select form-select-sm" required>
                            @foreach (['None', 'Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Source <span class="text-danger">*</span></label>
                        <select name="suspected_source" class="form-select form-select-sm" required>
                            @foreach (['CommunityAcquired', 'HospitalAcquired', 'IcuAcquired', 'PostSurgical', 'DeviceAssociated', 'Unknown'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">First Detected</label>
                        <input type="datetime-local" name="first_detected_at"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Lab Report ID</label>
                        <input type="number" name="lab_report_id" class="form-control form-control-sm"
                            placeholder="(required if Confirmed)">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Save Record</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Records --}}
        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Infection History</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:160px;">Tagged</th>
                            <th>Infection</th>
                            <th>Organism</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:110px;">Isolation</th>
                            <th style="width:130px;">Source</th>
                            <th style="width:90px;">Lab</th>
                            <th style="width:200px;" class="text-end pe-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $r)
                            @php
                                $sCol = match ($r->infection_status) {
                                    'Confirmed' => 'danger',
                                    'Suspected' => 'warning',
                                    'Resolved'  => 'success',
                                    'RuledOut'  => 'secondary',
                                    default     => 'dark',
                                };
                            @endphp
                            <tr class="{{ $r->is_active && $r->isolation_type !== 'None' ? 'table-warning' : '' }}">
                                <td class="ps-2"><small>{{ $r->tagged_at?->format('Y-m-d H:i') }}</small></td>
                                <td>{{ $r->infection_name ?? '-' }}</td>
                                <td>{{ $r->organism ?? '-' }}</td>
                                <td><span class="badge bg-{{ $sCol }}">{{ $r->infection_status }}</span></td>
                                <td>{{ $r->isolation_type }}</td>
                                <td><small>{{ $r->suspected_source }}</small></td>
                                <td>{{ $r->lab_report_id ? '#' . $r->lab_report_id : '-' }}</td>
                                <td class="text-end pe-2">
                                    @if ($r->is_active && $r->infection_status !== 'Resolved')
                                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="collapse"
                                            data-bs-target="#resolve-{{ $r->id }}">Resolve</button>
                                    @endif
                                </td>
                            </tr>
                            @if ($r->is_active && $r->infection_status !== 'Resolved')
                                <tr class="collapse" id="resolve-{{ $r->id }}">
                                    <td colspan="8">
                                        <form method="POST"
                                            action="{{ route('icu.admissions.infection.resolve', [$admission->id, $r->id]) }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-md-9">
                                                <input type="text" name="resolution_remarks"
                                                    class="form-control form-control-sm"
                                                    placeholder="Resolution remarks (required)" required>
                                            </div>
                                            <div class="col-md-3">
                                                <button class="btn btn-success btn-sm w-100">Confirm Resolve</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No infection records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
