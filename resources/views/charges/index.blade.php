@extends('backend.layouts.master')

@section('title', 'Hospital Charges')

@section('content')
<div class="container-fluid px-3 px-md-4">
    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success')) <div class="alert alert-success py-2">{{ session('success') }}</div> @endif

    <div class="row g-4">

        {{-- LEFT: Setup Menu --}}
        <div class="col-xl-3 col-lg-4 col-md-4">
            @include('backend.layouts.charges_setup')
        </div>

        {{-- RIGHT: Content --}}
        <div class="col-xl-9 col-lg-8 col-md-8">

            {{-- KPI tiles --}}
            @php
                $totalCharges = $charges->count();
                $avgPrice     = $totalCharges > 0 ? round($charges->avg('standard_charge')) : 0;
                $byType       = $charges->groupBy(fn ($c) => optional($c->chargeType)->name ?? 'Uncategorised');
                $maxPrice     = $totalCharges > 0 ? $charges->max('standard_charge') : 0;
            @endphp
            <div class="row g-2 mb-3">
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-primary"><i class="bi bi-cash-coin"></i> Total Charges</small>
                            <h4 class="mb-0">{{ $totalCharges }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-success"><i class="bi bi-diagram-3"></i> Categories</small>
                            <h4 class="mb-0">{{ $byType->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-info">Avg Price</small>
                            <h4 class="mb-0">৳ {{ number_format($avgPrice) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                        <div class="card-body py-2 px-3">
                            <small class="text-warning">Max Price</small>
                            <h4 class="mb-0">৳ {{ number_format($maxPrice) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

                {{-- Header --}}
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-1 fw-semibold text-dark"><i class="bi bi-cash-coin text-primary"></i> Hospital Charges</h5>
                        <p class="mb-0 text-muted small">Master pricing for every service — synced to packages &amp; billing.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <input id="chgSearch" class="form-control form-control-sm" placeholder="🔍 Search…" style="width:200px;">
                        <select id="chgTypeFilter" class="form-select form-select-sm" style="width:180px;">
                            <option value="">All types</option>
                            @foreach ($byType as $type => $items)
                                <option value="{{ $type }}">{{ $type }} ({{ $items->count() }})</option>
                            @endforeach
                        </select>
                        <a class="btn btn-primary btn-sm" data-size="lg" data-url="{{ route('admin.charges.create') }}" data-ajax-popup="true" data-title="Create Charge">
                            <i class="bi bi-plus-lg"></i> New Charge
                        </a>
                    </div>
                </div>

                {{-- Type filter chips --}}
                <div class="px-3 py-2 border-bottom bg-light">
                    <div class="d-flex flex-wrap gap-1">
                        @php
                            $typeColours = [
                                'Consultation' => 'primary', 'Procedure' => 'danger', 'Investigation' => 'info',
                                'Pharmacy' => 'success', 'Bed Charge' => 'warning', 'Nursing' => 'info',
                                'OT' => 'danger', 'ICU' => 'danger', 'Equipment Usage' => 'secondary',
                                'Ambulance' => 'dark', 'Administrative' => 'secondary', 'Therapy' => 'success',
                            ];
                            $typeIcons = [
                                'Consultation' => 'person-badge', 'Procedure' => 'bandaid', 'Investigation' => 'eyedropper',
                                'Pharmacy' => 'capsule', 'Bed Charge' => 'house', 'Nursing' => 'person',
                                'OT' => 'scissors', 'ICU' => 'heart-pulse', 'Equipment Usage' => 'plug',
                                'Ambulance' => 'truck', 'Administrative' => 'folder', 'Therapy' => 'activity',
                            ];
                        @endphp
                        <button class="btn btn-sm btn-outline-dark chip-all active" data-type="">All <span class="badge bg-dark ms-1">{{ $totalCharges }}</span></button>
                        @foreach ($byType->sortKeys() as $type => $items)
                            @php $c = $typeColours[$type] ?? 'secondary'; $ic = $typeIcons[$type] ?? 'tag'; @endphp
                            <button class="btn btn-sm btn-outline-{{ $c }} chg-chip" data-type="{{ $type }}">
                                <i class="bi bi-{{ $ic }}"></i> {{ $type }}
                                <span class="badge bg-{{ $c }} ms-1">{{ $items->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Table --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="chargeTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Charge Name</th>
                                    <th>Type / Category</th>
                                    <th>Unit</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-center" width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($charges as $key => $charge)
                                    @php
                                        $type = optional($charge->chargeType)->name ?? 'Uncategorised';
                                        $colour = $typeColours[$type] ?? 'secondary';
                                        $icon = $typeIcons[$type] ?? 'tag';
                                    @endphp
                                    <tr class="chg-row" data-type="{{ $type }}" data-search="{{ strtolower($charge->charge_name . ' ' . optional($charge->chargeCategory)->name) }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $charge->charge_name }}</strong>
                                            @if ($charge->description)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($charge->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $colour }} bg-opacity-15 text-{{ $colour }}">
                                                <i class="bi bi-{{ $icon }}"></i> {{ $type }}
                                            </span>
                                            <br><small class="text-muted">{{ optional($charge->chargeCategory)->name ?? '—' }}</small>
                                        </td>
                                        <td><small>{{ optional($charge->uniteType)->name ?? '—' }}</small></td>
                                        <td class="text-end"><strong>৳ {{ number_format($charge->standard_charge, 0) }}</strong></td>
                                        <td class="text-end">
                                            @if ($charge->tax > 0)
                                                <small class="badge bg-warning bg-opacity-15 text-warning">{{ $charge->tax }}%</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <button class="btn btn-sm btn-outline-info chg-detail-btn" title="Details"
                                                data-name="{{ $charge->charge_name }}"
                                                data-type="{{ $type }}"
                                                data-category="{{ optional($charge->chargeCategory)->name }}"
                                                data-unit="{{ optional($charge->uniteType)->name }}"
                                                data-tax-category="{{ optional($charge->taxCategory)->name }}"
                                                data-tax="{{ (float) $charge->tax }}"
                                                data-price="{{ (float) $charge->standard_charge }}"
                                                data-description="{{ $charge->description }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a class="btn btn-sm btn-outline-warning" data-size="lg"
                                                data-url="{{ route('admin.charges.edit', $charge->id) }}"
                                                data-ajax-popup="true" data-title="Edit Charge" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.charges.destroy', $charge->id) }}" class="d-inline"
                                                onsubmit="return confirm('Delete this charge?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-cash-stack display-4"></i>
                                        <p class="mt-3 mb-0">No charges yet</p>
                                        <a class="btn btn-sm btn-primary mt-2" data-size="lg" data-url="{{ route('admin.charges.create') }}" data-ajax-popup="true" data-title="Create Charge"><i class="bi bi-plus-lg"></i> Add the first charge</a>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if (method_exists($charges, 'links'))
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                            <small class="text-muted">Showing {{ $charges->firstItem() ?? 0 }} – {{ $charges->lastItem() ?? 0 }} of {{ $charges->total() }} charges</small>
                            {{ $charges->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="chgDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Charge Details</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th width="35%">Name</th><td id="dm_name"></td></tr>
                        <tr><th>Type</th><td id="dm_type"></td></tr>
                        <tr><th>Category</th><td id="dm_category"></td></tr>
                        <tr><th>Unit</th><td id="dm_unit"></td></tr>
                        <tr><th>Standard Price</th><td><strong id="dm_price" class="text-success"></strong></td></tr>
                        <tr><th>Tax</th><td id="dm_tax"></td></tr>
                        <tr><th>Description</th><td id="dm_description" class="text-muted"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Type / search filter
    function applyFilter() {
        const t = document.getElementById('chgTypeFilter')?.value || (document.querySelector('.chip-active')?.dataset.type ?? '');
        const q = (document.getElementById('chgSearch')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.chg-row').forEach(r => {
            const okType = !t || r.dataset.type === t;
            const okSearch = !q || (r.dataset.search || '').includes(q);
            r.style.display = (okType && okSearch) ? '' : 'none';
        });
    }
    document.getElementById('chgSearch')?.addEventListener('input', applyFilter);
    document.getElementById('chgTypeFilter')?.addEventListener('change', applyFilter);
    document.querySelectorAll('.chg-chip,.chip-all').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.chg-chip,.chip-all').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('chgTypeFilter').value = btn.dataset.type;
            applyFilter();
        });
    });

    // Detail modal — read from data-* attributes
    document.querySelectorAll('.chg-detail-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('dm_name').textContent = d.name;
            document.getElementById('dm_type').textContent = d.type || '—';
            document.getElementById('dm_category').textContent = d.category || '—';
            document.getElementById('dm_unit').textContent = d.unit || '—';
            document.getElementById('dm_price').textContent = '৳ ' + Number(d.price).toLocaleString();
            const tax = parseFloat(d.tax || 0);
            document.getElementById('dm_tax').textContent = tax > 0 ? tax + '% (' + (d.taxCategory || '') + ')' : '— No tax —';
            document.getElementById('dm_description').textContent = d.description || '— No description —';
            new bootstrap.Modal(document.getElementById('chgDetailModal')).show();
        });
    });

    // Cascading category dropdown (preserved)
    $(document).on('change', 'select[name="charge_type_id"]', function() {
        let chargeTypeId = $(this).val();
        let $chargeCategory = $('select[name="charge_category_id"]');
        $chargeCategory.html('<option value="">Loading...</option>');
        if (chargeTypeId) {
            $.ajax({
                url: "{{ route('admin.charges.get-charge-categories') }}",
                type: "GET",
                data: { charge_type_id: chargeTypeId },
                success: function(response) {
                    $chargeCategory.empty().append('<option value="">Select</option>');
                    $.each(response, (key, value) => $chargeCategory.append('<option value="' + key + '">' + value + '</option>'));
                },
                error: function() { $chargeCategory.html('<option value="">No Data Found</option>'); }
            });
        } else {
            $chargeCategory.html('<option value="">Select</option>');
        }
    });
</script>
@endpush
