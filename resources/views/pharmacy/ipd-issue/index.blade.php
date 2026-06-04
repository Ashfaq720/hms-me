@extends('backend.layouts.master')

@section('title', 'Ipd Issue')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Ipd Issue</h4>
            <p class="text-muted mb-0 small">Medicine delivery records for admitted patients and wards.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid var(--primary) !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-primary small fw-semibold">Total Cases &rarr;</span>
                            <h4 class="fw-bold mb-0 mt-1"><span style="font-family:sans-serif;">&#2547;</span> {{ number_format($totalAmount, 0) }}</h4>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#EEF2FF;">
                            <i class="bi bi-clipboard2-data text-primary fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #F9A825 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-warning small fw-semibold">Pending Requests</span>
                            <h4 class="fw-bold mb-0 mt-1">{{ $pendingCount }} <i class="bi bi-person-fill-up text-warning small"></i></h4>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#FFF8E1;">
                            <i class="bi bi-hourglass-split text-warning fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #388E3C !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-success small fw-semibold">Recently Issued</span>
                            <h4 class="fw-bold mb-0 mt-1"><i class="bi bi-check-circle text-success small"></i> {{ $recentIssuedCount }} Patient{{ $recentIssuedCount !== 1 ? 's' : '' }}</h4>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#E8F5E9;">
                            <i class="bi bi-people text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #7B1FA2 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small fw-semibold" style="color:#7B1FA2;">To Be Billed &rarr;</span>
                            <h4 class="fw-bold mb-0 mt-1"><span style="font-family:sans-serif;">&#2547;</span> {{ number_format($toBeBilled, 0) }}</h4>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#F3E5F5;">
                            <i class="bi bi-receipt-cutoff fs-5" style="color:#7B1FA2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pharmacy.ipd-issue') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="Search patient..." value="{{ request('patient_name') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Issue No</label>
                        <input type="text" name="issue_no" class="form-control form-control-sm" placeholder="Search issue..." value="{{ request('issue_no') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Requisition No</label>
                        <input type="text" name="requisition_no" class="form-control form-control-sm" placeholder="Search Req No..." value="{{ request('requisition_no') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Request Source</label>
                        <select name="request_source" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($requestSources as $src)
                                <option value="{{ $src }}" {{ request('request_source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search me-1"></i> Search</button>
                            <a data-size="xl"
                               class="btn btn-dark btn-sm"
                               data-url="{{ route('admin.pharmacy.ipd-issue.create') }}"
                               data-ajax-popup="true"
                               data-title="Add New Ipd Issue">Add Ipd Issue</a>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['date_from','patient_name','issue_no','requisition_no','request_source','status']))
                <div class="row g-2 mt-1 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="resumed" {{ request('status') === 'resumed' ? 'selected' : '' }}>Resumed</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.pharmacy.ipd-issue') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Ipd Issue List --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0 fw-semibold">Ipd Issue List</h6>
                    <small class="text-muted">{{ $issues->count() }} record{{ $issues->count() !== 1 ? 's' : '' }} found</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light rounded-circle" style="width:32px;height:32px;padding:0;" title="Refresh" onclick="location.reload()"><i class="bi bi-arrow-clockwise small"></i></button>
                    <a href="{{ route('admin.pharmacy.ipd-issue.export') . '?' . http_build_query(request()->only(['date_from','patient_name','issue_no','requisition_no','request_source','status'])) }}"
                       class="btn btn-sm btn-outline-primary btn-sm" title="Export CSV">
                        <i class="bi bi-download small me-1"></i> Export
                    </a>
                    <a href="{{ route('admin.pharmacy.ipd-issue.print') . '?' . http_build_query(request()->only(['date_from','patient_name','issue_no','requisition_no','request_source','status'])) }}"
                       target="_blank" class="btn btn-sm btn-outline-success btn-sm" title="Print List">
                        <i class="bi bi-printer small me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt_basic">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">ISSUE NO</th>
                        <th class="py-3 text-muted small fw-semibold">Ipd NO</th>
                        <th class="py-3 text-muted small fw-semibold">PATIENT BIO</th>
                        <th class="py-3 text-muted small fw-semibold">WARD / BED</th>
                        <th class="py-3 text-muted small fw-semibold">REQUISITION NO</th>
                        <th class="py-3 text-muted small fw-semibold">REQUEST SOURCE</th>
                        <th class="py-3 text-muted small fw-semibold">DRUG COUNT</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarColors = ['#4361EE','#F57C00','#7B1FA2','#D32F2F','#388E3C','#1565C0','#00897B','#C62828','#5E35B1','#EF6C00'];
                    @endphp
                    @forelse($issues as $issue)
                        @php
                            $patientName = $issue->patient->patient_name ?? '—';
                            $initial = strtoupper(substr($patientName, 0, 1));
                            $colorIdx = $issue->patient_id % count($avatarColors);
                        @endphp
                        <tr>
                            <td class="px-3 py-3 small">{{ $issue->issue_no }}</td>
                            <td class="py-3">
                                <span class="text-primary fw-medium">{{ $issue->ipdPatient->ipd_no ?? '—' }}</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width:32px;height:32px;background:{{ $avatarColors[$colorIdx] }};font-size:0.75rem;flex-shrink:0;">{{ $initial }}</span>
                                    <span class="fw-medium">{{ $patientName }}</span>
                                </div>
                            </td>
                            <td class="py-3 small">{{ $issue->ward_bed ?? '—' }}</td>
                            <td class="py-3 small">{{ $issue->requisition_no ?? '—' }}</td>
                            <td class="py-3 small">{{ $issue->request_source ?? '—' }}</td>
                            <td class="py-3 text-center fw-medium">{{ $issue->drug_count }}</td>
                            <td class="py-3">
                                @switch($issue->status)
                                    @case('approved')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Approved</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Pending Approval</span>
                                        @break
                                    @case('returned')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Returned</span>
                                        @break
                                    @case('resumed')
                                        <span class="badge rounded-pill" style="background:#FFF8E1;color:#F9A825;">Resumed</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ ucfirst($issue->status) }}</span>
                                @endswitch
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-inline-flex gap-1">
                                    {{-- View --}}
                                    <a data-size="xl"
                                       data-url="{{ route('admin.pharmacy.ipd-issue.show', $issue->id) }}"
                                       data-ajax-popup="true"
                                       data-title="Ipd Issue — {{ $issue->issue_no }}"
                                       class="btn btn-sm btn-light rounded-circle"
                                       style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                       title="View Details">
                                        <i class="bi bi-eye small"></i>
                                    </a>
                                    {{-- More dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle dropdown-toggle-no-caret"
                                                style="width:30px;height:30px;padding:0;"
                                                data-bs-toggle="dropdown" title="More">
                                            <i class="bi bi-three-dots-vertical small"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:160px;">
                                            @if($issue->status === 'pending')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.pharmacy.ipd-issue.approve', $issue->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success"
                                                                onclick="return confirm('Approve {{ $issue->issue_no }}?')">
                                                            <i class="bi bi-check-circle me-2"></i> Approve
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.pharmacy.ipd-issue.print-single', $issue->id) }}"
                                                   target="_blank">
                                                    <i class="bi bi-printer me-2"></i> Print Issue Note
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-hospital fs-1 d-block mb-2 opacity-25"></i>
                                <p class="mb-1">No Ipd issue records found.</p>
                                <small>Adjust your filters or create a new Ipd issue.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
