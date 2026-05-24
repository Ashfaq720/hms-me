@extends('backend.layouts.master')
@section('title', 'My Patients — Dr. ' . $doctor->name)

@push('styles')
<style>
.dp-hero {
    background: linear-gradient(135deg, #eef2ff 0%, #fce7f3 100%);
    border-radius: 16px;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 4px 24px rgba(20,30,50,.06);
}
.dp-kpi {
    border: 0; border-radius: 14px; padding: 1rem 1.15rem;
    background: #fff; box-shadow: 0 4px 24px rgba(20,30,50,.06);
    position: relative; overflow: hidden;
}
.dp-kpi::before {
    content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 4px;
    background: var(--accent, #0d6efd);
}
.dp-kpi .icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem;
    background: var(--accent-bg, rgba(13,110,253,.1)); color: var(--accent, #0d6efd);
}
.dp-kpi .label { color: #6b7280; font-size: .76rem; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.dp-kpi .value { font-size: 1.55rem; font-weight: 700; color: #111827; line-height: 1.1; }
.dp-tabs .nav-link { font-weight: 500; color: #475569; border-radius: 100px; padding: .5rem 1.1rem; }
.dp-tabs .nav-link.active { background: #0d6efd; color: #fff; box-shadow: 0 4px 12px rgba(13,110,253,.25); }
.dp-table thead th { background: #f9fafc; font-weight: 500; font-size: .72rem;
    text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
.dp-table tbody tr:hover { background: #fafbff; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Hero --}}
    <div class="dp-hero mb-4 d-flex flex-wrap align-items-center gap-3">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            @if ($doctor->image)
                <img src="{{ asset('storage/'.$doctor->image) }}" alt="dr"
                     class="rounded-circle border border-3 border-white shadow"
                     style="width:72px; height:72px; object-fit:cover;">
            @else
                <div class="rounded-circle bg-white shadow d-flex align-items-center justify-content-center"
                     style="width:72px; height:72px;">
                    <i class="bi bi-person-vcard text-primary" style="font-size:1.8rem;"></i>
                </div>
            @endif
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="letter-spacing:.05em;">Doctor Portal</small>
                <h4 class="mb-1 fw-bold">Dr. {{ $doctor->name }}</h4>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><i class="bi bi-buildings"></i> {{ optional($doctor->department)->name ?? '—' }}</span>
                    <span><i class="bi bi-mortarboard"></i> {{ optional($doctor->specialist)->name ?? 'Generalist' }}</span>
                    <span><i class="bi bi-card-text"></i> <code>{{ $doctor->doctor_code ?? '—' }}</code></span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('opd-patients.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New OPD Visit</a>
        </div>
    </div>

    {{-- KPI tiles --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#3b82f6; --accent-bg:rgba(59,130,246,.1);">
                <div class="d-flex justify-content-between"><div class="label">Today OPD</div><div class="icon"><i class="bi bi-calendar2-week"></i></div></div>
                <div class="value">{{ $stats['today_opd_count'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#06b6d4; --accent-bg:rgba(6,182,212,.1);">
                <div class="d-flex justify-content-between"><div class="label">Upcoming</div><div class="icon"><i class="bi bi-calendar3"></i></div></div>
                <div class="value">{{ $stats['upcoming_opd_count'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#10b981; --accent-bg:rgba(16,185,129,.1);">
                <div class="d-flex justify-content-between"><div class="label">Active IPD</div><div class="icon"><i class="bi bi-hospital"></i></div></div>
                <div class="value">{{ $stats['ipd_active_count'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#6b7280; --accent-bg:rgba(107,114,128,.12);">
                <div class="d-flex justify-content-between"><div class="label">Discharged</div><div class="icon"><i class="bi bi-box-arrow-right"></i></div></div>
                <div class="value">{{ $stats['ipd_discharged_count'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#f59e0b; --accent-bg:rgba(245,158,11,.12);">
                <div class="d-flex justify-content-between"><div class="label">Today Appt</div><div class="icon"><i class="bi bi-clock"></i></div></div>
                <div class="value">{{ $stats['today_appointments_count'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="dp-kpi" style="--accent:#ef4444; --accent-bg:rgba(239,68,68,.12);">
                <div class="d-flex justify-content-between"><div class="label">This-month Rx</div><div class="icon"><i class="bi bi-prescription2"></i></div></div>
                <div class="value">{{ $stats['rx_this_month'] }}</div>
                <small class="text-muted">of {{ $stats['rx_total'] }} total</small>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills dp-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link {{ $tab === 'today' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-today"><i class="bi bi-calendar2-week"></i> Today OPD ({{ $today_opd->total() }})</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'upcoming' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-upcoming"><i class="bi bi-calendar3"></i> Upcoming ({{ $upcoming_opd->total() }})</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'ipd' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-ipd"><i class="bi bi-hospital"></i> My IPD ({{ $ipd_patients->total() }})</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'appt' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-appt"><i class="bi bi-clock"></i> Appointments ({{ $today_appointments->total() }})</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'rx' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-rx"><i class="bi bi-prescription2"></i> Recent Rx</button></li>
    </ul>

    <div class="tab-content">
        {{-- TODAY OPD --}}
        <div class="tab-pane fade {{ $tab === 'today' ? 'show active' : '' }}" id="tab-today">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dp-table">
                        <thead class="table-light"><tr><th>#</th><th>Token</th><th>Patient</th><th>Slot</th><th>Department</th><th>Complaint</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse ($today_opd as $o)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $o->token_no }}</strong></td>
                                    <td>{{ optional($o->patient)->patient_name }}<br><small class="text-muted">{{ optional($o->patient)->mobileno }}</small></td>
                                    <td>{{ $o->slot_time_from }} – {{ $o->slot_time_to }}</td>
                                    <td><small>{{ optional($o->department)->name }}</small></td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($o->chief_complaint ?? '', 40) }}</small></td>
                                    <td><span class="badge bg-secondary">{{ $o->status }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('opd-patients.show', $o->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No patients booked today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($today_opd->hasPages())
                    <div class="p-2">{{ $today_opd->links() }}</div>
                @endif
            </div>
        </div>

        {{-- UPCOMING --}}
        <div class="tab-pane fade {{ $tab === 'upcoming' ? 'show active' : '' }}" id="tab-upcoming">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dp-table">
                        <thead class="table-light"><tr><th>#</th><th>Date</th><th>Token</th><th>Patient</th><th>Department</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse ($upcoming_opd as $o)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($o->date)->format('Y-m-d') }}</td>
                                    <td><strong>{{ $o->token_no }}</strong></td>
                                    <td>{{ optional($o->patient)->patient_name }}</td>
                                    <td><small>{{ optional($o->department)->name }}</small></td>
                                    <td class="text-end">
                                        <a href="{{ route('opd-patients.show', $o->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No upcoming visits.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($upcoming_opd->hasPages())
                    <div class="p-2">{{ $upcoming_opd->links() }}</div>
                @endif
            </div>
        </div>

        {{-- IPD --}}
        <div class="tab-pane fade {{ $tab === 'ipd' ? 'show active' : '' }}" id="tab-ipd">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dp-table">
                        <thead class="table-light"><tr><th>#</th><th>IPD No</th><th>Patient</th><th>Admitted</th><th>Bed</th><th>Department</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse ($ipd_patients as $i)
                                @php $bed = optional($i->bedAllocations->last())->bed; @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $i->ipd_no ?? '#'.$i->id }}</strong></td>
                                    <td>{{ optional($i->patient)->patient_name }}</td>
                                    <td><small>{{ $i->admission_date }}</small></td>
                                    <td>{{ optional($bed)->name ?? '—' }}</td>
                                    <td><small>{{ optional($i->department)->name }}</small></td>
                                    <td><span class="badge bg-success">{{ $i->status }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('ipd-patients.ipd-patients.show', $i->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No active IPD patients under your care.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($ipd_patients->hasPages())
                    <div class="p-2">{{ $ipd_patients->links() }}</div>
                @endif
            </div>
        </div>

        {{-- APPOINTMENTS --}}
        <div class="tab-pane fade {{ $tab === 'appt' ? 'show active' : '' }}" id="tab-appt">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dp-table">
                        <thead class="table-light"><tr><th>#</th><th>Time</th><th>Patient</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse ($today_appointments as $a)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $a->slot_time_from }} – {{ $a->slot_time_to }}</td>
                                    <td>{{ optional($a->patient)->patient_name }}</td>
                                    <td><span class="badge bg-info">{{ ucfirst($a->visit_status) }}</span></td>
                                    <td class="text-end">
                                        @if (optional($a->patient)->id)
                                            <a href="{{ route('patients.show', $a->patient->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-person"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No appointments today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($today_appointments->hasPages())
                    <div class="p-2">{{ $today_appointments->links() }}</div>
                @endif
            </div>
        </div>

        {{-- RECENT RX --}}
        <div class="tab-pane fade {{ $tab === 'rx' ? 'show active' : '' }}" id="tab-rx">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dp-table">
                        <thead class="table-light"><tr><th>#</th><th>Date</th><th>Rx No</th><th>Patient</th><th>Findings</th></tr></thead>
                        <tbody>
                            @forelse ($recent_prescriptions as $r)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><small>{{ \Carbon\Carbon::parse($r->date ?? $r->created_at)->format('Y-m-d') }}</small></td>
                                    <td><strong>{{ $r->prescription_no ?? '#'.$r->id }}</strong></td>
                                    <td>{{ optional($r->patient)->patient_name }}</td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($r->findings ?? '', 60) }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No prescriptions written yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
