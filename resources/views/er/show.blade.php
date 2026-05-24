@extends('backend.layouts.master')
@section('title', 'ER Patient · ' . (optional($er->patient)->patient_name ?? 'ER#' . $er->id))
@section('content')
<div class="container-fluid py-3">

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    @php $triage = $er->latestTriage; $triColour = $triage ? \App\Models\Er\ErTriage::levelColour($triage->triage_level) : 'secondary'; @endphp

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-heartbeat text-danger"></i>
                {{ optional($er->patient)->patient_name ?? 'Unknown patient' }}
                <span class="badge bg-{{ $er->statusBadgeClass() }}">{{ str_replace('_', ' ', $er->status) }}</span>
                @if ($triage)<span class="badge bg-{{ $triColour }}">TRIAGE: {{ $triage->triage_level }}</span>@endif
                <span class="badge bg-{{ $er->priorityBadgeClass() }}">{{ $er->priority }}</span>
            </h4>
            <small class="text-muted">
                <i class="bi bi-hash"></i> {{ optional($er->patient)->mrn ?? 'ER#'.$er->id }} ·
                <i class="bi bi-clock"></i> Arrived {{ $er->arrival_time?->diffForHumans() ?? '—' }} ·
                <i class="bi bi-person-badge"></i> {{ optional($er->doctor)->name ?? 'No doctor assigned' }}
                @if ($er->encounter)
                    · <i class="bi bi-clipboard"></i> Encounter <code>{{ $er->encounter->encounter_no }}</code>
                @endif
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('er.board') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Board</a>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="bi bi-arrow-left-right"></i> Transfer / Admit Patient
            </button>
        </div>
    </div>

    {{-- Quick info row --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="card bg-light p-2"><small class="text-muted">Age / Gender</small><div>{{ $er->age ?? '?' }} · {{ $er->gender ?? '?' }}</div></div></div>
        <div class="col-md-3"><div class="card bg-light p-2"><small class="text-muted">Blood Group</small><div>{{ $er->blood_group ?? '—' }}</div></div></div>
        <div class="col-md-3"><div class="card bg-light p-2"><small class="text-muted">Department</small><div>{{ optional($er->department)->name ?? '—' }}</div></div></div>
        <div class="col-md-3"><div class="card bg-light p-2"><small class="text-muted">Contact</small><div>{{ optional($er->patient)->mobileno ?? '—' }}</div></div></div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#triage"><i class="bi bi-funnel"></i> Triage ({{ $er->triages->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#notes"><i class="bi bi-journal-medical"></i> SOAP Notes ({{ $er->clinicalNotes->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#obs"><i class="bi bi-activity"></i> Observation ({{ $er->observations->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#transfers"><i class="bi bi-arrow-left-right"></i> Transfers ({{ $er->transfers->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bills"><i class="bi bi-receipt"></i> Bills ({{ $bills->count() }})</button></li>
    </ul>

    <div class="tab-content border border-top-0 p-3 bg-white">

        {{-- TRIAGE --}}
        <div class="tab-pane fade show active" id="triage">
            <form method="POST" action="{{ route('er.triage.store', $er->id) }}" class="row g-2 mb-3">
                @csrf
                <div class="col-md-2"><label class="form-label small">Triage Level *</label>
                    <select name="triage_level" class="form-select form-select-sm" required>
                        @foreach (['RED' => 'Critical', 'ORANGE' => 'Very Urgent', 'YELLOW' => 'Urgent', 'GREEN' => 'Stable', 'BLACK' => 'Deceased'] as $k => $v)
                            <option value="{{ $k }}">{{ $k }} — {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Pain (0-10)</label><input type="number" name="pain_score" min="0" max="10" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">BP</label><input name="blood_pressure" placeholder="120/80" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">Pulse</label><input type="number" name="pulse" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">RR</label><input type="number" name="respiratory_rate" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">SpO₂</label><input type="number" name="spo2" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">Temp °C</label><input type="number" step="0.1" name="temperature_c" class="form-control form-control-sm"></div>
                <div class="col-md-12"><label class="form-label small">Chief Complaint / Notes</label><textarea name="chief_complaint" rows="2" class="form-control form-control-sm"></textarea></div>
                <div class="col-md-12 text-end"><button class="btn btn-sm btn-danger">Record Triage</button></div>
            </form>
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Time</th><th>Level</th><th>BP</th><th>Pulse</th><th>SpO₂</th><th>Temp</th><th>Pain</th><th>Complaint</th></tr></thead>
                <tbody>
                @forelse ($er->triages as $t)
                    <tr>
                        <td><small>{{ $t->triaged_at?->format('Y-m-d H:i') }}</small></td>
                        <td><span class="badge bg-{{ \App\Models\Er\ErTriage::levelColour($t->triage_level) }}">{{ $t->triage_level }}</span></td>
                        <td>{{ $t->blood_pressure }}</td>
                        <td>{{ $t->pulse }}</td>
                        <td>{{ $t->spo2 }}</td>
                        <td>{{ $t->temperature_c }}</td>
                        <td>{{ $t->pain_score ?? '—' }}</td>
                        <td>{{ $t->chief_complaint }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">No triage recorded yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- SOAP NOTES --}}
        <div class="tab-pane fade" id="notes">
            <form method="POST" action="{{ route('er.note.store', $er->id) }}" class="row g-2 mb-3">
                @csrf
                <div class="col-md-3"><label class="form-label small">Type *</label>
                    <select name="note_type" class="form-select form-select-sm" required>
                        @foreach (['SOAP', 'PROGRESS', 'PROCEDURE', 'CONSULT', 'DISCHARGE'] as $n)<option>{{ $n }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label small">Doctor</label>
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach ($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label small">S — Subjective</label><textarea name="subjective" rows="2" class="form-control form-control-sm" placeholder="Patient complains of…"></textarea></div>
                <div class="col-md-6"><label class="form-label small">O — Objective</label><textarea name="objective" rows="2" class="form-control form-control-sm" placeholder="BP/Pulse/SpO₂/ECG findings…"></textarea></div>
                <div class="col-md-6"><label class="form-label small">A — Assessment</label><textarea name="assessment" rows="2" class="form-control form-control-sm" placeholder="Diagnosis / differential…"></textarea></div>
                <div class="col-md-6"><label class="form-label small">P — Plan</label><textarea name="plan" rows="2" class="form-control form-control-sm" placeholder="Treatment / orders / transfer plan…"></textarea></div>
                <div class="col-md-12 text-end"><button class="btn btn-sm btn-primary">Save Note</button></div>
            </form>
            @forelse ($er->clinicalNotes as $n)
                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between">
                            <strong><span class="badge bg-secondary">{{ $n->note_type }}</span> · {{ optional($n->doctor)->name ?? 'Doctor' }}</strong>
                            <small class="text-muted">{{ $n->recorded_at?->format('Y-m-d H:i') }}</small>
                        </div>
                        @if ($n->subjective) <div class="mt-1"><strong>S:</strong> {{ $n->subjective }}</div> @endif
                        @if ($n->objective)  <div><strong>O:</strong> {{ $n->objective }}</div> @endif
                        @if ($n->assessment) <div><strong>A:</strong> {{ $n->assessment }}</div> @endif
                        @if ($n->plan)       <div><strong>P:</strong> {{ $n->plan }}</div> @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3">No clinical notes yet</div>
            @endforelse
        </div>

        {{-- OBSERVATIONS --}}
        <div class="tab-pane fade" id="obs">
            <form method="POST" action="{{ route('er.observation.store', $er->id) }}" class="row g-2 mb-3">
                @csrf
                <div class="col-md-2"><label class="form-label small">Time</label><input type="datetime-local" name="observed_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">BP</label><input name="blood_pressure" placeholder="120/80" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">Pulse</label><input type="number" name="pulse" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">RR</label><input type="number" name="respiratory_rate" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">SpO₂</label><input type="number" name="spo2" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">Temp</label><input type="number" step="0.1" name="temperature_c" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">Pain</label><input type="number" name="pain_score" min="0" max="10" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">O₂ L/m</label><input type="number" step="0.5" name="o2_lpm" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">IN (ml)</label><input type="number" name="fluid_intake_ml" class="form-control form-control-sm"></div>
                <div class="col-md-1"><label class="form-label small">OUT (ml)</label><input type="number" name="fluid_output_ml" class="form-control form-control-sm"></div>
                <div class="col-md-12 text-end"><button class="btn btn-sm btn-info">Add Observation</button></div>
            </form>
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Time</th><th>BP</th><th>Pulse</th><th>RR</th><th>SpO₂</th><th>Temp</th><th>Pain</th><th>O₂</th><th>IN/OUT</th><th>Alert</th></tr></thead>
                <tbody>
                @forelse ($er->observations as $o)
                    <tr class="{{ $o->alert_critical ? 'table-danger' : '' }}">
                        <td><small>{{ $o->observed_at?->format('H:i') }}</small></td>
                        <td>{{ $o->blood_pressure }}</td>
                        <td>{{ $o->pulse }}</td>
                        <td>{{ $o->respiratory_rate }}</td>
                        <td>{{ $o->spo2 }}</td>
                        <td>{{ $o->temperature_c }}</td>
                        <td>{{ $o->pain_score ?? '—' }}</td>
                        <td>{{ $o->o2_lpm ?? '—' }}</td>
                        <td>{{ $o->fluid_intake_ml ?? 0 }} / {{ $o->fluid_output_ml ?? 0 }}</td>
                        <td>@if ($o->alert_critical)<span class="badge bg-danger">CRITICAL</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-3">No observations yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- TRANSFERS --}}
        <div class="tab-pane fade" id="transfers">
            <table class="table table-sm align-middle">
                <thead class="table-light"><tr><th>Time</th><th>Target</th><th>Bed</th><th>Doctor</th><th>Indication</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($er->transfers as $t)
                    <tr>
                        <td><small>{{ $t->requested_at?->format('Y-m-d H:i') }}</small></td>
                        <td><span class="badge bg-info">{{ $t->target }}</span></td>
                        <td>{{ optional(optional($t->targetBed)->bedType)->name }} {{ optional($t->targetBed)->name }}</td>
                        <td>{{ optional($t->targetDoctor)->name ?? '—' }}</td>
                        <td>{{ $t->clinical_indication }}</td>
                        <td><span class="badge bg-{{ ['PENDING'=>'warning text-dark','ACCEPTED'=>'info','COMPLETED'=>'success','REJECTED'=>'danger','CANCELLED'=>'secondary'][$t->status] ?? 'secondary' }}">{{ $t->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No transfers — use the red button above to initiate one.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- BILLS --}}
        <div class="tab-pane fade" id="bills">
            {{-- KPI tiles --}}
            <div class="row g-2 mb-3">
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-primary"><i class="bi bi-cash-stack"></i> Bill Grand</small>
                            <h5 class="mb-0">৳ {{ number_format($billSummary['grand'], 0) }}</h5>
                            <small class="text-muted">{{ $billSummary['count'] }} bill(s)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-success"><i class="bi bi-check-circle"></i> Paid</small>
                            <h5 class="mb-0">৳ {{ number_format($billSummary['paid'], 0) }}</h5>
                            <small class="text-muted">{{ $billSummary['payments'] }} payment(s)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-{{ $billSummary['due'] > 0 ? 'danger' : 'success' }} bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-{{ $billSummary['due'] > 0 ? 'danger' : 'success' }}"><i class="bi bi-currency-exchange"></i> Balance Due</small>
                            <h5 class="mb-0">৳ {{ number_format($billSummary['due'], 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-info"><i class="bi bi-plus-circle"></i> Auto-Posted</small>
                            <h5 class="mb-0">৳ {{ number_format($billSummary['postSum'], 0) }}</h5>
                            <small class="text-muted">{{ $billSummary['postings'] }} postings</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bills --}}
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Assembled Bills</h6>
                    <span class="badge bg-primary">{{ $bills->count() }}</span>
                </div>
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Bill No</th><th>Date</th><th>Type</th><th class="text-end">Grand ৳</th><th class="text-end">Paid ৳</th><th class="text-end">Due ৳</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    @forelse ($bills as $b)
                        @php $cls = ['paid'=>'success','final'=>'success','partially_paid'=>'warning text-dark','draft'=>'secondary','cancelled'=>'danger'][$b->status] ?? 'secondary'; @endphp
                        <tr>
                            <td><strong>{{ $b->bill_no }}</strong></td>
                            <td><small>{{ \Carbon\Carbon::parse($b->bill_date)->format('Y-m-d H:i') }}</small></td>
                            <td><span class="badge bg-secondary bg-opacity-15 text-secondary">{{ strtoupper($b->bill_type) }}</span></td>
                            <td class="text-end">{{ number_format($b->grand_total, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($b->paid_total, 2) }}</td>
                            <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger fw-bold' : '' }}">{{ number_format($b->balance_due, 2) }}</td>
                            <td><span class="badge bg-{{ $cls }}">{{ ucfirst(str_replace('_', ' ', $b->status)) }}</span></td>
                            <td class="text-nowrap">
                                <a href="{{ route('billing.bills.show', $b->id) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('billing.category.pdf', $b->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">No bills on this ER encounter yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Auto-posted charges --}}
            @if ($postings->isNotEmpty())
            <div class="card border-0 shadow-sm mb-2">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-info"></i>Auto-Posted Charges</h6>
                    <span class="badge bg-info">{{ $postings->count() }}</span>
                </div>
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Service</th><th>Trigger</th><th class="text-center">Qty</th><th class="text-end">Net ৳</th><th>When</th></tr></thead>
                    <tbody>
                        @foreach ($postings as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ optional($p->catalog)->name ?? $p->reason }}</strong>
                                    @if(optional($p->catalog)->code)<br><small class="text-muted">{{ $p->catalog->code }}</small>@endif
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $p->trigger_event }}</span></td>
                                <td class="text-center">{{ rtrim(rtrim(number_format($p->quantity, 2), '0'), '.') }}</td>
                                <td class="text-end fw-semibold">৳ {{ number_format($p->net_amount, 0) }}</td>
                                <td><small>{{ \Carbon\Carbon::parse($p->created_at)->diffForHumans() }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Bill payments --}}
            @if ($billPayments->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2 text-success"></i>Bill Payments</h6>
                    <span class="badge bg-success">{{ $billPayments->count() }}</span>
                </div>
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>#</th><th>Bill</th><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>Reference</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($billPayments as $bp)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('billing.bills.show', $bp->bill_id) }}">#{{ $bp->bill_id }}</a></td>
                                <td><small>{{ \Carbon\Carbon::parse($bp->payment_date ?? $bp->created_at)->format('Y-m-d H:i') }}</small></td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($bp->method ?? '—') }}</span></td>
                                <td class="text-end fw-semibold text-success">৳ {{ number_format($bp->amount, 0) }}</td>
                                <td><small>{{ $bp->reference_no ?? '—' }}</small></td>
                                <td><span class="badge bg-{{ ($bp->status ?? 'completed') === 'completed' ? 'success' : 'warning text-dark' }}">{{ ucfirst($bp->status ?? 'completed') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Transfer / Admit Modal (the mega-button) ── --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('er.transfer', $er->id) }}" class="modal-content">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Transfer / Admit Patient</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Pick the destination — bed allocation + clinical handover will be recorded; ER encounter closes for HOME / DISCHARGED / REFERRED / EXPIRED.</p>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label">Target *</label>
                        <div class="btn-group w-100 flex-wrap" role="group" id="targetButtons">
                            @foreach ([
                                'ICU'  => ['danger', 'heart-pulse', 'ICU'],
                                'CCU'  => ['danger', 'heart', 'CCU'],
                                'NICU' => ['warning text-dark', 'emoji-smile', 'NICU'],
                                'OT'   => ['primary', 'scissors', 'OT'],
                                'IPD'  => ['info', 'hospital', 'IPD'],
                                'WARD' => ['secondary', 'house', 'Ward'],
                                'HOME' => ['success', 'house-check', 'Home'],
                                'REFERRED' => ['dark', 'box-arrow-up-right', 'Refer'],
                                'EXPIRED' => ['dark', 'x-octagon', 'Expired'],
                            ] as $t => [$col, $ico, $label])
                                <input type="radio" class="btn-check" name="target" id="target_{{ $t }}" value="{{ $t }}" required>
                                <label class="btn btn-outline-{{ $col }} mb-1" for="target_{{ $t }}">
                                    <i class="bi bi-{{ $ico }}"></i> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Bed</label>
                        <select name="target_bed_id" class="form-select">
                            <option value="">-- Optional --</option>
                            @foreach ($beds as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} [{{ optional($b->bedType)->name }}] (৳ {{ $b->rent }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Doctor</label>
                        <select name="target_doctor_id" class="form-select">
                            <option value="">-- Optional --</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Clinical Indication</label>
                        <input name="clinical_indication" class="form-control" placeholder="Why is the patient being transferred?">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Handover Summary</label>
                        <textarea name="handover_summary" rows="3" class="form-control" placeholder="Vitals, treatments given, pending orders, allergies, family contacted, etc."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-circle"></i> Confirm Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
