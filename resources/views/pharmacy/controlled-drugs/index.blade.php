@extends('backend.layouts.master')

@section('title', 'Controlled Drugs')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Controlled Drugs</h4>
            <p class="text-muted mb-0 small">Register and track Schedule II–V controlled substances.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#EEF2FF;">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                        </div>
                        <span class="text-muted small">Total Entries</span>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalCount }}</h3>
                </div>
                <div style="height:3px;background:var(--primary);"></div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-check-circle" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Available</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">{{ $activeCount }}</h3>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                             style="width:36px;height:36px;background:#F3E5F5;">
                            <i class="bi bi-shield-exclamation" style="color:#7B1FA2;"></i>
                        </div>
                        <span class="text-muted small">Schedule II–V</span>
                    </div>
                    <h3 class="fw-bold mb-0" style="color:#7B1FA2;">{{ $scheduleIIVCount }}</h3>
                </div>
                <div style="height:3px;background:#7B1FA2;"></div>
            </div>
        </div>
    </div>

    {{-- Register Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0 fw-semibold">Controlled Drugs Register</h6>
                    <small class="text-muted">{{ $entries->total() }} entr{{ $entries->total() !== 1 ? 'ies' : 'y' }} found</small>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    {{-- Search --}}
                    <form method="GET" action="{{ route('admin.pharmacy.controlled-drugs') }}" class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Search by drug, DEA, or LOT..."
                               value="{{ request('search') }}" style="min-width:240px;">
                        <select name="schedule" class="form-select form-select-sm" style="width:140px;">
                            <option value="">All Schedules</option>
                            @foreach(['Schedule II','Schedule III','Schedule IV','Schedule V'] as $sch)
                                <option value="{{ $sch }}" {{ request('schedule') === $sch ? 'selected' : '' }}>{{ $sch }}</option>
                            @endforeach
                        </select>
                        <select name="action_type" class="form-select form-select-sm" style="width:130px;">
                            <option value="">All Actions</option>
                            <option value="received" {{ request('action_type') === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="removed"  {{ request('action_type') === 'removed'  ? 'selected' : '' }}>Removed</option>
                        </select>
                        <select name="inventory_status" class="form-select form-select-sm" style="width:140px;">
                            <option value="">All Status</option>
                            <option value="available"   {{ request('inventory_status') === 'available'   ? 'selected' : '' }}>Available</option>
                            <option value="low_stock"   {{ request('inventory_status') === 'low_stock'   ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock"{{ request('inventory_status') === 'out_of_stock'? 'selected' : '' }}>Out of Stock</option>
                        </select>
                        <input type="date" name="date_from" class="form-control form-control-sm" style="width:140px;" value="{{ request('date_from') }}" title="From Date">
                        <input type="date" name="date_to"   class="form-control form-control-sm" style="width:140px;" value="{{ request('date_to') }}"   title="To Date">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Search</button>
                        <a href="{{ route('admin.pharmacy.controlled-drugs') }}" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </form>
                    <a href="{{ route('admin.pharmacy.controlled-drugs.export') }}?{{ http_build_query(request()->only(['search','schedule','action_type','inventory_status','date_from','date_to'])) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                    {{-- Add Entry --}}
                    <a data-url="{{ route('admin.pharmacy.controlled-drugs.create') }}"
                       data-ajax-popup="true"
                       data-title="Add Controlled Drug Entry"
                       data-size="lg"
                       class="btn btn-warning btn-sm fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> + Add Entry
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">ENTRY DATE/TIME</th>
                        <th class="py-3 text-muted small fw-semibold">DR. NAME</th>
                        <th class="py-3 text-muted small fw-semibold">GENERIC NAME</th>
                        <th class="py-3 text-muted small fw-semibold">DR. DEA</th>
                        <th class="py-3 text-muted small fw-semibold">LOT NUMBER</th>
                        <th class="py-3 text-muted small fw-semibold">SCHEDULE</th>
                        <th class="py-3 text-muted small fw-semibold">EXPIRATION DATE</th>
                        <th class="py-3 text-muted small fw-semibold">NDC CODE</th>
                        <th class="py-3 text-muted small fw-semibold">RECEIVED/REMOVED</th>
                        <th class="py-3 text-muted small fw-semibold">INVENTORY STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $isExpired = $entry->expiration_date && $entry->expiration_date->isPast();
                        @endphp
                        <tr class="{{ $isExpired ? 'table-danger bg-opacity-25' : '' }}">
                            <td class="px-3 py-3 small">
                                <div>{{ $entry->entry_date->format('d/m/Y') }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">{{ $entry->entry_date->format('h:i A') }}</div>
                            </td>
                            <td class="py-3 fw-medium small">{{ $entry->doctor_name }}</td>
                            <td class="py-3 small">{{ $entry->generic_name }}</td>
                            <td class="py-3 small font-monospace text-muted">{{ $entry->dea_number ?? '—' }}</td>
                            <td class="py-3 fw-bold small font-monospace">{{ $entry->lot_number }}</td>
                            <td class="py-3">
                                <span class="text-primary fw-medium small">{{ $entry->schedule }}</span>
                            </td>
                            <td class="py-3 small {{ $isExpired ? 'text-danger fw-medium' : '' }}">
                                {{ $entry->expiration_date?->format('m/d/Y') ?? '—' }}
                                @if($isExpired)
                                    <div style="font-size:0.68rem;" class="text-danger">Expired</div>
                                @endif
                            </td>
                            <td class="py-3 small font-monospace text-muted">{{ $entry->ndc_code ?? '—' }}</td>
                            <td class="py-3 small">
                                @if($entry->action_type === 'received')
                                    <span class="text-success fw-medium">Received {{ $entry->quantity }}{{ $entry->unit }}</span>
                                @else
                                    <span class="text-danger fw-medium">Removed {{ $entry->quantity }}{{ $entry->unit }}</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @switch($entry->inventory_status)
                                    @case('available')
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Available</span>
                                        @break
                                    @case('low_stock')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Low Stock</span>
                                        @break
                                    @case('out_of_stock')
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Out of Stock</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="py-3 text-center">
                                <a data-url="{{ route('admin.pharmacy.controlled-drugs.show', $entry->id) }}"
                                   data-ajax-popup="true"
                                   data-title="Entry Details — {{ $entry->entry_no }}"
                                   data-size="lg"
                                   class="btn btn-sm btn-success text-white px-3"
                                   style="font-size:0.78rem;"
                                   title="View Details">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-exclamation fs-1 d-block mb-2 opacity-25"></i>
                                <p class="mb-1">No controlled drug entries found.</p>
                                <small>Add an entry using the "+ Add Entry" button above.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($entries->hasPages())
            <div class="card-body border-top py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <small class="text-muted">
                        Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }} entries
                    </small>
                    {{ $entries->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
