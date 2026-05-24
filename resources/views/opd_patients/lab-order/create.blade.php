@extends('backend.layouts.master')
@section('title', 'New Lab Order')
@section('content')
<div class="container-fluid py-3" style="padding-bottom:80px !important;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-clipboard2-pulse text-primary"></i> New Lab Order
                <small class="text-muted">for {{ optional($opd->patient)->patient_name }}</small>
            </h4>
            <small class="text-muted">Pick investigations from any of the 9 lab types. Each line auto-creates a patient charge.</small>
        </div>
        <a href="{{ route('opd-patients.show', $opd->id) }}?tab=lab" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Patient
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('opd-patients.lab-orders.store', $opd->id) }}" id="labOrderForm">
        @csrf

        {{-- Header --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Order Date / Time</label>
                        <input type="datetime-local" name="datetime" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">— Select doctor —</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}" @selected($opd->doctor_id == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="Regular">Regular</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lab / Centre</label>
                        <input name="lab_name" class="form-control form-control-sm" placeholder="In-house / External lab name">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control form-control-sm" placeholder="Clinical notes for the lab"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test type filter chips --}}
        @php
            $typeColours = [
                'Pathology' => 'info',
                'Radiology' => 'primary',
                'Microbiology' => 'danger',
                'Histopathology' => 'warning',
                'Cytopathology' => 'info',
                'Immunology / Serology' => 'primary',
                'Endocrinology' => 'success',
                'Cardiology Diagnostics' => 'danger',
                'Genetics & Molecular' => 'secondary',
            ];
            $typeIcons = [
                'Pathology' => 'eyedropper', 'Radiology' => 'broadcast', 'Microbiology' => 'bug',
                'Histopathology' => 'scissors', 'Cytopathology' => 'circle',
                'Immunology / Serology' => 'shield-check', 'Endocrinology' => 'droplet',
                'Cardiology Diagnostics' => 'heart-pulse', 'Genetics & Molecular' => 'cpu',
            ];
        @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-1 align-items-center mb-2">
                    <small class="text-muted me-2"><i class="bi bi-funnel"></i> Filter by type:</small>
                    <button type="button" class="btn btn-sm btn-dark filter-chip active" data-type-filter="">All</button>
                    @foreach ($types as $t)
                        @php $col = $typeColours[$t->name] ?? 'secondary'; $ico = $typeIcons[$t->name] ?? 'box'; @endphp
                        <button type="button" class="btn btn-sm btn-outline-{{ $col }} filter-chip"
                                data-type-filter="{{ $t->id }}">
                            <i class="bi bi-{{ $ico }}"></i> {{ $t->name }}
                        </button>
                    @endforeach
                </div>
                <div class="input-group input-group-sm" style="max-width:400px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" id="invSearch" class="form-control form-control-sm" placeholder="Search by name, code or sample…">
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- LEFT: catalog of investigations to pick from --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-list-check"></i> Available Investigations
                            <span class="badge bg-primary ms-1" id="visibleCount">{{ $investigations->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0" style="max-height:480px;overflow-y:auto;">
                        <ul class="list-group list-group-flush" id="invCatalog">
                            @foreach ($investigations as $inv)
                                @php
                                    $cat = $categories->firstWhere('id', $inv->category_id);
                                    $typeName = optional(optional($cat)->type)->name ?? '—';
                                    $typeId = optional(optional($cat)->type)->id ?? '';
                                    $col = $typeColours[$typeName] ?? 'secondary';
                                @endphp
                                <li class="list-group-item p-2 inv-row"
                                    data-type-id="{{ $typeId }}"
                                    data-cat-id="{{ $inv->category_id }}"
                                    data-search="{{ strtolower($inv->name . ' ' . $inv->short_name . ' ' . $inv->sample_type) }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <div class="flex-grow-1">
                                            <strong>{{ $inv->name }}</strong>
                                            @if ($inv->short_name)<small class="text-muted">({{ $inv->short_name }})</small>@endif
                                            <span class="badge bg-{{ $col }} bg-opacity-15 text-{{ $col }} ms-1">{{ $typeName }}</span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-droplet"></i> {{ $inv->sample_type ?: '—' }}
                                                · <i class="bi bi-clock"></i> {{ $inv->report_time_hours ?: '?' }}h
                                                · <strong class="text-success">৳ {{ number_format((float) $inv->price, 0) }}</strong>
                                            </small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success"
                                                onclick="addToOrder({{ $inv->id }}, '{{ addslashes($inv->name) }}', '{{ addslashes($typeName) }}', {{ (int) $typeId }}, {{ $inv->category_id }}, {{ (float) $inv->price }})"
                                                title="Add to order">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- RIGHT: order basket --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-cart"></i> Order Items
                            <span class="badge bg-success ms-1" id="orderCount">0</span>
                        </h6>
                        <small class="text-muted">Total: <strong id="orderTotal">৳ 0.00</strong></small>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>Investigation</th><th>Type</th><th class="text-end">Price ৳</th><th></th>
                                </tr>
                            </thead>
                            <tbody id="orderRows">
                                <tr id="orderEmpty"><td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-arrow-left"></i> Click ➕ on the left to add investigations
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Sticky bar --}}
<div class="position-fixed bottom-0 start-0 end-0 bg-white shadow-lg border-top py-2 px-4 d-flex justify-content-between align-items-center" style="z-index:1030;">
    <small class="text-muted">
        <i class="bi bi-info-circle"></i>
        Charges are auto-posted to the patient on submit. Order is split by lab type (Pathology / Radiology) automatically.
    </small>
    <div class="d-flex gap-2">
        <a href="{{ route('opd-patients.show', $opd->id) }}?tab=lab" class="btn btn-light btn-sm">Cancel</a>
        <button type="submit" form="labOrderForm" class="btn btn-primary btn-sm px-4" id="submitBtn" disabled>
            <i class="bi bi-check2"></i> Submit Order
        </button>
    </div>
</div>

@push('scripts')
<script>
    const orderItems = new Map();    // invId → {name, typeName, typeId, catId, price}
    let rowSeq = 0;

    function addToOrder(invId, name, typeName, typeId, catId, price) {
        if (orderItems.has(invId)) {
            alert('Already in order');
            return;
        }
        orderItems.set(invId, { name, typeName, typeId, catId, price });
        const i = rowSeq++;
        const row = `
            <tr data-inv-id="${invId}">
                <td class="text-center">${orderItems.size}</td>
                <td>
                    <strong>${name}</strong>
                    <input type="hidden" name="requests[${i}][lab_inv]" value="${invId}">
                    <input type="hidden" name="requests[${i}][type_id]" value="${typeId}">
                    <input type="hidden" name="requests[${i}][category_id]" value="${catId}">
                </td>
                <td><small>${typeName}</small></td>
                <td class="text-end fw-semibold">${price.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromOrder(this, ${invId})"><i class="bi bi-x"></i></button></td>
            </tr>`;
        const empty = document.getElementById('orderEmpty');
        if (empty) empty.remove();
        document.getElementById('orderRows').insertAdjacentHTML('beforeend', row);
        recalcOrder();
    }
    function removeFromOrder(btn, invId) {
        orderItems.delete(invId);
        btn.closest('tr').remove();
        if (orderItems.size === 0) {
            document.getElementById('orderRows').innerHTML = '<tr id="orderEmpty"><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-arrow-left"></i> Click ➕ on the left to add investigations</td></tr>';
        } else {
            // renumber visible rows
            document.querySelectorAll('#orderRows tr[data-inv-id]').forEach((tr, idx) => {
                tr.querySelector('td:first-child').textContent = idx + 1;
            });
        }
        recalcOrder();
    }
    function recalcOrder() {
        const total = [...orderItems.values()].reduce((s, x) => s + x.price, 0);
        document.getElementById('orderTotal').textContent = '৳ ' + total.toFixed(2);
        document.getElementById('orderCount').textContent = orderItems.size;
        document.getElementById('submitBtn').disabled = orderItems.size === 0;
    }

    // Type filter chips
    document.querySelectorAll('.filter-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.typeFilter;
            applyFilter(filter, document.getElementById('invSearch').value);
        });
    });
    // Search
    document.getElementById('invSearch').addEventListener('input', e => {
        const activeChip = document.querySelector('.filter-chip.active');
        applyFilter(activeChip ? activeChip.dataset.typeFilter : '', e.target.value);
    });
    function applyFilter(typeId, query) {
        const q = (query || '').toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('#invCatalog .inv-row').forEach(row => {
            const matchesType = !typeId || row.dataset.typeId === typeId;
            const matchesSearch = !q || row.dataset.search.includes(q);
            const show = matchesType && matchesSearch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('visibleCount').textContent = visible;
    }
</script>
@endpush
@endsection
