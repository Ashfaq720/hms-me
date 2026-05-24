@extends('backend.layouts.master')

@section('title', 'Pharmacy Inventory')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Inventory</h4>
            <p class="text-muted mb-0 small">Real-time stock overview across all stores, batches, and expiry.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-currency-dollar" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Total Stock Value</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($totalStockValue, 2) }}</h4>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#EEF2FF;">
                            <i class="bi bi-capsule text-primary"></i>
                        </div>
                        <span class="text-muted small">Total Drugs</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $totalDrugs }}</h4>
                </div>
                <div style="height:3px;background:var(--primary);"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-exclamation-triangle" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Low Stock Items</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-warning">{{ $lowStockCount }}</h4>
                    <small class="text-muted">Below Reorder Level</small>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFEBEE;">
                            <i class="bi bi-calendar-x" style="color:#D32F2F;"></i>
                        </div>
                        <span class="text-muted small">Near Expiry</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">{{ $nearExpiryCount }}</h4>
                    <small class="text-muted">Within 90 Days</small>
                </div>
                <div style="height:3px;background:#D32F2F;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FCE4EC;">
                            <i class="bi bi-x-circle" style="color:#C62828;"></i>
                        </div>
                        <span class="text-muted small">Expired</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">{{ $expiredCount }}</h4>
                </div>
                <div style="height:3px;background:#C62828;"></div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#F3E5F5;">
                            <i class="bi bi-bag-x" style="color:#7B1FA2;"></i>
                        </div>
                        <span class="text-muted small">Out of Stock</span>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:#7B1FA2;">{{ $outOfStockCount }}</h4>
                </div>
                <div style="height:3px;background:#7B1FA2;"></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pharmacy.inventory') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Store</label>
                        <select name="store" class="form-select form-select-sm">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store }}" {{ request('store') === $store ? 'selected' : '' }}>{{ $store }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Drug</label>
                        <input type="text" name="medicine_name" class="form-control form-control-sm" placeholder="Search drug name..." value="{{ request('medicine_name') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Batch</label>
                        <input type="text" name="batch_no" class="form-control form-control-sm" placeholder="Search batch no..." value="{{ request('batch_no') }}">
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Expiry Status</label>
                        <select name="expiry_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="expired" {{ request('expiry_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="near" {{ request('expiry_status') === 'near' ? 'selected' : '' }}>Near Expiry (90 days)</option>
                            <option value="valid" {{ request('expiry_status') === 'valid' ? 'selected' : '' }}>Valid</option>
                        </select>
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Stock Level</label>
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Below Reorder</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-lg col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-auto col-md-4 col-sm-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                            <a href="{{ route('admin.pharmacy.inventory') }}" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Stock Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0 fw-semibold">Stock Overview</h6>
                    <small class="text-muted">{{ $batches->count() }} batch{{ $batches->count() !== 1 ? 'es' : '' }} found</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="#"
                       data-ajax-popup="true"
                       data-url="{{ route('admin.pharmacy.inventory.purchase.create') }}"
                       data-title="Record Purchase / Receive Stock"
                       data-size="xl"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Add Purchase
                    </a>
                    <a href="{{ route('admin.pharmacy.inventory.export') }}?{{ http_build_query(request()->only(['medicine_name','batch_no','store','category_id','stock_status','expiry_status'])) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                    <a href="{{ route('admin.pharmacy.inventory.print') }}?{{ http_build_query(request()->only(['medicine_name','batch_no','store','category_id','stock_status','expiry_status'])) }}"
                       target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt_basic">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">DRUG</th>
                        <th class="py-3 text-muted small fw-semibold">BATCH #</th>
                        <th class="py-3 text-muted small fw-semibold">EXPIRY DATE</th>
                        <th class="py-3 text-muted small fw-semibold">STORE</th>
                        <th class="py-3 text-muted small fw-semibold">AVAILABLE</th>
                        <th class="py-3 text-muted small fw-semibold">REORDER LVL</th>
                        <th class="py-3 text-muted small fw-semibold">UNIT COST</th>
                        <th class="py-3 text-muted small fw-semibold">STOCK VALUE</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        @php
                            $medicine = $batch->medicine;
                            $stockValue = $batch->quantity * $batch->purchase_price;
                            $reorderLevel = (int) ($medicine->reorder_level ?? 0);
                            $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();
                            $isNearExpiry = $batch->expiry_date && !$isExpired && $batch->expiry_date->diffInDays(now()) <= 90;
                            $isOutOfStock = $batch->quantity <= 0;
                            $isLowStock = !$isOutOfStock && $reorderLevel > 0 && $batch->quantity <= $reorderLevel;
                        @endphp
                        <tr>
                            <td class="px-3 py-3">
                                <div class="fw-medium">{{ $medicine->medicine_name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ $medicine->medicalGroup->name ?? $medicine->category->name ?? '' }}</div>
                            </td>
                            <td class="py-3 small font-monospace">{{ $batch->batch_no }}</td>
                            <td class="py-3 small">
                                @if($batch->expiry_date)
                                    <span class="{{ $isExpired || $isNearExpiry ? 'text-danger fw-medium' : '' }}">
                                        {{ $batch->expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($isExpired)
                                        <div class="text-danger" style="font-size:0.7rem;">Expired</div>
                                    @elseif($isNearExpiry)
                                        <div class="text-warning" style="font-size:0.7rem;">{{ $batch->expiry_date->diffInDays(now()) }} days left</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 small">{{ $batch->store }}</td>
                            <td class="py-3 fw-medium {{ $isOutOfStock ? 'text-danger' : ($isLowStock ? 'text-warning' : '') }}">
                                {{ number_format($batch->quantity) }}
                            </td>
                            <td class="py-3 small">{{ $reorderLevel > 0 ? number_format($reorderLevel) : '—' }}</td>
                            <td class="py-3 small">{{ number_format($batch->purchase_price, 2) }}</td>
                            <td class="py-3 fw-medium">{{ number_format($stockValue, 2) }}</td>
                            <td class="py-3">
                                @if($isExpired)
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Expired</span>
                                @elseif($isOutOfStock)
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                                @elseif($isNearExpiry)
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Near Expiry</span>
                                @elseif($isLowStock)
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Normal</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <a data-url="{{ route('admin.pharmacy.inventory.show', $batch->id) }}"
                                   data-ajax-popup="true"
                                   data-title="Batch Details — {{ $batch->batch_no }}"
                                   data-size="lg"
                                   class="btn btn-sm btn-light rounded-circle"
                                   style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                   title="View Details">
                                    <i class="bi bi-eye small"></i>
                                </a>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
