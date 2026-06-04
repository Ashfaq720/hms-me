@extends('backend.layouts.master')

@section('title', 'Infection Control Reports')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <h1 class="app-page-title">Infection Control Reports</h1>
        </div>

        <form method="GET" class="row g-2 align-items-end mt-1 mb-2">
            <div class="col-md-2"><label class="form-label small">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">ICU Type</label>
                <select name="icu_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                        <option value="{{ $t }}" {{ $icuType === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label small">Isolation Type</label>
                <select name="isolation_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach (['Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                        <option value="{{ $t }}" {{ $isolationType === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-primary">Apply</button>
                <a href="{{ route('icu.infection.reports') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        {{-- KPI cards --}}
        <div class="row g-2">
            <div class="col-md-3"><div class="card"><div class="card-body py-2">
                <div class="text-muted small">Total Records</div>
                <div class="fs-4 fw-semibold">{{ $records->count() }}</div>
            </div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body py-2">
                <div class="text-muted small">ICU Patients in Window</div>
                <div class="fs-4 fw-semibold">{{ $totalIcuPatients }}</div>
            </div></div></div>
            <div class="col-md-3"><div class="card border-warning-subtle"><div class="card-body py-2">
                <div class="text-muted small">Infection Rate</div>
                <div class="fs-4 fw-semibold">{{ $infectionRate }}%</div>
            </div></div></div>
            <div class="col-md-3"><div class="card border-danger-subtle"><div class="card-body py-2">
                <div class="text-muted small">ICU-Acquired</div>
                <div class="fs-4 fw-semibold text-danger">{{ $icuAcquired }}</div>
                <div class="text-muted small">Device-Associated: {{ $deviceAssociated }}</div>
            </div></div></div>
        </div>

        {{-- Currently isolated --}}
        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Currently Isolated Patients</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2">Case</th>
                            <th>Patient</th>
                            <th>Bed</th>
                            <th>Isolation</th>
                            <th>Infection</th>
                            <th>Tagged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($isolationPatients as $r)
                            <tr>
                                <td class="ps-2"><a href="{{ route('icu.admissions.show', $r->icu_admission_id) }}">
                                    {{ $r->icu_case_id }}</a></td>
                                <td>{{ $r->admission?->patient?->patient_name ?? '-' }}</td>
                                <td>{{ $r->admission?->bed?->name ?? '-' }}</td>
                                <td>{{ $r->isolation_type }}</td>
                                <td>{{ $r->infection_name ?? '-' }} ({{ $r->infection_status }})</td>
                                <td><small>{{ $r->tagged_at?->format('Y-m-d H:i') }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No patients currently isolated.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Breakdowns --}}
        <div class="row g-2 mt-1">
            <div class="col-md-4">
                <div class="card"><div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">By Status</h6>
                    <table class="table table-sm mb-0">
                        @foreach ($byStatus as $status => $n)
                            <tr><td class="ps-2">{{ $status }}</td><td class="text-end pe-2">{{ $n }}</td></tr>
                        @endforeach
                    </table>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">By Source</h6>
                    <table class="table table-sm mb-0">
                        @foreach ($bySource as $src => $n)
                            <tr><td class="ps-2">{{ $src }}</td><td class="text-end pe-2">{{ $n }}</td></tr>
                        @endforeach
                    </table>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body p-2">
                    <h6 class="card-title px-2 pt-2">By Organism</h6>
                    <table class="table table-sm mb-0">
                        @forelse ($byOrganism as $o => $n)
                            <tr><td class="ps-2">{{ $o }}</td><td class="text-end pe-2">{{ $n }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted small ps-2">No organism data.</td></tr>
                        @endforelse
                    </table>
                </div></div>
            </div>
        </div>

        {{-- Antibiotics --}}
        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Antibiotic Usage</h6>
                <p class="px-2 mb-1 small text-muted">
                    Long-running (>7 days, still active): <strong>{{ $longRunning->count() }}</strong>
                </p>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2">Case</th>
                            <th>Antibiotic</th>
                            <th>Start</th>
                            <th>Stop</th>
                            <th style="width:80px;">Days</th>
                            <th style="width:90px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($antibiotics as $a)
                            @php $longRun = $a->status === 'Active' && $a->durationDays() > 7; @endphp
                            <tr class="{{ $longRun ? 'table-warning' : '' }}">
                                <td class="ps-2"><a href="{{ route('icu.admissions.antibiotics.index', $a->icu_admission_id) }}">
                                    {{ $a->icu_case_id }}</a></td>
                                <td>{{ $a->antibiotic_name }}</td>
                                <td><small>{{ $a->start_date?->format('Y-m-d') }}</small></td>
                                <td><small>{{ $a->stop_date?->format('Y-m-d') ?? '-' }}</small></td>
                                <td>{{ $a->durationDays() }}</td>
                                <td>{{ $a->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No antibiotic records in window.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
