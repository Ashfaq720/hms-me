@extends('backend.layouts.master')

@section('title', 'Surgery Request — ' . $surgeryRequest->request_no)

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">
                {{ $surgeryRequest->request_no }}
                <span class="badge {{ $surgeryRequest->status_badge_class }}">{{ $surgeryRequest->status }}</span>
                @if($surgeryRequest->is_emergency)<span class="badge bg-danger">EMERGENCY</span>@endif
            </h1>
            <div class="text-muted">
                {{ optional($surgeryRequest->patient)->patient_name }}
                · {{ $surgeryRequest->encounter_type }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ot.surgery-requests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            @if(in_array($surgeryRequest->status, ['Draft','Submitted']))
                <a href="{{ route('ot.surgery-requests.edit', $surgeryRequest->id) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            @endif

            @php $s = $surgeryRequest->status; @endphp

            @if($s === 'Draft')
                <form action="{{ route('ot.surgery-requests.submit', $surgeryRequest->id) }}" method="POST">@csrf<button class="btn btn-primary">Submit</button></form>
            @endif

            @if($s === 'Submitted')
                <form action="{{ route('ot.surgery-requests.review', $surgeryRequest->id) }}" method="POST">@csrf<button class="btn btn-info">Start Review</button></form>
            @endif

            @if($s === 'Under Review')
                <form action="{{ route('ot.surgery-requests.accept', $surgeryRequest->id) }}" method="POST">@csrf<button class="btn btn-success">Accept</button></form>
                <button type="button" class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#sendBackModal"><i class="bi bi-arrow-counterclockwise"></i> Send Back</button>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#pendingInfoModal"><i class="bi bi-info-circle"></i> Pending Info</button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                @if($surgeryRequest->is_emergency || $surgeryRequest->priority === 'Emergency')
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#fastTrackModal"><i class="bi bi-lightning"></i> Fast-Track</button>
                @endif
            @endif

            @if(in_array($s, ['Pending Information','Sent Back for Correction']))
                <a href="{{ route('ot.surgery-requests.edit', $surgeryRequest->id) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit &amp; Resubmit</a>
            @endif

            @if($s === 'Accepted' || $s === 'Emergency Fast-Tracked')
                <form action="{{ route('ot.surgery-requests.move-to-scheduling', $surgeryRequest->id) }}" method="POST">
                    @csrf<button class="btn btn-primary">Move to Scheduling</button>
                </form>
            @endif

            @if(in_array($s, ['Accepted','Emergency Fast-Tracked','Moved to Scheduling']))
                <a href="{{ route('ot.schedules.create', ['request_id' => $surgeryRequest->id]) }}" class="btn btn-success"><i class="bi bi-calendar-plus"></i> Schedule</a>
            @endif

            @if(! in_array($s, ['Cancelled','Rejected']))
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel</button>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    {{-- Top summary strip: key request info at a glance --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body p-3">
                <div class="text-muted small">Requested by</div>
                <div class="fw-semibold">{{ optional($surgeryRequest->requestedByDoctor)->name ?? '—' }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body p-3">
                <div class="text-muted small">Primary Surgeon</div>
                <div class="fw-semibold">{{ optional($surgeryRequest->primarySurgeon)->name ?? '—' }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body p-3">
                <div class="text-muted small">Preferred Date / Time</div>
                <div class="fw-semibold">
                    {{ $surgeryRequest->requested_surgery_date?->format('Y-m-d') ?? '—' }}
                    @if($surgeryRequest->requested_surgery_time) {{ $surgeryRequest->requested_surgery_time }}@endif
                </div>
                <div class="small text-muted">
                    Duration: {{ $surgeryRequest->estimated_duration_minutes ?? '—' }} min
                    · {{ $surgeryRequest->date_flexibility ?? 'Flexible' }}
                </div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body p-3">
                <div class="text-muted small">Priority / ASA</div>
                <div class="fw-semibold">{{ $surgeryRequest->priority }} · ASA {{ $surgeryRequest->asa_grade ?? '—' }}</div>
            </div></div>
        </div>
    </div>

    {{-- Status alerts (full-width) --}}
    @if($surgeryRequest->rejection_reason)
        <div class="alert alert-danger">
            <strong>{{ $surgeryRequest->status }}:</strong> {{ $surgeryRequest->rejection_reason }}
        </div>
    @endif
    @if($surgeryRequest->pending_info_reason)
        <div class="alert alert-warning">
            <strong>Pending Information:</strong> {{ $surgeryRequest->pending_info_reason }}
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- NEXT STEP — context-aware suggestion --}}
    {{-- ───────────────────────────────────────────── --}}
    @if(isset($nextStep))
        <div class="card border-{{ $nextStep['color'] }} mb-3">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-1 text-{{ $nextStep['color'] }}">
                        <i class="bi {{ $nextStep['icon'] }} me-1"></i> Next step — {{ $nextStep['title'] }}
                    </h6>
                    <div class="small text-muted">{{ $nextStep['desc'] }}</div>
                </div>
                @if($nextStep['url'])
                    <a href="{{ $nextStep['url'] }}" class="btn btn-{{ $nextStep['color'] }}">
                        <i class="bi {{ $nextStep['icon'] }} me-1"></i> {{ $nextStep['label'] }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- SUBMISSION READINESS CHECKLIST (Draft only) --}}
    {{-- ───────────────────────────────────────────── --}}
    @if($surgeryRequest->status === 'Draft' && isset($completionItems))
        @php
            $doneCount = collect($completionItems)->where('ok', true)->count();
            $total = count($completionItems);
            $pct = $total > 0 ? round(($doneCount / $total) * 100) : 0;
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-list-check me-1"></i> Submission Readiness ({{ $doneCount }}/{{ $total }})</strong>
                <span class="badge bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning text-dark' : 'danger') }}">{{ $pct }}%</span>
            </div>
            <div class="progress" style="height: 4px; border-radius: 0;">
                <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}"
                     style="width: {{ $pct }}%"></div>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    @foreach($completionItems as $item)
                        <div class="col-md-6">
                            @if($item['ok'])
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="text-success">{{ $item['label'] }}</span>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                                <span class="text-muted">{{ $item['label'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- LINKED CLINICAL RECORDS (labs, prescriptions, appointments) --}}
    {{-- ───────────────────────────────────────────── --}}
    @php
        $p = $surgeryRequest->patient;
        $labOrders = $p ? $p->labOrders()->latest('datetime')->take(5)->get() : collect();
        $prescriptions = $p ? $p->prescriptions()->latest('date')->take(5)->get() : collect();
        $appointments = $p ? $p->appointments()->latest('date')->take(3)->get() : collect();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-flask me-1"></i> Recent Lab Orders</strong>
                    <span class="badge bg-light text-dark border">{{ $labOrders->count() }}</span>
                </div>
                @if($labOrders->isEmpty())
                    <div class="card-body text-center text-muted small">No lab orders on record.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($labOrders as $lo)
                            <li class="list-group-item small d-flex justify-content-between">
                                <span>
                                    <strong>{{ $lo->order_number ?? '#'.$lo->id }}</strong>
                                    @if($lo->lab_name) · {{ $lo->lab_name }}@endif
                                </span>
                                <span class="text-muted">
                                    {{ optional($lo->datetime)->format('Y-m-d') ?? '—' }}
                                    @if($lo->priority)<span class="badge bg-light text-dark border ms-1">{{ $lo->priority }}</span>@endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-prescription2 me-1"></i> Recent Prescriptions</strong>
                    <span class="badge bg-light text-dark border">{{ $prescriptions->count() }}</span>
                </div>
                @if($prescriptions->isEmpty())
                    <div class="card-body text-center text-muted small">No prescriptions on record.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($prescriptions as $rx)
                            <li class="list-group-item small d-flex justify-content-between">
                                <span>
                                    Rx #{{ $rx->id }}
                                    @if(isset($rx->doctor)) · Dr. {{ optional($rx->doctor)->name }}@endif
                                </span>
                                <span class="text-muted">{{ optional($rx->date)->format('Y-m-d') ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @if($appointments->count() > 0)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-calendar-event me-1"></i> Recent Appointments</strong>
                <span class="badge bg-light text-dark border">{{ $appointments->count() }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($appointments as $apt)
                    <li class="list-group-item small d-flex justify-content-between">
                        <span>
                            Dr. {{ optional($apt->doctor ?? null)->name ?? '—' }}
                            · {{ $apt->status ?? '—' }}
                        </span>
                        <span class="text-muted">{{ optional($apt->date)->format('Y-m-d') ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- LIFECYCLE PROGRESS TRACKER --}}
    {{-- ───────────────────────────────────────────── --}}
    @php
        $sched = $surgeryRequest->schedules->sortByDesc('id')->first();
        $allStatuses = [
            'Draft','Submitted','Under Review','Accepted','Moved to Scheduling',
            'Scheduled','Pre-Op Pending','Ready for OT','Transfer Started',
            'Patient Received in OT','Anesthesia Started','Surgery Running',
            'Surgery Completed','In Recovery','Transferred Back','Closed',
        ];
        $currentStage = $sched?->status ?? $surgeryRequest->status;
        $currentIdx = array_search($currentStage, $allStatuses);
        if ($currentIdx === false) $currentIdx = -1;
    @endphp
    <div class="card mb-3">
        <div class="card-header"><strong><i class="bi bi-diagram-3 me-1"></i> Surgery Lifecycle Progress</strong></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-1">
                @foreach($allStatuses as $idx => $stage)
                    @php
                        $isPast = $currentIdx !== -1 && $idx < $currentIdx;
                        $isNow  = $stage === $currentStage;
                    @endphp
                    <span class="badge {{ $isNow ? 'bg-primary' : ($isPast ? 'bg-success' : 'bg-light text-dark border') }}"
                          title="Step {{ $idx + 1 }}">
                        {{ $idx + 1 }}. {{ $stage }}
                    </span>
                @endforeach
            </div>
            <div class="small text-muted mt-2">
                Current stage: <strong>{{ $currentStage }}</strong>
                @if($sched) · Schedule: <a href="{{ route('ot.schedules.show', $sched->id) }}">{{ $sched->schedule_no }}</a>@endif
            </div>
        </div>
    </div>

    {{-- ───────────────────────────────────────────── --}}
    {{-- PATIENT HISTORY (relevant to surgery) --}}
    {{-- ───────────────────────────────────────────── --}}
    @php
        $patient = $surgeryRequest->patient;
        $ipd = $surgeryRequest->ipdAdmission ?? null;
        $patientHistories = $patient?->histories()->latest()->take(5)->get() ?? collect();
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-person-vcard me-1"></i> Patient Information &amp; History</strong>
                    @if($patient)
                        <a href="{{ url('/patients/' . $patient->id) }}" class="small">Open patient profile →</a>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-3 small">
                        <dt class="col-sm-3">Patient Name</dt><dd class="col-sm-9">{{ $patient->patient_name ?? '—' }}</dd>
                        <dt class="col-sm-3">MRN</dt><dd class="col-sm-9">{{ $patient->mrn ?? '—' }} · {{ $patient->health_card_no ?? '—' }}</dd>
                        <dt class="col-sm-3">Age / Gender</dt><dd class="col-sm-9">
                            {{ $patient?->dob ? \Carbon\Carbon::parse($patient->dob)->age . ' yrs' : '—' }}
                            · {{ ucfirst($patient->gender ?? '—') }}
                        </dd>
                        <dt class="col-sm-3">Mobile / Email</dt><dd class="col-sm-9">{{ $patient->mobileno ?? '—' }} · {{ $patient->email ?? '—' }}</dd>
                        <dt class="col-sm-3">Blood Group (master)</dt><dd class="col-sm-9">{{ optional($patient->bloodGroup ?? null)->combined ?? $patient->blood_group ?? '—' }}</dd>
                        <dt class="col-sm-3 text-danger">Known Allergies</dt><dd class="col-sm-9 text-danger">{{ $patient->known_allergies ?: 'None recorded' }}</dd>
                        <dt class="col-sm-3">Patient Note</dt><dd class="col-sm-9">{{ $patient->note ?: '—' }}</dd>
                    </dl>

                    @if($patientHistories->count() > 0)
                        <h6 class="text-muted text-uppercase small fw-semibold border-top pt-2">Recent Medical History</h6>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Date</th><th>Diagnosis</th><th>Notes</th></tr>
                            </thead>
                            <tbody>
                                @foreach($patientHistories as $h)
                                    <tr>
                                        <td class="small">{{ optional($h->date ?? $h->created_at)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="small">{{ $h->diagnosis ?? $h->title ?? '—' }}</td>
                                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($h->note ?? $h->description ?? '', 100) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="small text-muted">No prior medical history recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><strong><i class="bi bi-hospital me-1"></i> IPD Admission</strong></div>
                <div class="card-body">
                    @if($ipd)
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5">IPD No</dt><dd class="col-sm-7">
                                <a href="{{ url('/ipd-patients/' . $ipd->id) }}">{{ $ipd->ipd_no ?? '—' }}</a>
                            </dd>
                            <dt class="col-sm-5">Admission Date</dt><dd class="col-sm-7">{{ optional($ipd->admission_date)->format('Y-m-d') ?? '—' }}</dd>
                            <dt class="col-sm-5">Admission Type</dt><dd class="col-sm-7">{{ $ipd->admission_type ?? '—' }}</dd>
                            <dt class="col-sm-5">Case ID</dt><dd class="col-sm-7">{{ $ipd->case_id ?? '—' }}</dd>
                            <dt class="col-sm-5">Attending Dr.</dt><dd class="col-sm-7">{{ optional($ipd->doctor ?? null)->name ?? '—' }}</dd>
                            <dt class="col-sm-5">IPD Status</dt><dd class="col-sm-7"><span class="badge bg-light text-dark border">{{ $ipd->status ?? '—' }}</span></dd>
                        </dl>
                        <div class="mt-2">
                            <a href="{{ url('/ipd-patients/' . $ipd->id . '?tab=surgery-request') }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-arrow-left me-1"></i> Back to IPD Patient
                            </a>
                        </div>
                    @else
                        <div class="text-muted small">No IPD admission linked.</div>
                    @endif

                    @if($surgeryRequest->encounter_type === 'OPD' || $surgeryRequest->encounter_type === 'ER')
                        @php $enc = $surgeryRequest->resolveEncounter(); @endphp
                        <hr>
                        <div class="small">
                            <strong>{{ $surgeryRequest->encounter_type }} Encounter:</strong>
                            {{ optional($enc)->case_id ?? optional($enc)->id ?? 'not found' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────────────────────────────── --}}
    {{-- QUICK NAVIGATION TO OT WORKFLOW PHASES --}}
    {{-- ───────────────────────────────────────────── --}}
    @if($sched)
        @php
            $phases = [
                ['Pre-Op',    'ot.pre-op.show',    $sched->id, 'bi-check2-square', 'info',    $sched->preOpChecklist],
                ['Transfers', 'ot.transfers.index', null,       'bi-arrow-left-right', 'info',  $sched->transfers->count()],
                ['Anesthesia','ot.anesthesia.show', $sched->id, 'bi-droplet',     'warning', $sched->anesthesiaRecord],
                ['Intra-Op',  'ot.intra-op.show',   $sched->id, 'bi-activity',    'warning', $sched->intraOpRecord],
                ['Consumables','ot.consumables.show', $sched->id, 'bi-box-seam',   'secondary', $sched->consumableUsages->count()],
                ['Post-Op',   'ot.post-op.show',    $sched->id, 'bi-journal-medical', 'success', $sched->postOpNote],
                ['PACU',      'ot.pacu.show',       $sched->id, 'bi-bandaid',     'success', $sched->pacuRecord],
                ['Billing',   'ot.billing.show',    $sched->id, 'bi-receipt',     'primary',  null],
            ];
        @endphp
        <div class="card mb-3">
            <div class="card-header"><strong><i class="bi bi-signpost-split me-1"></i> Continue Workflow in OT Module</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($phases as [$label, $route, $arg, $icon, $color, $payload])
                        @if(\Illuminate\Support\Facades\Route::has($route))
                            @php
                                $url = $arg ? route($route, $arg) : route($route);
                                $hasData = is_object($payload) || (is_numeric($payload) && $payload > 0);
                            @endphp
                            <div class="col-6 col-md-3">
                                <a href="{{ $url }}" class="btn btn-{{ $hasData ? '' : 'outline-' }}{{ $color }} w-100 text-start" style="min-height: 56px;">
                                    <i class="bi {{ $icon }} me-1"></i> {{ $label }}
                                    @if(is_numeric($payload) && $payload > 0)
                                        <span class="badge bg-light text-dark ms-1">{{ $payload }}</span>
                                    @elseif(is_object($payload))
                                        <i class="bi bi-check-circle-fill ms-1 text-success"></i>
                                    @endif
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Main content stacks full-width — no uneven-column whitespace --}}
    <div class="d-block">
            <div class="card mb-3">
                <div class="card-header"><strong>Clinical Details</strong></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Procedure</dt><dd class="col-sm-9">{{ optional($surgeryRequest->surgeryType)->name ?? '—' }}</dd>
                        <dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ optional($surgeryRequest->category)->name ?? '—' }}</dd>
                        <dt class="col-sm-3">Required OT Type</dt><dd class="col-sm-9">{{ $surgeryRequest->required_ot_type ?? '—' }}</dd>
                        <dt class="col-sm-3">Primary Diagnosis</dt><dd class="col-sm-9">{{ $surgeryRequest->diagnosis ?? '—' }}</dd>
                        <dt class="col-sm-3">Secondary Diagnosis</dt><dd class="col-sm-9">{{ $surgeryRequest->secondary_diagnosis ?? '—' }}</dd>
                        <dt class="col-sm-3">ICD-10 Code</dt><dd class="col-sm-9">{{ $surgeryRequest->icd_code ?? '—' }}</dd>
                        <dt class="col-sm-3">Clinical Indication</dt><dd class="col-sm-9">{{ $surgeryRequest->clinical_indication ?? '—' }}</dd>
                        <dt class="col-sm-3">Procedure Notes</dt><dd class="col-sm-9">{{ $surgeryRequest->procedure_notes ?? '—' }}</dd>
                        <dt class="col-sm-3">Special Requirements</dt><dd class="col-sm-9">{{ $surgeryRequest->special_requirements ?? '—' }}</dd>
                        <dt class="col-sm-3">Date Flexibility</dt><dd class="col-sm-9">{{ $surgeryRequest->date_flexibility ?? '—' }} @if($surgeryRequest->flexibility_reason) — <small class="text-muted">{{ $surgeryRequest->flexibility_reason }}</small>@endif</dd>
                        <dt class="col-sm-3">Blood Arrangement</dt><dd class="col-sm-9">
                            @if($surgeryRequest->blood_required)
                                @php
                                    $bg = $surgeryRequest->bloodGroup;
                                    $bgLabel = $bg ? ($bg->display_name ?: $bg->combined) : ($surgeryRequest->blood_group ?: '—');
                                @endphp
                                <strong>{{ $surgeryRequest->blood_units ?? '?' }} units</strong> · Group: {{ $bgLabel }}
                                @if(! empty($surgeryRequest->blood_components))
                                    <div class="small text-muted">Components: {{ implode(', ', $surgeryRequest->blood_components) }}</div>
                                @endif
                                @if($surgeryRequest->crossmatch_required)<span class="badge bg-info">Crossmatch required</span>@endif
                                @if($surgeryRequest->blood_bank_instruction)
                                    <div class="small mt-1">BB note: {{ $surgeryRequest->blood_bank_instruction }}</div>
                                @endif
                            @else
                                Not required
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Emergency details (FR-04) --}}
            @if($surgeryRequest->is_emergency || $surgeryRequest->priority === 'Emergency')
                <div class="card mb-3 border-danger">
                    <div class="card-header bg-danger text-white py-2"><strong><i class="bi bi-exclamation-triangle"></i> Emergency Details</strong></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Emergency Reason</dt><dd class="col-sm-8">{{ $surgeryRequest->emergency_reason ?? '—' }}</dd>
                            <dt class="col-sm-4">Life-Threatening</dt><dd class="col-sm-8">{{ $surgeryRequest->is_life_threatening ? 'Yes' : 'No' }}</dd>
                            <dt class="col-sm-4">Immediate OT Requested</dt><dd class="col-sm-8">{{ $surgeryRequest->is_immediate_ot ? 'Yes' : 'No' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif

            {{-- Equipment Requirements (FR-08) --}}
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-tools"></i> Equipment Requirements</strong></div>
                @if($surgeryRequest->equipments->count() === 0)
                    <div class="card-body text-center text-muted small">No equipment specified.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>Equipment</th><th>Qty</th><th>Type</th><th>Setup Instruction</th></tr></thead>
                            <tbody>
                                @foreach($surgeryRequest->equipments as $eq)
                                    <tr>
                                        <td>{{ $eq->equipment_name }}</td>
                                        <td>{{ $eq->quantity }}</td>
                                        <td>
                                            @if($eq->is_mandatory)<span class="badge bg-danger">Mandatory</span>
                                            @else<span class="badge bg-secondary">Optional</span>@endif
                                        </td>
                                        <td class="small text-muted">{{ $eq->setup_instruction ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($surgeryRequest->schedules->count() > 0)
                <div class="card mb-3">
                    <div class="card-header"><strong>Scheduled Surgeries</strong></div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light"><tr><th>Schedule</th><th>Room</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($surgeryRequest->schedules as $sch)
                                    <tr>
                                        <td><a href="{{ route('ot.schedules.show', $sch->id) }}">{{ $sch->schedule_no }}</a></td>
                                        <td>{{ optional($sch->room)->name }}</td>
                                        <td>{{ $sch->scheduled_start?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $sch->scheduled_end?->format('Y-m-d H:i') }}</td>
                                        <td><span class="badge {{ $sch->status_badge_class }}">{{ $sch->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ───────────────────────────────────────────── --}}
            {{-- PHASE DATA — read-only inline summary for each --}}
            {{-- workflow phase that has data on the latest      --}}
            {{-- schedule (Pre-Op → Cleaning).                   --}}
            {{-- ───────────────────────────────────────────── --}}
            @php $sd = $surgeryRequest->schedules->sortByDesc('id')->first(); @endphp
            @if($sd)
                {{-- Pre-Op summary --}}
                @if($sd->preOpChecklist)
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info-subtle d-flex justify-content-between">
                            <strong><i class="bi bi-check2-square me-1"></i> Pre-Op Checklist</strong>
                            <a href="{{ route('ot.pre-op.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="card-body">
                            @php
                                $pre = $sd->preOpChecklist;
                                $items = [
                                    'Consent'=>'consent_obtained','Labs'=>'lab_completed','Radiology'=>'radiology_completed',
                                    'Fasting'=>'fasting_confirmed','Blood'=>'blood_arranged','Allergy'=>'allergy_reviewed',
                                    'Vitals'=>'vitals_recorded','Anesthesia Clearance'=>'anesthesia_clearance',
                                    'Doctor Clearance'=>'doctor_clearance','Nurse Confirm'=>'nurse_confirmation',
                                    'Site Marked'=>'site_marked','ID Band'=>'id_band_verified',
                                ];
                            @endphp
                            <div class="row g-2 mb-2">
                                @foreach($items as $lbl => $key)
                                    <div class="col-sm-4 col-md-3">
                                        <span class="badge {{ $pre->{$key} ? 'bg-success' : 'bg-secondary' }} w-100 text-start">
                                            <i class="bi {{ $pre->{$key} ? 'bi-check-circle' : 'bi-circle' }}"></i> {{ $lbl }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            @if(! empty($pre->vitals_snapshot))
                                <div class="small text-muted">
                                    <strong>Vitals:</strong>
                                    @foreach((array)$pre->vitals_snapshot as $k => $v)
                                        <span class="badge bg-light text-dark border me-1">{{ strtoupper($k) }}: {{ $v }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($pre->notes)<div class="small mt-1"><strong>Notes:</strong> {{ $pre->notes }}</div>@endif
                            <div class="small text-muted mt-1">
                                Status: <strong>{{ $pre->is_complete ? 'Complete' : 'Pending' }}</strong>
                                @if($pre->completed_at) · {{ $pre->completed_at->format('Y-m-d H:i') }}@endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Transfers --}}
                @if($sd->transfers->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header"><strong><i class="bi bi-arrow-left-right me-1"></i> Patient Transfers</strong></div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Direction</th><th>From</th><th>To</th><th>Initiated</th><th>Arrived</th><th>Status</th><th>Notes</th></tr></thead>
                                <tbody>
                                    @foreach($sd->transfers as $tr)
                                        <tr>
                                            <td>{{ $tr->direction === 'to_ot' ? 'Ward → OT' : 'OT → Ward' }}</td>
                                            <td>{{ $tr->from_location }}</td>
                                            <td>{{ $tr->to_location }}</td>
                                            <td>{{ optional($tr->initiated_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ optional($tr->arrived_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $tr->status }}</span></td>
                                            <td class="small text-muted">{{ $tr->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Anesthesia --}}
                @if($sd->anesthesiaRecord)
                    @php $a = $sd->anesthesiaRecord; @endphp
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning-subtle d-flex justify-content-between">
                            <strong><i class="bi bi-droplet me-1"></i> Anesthesia Record</strong>
                            <a href="{{ route('ot.anesthesia.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-2">
                                <dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ optional($a->anesthesiaType)->name ?? '—' }} <span class="badge bg-light text-dark border ms-1">{{ $a->asa_grade }}</span></dd>
                                <dt class="col-sm-3">Induction</dt><dd class="col-sm-9">{{ optional($a->induction_time)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Recovery</dt><dd class="col-sm-9">{{ optional($a->recovery_time)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Airway</dt><dd class="col-sm-9 small">{{ $a->airway_management ?? '—' }}</dd>
                                <dt class="col-sm-3">Pre-Anesthesia Assessment</dt><dd class="col-sm-9 small">{{ $a->pre_anesthesia_assessment ?? '—' }}</dd>
                                <dt class="col-sm-3">Drugs Used</dt><dd class="col-sm-9 small"><pre class="mb-0" style="white-space:pre-wrap;background:transparent;padding:0;border:0;">{{ $a->drugs_used }}</pre></dd>
                                <dt class="col-sm-3">Complications</dt><dd class="col-sm-9 small">{{ $a->complications ?? 'None' }}</dd>
                                <dt class="col-sm-3">Post-Anesthesia</dt><dd class="col-sm-9 small">{{ $a->post_anesthesia_notes ?? '—' }}</dd>
                            </dl>
                            @php
                                $vits = $a->intra_op_vitals;
                                if (is_string($vits)) { $vits = json_decode($vits, true) ?: []; }
                                $vits = is_array($vits) ? $vits : [];
                            @endphp
                            @if(count($vits) > 0)
                                <div class="table-responsive"><table class="table table-sm">
                                    <thead class="table-light"><tr><th>Time</th><th>BP</th><th>HR</th><th>SpO2</th></tr></thead>
                                    <tbody>
                                        @foreach($vits as $v)
                                            <tr><td>{{ $v['t'] ?? $v['time'] ?? '' }}</td><td>{{ $v['bp'] ?? '' }}</td><td>{{ $v['hr'] ?? $v['pulse'] ?? '' }}</td><td>{{ $v['spo2'] ?? '' }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table></div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Intra-Op --}}
                @if($sd->intraOpRecord)
                    @php $io = $sd->intraOpRecord; @endphp
                    <div class="card mb-3 border-warning">
                        <div class="card-header bg-warning-subtle d-flex justify-content-between">
                            <strong><i class="bi bi-activity me-1"></i> Intra-Op (Surgery Execution)</strong>
                            <a href="{{ route('ot.intra-op.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Incision Time</dt><dd class="col-sm-3">{{ optional($io->incision_time)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Closure Time</dt><dd class="col-sm-3">{{ optional($io->closure_time)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Blood Loss</dt><dd class="col-sm-3">{{ $io->blood_loss_ml ?? 0 }} ml</dd>
                                <dt class="col-sm-3">Transfused</dt><dd class="col-sm-3">{{ $io->blood_transfused_ml ?? 0 }} ml</dd>
                                <dt class="col-sm-3">Counts Verified</dt><dd class="col-sm-9">
                                    @if($io->counts_verified)<span class="badge bg-success">Yes</span>
                                    @else<span class="badge bg-secondary">No</span>@endif
                                </dd>
                                <dt class="col-sm-3">Operative Findings</dt><dd class="col-sm-9 small">{{ $io->operative_findings ?? '—' }}</dd>
                                <dt class="col-sm-3">Procedure Performed</dt><dd class="col-sm-9 small">{{ $io->procedure_performed ?? '—' }}</dd>
                                <dt class="col-sm-3">Operative Notes</dt><dd class="col-sm-9 small">{{ $io->operative_notes ?? '—' }}</dd>
                                <dt class="col-sm-3">Specimens</dt><dd class="col-sm-9 small">{{ $io->specimens_collected ?? '—' }}</dd>
                                <dt class="col-sm-3">Implants</dt><dd class="col-sm-9 small">{{ $io->implants_used ?? 'None' }}</dd>
                                <dt class="col-sm-3">Complications</dt><dd class="col-sm-9 small">{{ $io->complications ?? 'None' }}</dd>
                                <dt class="col-sm-3">Post-Op Instructions</dt><dd class="col-sm-9 small">{{ $io->post_op_instructions ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                @endif

                {{-- Consumables --}}
                @if($sd->consumableUsages->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <strong><i class="bi bi-box-seam me-1"></i> Consumables & Items Used ({{ $sd->consumableUsages->count() }})</strong>
                            <a href="{{ route('ot.consumables.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Item</th><th>Type</th><th>Qty</th><th>Rate</th><th>Amount</th><th>Billed</th></tr></thead>
                                <tbody>
                                    @foreach($sd->consumableUsages as $u)
                                        <tr>
                                            <td>{{ $u->item_name }} <small class="text-muted">{{ $u->item_code }}</small></td>
                                            <td>{{ ucfirst($u->type) }}</td>
                                            <td>{{ $u->quantity }} {{ $u->unit }}</td>
                                            <td>{{ number_format($u->rate, 2) }}</td>
                                            <td>{{ number_format($u->amount, 2) }}</td>
                                            <td>
                                                @if($u->is_billed)<span class="badge bg-success">Billed</span>
                                                @else<span class="badge bg-secondary">Pending</span>@endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr class="table-light"><th colspan="4" class="text-end">Total</th><th>{{ number_format($sd->consumableUsages->sum('amount'), 2) }}</th><th></th></tr></tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Post-Op --}}
                @if($sd->postOpNote)
                    @php $po = $sd->postOpNote; @endphp
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success-subtle d-flex justify-content-between">
                            <strong><i class="bi bi-journal-medical me-1"></i> Post-Op Note</strong>
                            <a href="{{ route('ot.post-op.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Procedure Summary</dt><dd class="col-sm-9 small">{{ $po->procedure_summary }}</dd>
                                <dt class="col-sm-3">Immediate Findings</dt><dd class="col-sm-9 small">{{ $po->immediate_findings ?? '—' }}</dd>
                                <dt class="col-sm-3">Post-Op Diagnosis</dt><dd class="col-sm-9 small">{{ $po->post_op_diagnosis ?? '—' }}</dd>
                                <dt class="col-sm-3">Orders</dt><dd class="col-sm-9 small"><pre class="mb-0" style="white-space:pre-wrap;background:transparent;padding:0;border:0;">{{ $po->orders }}</pre></dd>
                                <dt class="col-sm-3">Medications</dt><dd class="col-sm-9 small">{{ $po->medications ?? '—' }}</dd>
                                <dt class="col-sm-3">Care Instructions</dt><dd class="col-sm-9 small">{{ $po->care_instructions ?? '—' }}</dd>
                                <dt class="col-sm-3">Follow-up Plan</dt><dd class="col-sm-9 small">{{ $po->follow_up_plan ?? '—' }}</dd>
                                <dt class="col-sm-3">Disposition</dt><dd class="col-sm-9"><span class="badge bg-light text-dark border">{{ $po->disposition ?? '—' }}</span></dd>
                            </dl>
                        </div>
                    </div>
                @endif

                {{-- PACU --}}
                @if($sd->pacuRecord)
                    @php $pacu = $sd->pacuRecord; @endphp
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success-subtle d-flex justify-content-between">
                            <strong><i class="bi bi-bandaid me-1"></i> PACU / Recovery</strong>
                            <a href="{{ route('ot.pacu.show', $sd->id) }}" class="small">Open <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-2">
                                <dt class="col-sm-3">Bed</dt><dd class="col-sm-3">{{ $pacu->bed_no ?? '—' }}</dd>
                                <dt class="col-sm-3">Status</dt><dd class="col-sm-3">
                                    <span class="badge {{ $pacu->status === 'Discharged' ? 'bg-success' : 'bg-info' }}">{{ $pacu->status ?? '—' }}</span>
                                </dd>
                                <dt class="col-sm-3">Admitted</dt><dd class="col-sm-3">{{ optional($pacu->admitted_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Discharged</dt><dd class="col-sm-3">{{ optional($pacu->discharged_at)->format('Y-m-d H:i') ?? '—' }}</dd>
                                <dt class="col-sm-3">Aldrete Score</dt><dd class="col-sm-3"><strong>{{ $pacu->aldrete_score ?? '—' }}/10</strong></dd>
                                <dt class="col-sm-3">Consciousness</dt><dd class="col-sm-3">{{ $pacu->consciousness_level ?? '—' }}</dd>
                                <dt class="col-sm-3">Recovery Cleared</dt><dd class="col-sm-3">
                                    @if($pacu->recovery_clearance)<span class="badge bg-success">Cleared</span>
                                    @else<span class="badge bg-secondary">Not Cleared</span>@endif
                                </dd>
                                <dt class="col-sm-3">Destination</dt><dd class="col-sm-3">{{ $pacu->discharge_destination ?? '—' }}</dd>
                                <dt class="col-sm-3">Medications Given</dt><dd class="col-sm-9 small">{{ $pacu->medications_given ?? '—' }}</dd>
                                <dt class="col-sm-3">Observations</dt><dd class="col-sm-9 small">{{ $pacu->observations ?? '—' }}</dd>
                            </dl>
                            @php
                                $vlog = $pacu->vitals_log;
                                if (is_string($vlog)) { $vlog = json_decode($vlog, true) ?: []; }
                                $vlog = is_array($vlog) ? $vlog : [];
                            @endphp
                            @if(count($vlog) > 0)
                                <div class="table-responsive"><table class="table table-sm mb-0">
                                    <thead class="table-light"><tr><th>Time</th><th>BP</th><th>Pulse</th><th>SpO2</th><th>Temp</th><th>Pain</th><th>Aldrete</th></tr></thead>
                                    <tbody>
                                        @foreach($vlog as $v)
                                            <tr>
                                                <td>{{ $v['time'] ?? $v['t'] ?? '' }}</td>
                                                <td>{{ $v['bp'] ?? '' }}</td>
                                                <td>{{ $v['pulse'] ?? $v['hr'] ?? '' }}</td>
                                                <td>{{ $v['spo2'] ?? '' }}</td>
                                                <td>{{ $v['temp'] ?? '' }}</td>
                                                <td>{{ $v['pain_score'] ?? $v['pain'] ?? '' }}</td>
                                                <td>{{ $v['aldrete_score'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table></div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Cleaning --}}
                @if($sd->cleaningLogs->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header"><strong><i class="bi bi-brush me-1"></i> OT Room Cleaning Logs</strong></div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light"><tr><th>Type</th><th>Started</th><th>Completed</th><th>Status</th><th>Remarks</th></tr></thead>
                                <tbody>
                                    @foreach($sd->cleaningLogs as $cl)
                                        <tr>
                                            <td>{{ $cl->cleaning_type }}</td>
                                            <td>{{ optional($cl->started_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ optional($cl->completed_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                            <td>
                                                @if($cl->is_complete)<span class="badge bg-success">Complete</span>
                                                @else<span class="badge bg-warning text-dark">In Progress</span>@endif
                                            </td>
                                            <td class="small text-muted">{{ $cl->remarks }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Approvals panel (FR-13) --}}
            @if($surgeryRequest->junior_approval_required || $surgeryRequest->consultant_approval_required)
                <div class="card mb-3">
                    <div class="card-header"><strong><i class="bi bi-shield-check"></i> Hierarchical Approvals</strong></div>
                    <div class="card-body">
                        @if($surgeryRequest->junior_approval_required)
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Junior approval</div>
                                    @if($surgeryRequest->junior_approved_at)
                                        <span class="badge bg-success">Approved</span>
                                        <small class="text-muted">{{ $surgeryRequest->junior_approved_at->format('Y-m-d H:i') }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </div>
                                @if(! $surgeryRequest->junior_approved_at && in_array($surgeryRequest->status, ['Submitted','Under Review','Pending Information','Sent Back for Correction']))
                                    <form method="POST" action="{{ route('ot.surgery-requests.junior-approve', $surgeryRequest->id) }}">
                                        @csrf<button class="btn btn-sm btn-outline-success">Grant</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                        @if($surgeryRequest->consultant_approval_required)
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Consultant approval</div>
                                    @if($surgeryRequest->consultant_approved_at)
                                        <span class="badge bg-success">Approved</span>
                                        <small class="text-muted">{{ $surgeryRequest->consultant_approved_at->format('Y-m-d H:i') }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </div>
                                @if(! $surgeryRequest->consultant_approved_at && in_array($surgeryRequest->status, ['Submitted','Under Review','Pending Information','Sent Back for Correction']))
                                    <form method="POST" action="{{ route('ot.surgery-requests.consultant-approve', $surgeryRequest->id) }}">
                                        @csrf<button class="btn btn-sm btn-outline-success">Grant</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

    {{-- ───────────────────────────────────────────── --}}
    {{-- DOCUMENTS attached to this request --}}
    {{-- ───────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-file-earmark-text me-1"></i> Documents ({{ $surgeryRequest->documents->count() }})</strong>
            @if(\Illuminate\Support\Facades\Route::has('ot.documents.index'))
                <a href="{{ route('ot.documents.index') }}" class="small">
                    <i class="bi bi-plus-circle me-1"></i> Upload new
                </a>
            @endif
        </div>
        @if($surgeryRequest->documents->isEmpty())
            <div class="card-body text-center text-muted small">
                No documents linked. Upload consent forms, lab reports, imaging via OT Documents.
            </div>
        @else
            <ul class="list-group list-group-flush">
                @foreach($surgeryRequest->documents as $doc)
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <div>
                            <strong>{{ $doc->title }}</strong>
                            <span class="badge bg-light text-dark border ms-1">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                            @if($doc->is_signed)<span class="badge bg-success ms-1"><i class="bi bi-check-circle"></i> Signed</span>@endif
                            <div class="text-muted">
                                Uploaded {{ optional($doc->created_at)->diffForHumans() }}
                                by {{ optional($doc->uploadedBy)->name ?? '#'.$doc->uploaded_by }}
                            </div>
                        </div>
                        <div>
                            @if(\Illuminate\Support\Facades\Route::has('ot.documents.download'))
                                <a href="{{ route('ot.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- ───────────────────────────────────────────── --}}
    {{-- AUDIT TRAIL for this request --}}
    {{-- ───────────────────────────────────────────── --}}
    @if(isset($auditLogs) && $auditLogs->count() > 0)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-clock-history me-1"></i> Audit Trail (last {{ $auditLogs->count() }})</strong>
                <a href="{{ route('ot.reports.audit', ['entity_type' => 'surgery_request']) }}" class="small">
                    Full audit log →
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 12%">When</th>
                            <th style="width: 12%">By</th>
                            <th style="width: 14%">Action</th>
                            <th style="width: 30%">From → To</th>
                            <th>Reason / payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                            <tr>
                                <td class="small">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="small">{{ optional($log->user)->name ?? '#'.$log->user_id }}</td>
                                <td><span class="badge bg-info">{{ $log->action }}</span></td>
                                <td class="small">
                                    @if($log->from_status || $log->to_status)
                                        <span class="text-muted">{{ $log->from_status ?? '—' }}</span>
                                        <i class="bi bi-arrow-right small"></i>
                                        <strong>{{ $log->to_status ?? '—' }}</strong>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit($log->reason ?? '', 80) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    </div>{{-- /.d-block --}}
</div>{{-- /.container-fluid --}}

{{-- Reject modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.surgery-requests.reject', $surgeryRequest->id) }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Reason *</label>
                <textarea name="reason" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- Send Back modal --}}
<div class="modal fade" id="sendBackModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.surgery-requests.send-back', $surgeryRequest->id) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Send Back for Correction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-warning small">The requesting doctor will be able to edit and resubmit. This is for fixable issues, not full rejection.</div>
                <label class="form-label">What needs correction? *</label>
                <textarea name="reason" rows="3" class="form-control" required placeholder="e.g. Missing surgical site indication, incomplete blood requirements…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-warning">Send Back</button>
            </div>
        </form>
    </div>
</div>

{{-- Pending Information modal --}}
<div class="modal fade" id="pendingInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.surgery-requests.pending-info', $surgeryRequest->id) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Mark Pending Information</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">What information is pending? *</label>
                <textarea name="reason" rows="3" class="form-control" required placeholder="e.g. Awaiting lab clearance, awaiting cardiac evaluation…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-info">Mark Pending</button>
            </div>
        </form>
    </div>
</div>

{{-- Fast-Track modal --}}
<div class="modal fade" id="fastTrackModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.surgery-requests.fast-track', $surgeryRequest->id) }}" method="POST">@csrf
            <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-lightning"></i> Emergency Fast-Track</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger small"><strong>Bypasses normal review.</strong> Notifies surgeon, anesthetist, OT nurse and ward immediately. Logged in audit trail.</div>
                <label class="form-label">Fast-track reason *</label>
                <textarea name="reason" rows="3" class="form-control" required>{{ $surgeryRequest->emergency_reason }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger"><i class="bi bi-lightning"></i> Fast-Track Now</button>
            </div>
        </form>
    </div>
</div>

{{-- Cancel modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('ot.surgery-requests.cancel', $surgeryRequest->id) }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Cancel Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Reason *</label>
                <textarea name="reason" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger">Confirm Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
