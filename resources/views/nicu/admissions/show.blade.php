@extends('backend.layouts.master')

@section('title', 'NICU Admission · ' . $admission->baby_id)

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-clipboard2-heart text-info"></i>
                NICU Admission — <strong>{{ $admission->baby_id }}</strong>
                @if ($admission->is_critical) <span class="badge bg-danger ms-1">CRITICAL</span> @endif
                @if ($admission->is_preterm)  <span class="badge bg-warning text-dark ms-1">PRETERM</span> @endif
                @if ($admission->is_low_birth_weight) <span class="badge bg-warning text-dark ms-1">LBW</span> @endif
            </h4>
            <small class="text-muted">
                Status: <strong>{{ ucfirst($admission->status) }}</strong>
                · Admitted {{ optional($admission->admission_time)->format('Y-m-d H:i') ?: '—' }}
                @if ($admission->discharge_time) · Discharged {{ $admission->discharge_time->format('Y-m-d H:i') }} @endif
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('nicu.admissions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="{{ route('nicu.dashboard') }}" class="btn btn-sm btn-outline-info"><i class="bi bi-grid"></i> Dashboard</a>
        </div>
    </div>

    {{-- Baby + Mother info --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light"><strong><i class="bi bi-emoji-smile"></i> Baby & Birth Details</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted">Baby Name</small>
                            <div class="fw-semibold">{{ optional($admission->patient)->patient_name ?: '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Gender</small>
                            <div>{{ optional($admission->patient)->gender ?: '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Source</small>
                            <div><span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $admission->source ?: '—' }}</span></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Birth Weight</small>
                            <div class="fw-semibold">{{ $admission->birth_weight_g ? number_format($admission->birth_weight_g) . ' g' : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Birth Length</small>
                            <div>{{ $admission->birth_length_cm ? $admission->birth_length_cm . ' cm' : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Head Circ</small>
                            <div>{{ $admission->head_circumference_cm ? $admission->head_circumference_cm . ' cm' : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Gestational Age</small>
                            <div>{{ $admission->gestational_age_weeks ? $admission->gestational_age_weeks . ' wks' : '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Birth Type</small>
                            <div>{{ $admission->birth_type ?: '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Multiple Birth?</small>
                            <div>{{ $admission->is_multiple_birth ? 'Yes · order ' . ($admission->birth_order ?? '?') : 'No' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">APGAR (1·5·10 min)</small>
                            <div>
                                <span class="badge bg-light text-dark border">
                                    {{ $admission->apgar_1min ?? '?' }} · {{ $admission->apgar_5min ?? '?' }} · {{ $admission->apgar_10min ?? '?' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light"><strong><i class="bi bi-person-heart"></i> Mother & Resources</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <small class="text-muted">Mother</small>
                            <div class="fw-semibold">{{ optional($admission->mother)->patient_name ?: '—' }}</div>
                            <small class="text-muted">{{ optional($admission->mother)->mobileno ?: '' }}</small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Active Resources</small>
                            @forelse ($admission->resources->where('status', 'active') as $r)
                                <span class="badge bg-info bg-opacity-15 text-info">
                                    <i class="bi bi-plug"></i> {{ $r->resource_type }}
                                    @if (optional($r->bed)->name) · Bed: {{ $r->bed->name }} @endif
                                </span>
                            @empty
                                <small class="text-muted">No resources allocated</small>
                            @endforelse
                        </div>
                        @if ($admission->admission_notes)
                            <div class="col-12">
                                <small class="text-muted">Admission Notes</small>
                                <div class="small">{{ $admission->admission_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Vitals row --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
            <strong class="text-danger"><i class="bi bi-activity"></i> Latest Vitals</strong>
            <a href="{{ route('nicu.vitals.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-danger">
                View all ({{ $vitals->count() >= 20 ? '20+' : $vitals->count() }})
            </a>
        </div>
        <div class="card-body">
            @if ($latestVital)
                <div class="row g-2 text-center">
                    <div class="col"><small class="text-muted">HR</small><h5 class="mb-0">{{ $latestVital->heart_rate ?? '—' }} <small>bpm</small></h5></div>
                    <div class="col"><small class="text-muted">RR</small><h5 class="mb-0">{{ $latestVital->respiratory_rate ?? '—' }} <small>/min</small></h5></div>
                    <div class="col"><small class="text-muted">SpO₂</small><h5 class="mb-0">{{ $latestVital->spo2 ?? '—' }} <small>%</small></h5></div>
                    <div class="col"><small class="text-muted">Temp</small><h5 class="mb-0">{{ $latestVital->temperature ?? '—' }} <small>°F</small></h5></div>
                    <div class="col"><small class="text-muted">Recorded</small><h6 class="mb-0">{{ $latestVital->recorded_at ? \Carbon\Carbon::parse($latestVital->recorded_at)->diffForHumans() : '—' }}</h6></div>
                </div>
            @else
                <small class="text-muted">No vitals recorded yet — <a href="{{ route('nicu.vitals.index') }}?admission_id={{ $admission->id }}">add the first reading →</a></small>
            @endif
        </div>
    </div>

    {{-- Tabs for sub-modules --}}
    <ul class="nav nav-tabs mb-2" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-growth"><i class="bi bi-graph-up"></i> Growth ({{ $growth->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-feeds"><i class="bi bi-cup-hot"></i> Feeding ({{ $feeds->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-meds"><i class="bi bi-capsule"></i> Meds ({{ $meds->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-proc"><i class="bi bi-bandaid"></i> Procedures ({{ $procedures->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-infec"><i class="bi bi-shield-exclamation"></i> Infections ({{ $infections->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-consents"><i class="bi bi-file-text"></i> Consents ({{ $consents->count() }})</button></li>
    </ul>

    <div class="tab-content">
        {{-- GROWTH --}}
        <div class="tab-pane fade show active" id="tab-growth">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-success"><i class="bi bi-graph-up"></i> Growth Records</strong>
                    <a href="{{ route('nicu.growth.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Weight (g)</th><th>Length (cm)</th><th>Head Circ (cm)</th><th>Notes</th></tr></thead>
                        <tbody>
                            @forelse ($growth as $g)
                                <tr>
                                    <td><small>{{ optional($g->measured_on)->format('Y-m-d') }}</small></td>
                                    <td>{{ $g->weight_g ?? '—' }}</td>
                                    <td>{{ $g->length_cm ?? '—' }}</td>
                                    <td>{{ $g->head_circumference_cm ?? '—' }}</td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($g->notes ?? '', 40) }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No growth records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- FEEDING --}}
        <div class="tab-pane fade" id="tab-feeds">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-warning"><i class="bi bi-cup-hot"></i> Feeding Schedules</strong>
                    <a href="{{ route('nicu.feeding.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>Method</th><th>Volume</th><th>Frequency</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($feeds as $f)
                                <tr>
                                    <td>{{ $f->feed_type ?? '—' }}</td>
                                    <td>{{ $f->feed_method ?? '—' }}</td>
                                    <td>{{ $f->volume_per_feed_ml ?? '—' }} ml</td>
                                    <td>{{ $f->frequency_hours ? 'q' . $f->frequency_hours . 'h' : '—' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $f->status ?? '—' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No feeding schedule</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MEDICATIONS --}}
        <div class="tab-pane fade" id="tab-meds">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-info"><i class="bi bi-capsule"></i> Medication Orders</strong>
                    <a href="{{ route('nicu.medications.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-info">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Drug</th><th>Dose</th><th>Route</th><th>Frequency</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($meds as $m)
                                <tr>
                                    <td>{{ $m->drug_name ?? '—' }}</td>
                                    <td>{{ $m->dose ?? '—' }} {{ $m->dose_unit ?? '' }}</td>
                                    <td>{{ $m->route ?? '—' }}</td>
                                    <td>{{ $m->frequency ?? '—' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $m->status ?? '—' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No medication orders</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PROCEDURES --}}
        <div class="tab-pane fade" id="tab-proc">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-primary"><i class="bi bi-bandaid"></i> Procedures</strong>
                    <a href="{{ route('nicu.procedures.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Procedure</th><th>Date</th><th>Performed By</th><th>Notes</th></tr></thead>
                        <tbody>
                            @forelse ($procedures as $p)
                                <tr>
                                    <td>{{ $p->procedure_name ?? '—' }}</td>
                                    <td><small>{{ optional($p->performed_at)->format('Y-m-d H:i') }}</small></td>
                                    <td><small>{{ $p->performed_by_name ?? '—' }}</small></td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($p->notes ?? '', 40) }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No procedures</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- INFECTIONS --}}
        <div class="tab-pane fade" id="tab-infec">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-danger"><i class="bi bi-shield-exclamation"></i> Infections</strong>
                    <a href="{{ route('nicu.infections.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-danger">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Organism</th><th>Site</th><th>Date</th><th>Antibiotics</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($infections as $i)
                                <tr>
                                    <td>{{ $i->organism ?? '—' }}</td>
                                    <td>{{ $i->infection_site ?? '—' }}</td>
                                    <td><small>{{ optional($i->detected_at)->format('Y-m-d') }}</small></td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($i->antibiotics ?? '', 40) }}</small></td>
                                    <td><span class="badge bg-{{ $i->status === 'active' ? 'danger' : 'secondary' }}">{{ ucfirst($i->status ?? '') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No infections recorded</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CONSENTS --}}
        <div class="tab-pane fade" id="tab-consents">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary bg-opacity-10 d-flex justify-content-between align-items-center">
                    <strong class="text-secondary"><i class="bi bi-file-text"></i> Consents</strong>
                    <a href="{{ route('nicu.consents.index') }}?admission_id={{ $admission->id }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>Signed By</th><th>Signed At</th><th>Witness</th></tr></thead>
                        <tbody>
                            @forelse ($consents as $c)
                                <tr>
                                    <td>{{ $c->consent_type ?? '—' }}</td>
                                    <td>{{ $c->signed_by_name ?? '—' }}</td>
                                    <td><small>{{ optional($c->signed_at)->format('Y-m-d H:i') }}</small></td>
                                    <td><small>{{ $c->witness_name ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No consents on file</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
