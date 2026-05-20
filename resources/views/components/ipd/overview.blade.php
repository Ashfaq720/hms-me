{{-- =========================
    OVERVIEW TAB ONLY
========================= --}}
{{-- <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab"> --}}
<div class="p-2" style="font-size: 0.82rem;">

    {{-- Top Action --}}
    {{-- <div class="d-flex justify-content-end mb-2">
            <a href="javascript:void(0)" class="btn btn-warning fw-semibold px-2 rounded-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Patient History
            </a>
        </div> --}}

    {{-- Top Overview Section --}}
    <div class="row g-2 mb-4">

        {{-- Patient Header / Info --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">

                    <div class="row g-2 align-items-start">
                        {{-- Left: Image + Name --}}
                        <div class="col-md-3 text-center">
                            @if ($iPDPatient->patient?->image)
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#patientImageModal">
                                    <img src="{{ asset('storage/' . $iPDPatient->patient->image) }}"
                                        class="rounded-4 border shadow-sm object-fit-cover" role="button"
                                        width="100" height="110" alt="Patient Image">
                                </a>
                            @else
                                <div class="rounded-4 border d-flex align-items-center justify-content-center bg-light mx-auto"
                                    style="width:100px; height:110px;">
                                    <i class="bi bi-person fs-1 text-secondary"></i>
                                </div>
                            @endif

                            <div class="mt-2">
                                <span class="badge rounded-pill bg-primary px-2 py-1" style="font-size: 0.75rem;">
                                    Health Card Details
                                </span>
                            </div>

                            <h6 class="fw-bold mt-1 mb-0">
                                {{ $iPDPatient->patient?->patient_name ?? '-' }}
                            </h6>

                            <div class="text-muted" style="font-size: 0.75rem;">
                                {{ $iPDPatient->patient?->mrn ?? '' }}
                                <br>
                                Age:
                                {{ calculateAgeFromDob($iPDPatient->patient?->dob) ?? '-' }}
                            </div>

                            <div class="mt-2">
                                <a href="{{ route('health-card.show', $iPDPatient->patient?->id) }}" target="_blank" class="btn btn-warning btn-sm w-100 fw-semibold rounded-3">
                                    <i class="bi bi-printer me-1"></i> Print
                                </a>
                            </div>
                        </div>

                        {{-- Middle: Patient Details --}}
                        <div class="col-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                <h6 class="fw-bold mb-0">Patient Information</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"
                                        style="font-size: 0.7rem;">
                                       {{ $iPDPatient->status ?? '' }}
                                    </span>
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill"
                                        style="font-size: 0.7rem;">
                                        Case #{{ $iPDPatient->case_id ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="row g-1" style="font-size: 0.78rem;">
                                <div class="col-6">
                                    <div class="text-muted mb-0">Gender</div>
                                    <div class="fw-semibold">{{ $iPDPatient->patient?->gender ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Phone</div>
                                    <div class="fw-semibold">{{ $iPDPatient->patient?->mobileno ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Guardian</div>
                                    <div class="fw-semibold">{{ $iPDPatient->patient?->guardian_name ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Ipd No</div>
                                    <div class="fw-semibold">{{ $iPDPatient->ipd_no ?? '-' }}</div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Admission Date</div>
                                    <div class="fw-semibold">
                                        {{ format_datetime($iPDPatient->admission_date) }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Bed</div>
                                    <div class="fw-semibold">
                                        {{ optional(optional($iPDPatient->bedAllocations->last())->bed)->name ?? 'Not Assigned' }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Discharge Date</div>
                                    <div class="fw-semibold">
                                        {{ format_datetime($iPDPatient->discharge_date ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="text-muted mb-0">Organization Id</div>
                                    <div class="fw-semibold text-primary">
                                        {{ $iPDPatient->patient?->organization_id ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Doctor + Actions --}}
                        <div class="col-md-4">
                            <div class="card border-0 bg-light rounded-3 mb-4">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center"
                                            style="width:40px; height:40px;">
                                            <i class="bi bi-hospital fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold">
                                                Consulting Doctor
                                            </div>
                                            <div class="fw-bold fs-6 text-primary">
                                                {{ $iPDPatient->doctor?->name ?? 'Not Assigned' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-1">
                                {{-- <a href="javascript:void(0)"
                                    class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Profile
                                </a> --}}

                                <a href="javascript:void(0)" class="btn btn-danger btn-sm rounded-3 fw-semibold">
                                    <i class="bi bi-box-arrow-right me-1"></i> Discharge Patient
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Financial Summary --}}
        {{-- <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold mb-2">Financial Summary</h6>

                    @php
                        $financialItems = [
                            ['label' => 'OPD', 'percent' => 100, 'class' => 'bg-success', 'text' => 'text-success'],
                            ['label' => 'PHARMACY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            ['label' => 'PATHOLOGY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            ['label' => 'RADIOLOGY', 'percent' => 0, 'class' => 'bg-secondary', 'text' => 'text-muted'],
                            [
                                'label' => 'APPOINTMENT',
                                'percent' => 0,
                                'class' => 'bg-secondary',
                                'text' => 'text-muted',
                            ],
                        ];
                    @endphp

                    @foreach ($financialItems as $item)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">{{ $item['label'] }}</span>
                                <span class="fw-semibold {{ $item['text'] }}">
                                    {{ $item['percent'] }}{{ $item['percent'] > 0 ? '% PAID' : '%' }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $item['class'] }}" role="progressbar"
                                    style="width: {{ $item['percent'] }}%;" aria-valuenow="{{ $item['percent'] }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div> --}}
    </div>

    {{-- Current Vitals + Timeline --}}
    <div class="row g-2 mb-4">

        {{-- Left: Current Vitals --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold mb-0">Latest Vitals</h6>
                            <i class="bi bi-arrow-clockwise text-muted small"></i>
                        </div>
                        @if ($latestVital)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-clock me-1"></i>{{ $latestVital->checked_at->format('d M Y, h:i A') }}
                            </span>
                        @endif
                    </div>

                    @if ($latestVital)
                        @php
                            // BP status
                            $bpParts = explode('/', $latestVital->blood_pressure ?? '');
                            $sys = (int) ($bpParts[0] ?? 0);
                            $dia = (int) ($bpParts[1] ?? 0);
                            if ($sys > 140 || $dia > 90) {
                                $bpStatus = 'HIGH'; $bpBadge = 'bg-danger-subtle text-danger'; $bpBorder = 'border-danger';
                            } elseif ($sys < 90 || $dia < 60) {
                                $bpStatus = 'LOW'; $bpBadge = 'bg-warning-subtle text-warning'; $bpBorder = 'border-warning';
                            } else {
                                $bpStatus = 'NORMAL'; $bpBadge = 'bg-success-subtle text-success'; $bpBorder = '';
                            }

                            // Pulse / Heart Rate status
                            $pulse = $latestVital->heart_rate;
                            if ($pulse > 100) {
                                $pulseStatus = 'HIGH'; $pulseBadge = 'bg-danger-subtle text-danger'; $pulseBorder = 'border-danger';
                            } elseif ($pulse < 60) {
                                $pulseStatus = 'LOW'; $pulseBadge = 'bg-warning-subtle text-warning'; $pulseBorder = 'border-warning';
                            } else {
                                $pulseStatus = 'NORMAL'; $pulseBadge = 'bg-success-subtle text-success'; $pulseBorder = '';
                            }

                            // Temp status (°F)
                            $temp = $latestVital->temperature;
                            if ($temp > 99.5) {
                                $tempStatus = 'HIGH'; $tempBadge = 'bg-danger-subtle text-danger'; $tempBorder = 'border-danger';
                            } elseif ($temp < 96.8) {
                                $tempStatus = 'LOW'; $tempBadge = 'bg-warning-subtle text-warning'; $tempBorder = 'border-warning';
                            } else {
                                $tempStatus = 'NORMAL'; $tempBadge = 'bg-success-subtle text-success'; $tempBorder = '';
                            }

                            // SpO2 status
                            $spo2 = $latestVital->spo2;
                            if ($spo2 >= 95) {
                                $spo2Status = 'NORMAL'; $spo2Badge = 'bg-success-subtle text-success'; $spo2Border = '';
                            } elseif ($spo2 >= 90) {
                                $spo2Status = 'LOW'; $spo2Badge = 'bg-warning-subtle text-warning'; $spo2Border = 'border-warning';
                            } else {
                                $spo2Status = 'CRITICAL'; $spo2Badge = 'bg-danger text-white'; $spo2Border = 'border-danger';
                            }

                            // Resp Rate status
                            $respRate = $latestVital->respiratory_rate;
                            if ($respRate > 20) {
                                $respStatus = 'HIGH'; $respBadge = 'bg-danger-subtle text-danger'; $respBorder = 'border-danger';
                            } elseif ($respRate < 12) {
                                $respStatus = 'LOW'; $respBadge = 'bg-warning-subtle text-warning'; $respBorder = 'border-warning';
                            } else {
                                $respStatus = 'NORMAL'; $respBadge = 'bg-success-subtle text-success'; $respBorder = '';
                            }
                        @endphp

                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <div class="border rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">WEIGHT</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->weight ?? 'N/A' }} @if(!empty($latestVital->weight))<small class="fw-normal text-muted">kg</small>@endif</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">HEIGHT</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->height ?? 'N/A' }} @if(!empty($latestVital->height))<small class="fw-normal text-muted">cm</small>@endif</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $bpBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">BP (mmHg)</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $latestVital->blood_pressure ?? '-' }}</h5>
                                        <span class="badge rounded-pill {{ $bpBadge }}">{{ $bpStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $tempBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">TEMP</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $temp ?? '-' }} <small class="fw-normal text-muted">°F</small></h5>
                                        <span class="badge rounded-pill {{ $tempBadge }}">{{ $tempStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $pulseBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">HEART RATE</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $pulse ?? '-' }} <small class="fw-normal text-muted">bpm</small></h5>
                                        <span class="badge rounded-pill {{ $pulseBadge }}">{{ $pulseStatus }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $spo2Border }} rounded-3 p-2 h-100">
                                    <div class="small {{ $spo2Status !== 'NORMAL' ? 'text-danger' : 'text-muted' }} fw-semibold mb-1">SPO2</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $spo2 ?? '-' }} <small class="fw-normal text-muted">%</small></h5>
                                        <span class="badge rounded-pill {{ $spo2Badge }}">{{ $spo2Status }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-6">
                                <div class="border {{ $respBorder }} rounded-3 p-2 h-100">
                                    <div class="small text-muted fw-semibold mb-1">RESP RATE</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">{{ $respRate ?? '-' }} <small class="fw-normal text-muted">/min</small></h5>
                                        <span class="badge rounded-pill {{ $respBadge }}">{{ $respStatus }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-heart-pulse fs-4 d-block mb-1"></i>
                            No vital records found.
                        </div>
                    @endif
                </div>
            </div>
        </div>


    </div>

    @php
        $tlEvents = collect();

        if ($iPDPatient->admission_date) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($iPDPatient->admission_date), 'color' => 'primary', 'title' => 'Patient admitted', 'desc' => 'Ipd No: ' . ($iPDPatient->ipd_no ?? '-')]);
        }
        foreach ($iPDPatient->bedAllocations ?? [] as $ba) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($ba->allocate_date ?? $ba->created_at), 'color' => 'secondary', 'title' => 'Bed allocated', 'desc' => $ba->bed->name ?? null]);
        }
        foreach ($iPDPatient->vitalChecks ?? [] as $vc) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($vc->created_at), 'color' => 'danger', 'title' => 'Vital check recorded', 'desc' => trim(collect([$vc->blood_pressure ? 'BP ' . $vc->blood_pressure : null, $vc->pulse ? 'Pulse ' . $vc->pulse : null, $vc->temperature ? 'Temp ' . $vc->temperature : null])->filter()->implode(' • ')) ?: null]);
        }
        foreach ($iPDPatient->nurseNotes ?? [] as $nn) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($nn->date ?? $nn->created_at), 'color' => 'info', 'title' => 'Nurse note', 'desc' => $nn->description ?? ($nn->note ?? null)]);
        }
        foreach ($iPDPatient->roundDrs ?? [] as $rd) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($rd->date ?? $rd->created_at), 'color' => 'primary', 'title' => 'Round doctor visit', 'desc' => $rd->description ?? null]);
        }
        foreach ($iPDPatient->caseDrs ?? [] as $cd) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($cd->date ?? $cd->created_at), 'color' => 'primary', 'title' => 'Case doctor note', 'desc' => $cd->description ?? null]);
        }
        foreach ($iPDPatient->operationHistories ?? [] as $op) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($op->date ?? $op->created_at), 'color' => 'dark', 'title' => 'Operation: ' . ($op->operation->name ?? $op->name ?? '-'), 'desc' => $op->description ?? null]);
        }
        foreach ($iPDPatient->medicineOrders ?? [] as $mo) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($mo->date ?? $mo->created_at), 'color' => 'success', 'title' => 'Medicine order', 'desc' => $mo->note ?? null]);
        }
        foreach ($iPDPatient->medications ?? [] as $med) {
            $tlEvents->push(['date' => $med->datetime ?? $med->created_at, 'color' => 'success', 'title' => 'Medication: ' . ($med->medicine->medicine_name ?? 'N/A'), 'desc' => trim(collect([$med->dosage ?? null, $med->medicated_by ?? null])->filter()->implode(' • ')) ?: null]);
        }
        foreach ($iPDPatient->pathologyOrders ?? [] as $po) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($po->date ?? $po->created_at), 'color' => 'warning', 'title' => 'Pathology order', 'desc' => $po->pathology->name ?? null]);
        }
        foreach ($iPDPatient->radiologyOrders ?? [] as $ro) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($ro->date ?? $ro->created_at), 'color' => 'warning', 'title' => 'Radiology order', 'desc' => $ro->radiology->name ?? null]);
        }
        foreach ($iPDPatient->treatmentHistories ?? [] as $th) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($th->date ?? $th->created_at), 'color' => 'info', 'title' => 'Treatment history', 'desc' => $th->description ?? null]);
        }
        foreach ($iPDPatient->charges ?? [] as $ch) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($ch->date), 'color' => 'warning', 'title' => 'Charge added: ' . ($ch->charge_item ?? '-'), 'desc' => 'Qty ' . $ch->quantity . ' • Net ' . number_format($ch->net_amount, 2)]);
        }
        foreach ($iPDPatient->transactions ?? [] as $tx) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($tx->payment_date), 'color' => 'info', 'title' => 'Payment received: ' . number_format($tx->net_amount, 2), 'desc' => ucfirst($tx->payment_via ?? '-') . ($tx->invoice_no ? ' • ' . $tx->invoice_no : '')]);
        }
        if ($iPDPatient->discharge_date) {
            $tlEvents->push(['date' => \Illuminate\Support\Carbon::parse($iPDPatient->discharge_date), 'color' => 'success', 'title' => 'Patient discharged', 'desc' => null]);
        }

        $tlEvents = $tlEvents->filter(fn($e) => !empty($e['date']))->sortByDesc(fn($e) => $e['date'])->take(5)->values();
    @endphp

    @php
        $iconMap = [
            'primary'   => 'bi-person-badge',
            'secondary' => 'bi-hospital',
            'danger'    => 'bi-heart-pulse-fill',
            'info'      => 'bi-journal-medical',
            'success'   => 'bi-capsule',
            'warning'   => 'bi-clipboard2-pulse',
            'dark'      => 'bi-scissors',
        ];
    @endphp

    <div class="row g-2 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                            style="width:32px; height:32px;">
                            <i class="bi bi-clock-history"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0">Patient Timeline</h6>
                            <small class="text-muted">Latest 5 events</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="document.getElementById('timeline-tab').click();">
                        See full timeline <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>

                <div class="card-body pt-2 pb-3 px-3">
                    @if ($tlEvents->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inboxes fs-3 d-block mb-1 opacity-50"></i>
                            No timeline events yet.
                        </div>
                    @else
                        <ul class="list-unstyled mb-0 position-relative ps-4 pt-2"
                            style="border-left: 2px dashed #e5e7eb; margin-left: 14px;">
                            @foreach ($tlEvents as $event)
                                @php $icon = $iconMap[$event['color']] ?? 'bi-circle-fill'; @endphp
                                <li class="position-relative mb-3">
                                    <span class="position-absolute d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $event['color'] }}-subtle text-{{ $event['color'] }} border border-2 border-white shadow-sm"
                                        style="width:32px; height:32px; left:-32px; top:-2px;">
                                        <i class="bi {{ $icon }}"></i>
                                    </span>
                                    <div class="bg-light rounded-3 px-3 py-2">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                            <div class="fw-semibold small text-dark">{{ $event['title'] }}</div>
                                            <span class="badge bg-white text-muted border" style="font-weight:500;">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Illuminate\Support\Carbon::parse($event['date'])->format('d M Y, h:i A') }}
                                            </span>
                                        </div>
                                        @if (!empty($event['desc']))
                                            <div class="text-muted small mb-0">{{ $event['desc'] }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $patientHistory = trim((string) ($iPDPatient->patient_history ?? ''));
        $findings = trim((string) ($iPDPatient->remarks ?? ''));
        $symptomNames = collect();
        foreach ($iPDPatient->prescriptions ?? [] as $prescription) {
            foreach ($prescription->symptoms ?? [] as $ps) {
                if (!empty($ps->symptom->name)) {
                    $symptomNames->push($ps->symptom->name);
                }
            }
        }
        $symptomNames = $symptomNames->unique()->values();
    @endphp

    {{-- History / Findings / Symptoms --}}
    <div class="row g-2 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold text-danger mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>Previous History
                    </h6>
                    @if ($patientHistory !== '')
                        <p class="mb-0 text-muted small">{{ $patientHistory }}</p>
                    @else
                        <p class="mb-0 text-muted small fst-italic">No previous history recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-lg rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="bi bi-clipboard2-pulse me-2"></i>Findings
                    </h6>
                    @if ($findings !== '')
                        <p class="mb-0 text-muted small">{{ $findings }}</p>
                    @else
                        <p class="mb-0 text-muted small fst-italic">No findings recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-2">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-emoji-dizzy me-2"></i>Symptoms
                    </h6>
                    @if ($symptomNames->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($symptomNames as $name)
                                <span class="badge rounded-pill bg-info-subtle text-info px-2 py-1">{{ $name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 text-muted small fst-italic">No symptoms recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Medication List + Unbilled Charges --}}
    <div class="row g-2 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="p-2 pb-1">
                        <h6 class="fw-bold mb-0">Medication List</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-2 py-1">Date</th>
                                    <th class="py-1">Medicine Name</th>
                                    <th class="py-1">Dose</th>
                                    <th class="py-1">Time</th>
                                    <th class="py-1">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $latestMeds = $iPDPatient->medications->sortByDesc('datetime')->take(5);
                                    $slotFor = function ($dt) {
                                        if (!$dt) return ['Day', 'secondary'];
                                        $h = (int) \Illuminate\Support\Carbon::parse($dt)->format('H');
                                        if ($h < 12) return ['Morning', 'primary'];
                                        if ($h < 17) return ['Afternoon', 'warning'];
                                        if ($h < 21) return ['Evening', 'info'];
                                        return ['Night', 'dark'];
                                    };
                                @endphp
                                @forelse ($latestMeds as $med)
                                    @php [$slot, $slotColor] = $slotFor($med->datetime); @endphp
                                    <tr>
                                        <td class="px-2">{{ optional($med->datetime)->format('d M Y') ?? '-' }}</td>
                                        <td class="fw-semibold">{{ $med->medicine->medicine_name ?? 'N/A' }}</td>
                                        <td>{{ $med->dosage ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $slotColor }}-subtle text-{{ $slotColor }}">{{ strtoupper($slot) }}</span>
                                        </td>
                                        <td class="text-muted">{{ $med->remarks ?? $med->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No medications recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unbilled Charges --}}
        {{-- <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-2">
                    <h6 class="fw-bold mb-2">Unbilled Charges</h6>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">General Fee</span>
                        <span class="fw-bold">Tk. 45.00</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">Nurse Visit</span>
                        <span class="fw-bold">Tk. 12.50</span>
                    </div>

                    <hr class="my-1">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary fs-6">Total Payable</span>
                        <span class="fw-bold text-primary fs-6">Tk. 57.50</span>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    {{-- Patient Documents --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 pb-1">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-folder2-open me-1"></i> Patient Documents
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                    {{ $iPDPatient->documents->count() }} file(s)
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-2 py-1">SN</th>
                            <th class="py-1">Title</th>
                            <th class="py-1">File</th>
                            <th class="py-1">Remarks</th>
                            <th class="py-1">Uploaded</th>
                            <th class="py-1 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($iPDPatient->documents as $doc)
                            @php
                                $ext = strtolower(pathinfo($doc->file, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                $iconClass = match (true) {
                                    $isImage => 'bi-file-earmark-image text-info',
                                    $ext === 'pdf' => 'bi-file-earmark-pdf text-danger',
                                    in_array($ext, ['doc', 'docx']) => 'bi-file-earmark-word text-primary',
                                    in_array($ext, ['xls', 'xlsx', 'csv']) => 'bi-file-earmark-excel text-success',
                                    default => 'bi-file-earmark text-secondary',
                                };
                                $url = asset('storage/' . $doc->file);
                            @endphp
                            <tr>
                                <td class="px-2">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $doc->title ?: '—' }}</td>
                                <td>
                                    <a href="{{ $url }}" target="_blank" class="text-decoration-none">
                                        <i class="bi {{ $iconClass }} me-1"></i>
                                        <span class="text-uppercase small">{{ $ext ?: 'file' }}</span>
                                    </a>
                                </td>
                                <td class="text-muted small">{{ $doc->remarks ?: '—' }}</td>
                                <td class="small">{{ \Illuminate\Support\Carbon::parse($doc->created_at)->format('d M Y, h:i A') }}</td>
                                <td class="text-center">
                                    <a href="{{ $url }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                        title="View / Download">
                                        <i class="bi bi-box-arrow-up-right"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="bi bi-folder2 fs-4 d-block mb-1 opacity-50"></i>
                                    No documents uploaded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Lab Investigation --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="p-2 pb-1">
                <h6 class="fw-bold mb-0">Lab Investigation</h6>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-2 py-1">Test Name</th>
                            <th class="py-1">Lab</th>
                            <th class="py-1">Sample Collected</th>
                            <th class="py-1">Expected Date</th>
                            <th class="py-1">Status</th>
                            <th class="py-1">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $labRows = collect();
                            foreach ($iPDPatient->pathologyOrders ?? [] as $po) {
                                $labRows->push([
                                    'name'      => $po->labInvestigation->name ?? '-',
                                    'lab'       => $po->lab_name ?? 'Pathology',
                                    'collected' => !empty($po->collected_by),
                                    'date'      => $po->datetime,
                                    'kind'      => 'pathology',
                                    'url'       => route('ipd-patients.pathology-orders.show', [$iPDPatient->id, $po->id]),
                                    'label'     => 'View',
                                ]);
                            }
                            foreach ($iPDPatient->radiologyOrders ?? [] as $ro) {
                                $labRows->push([
                                    'name'      => $ro->radiology->name ?? '-',
                                    'lab'       => $ro->lab_name ?? 'Radiology',
                                    'collected' => !empty($ro->collected_by),
                                    'date'      => $ro->datetime ?? $ro->created_at,
                                    'kind'      => 'radiology',
                                    'url'       => '#',
                                    'label'     => 'Imaging',
                                ]);
                            }
                            $labRows = $labRows->sortByDesc('date')->take(5);
                        @endphp
                        @forelse ($labRows as $row)
                            <tr>
                                <td class="px-4 fw-semibold">{{ $row['name'] }}</td>
                                <td>{{ $row['lab'] }}</td>
                                <td class="{{ $row['collected'] ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ $row['collected'] ? 'Collected' : 'Not Collected' }}
                                </td>
                                <td>{{ $row['date'] ? \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y') : '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $row['collected'] ? 'success' : 'warning' }}-subtle text-{{ $row['collected'] ? 'success' : 'warning' }}">
                                        {{ $row['collected'] ? 'APPROVED' : 'PENDING' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ $row['url'] }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        {{ $row['label'] }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No lab investigations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Charges --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 pb-1">
                <h6 class="fw-bold mb-0">Patient Charges</h6>
                <a href="{{ route('ipd-patients.charges.create', $iPDPatient->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Charge
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SN</th>
                            <th class="px-2 py-1">Name</th>
                            <th class="py-1">Charge Type</th>
                            <th class="py-1">Unit Charge</th>
                            <th class="py-1">Qty</th>
                            <th class="py-1">Vat</th>
                            <th class="py-1">Tax</th>
                            <th class="py-1">Applied Charge</th>
                            <th class="py-1">Net Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($iPDPatient->charges as $charge)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="px-2">
                                    {{ $charge->charge_item }}
                                </td>
                                <td>{{ ucfirst($charge->charge_module) }}</td>
                                <td>{{ number_format($charge->unit_price, 2) }}</td>
                                <td>{{ $charge->quantity ?? '' }}</td>
                                <td>
                                    {{ number_format($charge->vat, 2) }}
                                    @if ($charge->amount > 0)
                                        <div class="small text-muted">
                                            ({{ number_format(($charge->vat / $charge->amount) * 100, 2) }}%)
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($charge->tax, 2) }}
                                    @if ($charge->amount > 0)
                                        <div class="small text-muted">
                                            ({{ number_format(($charge->tax / $charge->amount) * 100, 2) }}%)</div>
                                    @endif
                                </td>
                                <td>{{ number_format($charge->amount, 2) }}</td>
                                <td class="fw-bold">{{ number_format($charge->net_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No charges found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Payment --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="p-2 pb-1">
                <h6 class="fw-bold mb-0">PAYMENT</h6>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-1">SN</th>
                            <th class="px-2 py-1">Invoice No.</th>
                            <th class="py-1">Date</th>
                            <th class="py-1">Type</th>
                            <th class="py-1">Payment Mode</th>
                            <th class="py-1">Amount</th>
                            <th class="py-1">Vat</th>
                            <th class="py-1">Tax</th>
                            <th class="py-1">Total</th>
                            <th class="py-1">Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($iPDPatient->transactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="px-4 fw-bold">{{ $transaction->invoice_no }}</td>
                                <td>
                                    {{ format_datetime($transaction->payment_date) }}
                                </td>
                                <td>{{ $transaction->type }}</td>
                                <td>{{ ucfirst($transaction->payment_via ?? '-') }}</td>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                                <td>{{ number_format($transaction->vat, 2) }}</td>
                                <td>{{ number_format($transaction->tax, 2) }}</td>
                                <td class="fw-bold">{{ number_format($transaction->net_amount, 2) }}</td>
                                <td>{{ $transaction->received_by ?? '-'}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No payment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
{{-- </div> --}}

{{-- Patient Image Zoom Modal --}}
@if ($iPDPatient->patient?->image)
    <div class="modal fade" id="patientImageModal" tabindex="-1" aria-label="Patient Image" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ $iPDPatient->patient?->patient_name ?? 'Patient' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="{{ asset('storage/' . $iPDPatient->patient->image) }}"
                        class="img-fluid rounded-4 shadow" alt="Patient Image">
                </div>
            </div>
        </div>
    </div>
@endif
