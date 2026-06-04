@php
    /** @var \App\Models\ServicePackage|null $package */
    $isEdit = isset($package) && $package?->exists;
    $existingItems = $isEdit && $package->relationLoaded('items')
        ? $package->items
        : ($isEdit ? $package->items : collect());
    $bedPriceMap = $isEdit ? $package->bedPrices->pluck('price', 'bed_type_id')->toArray() : [];

    // Precompute master option payloads for the JS row-builder. The
    // Charge master is the primary picker because patient_charges is
    // where actual billable line items live in this HMS. Other masters
    // (surgery, consumable, equipment) are still selectable for items
    // that don't map to a Charge row.
    $jsMasters = [
        'charge' => [
            'label'   => 'Charge Master',
            'options' => ($charges ?? collect())->map(fn ($x) => [
                'id'   => $x->id,
                'name' => $x->name,
                'rate' => $x->rate ?? '',
            ])->values(),
        ],
        'surgery_type' => [
            'label'   => 'Surgery Type',
            'options' => $surgeryTypes->map(fn ($x) => [
                'id' => $x->id, 'name' => $x->name,
            ])->values(),
        ],
        'surgery_category' => [
            'label'   => 'Surgery Category',
            'options' => $surgeryCategories->map(fn ($x) => [
                'id' => $x->id, 'name' => $x->name,
            ])->values(),
        ],
        'consumable' => [
            'label'   => 'Consumable',
            'options' => $consumables->map(fn ($x) => [
                'id'   => $x->id,
                'name' => $x->name,
                'unit' => $x->unit ?? '',
                'rate' => $x->rate ?? '',
            ])->values(),
        ],
        'equipment' => [
            'label'   => 'Equipment',
            'options' => $equipments->map(fn ($x) => [
                'id' => $x->id, 'name' => $x->name,
            ])->values(),
        ],
        'lab_investigation' => [
            'label'   => 'Lab Investigation',
            'options' => ($labInvestigations ?? collect())->map(fn ($x) => [
                'id'   => $x->id,
                'name' => $x->name . ($x->category?->name ? ' — ' . $x->category->name : ''),
                'rate' => $x->price ?? '',
            ])->values(),
        ],
    ];
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- ─────────── Essentials (always visible) ─────────── --}}
<div class="card mb-3">
    <div class="card-header"><strong><i class="bi bi-info-circle text-primary me-1"></i> Package Details</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Package Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required
                       value="{{ old('name', $package->name ?? '') }}"
                       placeholder="e.g. C-Section Package, Cataract Surgery">
            </div>
            <div class="col-md-3">
                <label class="form-label">Package Type <span class="text-danger">*</span></label>
                <select name="package_type" id="sp-package-type" class="form-select" required>
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected(old('package_type', $package->package_type ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $package->status ?? 'Active') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Base Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" min="0" step="0.01" name="base_price" class="form-control" required
                           value="{{ old('base_price', $package->base_price ?? '0.00') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Duration (days)</label>
                <input type="number" min="0" max="365" name="duration_days" class="form-control"
                       value="{{ old('duration_days', $package->duration_days ?? '') }}"
                       placeholder="e.g. 3">
            </div>
            <div class="col-md-3">
                <label class="form-label">Default Bed Type</label>
                <select name="bed_type_id" class="form-select">
                    <option value="">—</option>
                    @foreach($bedTypes as $b)
                        <option value="{{ $b->id }}" @selected((int) old('bed_type_id', $package->bed_type_id ?? 0) === $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">— Any —</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" @selected((int) old('department_id', $package->department_id ?? 0) === $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Included Services</label>
                <textarea name="included_services_text" rows="2" class="form-control"
                          placeholder="e.g. Bed for 3 days, Doctor visits, Basic medicine, Lab tests, Nursing">{{ old('included_services_text', $package->included_services_text ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Excluded Services</label>
                <textarea name="excluded_services_text" rows="2" class="form-control"
                          placeholder="e.g. Extra bed days, MRI/CT, Blood transfusion, Implants">{{ old('excluded_services_text', $package->excluded_services_text ?? '') }}</textarea>
            </div>

            {{-- Advanced collapsible — only the rarely-edited fields --}}
            <div class="col-12">
                <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" data-bs-target="#sp-advanced">
                    <i class="bi bi-chevron-down"></i> Advanced options (Code, Approval, Surgery linkage, …)
                </button>
            </div>

            <div class="col-12 collapse {{ ($package->code ?? '') || ($package->surgery_type_id ?? '') || ($package->requires_approval ?? false) ? 'show' : '' }}" id="sp-advanced">
                <div class="row g-3 border rounded p-3 bg-light">
                    <div class="col-md-3">
                        <label class="form-label small">Code</label>
                        <input type="text" name="code" class="form-control form-control-sm"
                               value="{{ old('code', $package->code ?? '') }}"
                               placeholder="Auto-generated">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Admission Type</label>
                        <select name="admission_type" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach($admissionTypes as $a)
                                <option value="{{ $a }}" @selected(old('admission_type', $package->admission_type ?? '') === $a)>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Patient Category</label>
                        <select name="patient_category" class="form-select form-select-sm">
                            <option value="">— Any —</option>
                            @foreach($patientCategories as $pc)
                                <option value="{{ $pc }}" @selected(old('patient_category', $package->patient_category ?? '') === $pc)>{{ $pc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="requires_approval" value="0">
                            <input type="checkbox" name="requires_approval" value="1" class="form-check-input" id="reqAppr"
                                   @checked(old('requires_approval', $package->requires_approval ?? false))>
                            <label class="form-check-label small" for="reqAppr">Requires Approval</label>
                        </div>
                    </div>

                    {{-- Surgery linkage — only meaningful for OT packages --}}
                    <div class="col-md-6 sp-ot-only">
                        <label class="form-label small">Surgery Type <small class="text-muted">(OT packages)</small></label>
                        <select name="surgery_type_id" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach($surgeryTypes as $st)
                                <option value="{{ $st->id }}" @selected((int) old('surgery_type_id', $package->surgery_type_id ?? 0) === $st->id)>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 sp-ot-only">
                        <label class="form-label small">Surgery Category</label>
                        <select name="surgery_category_id" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach($surgeryCategories as $sc)
                                <option value="{{ $sc->id }}" @selected((int) old('surgery_category_id', $package->surgery_category_id ?? 0) === $sc->id)>{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small">Approval Role <small class="text-muted">(if approval required)</small></label>
                        <input type="text" name="approval_role" class="form-control form-control-sm"
                               value="{{ old('approval_role', $package->approval_role ?? '') }}"
                               placeholder="e.g. Billing Manager">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm"
                               value="{{ old('remarks', $package->remarks ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide OT-specific surgery fields based on package_type
(function () {
    const typeSel = document.getElementById('sp-package-type');
    const otBlocks = document.querySelectorAll('.sp-ot-only');
    if (! typeSel) return;
    function sync() {
        const isOt = typeSel.value === 'OT';
        otBlocks.forEach(el => { el.style.display = isOt ? '' : 'none'; });
    }
    typeSel.addEventListener('change', sync);
    sync();
})();
</script>

<div class="card mb-3 border-info-subtle">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong><i class="bi bi-receipt text-info me-1"></i> Included Items
                <small class="text-muted">(audit breakdown — does not sum to Base Price)</small>
            </strong>
            <div class="small text-muted mt-1">
                <i class="bi bi-info-circle"></i>
                Link each item to a <strong>Charge</strong> from your Charges master so the bill auto-resolves the line.
                For OT / consumables / equipment, pick the matching master if the item isn't a billable charge.
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="spAddItemRow()">
            <i class="bi bi-plus-lg"></i> Add Item
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="sp-items-table">
            <thead class="table-light">
                <tr>
                    <th style="width:13%">Category</th>
                    <th style="width:11%">Link To</th>
                    <th style="width:22%">Charge / Master <small class="text-muted d-block fw-normal">(searchable)</small></th>
                    <th>Item Name</th>
                    <th style="width:9%">Qty</th>
                    <th style="width:10%">Unit</th>
                    <th>Notes</th>
                    <th style="width:4%"></th>
                </tr>
            </thead>
            <tbody id="sp-items-body">
                @forelse($existingItems as $i => $item)
                    <tr>
                        <td>
                            <select name="items[{{ $i }}][item_category]" class="form-select form-select-sm sp-cat" data-row="{{ $i }}">
                                @foreach($itemCategories as $c)
                                    <option value="{{ $c }}" @selected($item->item_category === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm sp-master-type-sel"
                                    name="items[{{ $i }}][master_type]" data-row="{{ $i }}">
                                <option value="">— none —</option>
                                <option value="charge"            @selected($item->master_type === 'charge')>Charge</option>
                                <option value="lab_investigation" @selected($item->master_type === 'lab_investigation')>Lab Test</option>
                                <option value="surgery_type"      @selected($item->master_type === 'surgery_type')>Surgery</option>
                                <option value="consumable"        @selected($item->master_type === 'consumable')>Consumable</option>
                                <option value="equipment"         @selected($item->master_type === 'equipment')>Equipment</option>
                            </select>
                        </td>
                        <td>
                            <select name="items[{{ $i }}][master_id]" class="form-select form-select-sm sp-master-id"
                                    data-row="{{ $i }}" data-current="{{ $item->master_id }}" data-name="{{ $item->item_name }}">
                                <option value="">—</option>
                                @if($item->master_type && $item->master_id)
                                    <option value="{{ $item->master_id }}" selected>{{ optional($item->resolveMaster())->name ?? '(deleted)' }}</option>
                                @endif
                            </select>
                        </td>
                        <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control form-control-sm sp-name" value="{{ $item->item_name }}"></td>
                        <td><input type="number" min="0" step="0.01" name="items[{{ $i }}][included_qty]" class="form-control form-control-sm" value="{{ $item->included_qty }}"></td>
                        <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ $item->unit }}"></td>
                        <td><input type="text" name="items[{{ $i }}][notes]" class="form-control form-control-sm" value="{{ $item->notes }}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                    </tr>
                @empty
                    {{-- starter row added by JS on load --}}
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ───────── Bed-wise Price (only shows added bed types) ─────────
    UX: instead of dumping all 29 bed types as empty inputs, only show
    the default bed type + variants the user explicitly adds via the
    picker. Clean by default, expandable when needed. --}}
@php
    $defaultBedTypeId = (int) old('bed_type_id', $package->bed_type_id ?? 0);
    // Variants that already exist (from old() on validation failure OR from
    // saved package being edited).
    $oldBedPrices = old('bed_prices', $bedPriceMap);
    $initialBedTypeIds = collect($oldBedPrices)
        ->filter(fn ($v) => $v !== '' && $v !== null)
        ->keys()->map(fn ($id) => (int) $id)
        ->merge([$defaultBedTypeId])
        ->filter()
        ->unique()
        ->values();

    // Build per-bed-type payload for JS row builder
    $jsBedTypes = $bedTypes->map(fn ($b) => [
        'id'              => $b->id,
        'name'            => $b->name,
        'category'        => $b->category,
        'is_icu'          => (bool) $b->is_icu,
        'icu_type'        => $b->icu_type,
        'has_ventilator'  => (bool) $b->has_ventilator_support,
        'is_isolation'    => (bool) $b->is_isolation_bed,
        'total'           => (int) ($b->beds_count ?? 0),
        'free'            => (int) ($b->beds_available_count ?? 0),
    ])->values();
@endphp

<div class="card mb-3 border-primary-subtle">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong><i class="bi bi-hospital text-primary me-1"></i> Bed-wise Pricing</strong>
            <small class="text-muted ms-2">Override Base Price for specific bed types. Empty rows → Base Price.</small>
        </div>
        <div class="d-flex gap-2 align-items-end flex-wrap">
            <div style="min-width:280px;">
                <label class="form-label small mb-0 text-muted">Add bed type variant</label>
                <select id="sp-bed-picker" class="form-select form-select-sm">
                    <option value="">— select bed type to add —</option>
                    @foreach(['Critical Care', 'Cabin', 'General Ward', 'Specialty'] as $cat)
                        @php $rows = $bedTypes->where('category', $cat); @endphp
                        @if($rows->count())
                            <optgroup label="{{ $cat }}">
                                @foreach($rows as $b)
                                    <option value="{{ $b->id }}">
                                        {{ $b->name }}
                                        @if($b->is_icu)— {{ $b->icu_type ?? 'ICU' }}@endif
                                        ({{ $b->beds_count ?? 0 }} beds)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="spAddBedRow()">
                <i class="bi bi-plus-lg"></i> Add
            </button>
        </div>
    </div>

    <div class="card-body py-2">
        <div id="sp-bed-rows" class="row g-2">
            {{-- Pre-render existing variant rows + default bed type --}}
            @foreach($initialBedTypeIds as $btId)
                @php $b = $bedTypes->firstWhere('id', $btId); @endphp
                @if($b)
                    @php
                        $isDefault  = $b->id === $defaultBedTypeId;
                        $currentVal = $oldBedPrices[$b->id] ?? '';
                        $total = (int) ($b->beds_count ?? 0);
                        $free  = (int) ($b->beds_available_count ?? 0);
                    @endphp
                    <div class="col-md-4 col-sm-6 sp-bed-row" data-bt-id="{{ $b->id }}">
                        <div class="border rounded p-2 h-100 {{ $isDefault ? 'border-primary bg-primary-subtle' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <div class="fw-semibold small">{{ $b->name }}
                                        @if($isDefault)
                                            <span class="badge bg-primary ms-1" style="font-size:9px;">Default</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">
                                        {{ $b->category }} ·
                                        <span class="badge bg-light text-dark border" style="font-size:9px;">{{ $total }} total</span>
                                        @if($total > 0)<span class="badge bg-success-subtle text-success border" style="font-size:9px;">{{ $free }} free</span>@endif
                                        @if($b->is_icu)<span class="badge bg-danger-subtle text-danger border" style="font-size:9px;">{{ $b->icu_type ?? 'ICU' }}</span>@endif
                                    </div>
                                </div>
                                @if(! $isDefault)
                                    <button type="button" class="btn-close btn-close-sm" aria-label="Remove"
                                            onclick="spRemoveBedRow(this, {{ $b->id }})"></button>
                                @endif
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" min="0" step="0.01" class="form-control sp-bed-price"
                                       data-bed-type-id="{{ $b->id }}"
                                       name="bed_prices[{{ $b->id }}]"
                                       value="{{ $currentVal }}"
                                       placeholder="Base">
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if($initialBedTypeIds->isEmpty())
            <div id="sp-bed-empty" class="text-center text-muted py-4 small">
                <i class="bi bi-info-circle"></i>
                No bed-type variants yet. Either set a <strong>Default Bed Type</strong> above
                or add variants from the picker on the right. Without variants every bed type uses the Base Price.
            </div>
        @endif

        <div class="alert alert-info py-2 mb-0 mt-2 small">
            <i class="bi bi-lightbulb"></i>
            <strong>How it works:</strong> When a patient is admitted to a bed type listed here,
            the IPD flow uses that variant price. Bed types NOT listed here use the Base Price.
        </div>
    </div>
</div>

<script>
const SP_BED_TYPES = @json($jsBedTypes);

function spBedTypeData(id) {
    return SP_BED_TYPES.find(b => String(b.id) === String(id));
}

function spAddBedRow() {
    const picker = document.getElementById('sp-bed-picker');
    if (! picker || ! picker.value) return;
    const btId = picker.value;

    // Prevent duplicate
    if (document.querySelector(`.sp-bed-row[data-bt-id="${btId}"]`)) {
        picker.value = '';
        return;
    }

    const b = spBedTypeData(btId);
    if (! b) return;

    const empty = document.getElementById('sp-bed-empty');
    if (empty) empty.remove();

    const wrap = document.createElement('div');
    wrap.className = 'col-md-4 col-sm-6 sp-bed-row';
    wrap.dataset.btId = btId;

    const icuBadge = b.is_icu ? `<span class="badge bg-danger-subtle text-danger border" style="font-size:9px;">${b.icu_type || 'ICU'}</span>` : '';
    const freeBadge = b.total > 0 ? `<span class="badge bg-success-subtle text-success border" style="font-size:9px;">${b.free} free</span>` : '';

    wrap.innerHTML = `
        <div class="border rounded p-2 h-100">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <div class="fw-semibold small">${b.name}</div>
                    <div class="small text-muted">
                        ${b.category} ·
                        <span class="badge bg-light text-dark border" style="font-size:9px;">${b.total} total</span>
                        ${freeBadge} ${icuBadge}
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-sm" aria-label="Remove" onclick="spRemoveBedRow(this, ${btId})"></button>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-text">৳</span>
                <input type="number" min="0" step="0.01" class="form-control sp-bed-price"
                       data-bed-type-id="${btId}"
                       name="bed_prices[${btId}]"
                       placeholder="${spBasePlaceholder()}">
            </div>
        </div>`;
    document.getElementById('sp-bed-rows').appendChild(wrap);

    picker.value = '';
}

function spRemoveBedRow(btn, btId) {
    const row = btn.closest('.sp-bed-row');
    if (! row) return;
    // Submit a blank for this bed type so server clears any saved variant
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = `bed_prices[${btId}]`;
    inp.value = '';
    row.parentNode.appendChild(inp);
    row.remove();

    if (document.querySelectorAll('.sp-bed-row').length === 0) {
        const rows = document.getElementById('sp-bed-rows');
        const empty = document.createElement('div');
        empty.id = 'sp-bed-empty';
        empty.className = 'text-center text-muted py-4 small';
        empty.innerHTML = '<i class="bi bi-info-circle"></i> No bed-type variants. Add from the picker above.';
        rows.parentNode.insertBefore(empty, rows.nextSibling);
    }
}

function spBasePlaceholder() {
    const base = document.querySelector('input[name="base_price"]');
    return base?.value ? ('Base: ৳' + Number(base.value).toLocaleString()) : 'Base';
}

// Live-update placeholder when Base Price changes
document.addEventListener('DOMContentLoaded', function () {
    const base = document.querySelector('input[name="base_price"]');
    if (! base) return;
    const sync = () => {
        const v = spBasePlaceholder();
        document.querySelectorAll('.sp-bed-price').forEach(i => i.placeholder = v);
    };
    base.addEventListener('input', sync);
    sync();
});
</script>

<div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button class="btn btn-primary">
        <i class="bi bi-save me-1"></i> {{ $isEdit ? 'Update Package' : 'Create Package' }}
    </button>
</div>

@push('scripts')
<script>
    const SP_CATEGORIES = @json($itemCategories);

    // Master options keyed by master_type slug. Each entry: {label, options: [{id, name, unit, rate}]}
    const SP_MASTERS = @json($jsMasters);

    // Map item_category → suggested master_type. Charge is the default
    // for billable categories; only OT-flavour items default to surgery.
    // The user can override per-row via the "Link To" selector.
    const CAT_TO_MASTER = {
        'OT':            'surgery_type',
        'Procedure':     'surgery_type',
        'Consumable':    'consumable',
        'Equipment':     'equipment',
        'Investigation': 'lab_investigation',
        'Doctor Visit':  'charge',
        'Nursing':       'charge',
        'Medicine':      'charge',
        'Other':         'charge',
        'Bed':           'charge',
    };

    function spPopulateMasterSelect(masterSel, type, currentVal = '') {
        if (! masterSel) return;
        if (! type || ! SP_MASTERS[type]) {
            masterSel.innerHTML = '<option value="">— (free text only)</option>';
            masterSel.disabled = true;
            return;
        }
        masterSel.disabled = false;
        let html = `<option value="">— pick ${SP_MASTERS[type].label} —</option>`;
        SP_MASTERS[type].options.forEach(opt => {
            const sel = String(opt.id) === String(currentVal) ? ' selected' : '';
            html += `<option value="${opt.id}" data-name="${opt.name}" data-unit="${opt.unit||''}" data-rate="${opt.rate||''}"${sel}>${opt.name}</option>`;
        });
        masterSel.innerHTML = html;
    }

    function spRefreshMaster(row, opts = {}) {
        const catSel    = row.querySelector('select[name$="[item_category]"]');
        const typeSel   = row.querySelector('.sp-master-type-sel');
        const masterSel = row.querySelector('.sp-master-id');
        const nameInp   = row.querySelector('.sp-name');
        if (! catSel || ! typeSel || ! masterSel) return;

        // When the category changes (and we're not preserving a manual
        // override), default the master-type to the category suggestion.
        if (opts.fromCat) {
            const suggested = CAT_TO_MASTER[catSel.value] || 'charge';
            typeSel.value = suggested;
        }

        spPopulateMasterSelect(masterSel, typeSel.value, masterSel.dataset.current || '');

        // Auto-fill item_name + unit when a master is picked
        masterSel.onchange = function () {
            const opt = this.options[this.selectedIndex];
            if (! opt || ! opt.value) return;
            if (nameInp && ! nameInp.value) nameInp.value = opt.dataset.name || '';
            const unitInp = row.querySelector('input[name$="[unit]"]');
            if (unitInp && ! unitInp.value && opt.dataset.unit) unitInp.value = opt.dataset.unit;
        };
    }

    function spWireRow(row) {
        const catSel  = row.querySelector('select[name$="[item_category]"]');
        const typeSel = row.querySelector('.sp-master-type-sel');
        if (catSel)  catSel.addEventListener('change',  () => spRefreshMaster(row, { fromCat: true }));
        if (typeSel) typeSel.addEventListener('change', () => spRefreshMaster(row));
        spRefreshMaster(row);
    }

    function spAddItemRow() {
        const tbody = document.getElementById('sp-items-body');
        const i     = tbody.children.length;
        const opts  = SP_CATEGORIES.map(c => `<option value="${c}">${c}</option>`).join('');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><select name="items[${i}][item_category]" class="form-select form-select-sm sp-cat">${opts}</select></td>
            <td>
                <select class="form-select form-select-sm sp-master-type-sel" name="items[${i}][master_type]">
                    <option value="">— none —</option>
                    <option value="charge" selected>Charge</option>
                    <option value="lab_investigation">Lab Test</option>
                    <option value="surgery_type">Surgery</option>
                    <option value="consumable">Consumable</option>
                    <option value="equipment">Equipment</option>
                </select>
            </td>
            <td><select name="items[${i}][master_id]" class="form-select form-select-sm sp-master-id" data-current=""><option value="">—</option></select></td>
            <td><input type="text" name="items[${i}][item_name]" class="form-control form-control-sm sp-name"></td>
            <td><input type="number" min="0" step="0.01" name="items[${i}][included_qty]" class="form-control form-control-sm" value="1"></td>
            <td><input type="text" name="items[${i}][unit]" class="form-control form-control-sm" placeholder="days / visits / pcs"></td>
            <td><input type="text" name="items[${i}][notes]" class="form-control form-control-sm"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>`;
        tbody.appendChild(row);
        spWireRow(row);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('sp-items-body');
        if (! tbody) return;
        Array.from(tbody.children).forEach(spWireRow);
        if (tbody.children.length === 0) spAddItemRow();
    });
</script>
@endpush
