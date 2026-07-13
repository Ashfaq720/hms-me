@extends('backend.layouts.master')

@section('title', 'ER Patient — ' . ($erPatient->patient?->patient_name ?? '#'.$erPatient->id))

@section('content')
@php $p = $erPatient; $pt = $p->patient; @endphp
<div class="container-fluid">

    <ul class="nav nav-pills mb-3 flex-wrap gap-1">
        <li class="nav-item"><a class="nav-link" href="{{ route('er.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('er.patients.index') }}"><i class="bi bi-clipboard2-pulse me-1"></i> Tracking Board</a></li>
        <li class="nav-item active"><a class="nav-link active" href="javascript:void(0)"><i class="bi bi-person-vcard me-1"></i> Patient #{{ $p->id }}</a></li>
    </ul>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif

    {{-- ─── Hero ─── --}}
    <div class="card border-{{ $p->triage_color }} mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex gap-2 align-items-center mb-1 flex-wrap">
                        <span class="badge bg-{{ $p->triage_color }} fs-6">{{ $p->triage_label }}</span>
                        <span class="badge {{ $p->status_badge_class }} fs-6">{{ $p->status }}</span>
                        @if(in_array($p->status, \App\Models\FrontDesk\ErPatient::STATUSES_ACTIVE, true))
                            <span class="badge bg-warning text-dark">Waiting {{ $p->waiting_minutes }} min</span>
                        @endif
                    </div>
                    <h1 class="app-page-title mb-1">{{ $pt?->patient_name ?? 'Unknown' }}</h1>
                    <div class="text-muted small">
                        @if($pt?->mrn) <strong>MRN:</strong> {{ $pt->mrn }} @endif
                        @if($pt?->mobileno) · <strong>Mobile:</strong> {{ $pt->mobileno }} @endif
                        @if($pt?->gender) · {{ $pt->gender }} @endif
                        @if($pt?->dob) · {{ \Carbon\Carbon::parse($pt->dob)->age }}y @endif
                        @if($pt?->blood_group) · <span class="badge bg-danger-subtle text-danger border">{{ $pt->blood_group }}</span> @endif
                    </div>
                    <div class="small text-muted mt-1">
                        Arrived {{ optional($p->arrival_time)?->format('Y-m-d H:i') }} · Doctor: {{ $p->doctor?->name ?? '—' }} · Dept: {{ $p->department?->name ?? '—' }}
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('front_desk.er_registration.edit', $p) }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Intake
                    </a>
                    <a href="{{ route('er.patients.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- LEFT: clinical info + update form --}}
        <div class="col-lg-7">

            <div class="card mb-3">
                <div class="card-header py-2"><strong><i class="bi bi-info-circle"></i> Intake Details</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Description / Chief Complaint</dt>
                        <dd class="col-sm-8">{{ $p->description ?? '—' }}</dd>
                        <dt class="col-sm-4">Mode of Arrival</dt>
                        <dd class="col-sm-8">{{ $p->arrival_mode ?? 'Walk-in / Unspecified' }}</dd>
                        <dt class="col-sm-4">Guardian</dt>
                        <dd class="col-sm-8">{{ $p->third_party_name ?? '—' }}
                            @if($p->relation) ({{ $p->relation }})@endif
                            @if($p->third_party_contact) · {{ $p->third_party_contact }}@endif
                        </dd>
                        <dt class="col-sm-4">Case ID</dt>
                        <dd class="col-sm-8">{{ $p->case_id ? '#'.$p->case_id : '—' }}</dd>
                        <dt class="col-sm-4">Discount Type</dt>
                        <dd class="col-sm-8">{{ $p->discount_type ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Lifecycle / clinical update --}}
            <div class="card mb-3 border-info-subtle">
                <div class="card-header bg-info-subtle py-2"><strong><i class="bi bi-arrow-repeat"></i> Update Status / Triage</strong></div>
                <form method="POST" action="{{ route('er.patients.update', $p) }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    @foreach(\App\Models\FrontDesk\ErPatient::STATUSES as $st)
                                        <option value="{{ $st }}" @selected($p->status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Triage Priority</label>
                                <select name="priority" class="form-select form-select-sm">
                                    @foreach(\App\Models\FrontDesk\ErPatient::PRIORITIES as $pri)
                                        <option value="{{ $pri }}" @selected($p->priority === $pri)>{{ $pri }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small">Clinical Remarks (appended to history)</label>
                                <textarea name="remarks" rows="3" class="form-control form-control-sm"
                                          placeholder="e.g. Started IV fluids, ECG done, vitals stabilizing">{{ $p->remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end py-2">
                        <button type="submit" class="btn btn-sm btn-info"><i class="bi bi-save"></i> Save Update</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: actions --}}
        <div class="col-lg-5">

            {{-- Transfer / Admit --}}
            @if(! in_array($p->status, [\App\Models\FrontDesk\ErPatient::STATUS_DISCHARGED, \App\Models\FrontDesk\ErPatient::STATUS_EXPIRED, \App\Models\FrontDesk\ErPatient::STATUS_CANCELLED], true))
                <div class="card mb-3 border-success">
                    <div class="card-header bg-success-subtle py-2"><strong><i class="bi bi-arrow-right-circle text-success"></i> Transfer / Admit</strong></div>
                    <form method="POST" action="{{ route('er.patients.transfer', $p) }}">
                        @csrf
                        <div class="card-body">
                            <label class="form-label small">Destination</label>
                            <div class="d-grid gap-2">
                                <button name="destination" value="IPD" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-hospital me-1"></i> Admit to IPD</button>
                                <button name="destination" value="ICU" class="btn btn-outline-danger btn-sm text-start"><i class="bi bi-heart-pulse me-1"></i> Transfer to ICU</button>
                                <button name="destination" value="CCU" class="btn btn-outline-danger btn-sm text-start"><i class="bi bi-heart me-1"></i> Transfer to CCU (Cardiac)</button>
                                <button name="destination" value="NICU" class="btn btn-outline-info btn-sm text-start"><i class="bi bi-emoji-smile me-1"></i> Transfer to NICU (Neonate)</button>
                                <button name="destination" value="OT" class="btn btn-outline-warning btn-sm text-start"><i class="bi bi-scissors me-1"></i> Request OT</button>
                                <button name="destination" value="REFER" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-box-arrow-up-right me-1"></i> Refer to External</button>
                            </div>
                            <input type="text" name="remarks" class="form-control form-control-sm mt-2" placeholder="Handover note (optional)">
                        </div>
                    </form>
                </div>
            @endif

            {{-- Close (Discharge / Expired / Cancel) --}}
            @if(! in_array($p->status, [\App\Models\FrontDesk\ErPatient::STATUS_DISCHARGED, \App\Models\FrontDesk\ErPatient::STATUS_EXPIRED, \App\Models\FrontDesk\ErPatient::STATUS_CANCELLED], true))
                <div class="card mb-3">
                    <div class="card-header py-2"><strong><i class="bi bi-box-arrow-right"></i> Close ER Visit</strong></div>
                    <form method="POST" action="{{ route('er.patients.close', $p) }}">
                        @csrf
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button name="outcome" value="{{ \App\Models\FrontDesk\ErPatient::STATUS_DISCHARGED }}"
                                        class="btn btn-outline-dark btn-sm"
                                        onclick="return confirm('Discharge this patient?')">
                                    <i class="bi bi-check2-circle me-1"></i> Discharge
                                </button>
                                <button name="outcome" value="{{ \App\Models\FrontDesk\ErPatient::STATUS_EXPIRED }}"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Mark this patient as Expired?')">
                                    <i class="bi bi-heartbreak me-1"></i> Mark Expired
                                </button>
                                <button name="outcome" value="{{ \App\Models\FrontDesk\ErPatient::STATUS_CANCELLED }}"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="return confirm('Cancel this ER visit?')">
                                    <i class="bi bi-x-circle me-1"></i> Cancel Visit
                                </button>
                            </div>
                            <input type="text" name="remarks" class="form-control form-control-sm mt-2" placeholder="Closing note (optional)">
                        </div>
                    </form>
                </div>
            @endif

            {{-- Patient history --}}
            @if($caseHistory && $caseHistory->count())
                <div class="card mb-3">
                    <div class="card-header py-2"><strong><i class="bi bi-clock-history"></i> Recent Patient History</strong></div>
                    <ul class="list-group list-group-flush">
                        @foreach($caseHistory as $h)
                            <li class="list-group-item small">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $h->type ?? 'Visit' }}</strong>
                                    <span class="text-muted">{{ optional($h->created_at)?->format('Y-m-d') }}</span>
                                </div>
                                <div class="text-muted">{{ \Illuminate\Support\Str::limit($h->description ?? $h->notes ?? '', 100) }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
