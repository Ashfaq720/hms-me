@extends('backend.layouts.master')

@section('title', 'Pharmacy Control Center')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Pharmacy Control Center</h4>
            <p class="text-muted mb-0 small">All Pharmacy Operations, Transactions, Inventory, and History — Unified In One Place.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> <span id="pharmacy-clock">{{ now()->format('h:i A') }}</span></span>
        </div>
    </div>

    {{-- Overview Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#EEF2FF;">
                            <i class="bi bi-graph-up-arrow text-primary"></i>
                        </div>
                        <span class="text-muted small">Total Sales</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalSales, 2) }}</h4>
                    <small class="text-muted">OPD + Ipd completed</small>
                </div>
                <div style="height:3px;background:var(--primary);"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-currency-dollar" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Total Revenue</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark {{ $totalRevenue >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totalRevenue, 2) }}</h4>
                    <small class="text-muted">Sales - Purchases</small>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-cart-check" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Total Purchases</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalPurchases, 2) }}</h4>
                    <small class="text-muted">From all batches</small>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFEBEE;">
                            <i class="bi bi-clock-history" style="color:#D32F2F;"></i>
                        </div>
                        <span class="text-muted small">Pending Amount</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalOpdPending + $totalIpdPending, 2) }}</h4>
                    <small class="text-warning">Awaiting completion</small>
                </div>
                <div style="height:3px;background:#D32F2F;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E3F2FD;">
                            <i class="bi bi-box-seam" style="color:#1565C0;"></i>
                        </div>
                        <span class="text-muted small">Total Items</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $medicines->count() }} <span class="fs-6 fw-normal text-muted">Items</span></h4>
                    <small class="text-muted">In inventory</small>
                </div>
                <div style="height:3px;background:#1565C0;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF8E1;">
                            <i class="bi bi-exclamation-triangle" style="color:#F9A825;"></i>
                        </div>
                        <span class="text-muted small">Low Stock</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $medicines->where('available_qty', '<=', 10)->count() }}</h4>
                    <small class="text-warning">Items need reorder</small>
                </div>
                <div style="height:3px;background:#F9A825;"></div>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="pharmacyTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold border-0 px-3 pb-3" id="transactions-tab" data-bs-toggle="tab" href="#transactions" role="tab">
                        <i class="bi bi-arrow-left-right me-1"></i> All Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold border-0 px-3 pb-3" id="stock-tab" data-bs-toggle="tab" href="#stock" role="tab">
                        <i class="bi bi-clipboard2-pulse me-1"></i> Drug Master
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold border-0 px-3 pb-3" id="gps-tab" data-bs-toggle="tab" href="#gps" role="tab">
                        <i class="bi bi-pin-map me-1"></i> GPS Tracking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold border-0 px-3 pb-3" id="reports-tab" data-bs-toggle="tab" href="#reports" role="tab">
                        <i class="bi bi-bar-chart-line me-1"></i> Reports and Analytics
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            {{-- All Transactions Tab --}}
            <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                {{-- Transactions Header --}}
                <div class="card-body border-bottom py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0 fw-semibold">Pharmacy Transactions History (All Activities)</h6>
                            <small class="text-muted">{{ $transactions->count() }} transactions found</small>
                        </div>
                        <a data-size="xl"
                           class="btn btn-primary btn-sm"
                           data-url="{{ route('admin.pharmacy.transactions.create') }}"
                           data-ajax-popup="true"
                           data-title="Add Transaction">
                            <i class="bi bi-plus-lg me-1"></i> Add Transaction
                        </a>
                    </div>
                </div>

                {{-- Transactions Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dt_transactions">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3 text-muted small fw-semibold">DATE/TIME</th>
                                <th class="py-3 text-muted small fw-semibold">TYPE</th>
                                <th class="py-3 text-muted small fw-semibold">REF #</th>
                                <th class="py-3 text-muted small fw-semibold">PATIENT</th>
                                <th class="py-3 text-muted small fw-semibold">QTY</th>
                                <th class="py-3 text-muted small fw-semibold">AMOUNT</th>
                                <th class="py-3 text-muted small fw-semibold">DEPARTMENT</th>
                                <th class="py-3 text-muted small fw-semibold">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="fw-medium small">{{ \Carbon\Carbon::parse($txn['date'])->format('d/m/Y') }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($txn['date'])->format('h:i A') }}</div>
                                    </td>
                                    <td class="py-3">
                                        @if($txn['type'] === 'PURCHASE')
                                            <span class="badge rounded-pill" style="background:#E8F5E9;color:#2E7D32;">PURCHASE</span>
                                        @elseif($txn['type'] === 'OPD SALE')
                                            <span class="badge rounded-pill" style="background:#E3F2FD;color:#1565C0;">OPD SALE</span>
                                        @elseif($txn['type'] === 'Ipd ISSUE')
                                            <span class="badge rounded-pill" style="background:#F3E5F5;color:#7B1FA2;">Ipd ISSUE</span>
                                        @endif
                                    </td>
                                    <td class="py-3 small fw-medium">{{ $txn['ref'] }}</td>
                                    <td class="py-3 small">{{ $txn['patient'] }}</td>
                                    <td class="py-3 small fw-medium">{{ number_format($txn['drug_count']) }}</td>
                                    <td class="py-3 small fw-medium">{{ number_format($txn['amount'], 2) }}</td>
                                    <td class="py-3 small">{{ $txn['department'] }}</td>
                                    <td class="py-3">
                                        @if($txn['status'] === 'completed')
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success">Completed</span>
                                        @elseif($txn['status'] === 'pending')
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning">Pending</span>
                                        @elseif($txn['status'] === 'cancelled')
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger">Cancelled</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($txn['status']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Drug Master Tab --}}
            <div class="tab-pane fade" id="stock" role="tabpanel">
                <div class="card-body border-bottom py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0 fw-semibold">Medicines Stock</h6>
                            <small class="text-muted">{{ $medicines->count() }} medicines in inventory</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-upload me-1"></i> Import
                            </a>
                            <a data-size="xl"
                               class="btn btn-primary btn-sm"
                               data-url="{{ route('admin.medicines.create') }}"
                               data-ajax-popup="true"
                               data-title="Add Medicine Details">
                                <i class="bi bi-plus-lg me-1"></i> Add Medicine
                            </a>
                        </div>
                    </div>
                </div>

                @include('pharmacy.medicine.validation-errors')

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dt_basic">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input">
                                </th>
                                <th class="py-3 text-muted small fw-semibold">MEDICINE NAME</th>
                                <th class="py-3 text-muted small fw-semibold">COMPANY</th>
                                <th class="py-3 text-muted small fw-semibold">COMPOSITION</th>
                                <th class="py-3 text-muted small fw-semibold">CATEGORY</th>
                                <th class="py-3 text-muted small fw-semibold">GROUP</th>
                                <th class="py-3 text-muted small fw-semibold">UNIT</th>
                                <th class="py-3 text-muted small fw-semibold">AVAILABLE QTY</th>
                                <th class="py-3 text-muted small fw-semibold text-center" style="width: 140px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicines as $medicine)
                                <tr>
                                    <td class="px-3 py-3">
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td class="py-3 fw-medium">{{ $medicine->medicine_name }}</td>
                                    <td class="py-3 small">{{ $medicine->company->name ?? '—' }}</td>
                                    <td class="py-3 small">{{ $medicine->medicine_composition ?? '—' }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ $medicine->category->name ?? '—' }}</span>
                                    </td>
                                    <td class="py-3 small">{{ $medicine->medicalGroup->name ?? '—' }}</td>
                                    <td class="py-3 small">{{ $medicine->unit->name ?? '—' }}</td>
                                    <td class="py-3">
                                        @if(($medicine->available_qty ?? 0) <= 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">{{ $medicine->available_qty ?? 0 }} — Out of Stock</span>
                                        @elseif(($medicine->available_qty ?? 0) <= 10)
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">{{ $medicine->available_qty }} — Low</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">{{ $medicine->available_qty }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a data-size="xl"
                                               class="btn btn-sm btn-light rounded-circle"
                                               style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;"
                                               data-url="{{ route('admin.medicines.edit', $medicine->id) }}"
                                               data-ajax-popup="true"
                                               data-title="Edit Medicine Details"
                                               title="Edit">
                                                <i class="bi bi-pencil small"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('admin.medicines.destroy', $medicine->id) }}"
                                                  onsubmit="return confirm('Delete this medicine?')"
                                                  class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light rounded-circle" style="width:30px;height:30px;padding:0;" title="Delete">
                                                    <i class="bi bi-trash small text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-capsule fs-1 d-block mb-2 opacity-25"></i>
                                        No medicines found. Add your first medicine to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- GPS Tracking Tab --}}
            <div class="tab-pane fade" id="gps" role="tabpanel">
                <div class="card-body text-center py-5">
                    <i class="bi bi-pin-map fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <h6 class="text-muted">GPS Tracking</h6>
                    <p class="text-muted small">Delivery tracking and route optimization coming soon.</p>
                </div>
            </div>

            {{-- Reports Tab --}}
            <div class="tab-pane fade" id="reports" role="tabpanel">
                <div class="card-body text-center py-5">
                    <i class="bi bi-bar-chart-line fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <h6 class="text-muted">Reports & Analytics</h6>
                    <p class="text-muted small">Comprehensive pharmacy analytics and reporting coming soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Live Clock Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clockEl = document.getElementById('pharmacy-clock');
    if (clockEl) {
        setInterval(function() {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }, 60000);
    }
});
</script>

@if(session('modal_type') === 'create')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[data-url="{{ route('admin.medicines.create') }}"]')?.click();
});
</script>
@endif

@if(session('modal_type') === 'edit' && session('edit_id'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[data-url="{{ route('admin.medicines.edit', session('edit_id')) }}"]')?.click();
});
</script>
@endif

@if(session('modal_type') === 'transaction')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('[data-url="{{ route('admin.pharmacy.transactions.create') }}"]')?.click();
});
</script>
@endif
@endsection
