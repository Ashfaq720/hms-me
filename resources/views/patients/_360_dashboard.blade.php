{{-- Patient 360° unified dashboard — KPIs + quick actions + chronological timeline --}}
@if (isset($kpi))
    <div class="card border-0 shadow-sm mb-3 mt-3">
        <div class="card-body">
            <h6 class="card-title text-uppercase text-muted small mb-3">
                <i class="fi fi-rr-chart-pie-alt me-1"></i> Patient 360°
            </h6>

            {{-- KPI grid --}}
            <div class="row g-2">
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-primary bg-opacity-10">
                        <div class="text-primary small">Total Visits</div>
                        <h4 class="mb-0">{{ $kpi['total_visits'] }}</h4>
                        <small class="text-muted">OPD {{ $kpi['opd_count'] }} · IPD {{ $kpi['ipd_count'] }} · ER {{ $kpi['er_count'] }}</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-info bg-opacity-10">
                        <div class="text-info small">Encounters</div>
                        <h4 class="mb-0">{{ $kpi['encounters_open'] + $kpi['encounters_closed'] }}</h4>
                        <small class="text-muted">Open {{ $kpi['encounters_open'] }} · Closed {{ $kpi['encounters_closed'] }}</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-success bg-opacity-10">
                        <div class="text-success small">Total Billed</div>
                        <h4 class="mb-0">৳ {{ number_format($kpi['total_billed'], 2) }}</h4>
                        <small class="text-success">Paid ৳ {{ number_format($kpi['total_paid'], 2) }}</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 {{ $kpi['outstanding_due'] > 0.01 ? 'bg-danger bg-opacity-10' : 'bg-light' }}">
                        <div class="text-{{ $kpi['outstanding_due'] > 0.01 ? 'danger' : 'muted' }} small">Outstanding Due</div>
                        <h4 class="mb-0 {{ $kpi['outstanding_due'] > 0.01 ? 'text-danger' : '' }}">৳ {{ number_format($kpi['outstanding_due'], 2) }}</h4>
                        <small class="text-muted">{{ $kpi['outstanding_due'] > 0.01 ? 'Pending' : 'Clear' }}</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-warning bg-opacity-10">
                        <div class="text-warning small">Charges</div>
                        <h4 class="mb-0">{{ $kpi['charges_count'] }}</h4>
                        <small class="text-muted">₿ {{ number_format($kpi['charges_total'], 2) }}</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="border rounded p-2 h-100 bg-secondary bg-opacity-10">
                        <div class="text-secondary small">Claims</div>
                        <h4 class="mb-0">{{ $kpi['claims_open'] + $kpi['claims_settled'] }}</h4>
                        <small class="text-muted">Open {{ $kpi['claims_open'] }} · Settled {{ $kpi['claims_settled'] }}</small>
                    </div>
                </div>
            </div>

            {{-- Status banners --}}
            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($kpi['active_admission'])
                    <span class="badge bg-info-soft text-info p-2">
                        <i class="fi fi-rr-bed me-1"></i>
                        Currently admitted: {{ $kpi['active_admission']->ipd_no }}
                        since {{ $kpi['active_admission']->admission_date?->toDateString() }}
                    </span>
                @endif
                @if ($kpi['active_policy'])
                    <span class="badge bg-primary-soft text-primary p-2">
                        <i class="fi fi-rr-shield-check me-1"></i>
                        Active insurance: {{ $kpi['active_policy']->payer->name ?? '—' }}
                        ({{ $kpi['active_policy']->plan_name ?? 'Plan' }})
                    </span>
                @endif
                @if ($patient->is_dead)
                    <span class="badge bg-dark text-light p-2"><i class="fi fi-rr-cross me-1"></i> Deceased</span>
                @endif
            </div>

            {{-- Quick actions --}}
            <div class="d-flex flex-wrap gap-2 mt-3">
                @can('encounter.create')
                    <a href="{{ route('opd-patients.create', ['patient_id' => $patient->id]) }}"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fi fi-rr-stethoscope me-1"></i> New OPD Visit
                    </a>
                @endcan
                @can('encounter.create')
                    <a href="{{ route('ipd-patients.create', ['patient_id' => $patient->id]) }}"
                        class="btn btn-sm btn-outline-info">
                        <i class="fi fi-rr-hospital me-1"></i> Admit IPD
                    </a>
                @endcan
                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}"
                    class="btn btn-sm btn-outline-warning">
                    <i class="fi fi-rr-calendar-clock me-1"></i> Book Appointment
                </a>
                @can('billing.bill.view')
                    <a href="{{ route('billing.bills.index', ['q' => $patient->mrn]) }}"
                        class="btn btn-sm btn-outline-success">
                        <i class="fi fi-rr-receipt me-1"></i> View Bills ({{ count($bills) }})
                    </a>
                @endcan
                @can('insurance.claim.view')
                    <a href="{{ route('insurance.claims.index', ['q' => $patient->mrn]) }}"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="fi fi-rr-shield-check me-1"></i> Claims ({{ count($claims) }})
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Activity Timeline --}}
    @if (isset($timeline) && $timeline->count())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fi fi-rr-list me-1"></i> Activity Timeline</h6>
                <small class="text-muted">Chronological feed across every module — newest first</small>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach ($timeline->take(20) as $event)
                        <li class="list-group-item d-flex align-items-start gap-3 py-2">
                            <div class="text-{{ $event->colour }}" style="font-size:1.5rem;">
                                <i class="{{ str_starts_with($event->icon, 'fi-') ? 'fi ' . $event->icon : ($event->icon) }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-{{ $event->colour }} bg-opacity-10 text-{{ $event->colour }}">{{ $event->type }}</span>
                                        <strong class="ms-1">{{ $event->title }}</strong>
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($event->at)->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted d-block">{{ $event->meta }}</small>
                                @if ($event->url && $event->url !== '#')
                                    <a href="{{ $event->url }}" class="small">Open →</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($timeline->count() > 20)
                    <div class="card-footer text-center text-muted small">
                        Showing 20 of {{ $timeline->count() }} events
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
