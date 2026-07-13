@extends('backend.layouts.master')

@section('title', 'Pharmacy Returns')

@section('content')
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Pharmacy Returns</h4>
            <p class="text-muted mb-0 small">Medicine return requests across all transaction types.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFEBEE;">
                            <i class="bi bi-arrow-return-left" style="color:#C62828;"></i>
                        </div>
                        <span class="text-muted small">Today's Returns</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($todayTotal, 2) }} TK</h4>
                    <small class="text-muted">{{ $todayCount }} return{{ $todayCount !== 1 ? 's' : '' }}</small>
                </div>
                <div style="height:3px;background:#C62828;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-clock-history" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Pending Approval</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-warning">{{ $pendingCount }}</h4>
                    <small class="text-muted">Awaiting review</small>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-3 col-sm-6">
            <div class="card border-0 rounded-3 h-100 d-flex align-items-center justify-content-center" style="background:var(--primary);min-height:120px;">
                <a href="{{ route('admin.pharmacy.returns.create') }}"
                   class="text-white text-decoration-none text-center">
                    <i class="bi bi-plus-circle fs-3 d-block mb-1"></i>
                    <span class="fw-semibold">New Return</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pharmacy.returns') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Return No</label>
                        <input type="text" name="return_no" class="form-control form-control-sm" placeholder="RTN-..." value="{{ request('return_no') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Type</label>
                        <select name="transaction_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="opd" {{ request('transaction_type') === 'opd' ? 'selected' : '' }}>OPD</option>
                            <option value="ipd" {{ request('transaction_type') === 'ipd' ? 'selected' : '' }}>Ipd</option>
                            <option value="otc" {{ request('transaction_type') === 'otc' ? 'selected' : '' }}>OTC</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="{{ route('admin.pharmacy.returns') }}" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">Return List &nbsp;<small class="text-muted fw-normal">({{ $returns->count() }} records)</small></h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small py-2">#</th>
                        <th class="small py-2">Return No</th>
                        <th class="small py-2">Txn No</th>
                        <th class="small py-2">Type</th>
                        <th class="small py-2">Patient</th>
                        <th class="small py-2 text-end">Amount (TK)</th>
                        <th class="small py-2">Reason</th>
                        <th class="small py-2">Status</th>
                        <th class="small py-2">Returned By</th>
                        <th class="small py-2">Date</th>
                        <th class="small py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $i => $rtn)
                    @php $rtnClass = match($rtn->status) { 'completed' => 'success', default => 'warning' }; @endphp
                    <tr>
                        <td class="small text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold small">{{ $rtn->return_no }}</td>
                        <td class="small">
                            @if($rtn->transaction)
                                <a href="{{ route('admin.pharmacy.transactions.show', $rtn->transaction_id) }}" class="text-decoration-none">
                                    {{ $rtn->transaction->transaction_no }}
                                </a>
                            @else —
                            @endif
                        </td>
                        <td>
                            @php $typeClass = match($rtn->transaction_type) { 'opd' => 'bg-primary', 'ipd' => 'bg-info text-dark', 'otc' => 'bg-success', default => 'bg-secondary' }; @endphp
                            <span class="badge {{ $typeClass }}">{{ strtoupper($rtn->transaction_type) }}</span>
                        </td>
                        <td class="small">{{ $rtn->patient->patient_name ?? 'Walk-in' }}</td>
                        <td class="small text-end fw-medium">{{ number_format($rtn->total_amount, 2) }}</td>
                        <td class="small text-muted">{{ Str::limit($rtn->reason, 30) }}</td>
                        <td><span class="badge bg-{{ $rtnClass }}-subtle text-{{ $rtnClass }}">{{ ucfirst($rtn->status) }}</span></td>
                        <td class="small">{{ $rtn->returnedBy->name ?? '—' }}</td>
                        <td class="small text-muted">{{ $rtn->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.pharmacy.returns.show', $rtn->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($rtn->status === 'pending')
                                    <form action="{{ route('admin.pharmacy.returns.approve', $rtn->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Approve"
                                                onclick="return confirm('Approve {{ $rtn->return_no }} and restore stock?')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i> No returns found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
