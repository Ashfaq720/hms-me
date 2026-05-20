@extends('backend.layouts.master')

@section('title', 'Pharmacy Approvals')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Approvals</h4>
            <p class="text-muted mb-0 small">Review and approve pending Ipd medicine issue requests.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#EEF2FF;">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                        </div>
                        <span class="text-muted small">Total Requests</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalCount }}</h3>
                </div>
                <div style="height:3px;background:var(--primary);"></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-clock-history" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Pending</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning">{{ $pendingCount }}</h3>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-check-circle" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Approved</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">{{ $approvedCount }}</h3>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-currency-dollar" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Pending Value</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($pendingValue, 0) }} <small class="fs-6 text-muted fw-normal">TK</small></h3>
                </div>
                <div style="height:3px;background:#1a237e;"></div>
            </div>
        </div>
    </div>

    {{-- Filter + Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <form method="GET" action="{{ route('admin.pharmacy.approvals') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Issue No</label>
                        <input type="text" name="issue_no" class="form-control form-control-sm"
                               placeholder="Search issue no..." value="{{ request('issue_no') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Patient Name</label>
                        <input type="text" name="patient_name" class="form-control form-control-sm"
                               placeholder="Search patient..." value="{{ request('patient_name') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="resumed"  {{ request('status') === 'resumed'  ? 'selected' : '' }}>Resumed</option>
                        </select>
                    </div>
                    <div class="col-lg-auto col-md-4 col-sm-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.pharmacy.approvals') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                            <a href="{{ route('admin.pharmacy.approvals.export') }}?{{ http_build_query(request()->only(['issue_no','patient_name','date_from','date_to','status'])) }}"
                               class="btn btn-outline-primary btn-sm" title="Export CSV">
                                <i class="bi bi-download me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">#</th>
                        <th class="py-3 text-muted small fw-semibold">ISSUE NO</th>
                        <th class="py-3 text-muted small fw-semibold">DATE</th>
                        <th class="py-3 text-muted small fw-semibold">Ipd NO</th>
                        <th class="py-3 text-muted small fw-semibold">PATIENT</th>
                        <th class="py-3 text-muted small fw-semibold">WARD / BED</th>
                        <th class="py-3 text-muted small fw-semibold">REQ. NO</th>
                        <th class="py-3 text-muted small fw-semibold text-center">DRUGS</th>
                        <th class="py-3 text-muted small fw-semibold text-end">AMOUNT (TK)</th>
                        <th class="py-3 text-muted small fw-semibold">ISSUED BY</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $i => $issue)
                        <tr>
                            <td class="px-3 py-3 text-muted small">{{ $issues->firstItem() + $i }}</td>
                            <td class="py-3 fw-medium text-primary small">{{ $issue->issue_no }}</td>
                            <td class="py-3 small">{{ $issue->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 small">{{ $issue->ipdPatient->ipd_no ?? '—' }}</td>
                            <td class="py-3 fw-medium small">{{ $issue->patient->patient_name ?? '—' }}</td>
                            <td class="py-3 small text-muted">{{ $issue->ward_bed ?? '—' }}</td>
                            <td class="py-3 small text-muted">{{ $issue->requisition_no ?? '—' }}</td>
                            <td class="py-3 text-center small fw-medium">{{ $issue->drug_count }}</td>
                            <td class="py-3 text-end fw-medium small">{{ number_format($issue->total_amount, 2) }}</td>
                            <td class="py-3 small text-muted">{{ $issue->issuedBy->name ?? '—' }}</td>
                            <td class="py-3">
                                @switch($issue->status)
                                    @case('approved')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Approved</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Pending</span>
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
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    {{-- View Details --}}
                                    <a data-url="{{ route('admin.pharmacy.ipd-issue.show', $issue->id) }}"
                                       data-ajax-popup="true"
                                       data-title="Ipd Issue — {{ $issue->issue_no }}"
                                       data-size="xl"
                                       class="btn btn-sm btn-light rounded-circle"
                                       style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                       title="View Details">
                                        <i class="bi bi-eye small"></i>
                                    </a>
                                    {{-- Approve (only if pending) --}}
                                    @if($issue->status === 'pending')
                                        <form method="POST"
                                              action="{{ route('admin.pharmacy.ipd-issue.approve', $issue->id) }}"
                                              onsubmit="return confirm('Approve issue {{ $issue->issue_no }}?')">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-success rounded-circle"
                                                    style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                                    title="Approve">
                                                <i class="bi bi-check-lg small"></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Print Issue Note --}}
                                    <a href="{{ route('admin.pharmacy.ipd-issue.print-single', $issue->id) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-light rounded-circle"
                                       style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                       title="Print Issue Note">
                                        <i class="bi bi-printer small"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard2-check fs-1 d-block mb-2 opacity-25"></i>
                                <p class="mb-1">No approval requests found.</p>
                                <small>Adjust your filters or check back later.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($issues->hasPages())
            <div class="card-body border-top py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <small class="text-muted">
                        Showing {{ $issues->firstItem() }}–{{ $issues->lastItem() }} of {{ $issues->total() }} records
                    </small>
                    {{ $issues->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
