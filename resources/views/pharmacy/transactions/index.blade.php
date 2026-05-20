@extends('backend.layouts.master')

@section('title', 'Pharmacy Transactions')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Pharmacy Transactions</h4>
            <p class="text-muted mb-0 small">Unified view of OPD Dispense · Ipd Issue · Counter Sale (OTC)</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted small d-none d-md-block">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
                <span class="ms-2"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
            </div>
            <a data-size="xl"
               data-url="{{ route('admin.pharmacy.transactions.create') }}"
               data-ajax-popup="true"
               data-title="New Pharmacy Transaction"
               id="btnNewTransaction"
               class="btn btn-primary btn-sm"
               style="cursor:pointer;">
                <i class="bi bi-plus-circle me-1"></i> New Transaction
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">

        {{-- Today's Revenue --}}
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #388E3C !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-success small fw-semibold">Today's Revenue</span>
                            <h4 class="fw-bold mb-0 mt-1">&#2547; {{ number_format($todayTotal, 0) }}</h4>
                            <small class="text-muted">{{ $todayCount }} transaction{{ $todayCount !== 1 ? 's' : '' }}</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#E8F5E9;">
                            <i class="bi bi-cash-stack text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OPD --}}
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #1565C0 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-primary small fw-semibold">OPD Today</span>
                            <h4 class="fw-bold mb-0 mt-1">&#2547; {{ number_format($opdTodayTotal, 0) }}</h4>
                            <small class="text-muted">{{ $opdTodayCount }} dispense{{ $opdTodayCount !== 1 ? 's' : '' }}
                                @if($unpaidOpdCount > 0)
                                    &nbsp;·&nbsp;<span class="text-danger">{{ $unpaidOpdCount }} unpaid</span>
                                @endif
                            </small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#E3F2FD;">
                            <i class="bi bi-prescription2 text-primary fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ipd --}}
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #7B1FA2 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small fw-semibold" style="color:#7B1FA2;">Ipd To Be Billed</span>
                            <h4 class="fw-bold mb-0 mt-1">&#2547; {{ number_format($ipdToBeBilled, 0) }}</h4>
                            <small class="text-muted">{{ $ipdTodayCount }} issue{{ $ipdTodayCount !== 1 ? 's' : '' }} today · <span class="text-warning">{{ $pendingCount }} pending</span></small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#F3E5F5;">
                            <i class="bi bi-hospital fs-5" style="color:#7B1FA2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- OTC --}}
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #00897B !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small fw-semibold" style="color:#00897B;">OTC / Counter Today</span>
                            <h4 class="fw-bold mb-0 mt-1">&#2547; {{ number_format($otcTotal, 0) }}</h4>
                            <small class="text-muted">{{ $otcTodayCount }} sale{{ $otcTodayCount !== 1 ? 's' : '' }}</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#E0F2F1;">
                            <i class="bi bi-shop-window fs-5" style="color:#00897B;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Approval --}}
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="border-left:4px solid #F9A825 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-warning small fw-semibold">Pending Approval</span>
                            <h4 class="fw-bold mb-0 mt-1">{{ $pendingCount }} <i class="bi bi-hourglass-split text-warning small"></i></h4>
                            <small class="text-muted">OPD + Ipd awaiting</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#FFF8E1;">
                            <i class="bi bi-clock-history text-warning fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pharmacy.transactions') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Type</label>
                        <select name="transaction_type" class="form-select form-select-sm" id="filterType">
                            <option value="">All Types</option>
                            <option value="opd" {{ request('transaction_type') === 'opd' ? 'selected' : '' }}>OPD Dispense</option>
                            <option value="ipd" {{ request('transaction_type') === 'ipd' ? 'selected' : '' }}>Ipd Issue</option>
                            <option value="otc" {{ request('transaction_type') === 'otc' ? 'selected' : '' }}>Counter Sale (OTC)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Txn No</label>
                        <input type="text" name="transaction_no" class="form-control form-control-sm" placeholder="e.g. OPDT-00001" value="{{ request('transaction_no') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Patient / Customer</label>
                        <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="Search name..." value="{{ request('patient_name') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Pharmacist</label>
                        <select name="pharmacist_id" class="form-select form-select-sm">
                            <option value="">All Pharmacists</option>
                            @foreach($pharmacists as $pharm)
                                <option value="{{ $pharm->id }}" {{ request('pharmacist_id') == $pharm->id ? 'selected' : '' }}>{{ $pharm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Search</button>
                        <a href="{{ route('admin.pharmacy.transactions') }}" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                    </div>
                </div>
                @if(request()->hasAny(['status','payment_status']))
                <div class="row g-2 mt-1 align-items-end">
                @endif
                    <div class="col-lg-2 col-md-4 col-sm-6 {{ request()->hasAny(['status','payment_status']) ? '' : 'd-none' }} advanced-filter">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 {{ request()->hasAny(['status','payment_status']) ? '' : 'd-none' }} advanced-filter">
                        <label class="form-label small text-muted mb-1">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="paid"    {{ request('payment_status') === 'paid'    ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid"  {{ request('payment_status') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>
                    <div class="col-auto {{ request()->hasAny(['status','payment_status']) ? '' : 'd-none' }} advanced-filter">
                        <a href="{{ route('admin.pharmacy.transactions') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                    </div>
                @if(request()->hasAny(['status','payment_status']))
                </div>
                @endif
            </form>
            <div class="mt-2">
                <a href="#" id="toggleAdvanced" class="text-muted small text-decoration-none">
                    <i class="bi bi-sliders me-1"></i>
                    {{ request()->hasAny(['status','payment_status']) ? 'Hide' : 'Show' }} advanced filters
                </a>
            </div>
        </div>
    </div>

    {{-- Type Quick-Filter Tabs --}}
    <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
        @php
            $baseUrl = route('admin.pharmacy.transactions');
            $currentType = request('transaction_type', '');
            $baseFilters = request()->only(['date_from','date_to','transaction_no','patient_name','pharmacist_id','status','payment_status']);
            $qsAll  = http_build_query([...$baseFilters, 'transaction_type' => '']);
            $qsOpd  = http_build_query([...$baseFilters, 'transaction_type' => 'opd']);
            $qsIpd  = http_build_query([...$baseFilters, 'transaction_type' => 'ipd']);
            $qsOtc  = http_build_query([...$baseFilters, 'transaction_type' => 'otc']);
            $allCount = $tabAllCount;
            $opdCount = $tabOpdCount;
            $ipdCount = $tabIpdCount;
            $otcCount = $tabOtcCount;
        @endphp
        <a href="{{ $baseUrl . '?' . $qsAll }}"
           class="btn btn-sm {{ $currentType === '' ? 'btn-dark' : 'btn-outline-secondary' }}">
            All &nbsp;<span class="badge bg-secondary bg-opacity-25 text-dark">{{ $allCount }}</span>
        </a>
        <a href="{{ $baseUrl . '?' . $qsOpd }}"
           class="btn btn-sm {{ $currentType === 'opd' ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="bi bi-prescription2 me-1"></i>OPD &nbsp;<span class="badge bg-primary bg-opacity-25 text-primary">{{ $opdCount }}</span>
        </a>
        <a href="{{ $baseUrl . '?' . $qsIpd }}"
           class="btn btn-sm {{ $currentType === 'ipd' ? 'btn-info' : 'btn-outline-info' }}">
            <i class="bi bi-hospital me-1"></i>Ipd &nbsp;<span class="badge bg-info bg-opacity-25 text-info">{{ $ipdCount }}</span>
        </a>
        <a href="{{ $baseUrl . '?' . $qsOtc }}"
           class="btn btn-sm {{ $currentType === 'otc' ? 'btn-success' : 'btn-outline-success' }}">
            <i class="bi bi-shop-window me-1"></i>OTC &nbsp;<span class="badge bg-success bg-opacity-25 text-success">{{ $otcCount }}</span>
        </a>

        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-light rounded-circle" style="width:32px;height:32px;padding:0;" title="Refresh" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise small"></i>
            </button>
            <a href="{{ route('admin.pharmacy.transactions.export') . '?' . http_build_query(request()->only(['transaction_type','date_from','date_to','transaction_no','patient_name','status','payment_status','pharmacist_id'])) }}"
               class="btn btn-sm btn-outline-primary" title="Export CSV">
                <i class="bi bi-download small me-1"></i> Export
            </a>
            <a href="{{ route('admin.pharmacy.transactions.print') . '?' . http_build_query(request()->only(['transaction_type','date_from','date_to','transaction_no','patient_name','status','payment_status','pharmacist_id'])) }}"
               target="_blank"
               class="btn btn-sm btn-outline-success" title="Print">
                <i class="bi bi-printer small me-1"></i> Print
            </a>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-semibold">
                        @if($currentType === 'opd') OPD Dispense
                        @elseif($currentType === 'ipd') Ipd Issue
                        @elseif($currentType === 'otc') Counter Sale / OTC
                        @else All Transactions
                        @endif
                    </h6>
                    <small class="text-muted">{{ $transactions->count() }} record{{ $transactions->count() !== 1 ? 's' : '' }} found</small>
                </div>
                @if($transactions->count() > 0)
                <small class="text-muted">
                    Total: <strong>&#2547; {{ number_format($transactions->sum('total_amount'), 2) }}</strong>
                </small>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt_basic">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">TXN NO</th>
                        <th class="py-3 text-muted small fw-semibold">TYPE</th>
                        <th class="py-3 text-muted small fw-semibold">PATIENT / CUSTOMER</th>
                        <th class="py-3 text-muted small fw-semibold">DETAILS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ITEMS</th>
                        <th class="py-3 text-muted small fw-semibold">AMOUNT (&#2547;)</th>
                        <th class="py-3 text-muted small fw-semibold">PAYMENT</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold">PHARMACIST</th>
                        <th class="py-3 text-muted small fw-semibold">DATE</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarColors = ['#4361EE','#F57C00','#7B1FA2','#D32F2F','#388E3C','#1565C0','#00897B','#C62828','#5E35B1','#EF6C00'];
                    @endphp
                    @forelse($transactions as $t)
                    @php
                        $displayName = $t->transaction_type === 'otc'
                            ? ($t->customer_name ?? 'Walk-in')
                            : ($t->patient->patient_name ?? '—');
                        $initial     = strtoupper(substr($displayName, 0, 1));
                        $colorIdx    = ($t->patient_id ?? $t->id) % count($avatarColors);
                        $stClass     = match($t->status) { 'completed' => 'success', 'approved' => 'info', 'pending' => 'warning', default => 'secondary' };
                        $payClass    = match($t->payment_status ?? '') { 'paid' => 'success', 'partial' => 'warning', 'unpaid' => 'danger', default => 'secondary' };
                    @endphp
                    <tr>
                        {{-- Txn No --}}
                        <td class="px-3 py-3">
                            <a href="{{ route('admin.pharmacy.transactions.show', $t->id) }}"
                               class="text-primary fw-semibold text-decoration-none">
                                {{ $t->transaction_no }}
                            </a>
                        </td>

                        {{-- Type --}}
                        <td class="py-3">
                            @if($t->transaction_type === 'opd')
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><i class="bi bi-prescription2 me-1"></i>OPD</span>
                            @elseif($t->transaction_type === 'ipd')
                                <span class="badge bg-info bg-opacity-10 text-info rounded-pill"><i class="bi bi-hospital me-1"></i>Ipd</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i class="bi bi-shop-window me-1"></i>OTC</span>
                            @endif
                        </td>

                        {{-- Patient / Customer --}}
                        <td class="py-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                      style="width:32px;height:32px;background:{{ $avatarColors[$colorIdx] }};font-size:0.75rem;">
                                    {{ $initial }}
                                </span>
                                <div>
                                    <div class="fw-medium small">{{ $displayName }}</div>
                                    @if($t->transaction_type === 'otc' && $t->customer_phone)
                                        <small class="text-muted">{{ $t->customer_phone }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Context details --}}
                        <td class="py-3 small text-muted">
                            @if($t->transaction_type === 'opd')
                                <div>Case: <span class="text-dark">{{ $t->opdPatient->case_id ?? '—' }}</span></div>
                                @if($t->prescription)
                                    <div>Rx: {{ $t->prescription->prescription_no }}</div>
                                @endif
                            @elseif($t->transaction_type === 'ipd')
                                <div>Ward/Bed: <span class="text-dark">{{ $t->ward_bed ?? '—' }}</span></div>
                                <div>Req: {{ $t->requisition_no ?? '—' }}</div>
                            @else
                                <span class="text-muted fst-italic">Walk-in sale</span>
                                @if($t->payment_method)
                                    <div>{{ ucfirst(str_replace('_', ' ', $t->payment_method)) }}</div>
                                @endif
                            @endif
                        </td>

                        {{-- Drug count --}}
                        <td class="py-3 text-center fw-medium">{{ $t->drug_count }}</td>

                        {{-- Amount --}}
                        <td class="py-3 fw-semibold">
                            {{ number_format($t->total_amount, 2) }}
                            @if($t->discount_amount > 0)
                                <br><small class="text-danger">-{{ number_format($t->discount_amount, 2) }} disc.</small>
                            @endif
                        </td>

                        {{-- Payment --}}
                        <td class="py-3">
                            @if($t->payment_status)
                                <span class="badge bg-{{ $payClass }} bg-opacity-10 text-{{ $payClass }} rounded-pill">
                                    {{ ucfirst($t->payment_status) }}
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">
                                    Running Bill
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="py-3">
                            <span class="badge bg-{{ $stClass }} bg-opacity-10 text-{{ $stClass }} rounded-pill">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>

                        {{-- Pharmacist --}}
                        <td class="py-3 small">{{ $t->pharmacist->name ?? '—' }}</td>

                        {{-- Date --}}
                        <td class="py-3 small">
                            {{ $t->created_at->format('d/m/Y') }}
                            <br><span class="text-muted">{{ $t->created_at->format('h:i A') }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.pharmacy.transactions.show', $t->id) }}"
                                   class="btn btn-sm btn-light rounded-circle"
                                   style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                   title="View Details">
                                    <i class="bi bi-eye small"></i>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle dropdown-toggle-no-caret"
                                            style="width:30px;height:30px;padding:0;"
                                            data-bs-toggle="dropdown" title="More actions">
                                        <i class="bi bi-three-dots-vertical small"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:180px;">
                                        @if($t->status === 'pending')
                                            <li>
                                                <form method="POST" action="{{ route('admin.pharmacy.transactions.approve', $t->id) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success"
                                                            onclick="return confirm('Approve {{ $t->transaction_no }}?')">
                                                        <i class="bi bi-check-circle me-2"></i> Approve
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.pharmacy.returns.create') }}?txn={{ $t->transaction_no }}">
                                                <i class="bi bi-arrow-return-left me-2 text-warning"></i> Process Return
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.pharmacy.transactions.show', $t->id) }}">
                                                <i class="bi bi-receipt me-2"></i> View Details
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                   

                    @endforeach
                </tbody>
                @if($transactions->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-end fw-semibold small text-muted">Totals:</td>
                        <td class="py-2 fw-bold text-primary">&#2547; {{ number_format($transactions->sum('total_amount'), 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

@if(session('show_create_modal'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnNewTransaction')?.click();
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Advanced filters toggle
    const toggle   = document.getElementById('toggleAdvanced');
    const fields   = document.querySelectorAll('.advanced-filter');
    let visible    = {{ request()->hasAny(['status','payment_status']) ? 'true' : 'false' }};

    toggle?.addEventListener('click', function (e) {
        e.preventDefault();
        visible = !visible;
        fields.forEach(f => f.classList.toggle('d-none', !visible));
        this.innerHTML = `<i class="bi bi-sliders me-1"></i>${visible ? 'Hide' : 'Show'} advanced filters`;
    });
});
</script>
@endsection
