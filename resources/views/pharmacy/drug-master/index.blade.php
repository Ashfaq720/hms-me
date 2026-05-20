@extends('backend.layouts.master')

@section('title', 'Drug Master')

@section('content')
<div class="container-fluid px-3 px-md-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Drug Master</h4>
            <p class="text-muted mb-0 small">Manage all drug definitions used by inventory, dispense, pricing, and compliance.</p>
        </div>
        <div class="text-muted small d-none d-md-block">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->format('l, M d, Y') }}
            <span class="ms-3"><i class="bi bi-clock me-1"></i> {{ now()->format('h:i A') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#EEF2FF;">
                            <i class="bi bi-capsule text-primary"></i>
                        </div>
                        <span class="text-muted small">Total Drugs</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $medicines->count() }}</h4>
                </div>
                <div style="height:3px;background:var(--primary);"></div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#FFF3E0;">
                            <i class="bi bi-tags" style="color:#F57C00;"></i>
                        </div>
                        <span class="text-muted small">Categories</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $categories->count() }}</h4>
                </div>
                <div style="height:3px;background:#F57C00;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E8F5E9;">
                            <i class="bi bi-diagram-3" style="color:#388E3C;"></i>
                        </div>
                        <span class="text-muted small">Generic Names</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $generics->count() }}</h4>
                </div>
                <div style="height:3px;background:#388E3C;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#E3F2FD;">
                            <i class="bi bi-box-seam" style="color:#1565C0;"></i>
                        </div>
                        <span class="text-muted small">All Drugs</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-primary">{{ $medicines->count() }} <span class="fs-6 fw-normal text-muted">Items</span></h4>
                </div>
                <div style="height:3px;background:#1565C0;"></div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;background:#F3E5F5;">
                            <i class="bi bi-toggle-on" style="color:#7B1FA2;"></i>
                        </div>
                        <span class="text-muted small">Status</span>
                    </div>
                    <h4 class="fw-bold mb-0">
                        <span class="text-success">{{ $activeCount }}</span>
                        <span class="text-muted fw-normal fs-6">/</span>
                        <span class="text-danger">{{ $inactiveCount }}</span>
                    </h4>
                </div>
                <div style="height:3px;background:#7B1FA2;"></div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3">
            <h6 class="fw-semibold mb-3">Drug Master List</h6>
            <form method="GET" action="{{ route('admin.pharmacy.drug-master') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Drug Name</label>
                        <input type="text" name="medicine_name" class="form-control form-control-sm" placeholder="Drug Name" value="{{ request('medicine_name') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Generic Name</label>
                        <input type="text" name="generic_name" class="form-control form-control-sm" placeholder="Generic Name" value="{{ request('generic_name') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Dosage Form</label>
                        <select name="group_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search me-1"></i> Search</button>
                            <a href="{{ route('admin.pharmacy.drug-master') }}" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Drug Master Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0 fw-semibold">Drug Master List</h6>
                    <small class="text-muted">{{ $medicines->count() }} drug{{ $medicines->count() !== 1 ? 's' : '' }} found</small>
                </div>
                <div class="d-flex gap-2">
                    <a data-size="xl"
                       class="btn btn-primary btn-sm"
                       data-url="{{ route('admin.medicines.create') }}"
                       data-ajax-popup="true"
                       data-title="Add Medicine Details">
                        <i class="bi bi-plus-lg me-1"></i> Add Drug
                    </a>
                    <button class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i> Export</button>
                </div>
            </div>
        </div>

        @include('pharmacy.medicine.validation-errors')

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dt_basic">
                <thead class="table-light">
                    <tr>
                        <th class="px-3 py-3 text-muted small fw-semibold">DRUG CODE</th>
                        <th class="py-3 text-muted small fw-semibold">DRUG NAME</th>
                        <th class="py-3 text-muted small fw-semibold">GENERIC / COMPOSITION</th>
                        <th class="py-3 text-muted small fw-semibold">CATEGORY</th>
                        <th class="py-3 text-muted small fw-semibold">PACKING</th>
                        <th class="py-3 text-muted small fw-semibold">GROUP</th>
                        <th class="py-3 text-muted small fw-semibold">UNIT</th>
                        <th class="py-3 text-muted small fw-semibold">AVAILABLE QTY</th>
                        <th class="py-3 text-muted small fw-semibold">STATUS</th>
                        <th class="py-3 text-muted small fw-semibold text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        <tr>
                            <td class="px-3 py-3">
                                <span class="text-primary fw-medium">DRG-{{ str_pad($medicine->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if($medicine->photo)
                                        <img src="{{ asset('uploads/pharmacy/medicines/' . $medicine->photo) }}"
                                             alt="{{ $medicine->medicine_name }}"
                                             class="rounded"
                                             style="width:32px;height:32px;object-fit:cover;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center"
                                             style="width:32px;height:32px;background:#EEF2FF;flex-shrink:0;">
                                            <i class="bi bi-capsule text-primary small"></i>
                                        </div>
                                    @endif
                                    <span class="fw-medium">{{ $medicine->medicine_name }}</span>
                                </div>
                            </td>
                            <td class="py-3 small">{{ $medicine->medicine_composition ?? '—' }}</td>
                            <td class="py-3">
                                @if($medicine->category)
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">{{ $medicine->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3 small">{{ $medicine->box_packing ?? '—' }}</td>
                            <td class="py-3 small">{{ $medicine->medicalGroup->name ?? '—' }}</td>
                            <td class="py-3 small">{{ $medicine->unit->name ?? '—' }}</td>
                            <td class="py-3">
                                @if(($medicine->available_qty ?? 0) <= 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">{{ $medicine->available_qty ?? 0 }} — Out</span>
                                @elseif($medicine->reorder_level && $medicine->available_qty <= (int) $medicine->reorder_level)
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">{{ $medicine->available_qty }} — Low</span>
                                @else
                                    <span class="fw-medium">{{ $medicine->available_qty }}</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($medicine->status)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Active</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-inline-flex gap-1">
                                    <a data-size="xl"
                                       class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center"
                                       style="width:30px;height:30px;padding:0;"
                                       data-url="{{ route('admin.medicines.edit', $medicine->id) }}"
                                       data-ajax-popup="true"
                                       data-title="Edit Medicine Details"
                                       title="Edit">
                                        <i class="bi bi-pencil small text-warning"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.medicines.destroy', $medicine->id) }}"
                                          onsubmit="return confirm('Delete this medicine?')"
                                          class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center"
                                                style="width:30px;height:30px;padding:0;"
                                                title="Delete">
                                            <i class="bi bi-trash small text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

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
@endsection
