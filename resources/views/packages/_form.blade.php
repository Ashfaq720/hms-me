@php
    $isEdit = $package->exists;
    $items = old('items', $isEdit
        ? $package->services->map(fn ($ps) => [
            'charge_id'   => $ps->charge_id,
            'quantity'    => $ps->quantity,
            'rate'        => $ps->rate,
            'is_included' => $ps->is_included,
            'note'        => $ps->note,
        ])->toArray()
        : []);
    $rules = old('rules', $isEdit
        ? $package->priceRules->map(fn ($r) => [
            'bed_type_id' => $r->bed_type_id,
            'department_id' => $r->department_id,
            'duration_days' => $r->duration_days,
            'patient_category' => $r->patient_category,
            'price' => $r->price,
            'valid_from' => optional($r->valid_from)->format('Y-m-d'),
            'valid_to' => optional($r->valid_to)->format('Y-m-d'),
            'notes' => $r->notes,
        ])->toArray()
        : []);

    $servicesCount = count($items);
    $rulesCount = count($rules);
@endphp

{{-- ───────────── TABBED EDITOR ───────────── --}}
<ul class="nav nav-pills nav-fill mb-3 package-tabs" role="tablist">
    <li class="nav-item">
        <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-basic">
            <i class="bi bi-info-circle"></i> 1. Basic Info
        </button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-services">
            <i class="bi bi-list-check"></i> 2. Included Charges
            <span class="badge bg-success ms-1" id="serviceCountBadge">{{ $servicesCount }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pricing">
            <i class="bi bi-tag"></i> 3. Bed / Room Pricing
            <span class="badge bg-info ms-1" id="ruleCountBadge">{{ $rulesCount }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-summary">
            <i class="bi bi-check2-circle"></i> 4. Review &amp; Save
        </button>
    </li>
</ul>

<div class="tab-content package-tab-content">

    {{-- ──────── TAB 1: BASIC INFO ──────── --}}
    <div class="tab-pane fade show active" id="tab-basic">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle text-primary"></i> Package Identity &amp; Eligibility</h6>
                <small class="text-muted">Who can use this package and where</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Package Name <span class="text-danger">*</span></label>
                        <input name="name" class="form-control" value="{{ old('name', $package->name) }}" required placeholder="e.g. C-Section Maternity Package">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Code</label>
                        <input name="code" class="form-control" value="{{ old('code', $package->code) }}" placeholder="auto-generated">
                        <small class="text-muted">Leave blank to auto-generate</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status', $package->status ?? 'active') === 'active')>🟢 Active</option>
                            <option value="inactive" @selected(old('status', $package->status) === 'inactive')>⚪ Inactive</option>
                            <option value="archived" @selected(old('status', $package->status) === 'archived')>📦 Archived</option>
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-2"></div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="package_type" class="form-select" required>
                            @foreach ($types as $code => $label)
                                <option value="{{ $code }}" @selected(old('package_type', $package->package_type) === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Admission Type</label>
                        <select name="admission_type" class="form-select">
                            @foreach (['ANY' => 'Any', 'PLANNED' => 'Planned', 'EMERGENCY' => 'Emergency', 'DAY_CARE' => 'Day Care', 'WALK_IN' => 'Walk-in'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('admission_type', $package->admission_type ?? 'ANY') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">— Any department —</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected(old('department_id', $package->department_id) == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Default Bed Type</label>
                        <select name="bed_type_id" class="form-select">
                            <option value="">— Not applicable —</option>
                            @foreach ($bedTypes as $b)
                                <option value="{{ $b->id }}" @selected(old('bed_type_id', $package->bed_type_id) == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12"><hr class="my-2"></div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Validity (days)</label>
                        <input type="number" name="validity_days" min="1" max="365" class="form-control" value="{{ old('validity_days', $package->validity_days ?? 7) }}">
                        <small class="text-muted">How long the package is valid after enrolment</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Discount (%)</label>
                        <input type="number" step="0.01" name="discount" min="0" max="100" class="form-control" value="{{ old('discount', $package->discount ?? 0) }}">
                        <small class="text-muted">Default discount on this package</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Patient Type</label>
                        <input name="patient_type" class="form-control" value="{{ old('patient_type', $package->patient_type) }}" placeholder="e.g. Adult / Pediatric / Geriatric">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="What this package covers, who it's for, what makes it special…">{{ old('description', $package->description) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="button" class="btn btn-primary btn-sm" onclick="document.querySelector('[data-bs-target=\'#tab-services\']').click()">
                    Next: Services <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ──────── TAB 2: CHARGES (grouped by charge_type) ──────── --}}
    @php
        // Group charges by their charge_type name (from /admin/charges master)
        $chargesByType = $charges->groupBy(fn ($c) => optional($c->chargeType)->name ?: 'Uncategorised');

        $typeIcon = [
            'Consultation'    => 'person-badge',
            'Procedure'       => 'bandaid',
            'Investigation'   => 'eyedropper',
            'Pharmacy'        => 'capsule',
            'Bed Charge'      => 'house',
            'Nursing'         => 'person',
            'OT'              => 'scissors',
            'ICU'             => 'heart-pulse',
            'Equipment Usage' => 'plug',
            'Ambulance'       => 'truck',
            'Administrative'  => 'folder',
            'Therapy'         => 'activity',
            'Uncategorised'   => 'three-dots',
        ];
    @endphp
    <div class="tab-pane fade" id="tab-services">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-0"><i class="bi bi-list-check text-success"></i> Included &amp; Excluded Charges</h6>
                        <small class="text-muted">Pick from <a href="{{ route('admin.charges.index') }}" target="_blank">Hospital Charges →</a> · Untick "Included" to flag as excluded</small>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="search" id="srvSearch" class="form-control form-control-sm" placeholder="🔍 Search rows…" style="width:200px;">
                        <button type="button" class="btn btn-success btn-sm" onclick="addServiceRow()">
                            <i class="bi bi-plus-lg"></i> Add Custom Row
                        </button>
                    </div>
                </div>

                {{-- Quick-add chips: click to instantly add a charge from a type --}}
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <small class="text-muted me-2 align-self-center"><i class="bi bi-lightning-charge"></i> Quick-add charges by type:</small>
                    @foreach ($chargesByType as $typeName => $typeCharges)
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-{{ $typeIcon[$typeName] ?? 'three-dots' }}"></i>
                                {{ $typeName }}
                                <span class="badge bg-primary ms-1">{{ $typeCharges->count() }}</span>
                            </button>
                            <ul class="dropdown-menu" style="max-height:300px; overflow-y:auto;">
                                @foreach ($typeCharges as $chg)
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item small"
                                           onclick="addChargeRow({{ $chg->id }}, '{{ addslashes('CHG-' . str_pad($chg->id, 4, '0', STR_PAD_LEFT)) }}', '{{ addslashes($chg->charge_name) }}', {{ (float) $chg->standard_charge }})">
                                            <strong>CHG-{{ str_pad($chg->id, 4, '0', STR_PAD_LEFT) }}</strong>
                                            <span class="text-muted ms-1">{{ $chg->charge_name }}</span>
                                            <span class="badge bg-success bg-opacity-15 text-success float-end">৳ {{ number_format($chg->standard_charge, 0) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle" id="servicesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%;" class="text-center">#</th>
                            <th style="width:34%;">Charge / Service</th>
                            <th style="width:8%;" class="text-center">Included?</th>
                            <th style="width:8%;" class="text-center">Qty</th>
                            <th style="width:11%;" class="text-end">Rate ৳</th>
                            <th style="width:11%;" class="text-end">Amount ৳</th>
                            <th>Note</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="serviceRows">
                        @foreach (($items ?: [['is_included' => 1, 'quantity' => 1]]) as $i => $row)
                            <tr data-row-index="{{ $i }}">
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <select name="items[{{ $i }}][charge_id]" class="form-select form-select-sm" onchange="autoFillRate(this, {{ $i }})">
                                        <option value="">-- Pick a charge from /admin/charges --</option>
                                        @foreach ($chargesByType as $typeName => $typeCharges)
                                            <optgroup label="{{ $typeName }} ({{ $typeCharges->count() }})">
                                                @foreach ($typeCharges as $c)
                                                    @php $code = 'CHG-' . str_pad($c->id, 4, '0', STR_PAD_LEFT); @endphp
                                                    <option value="{{ $c->id }}"
                                                            data-price="{{ $c->standard_charge }}"
                                                            data-type="{{ $typeName }}"
                                                            data-search="{{ strtolower($code . ' ' . $c->charge_name . ' ' . $typeName) }}"
                                                            @selected(($row['charge_id'] ?? null) == $c->id)>
                                                        {{ $code }} · {{ $c->charge_name }} · ৳{{ number_format($c->standard_charge, 0) }} ({{ optional($c->uniteType)->name }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="items[{{ $i }}][is_included]" value="0">
                                    <div class="form-check form-switch d-inline-flex">
                                        <input type="checkbox" name="items[{{ $i }}][is_included]" value="1"
                                               class="form-check-input" @checked($row['is_included'] ?? true)
                                               onchange="recalcAmount({{ $i }})">
                                    </div>
                                </td>
                                <td><input type="number" name="items[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="0" step="0.5" class="form-control form-control-sm text-center" onchange="recalcAmount({{ $i }})"></td>
                                <td><input type="number" name="items[{{ $i }}][rate]" value="{{ $row['rate'] ?? 0 }}" step="0.01" class="form-control form-control-sm text-end" onchange="recalcAmount({{ $i }})"></td>
                                <td><input type="text" id="amount_{{ $i }}" class="form-control form-control-sm text-end fw-semibold" readonly value="{{ number_format(($row['quantity'] ?? 1) * ($row['rate'] ?? 0), 2) }}"></td>
                                <td><input name="items[{{ $i }}][note]" class="form-control form-control-sm" value="{{ $row['note'] ?? '' }}" placeholder="e.g. 'limit 3 visits'"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)" title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Included Services Total:</td>
                            <td class="text-end fw-bold" id="servicesTotal">৳ 0.00</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('[data-bs-target=\'#tab-basic\']').click()">
                    <i class="bi bi-arrow-left"></i> Back: Basic Info
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.querySelector('[data-bs-target=\'#tab-pricing\']').click()">
                    Next: Price Rules <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ──────── TAB 3: PRICE RULES (Bed-wise quick pricing + advanced matrix) ──────── --}}
    @php
        // Split existing rules into "bed-only" (quick rules) and everything else (advanced)
        $bedQuickRules = collect($rules)
            ->filter(fn ($r) => !empty($r['bed_type_id']) && empty($r['department_id']) && empty($r['duration_days']) && ($r['patient_category'] ?? 'ANY') === 'ANY')
            ->keyBy('bed_type_id');
        $advancedRules = collect($rules)->reject(fn ($r) =>
            !empty($r['bed_type_id']) && empty($r['department_id']) && empty($r['duration_days']) && ($r['patient_category'] ?? 'ANY') === 'ANY'
        )->values()->all();
    @endphp
    <div class="tab-pane fade" id="tab-pricing">

        {{-- ── A. BED-TYPE QUICK PRICING (linked to Bed Management master) ── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0"><i class="bi bi-house-gear text-info"></i> Bed-Type Quick Pricing
                        <small class="text-muted">(optional · leave blank to fall back to Base Price)</small>
                    </h6>
                    <small class="text-muted">Linked to <a href="{{ route('beds.index') }}" target="_blank">Bed Management →</a> · prices apply to every bed of that type.</small>
                </div>
                <a href="{{ route('bed-types.index') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-tags"></i> Manage Bed Types
                </a>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @php
                        $bedIcons = [
                            'General' => 'house', 'Cabin' => 'door-closed', 'ICU' => 'heart-pulse',
                            'NICU' => 'emoji-smile', 'CCU' => 'heart', 'HDU' => 'activity',
                            'Deluxe' => 'gem', 'VIP Suite' => 'stars', 'Isolation' => 'shield-fill-exclamation',
                        ];
                        $bedColours = [
                            'General' => 'secondary', 'Cabin' => 'primary', 'ICU' => 'danger',
                            'NICU' => 'info', 'CCU' => 'warning', 'HDU' => 'warning',
                            'Deluxe' => 'warning', 'VIP Suite' => 'danger', 'Isolation' => 'dark',
                        ];
                    @endphp
                    @foreach ($bedTypes as $b)
                        @php
                            $existing = $bedQuickRules->get($b->id);
                            $colour   = $bedColours[$b->name] ?? 'secondary';
                            $availableCount = $b->beds->where('status', 'available')->count();
                            $occupiedCount  = $b->beds->where('status', 'occupied')->count();
                        @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="card border h-100" style="border-color: var(--bs-{{ $colour }}) !important; border-width: 2px !important;">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-{{ $colour }} bg-opacity-15 text-{{ $colour }}">
                                            <i class="bi bi-{{ $bedIcons[$b->name] ?? 'house' }}"></i>
                                            {{ $b->name }}
                                        </span>
                                        <small class="text-muted">{{ $b->beds_count ?? 0 }} bed{{ ($b->beds_count ?? 0) === 1 ? '' : 's' }}</small>
                                    </div>

                                    @if ($b->base_rent > 0)
                                        <small class="text-muted d-block mt-1">
                                            Master rate: <strong>৳ {{ number_format((float) $b->base_rent, 0) }}</strong>/day
                                        </small>
                                    @endif

                                    <div class="d-flex gap-1 mt-1" style="font-size: 11px;">
                                        @if ($availableCount > 0)
                                            <span class="badge bg-success bg-opacity-15 text-success">{{ $availableCount }} avail</span>
                                        @endif
                                        @if ($occupiedCount > 0)
                                            <span class="badge bg-danger bg-opacity-15 text-danger">{{ $occupiedCount }} occ</span>
                                        @endif
                                    </div>

                                    <div class="input-group input-group-sm mt-2">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01" min="0"
                                            name="bed_quick[{{ $b->id }}]"
                                            value="{{ $existing['price'] ?? '' }}"
                                            class="form-control form-control-sm text-end"
                                            placeholder="Use base">
                                        <span class="input-group-text">/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="alert alert-info py-2 px-3 mb-0 mt-3 small">
                    <i class="bi bi-info-circle"></i>
                    <strong>How it works:</strong> Type a price next to any bed type and that price applies for enrolments on that bed.
                    Leave blank and the package's <strong>Base Price</strong> (sum of included services) is used instead.
                </div>
            </div>
        </div>

        {{-- ── A.2 ROOM-LEVEL OVERRIDE PRICING (NEW · uses package_bed_links) ── --}}
        @php
            $roomsByFloor = $rooms->groupBy(fn ($r) => optional($r->floor)->name ?? 'Unassigned');
            $roomClassColours = [
                'general' => 'secondary', 'semi_private' => 'info', 'private_cabin' => 'primary',
                'deluxe' => 'warning', 'vvip_suite' => 'danger', 'icu' => 'danger',
                'ccu' => 'warning', 'nicu' => 'info', 'isolation' => 'dark',
                'recovery' => 'success', 'maternity' => 'success',
            ];
            $totalOverrides = $bedLinks->whereNotNull('room_id')->count();
        @endphp

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">
                        <i class="bi bi-door-closed text-success"></i> Specific Room Override
                        @if ($totalOverrides > 0)
                            <span class="badge bg-success">{{ $totalOverrides }} set</span>
                        @endif
                        <small class="text-muted ms-1">(optional · most precise)</small>
                    </h6>
                    <small class="text-muted d-block">Premium room? Charge differently than the bed-type default — e.g. VVIP Suite or Deluxe.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllFloors()">
                        <i class="bi bi-arrows-collapse" id="toggleAllIcon"></i> <span id="toggleAllText">Collapse all</span>
                    </button>
                    <a href="{{ route('rooms.index') }}" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-arrow-up-right"></i> Rooms
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($rooms->isEmpty())
                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="bi bi-exclamation-triangle"></i> No rooms configured yet.
                        <a href="{{ route('rooms.create') }}" target="_blank" class="alert-link">Create rooms first →</a>
                    </div>
                @else
                    <div class="accordion accordion-flush" id="floorAccordion">
                        @foreach ($roomsByFloor as $floorName => $roomsOnFloor)
                            @php
                                $slug = 'floor-' . md5($floorName);
                                $floorHasOverride = $roomsOnFloor->contains(fn ($r) => $bedLinks->has($r->id));
                            @endphp
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ ! $floorHasOverride ? 'collapsed' : '' }} bg-light py-2 px-3 shadow-sm"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#{{ $slug }}">
                                        <div class="d-flex align-items-center w-100">
                                            <i class="bi bi-layers text-info me-2"></i>
                                            <strong>{{ $floorName }}</strong>
                                            <span class="badge bg-light text-dark border ms-2">{{ $roomsOnFloor->count() }} rooms</span>
                                            <span class="badge bg-info bg-opacity-15 text-info ms-1">{{ $roomsOnFloor->sum('capacity') }} beds</span>
                                            @if ($floorHasOverride)
                                                <span class="badge bg-success ms-auto me-2">
                                                    <i class="bi bi-check-lg"></i> {{ $roomsOnFloor->filter(fn ($r) => $bedLinks->has($r->id))->count() }} override(s)
                                                </span>
                                            @endif
                                        </div>
                                    </button>
                                </h2>
                                <div id="{{ $slug }}" class="accordion-collapse collapse {{ $floorHasOverride ? 'show' : '' }}" data-bs-parent="#floorAccordion">
                                    <div class="accordion-body p-2">
                                        <div class="row g-2">
                                            @foreach ($roomsOnFloor as $r)
                                                @php
                                                    $col = $roomClassColours[$r->room_class] ?? 'secondary';
                                                    $existingOverride = $bedLinks->get($r->id);
                                                    $hasOverride = $existingOverride && $existingOverride->override_price > 0;
                                                @endphp
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="card h-100 {{ $hasOverride ? 'border-success bg-success bg-opacity-5' : 'border-light bg-light' }}" style="border-width:1.5px;">
                                                        <div class="card-body py-2 px-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <strong class="d-flex align-items-center gap-1">
                                                                    <i class="bi bi-door-closed text-{{ $col }}"></i>
                                                                    {{ $r->room_no }}
                                                                </strong>
                                                                <span class="badge bg-{{ $col }} bg-opacity-15 text-{{ $col }}" style="font-size:10px;">
                                                                    {{ \App\Models\Room::CLASSES[$r->room_class] ?? '—' }}
                                                                </span>
                                                            </div>
                                                            <div class="d-flex justify-content-between small text-muted mb-1" style="font-size:11px;">
                                                                <span title="{{ optional($r->bedGroup)->name }}">{{ \Illuminate\Support\Str::limit(optional($r->bedGroup)->name, 14) }}</span>
                                                                <span>{{ $r->capacity }} bed{{ $r->capacity > 1 ? 's' : '' }}</span>
                                                            </div>
                                                            <small class="text-muted d-block mb-1" style="font-size:10px;">
                                                                Default: ৳ {{ number_format($r->room_rent, 0) }}/day
                                                            </small>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">৳</span>
                                                                <input type="number" step="0.01" min="0"
                                                                    name="room_override[{{ $r->id }}]"
                                                                    value="{{ optional($existingOverride)->override_price }}"
                                                                    class="form-control form-control-sm text-end"
                                                                    placeholder="—"
                                                                    title="Leave blank to use default room rent">
                                                                <span class="input-group-text">/day</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Pricing resolution explainer --}}
                <div class="card border-success bg-success bg-opacity-5 mt-3">
                    <div class="card-body py-2 px-3 small">
                        <strong class="text-success"><i class="bi bi-stack"></i> Pricing Resolution Order:</strong>
                        <div class="row mt-1 g-1">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-success">1</span>
                                    <small><strong>Room Override</strong> (this section)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-warning">2</span>
                                    <small><strong>Advanced Rules</strong> (below)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-info">3</span>
                                    <small><strong>Bed-Type Quick</strong> (above)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-secondary">4</span>
                                    <small><strong>Package Base Price</strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── B. ADVANCED PRICE RULES ── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0"><i class="bi bi-tag text-warning"></i> Advanced Price Rules
                        <small class="text-muted">(combine bed × department × duration × patient category)</small>
                    </h6>
                </div>
                <button type="button" class="btn btn-warning btn-sm" onclick="addRuleRow()">
                    <i class="bi bi-plus-lg"></i> Add Advanced Rule
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bed Type</th>
                            <th>Department</th>
                            <th class="text-center">Duration (days)</th>
                            <th>Patient Category</th>
                            <th class="text-end">Price ৳</th>
                            <th>Valid From</th>
                            <th>Valid To</th>
                            <th>Notes</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="ruleRows">
                        @foreach ($advancedRules as $i => $row)
                            <tr>
                                <td>
                                    <select name="rules[{{ $i }}][bed_type_id]" class="form-select form-select-sm">
                                        <option value="">— Any —</option>
                                        @foreach ($bedTypes as $b)
                                            <option value="{{ $b->id }}" @selected(($row['bed_type_id'] ?? null) == $b->id)>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="rules[{{ $i }}][department_id]" class="form-select form-select-sm">
                                        <option value="">— Any —</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->id }}" @selected(($row['department_id'] ?? null) == $d->id)>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="rules[{{ $i }}][duration_days]" value="{{ $row['duration_days'] ?? '' }}" min="1" max="365" class="form-control form-control-sm text-center"></td>
                                <td>
                                    <select name="rules[{{ $i }}][patient_category]" class="form-select form-select-sm">
                                        @foreach (['ANY' => 'Any', 'GENERAL' => 'General', 'CORPORATE' => 'Corporate', 'INSURANCE' => 'Insurance', 'STAFF' => 'Staff'] as $v => $l)
                                            <option value="{{ $v }}" @selected(($row['patient_category'] ?? 'ANY') === $v)>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="rules[{{ $i }}][price]" value="{{ $row['price'] ?? '' }}" step="0.01" min="0" class="form-control form-control-sm text-end fw-semibold" required></td>
                                <td><input type="date" name="rules[{{ $i }}][valid_from]" value="{{ $row['valid_from'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input type="date" name="rules[{{ $i }}][valid_to]" value="{{ $row['valid_to'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td><input name="rules[{{ $i }}][notes]" value="{{ $row['notes'] ?? '' }}" class="form-control form-control-sm"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)" title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if (empty($advancedRules))
                            <tr id="noRulesRow"><td colspan="9" class="text-center text-muted py-3">
                                <i class="bi bi-info-circle"></i> No advanced rules — bed-wise quick prices above are sufficient. Click "Add Advanced Rule" for department × duration × category combinations.
                            </td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('[data-bs-target=\'#tab-services\']').click()">
                    <i class="bi bi-arrow-left"></i> Back: Services
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.querySelector('[data-bs-target=\'#tab-summary\']').click()">
                    Next: Review &amp; Save <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ──────── TAB 4: SUMMARY ──────── --}}
    <div class="tab-pane fade" id="tab-summary">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-check2-circle text-success"></i> Review &amp; Save</h6>
                <small class="text-muted">Final summary before saving</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle fs-4"></i>
                    <div>
                        <strong>Almost done!</strong>
                        Review what you've configured and click <strong>Save Package</strong> in the action bar at the bottom.
                        You can edit any field later.
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 p-3">
                            <h6 class="text-primary mb-2"><i class="bi bi-info-circle"></i> Basic Info</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li>📦 Code: <strong><span id="summaryCode">—</span></strong></li>
                                <li>📋 Type: <strong><span id="summaryType">—</span></strong></li>
                                <li>🏥 Department: <strong><span id="summaryDept">—</span></strong></li>
                                <li>🛏 Bed Type: <strong><span id="summaryBed">—</span></strong></li>
                                <li>⏱ Validity: <strong><span id="summaryValidity">—</span> days</strong></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0 p-3">
                            <h6 class="text-success mb-2"><i class="bi bi-list-check"></i> Services &amp; Pricing</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li>✅ Services configured: <strong><span id="summaryServices">0</span></strong></li>
                                <li>🏷 Price rules: <strong><span id="summaryRules">0</span></strong></li>
                                <li>💰 Base Total: <strong>৳ <span id="summaryTotal">0.00</span></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .package-tabs .nav-link { color: #6b7280; font-weight: 500; border-radius: 8px; margin: 0 4px; }
    .package-tabs .nav-link.active { background: #4361ee; color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
    .package-tabs .nav-link .badge { background-color: rgba(255,255,255,0.3) !important; }
    .package-tabs .nav-link:not(.active) .badge { background-color: #4361ee !important; color: #fff; }
    .package-tab-content .card { border-radius: 12px; }
    .package-tab-content .form-label { font-size: .85rem; color: #374151; }
    .package-tab-content thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .3px; color: #4b5563; }
</style>
@endpush

@push('scripts')
<script>
    let serviceIndex = {{ $servicesCount ?: 1 }};
    let ruleIndex    = {{ count($advancedRules) }};

    @php
        $chargesJsArray = $charges->map(function ($c) {
            return [
                'id'    => $c->id,
                'code'  => 'CHG-' . str_pad($c->id, 4, '0', STR_PAD_LEFT),
                'name'  => $c->charge_name,
                'price' => (float) $c->standard_charge,
                'type'  => optional($c->chargeType)->name ?: 'Uncategorised',
                'unit'  => optional($c->uniteType)->name,
            ];
        })->values();
    @endphp
    const chargeOptions  = @json($chargesJsArray);
    const bedTypeOptions = @json($bedTypes->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->values());
    const deptOptions    = @json($departments->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])->values());

    // Build grouped <optgroup> options for new rows using charges grouped by charge_type
    const chargesGroupedHtml = (() => {
        const groups = {};
        chargeOptions.forEach(c => { (groups[c.type] = groups[c.type] || []).push(c); });
        return Object.entries(groups).map(([type, list]) => {
            const inner = list.map(c =>
                `<option value="${c.id}" data-price="${c.price}" data-type="${type}" data-search="${(c.code + ' ' + c.name + ' ' + type).toLowerCase()}">` +
                `${c.code} · ${c.name} · ৳${c.price.toLocaleString()} (${c.unit || ''})</option>`
            ).join('');
            return `<optgroup label="${type} (${list.length})">${inner}</optgroup>`;
        }).join('');
    })();

    function addServiceRow(presetId = null, presetCode = '', presetName = '', presetPrice = 0) {
        const i = serviceIndex++;
        const html = `
            <tr data-row-index="${i}">
                <td class="text-center text-muted">${i + 1}</td>
                <td><select name="items[${i}][charge_id]" class="form-select form-select-sm" onchange="autoFillRate(this, ${i})">
                    <option value="">-- Pick a charge --</option>${chargesGroupedHtml}
                </select></td>
                <td class="text-center">
                    <input type="hidden" name="items[${i}][is_included]" value="0">
                    <div class="form-check form-switch d-inline-flex">
                        <input type="checkbox" name="items[${i}][is_included]" value="1" class="form-check-input" checked onchange="recalcAmount(${i})">
                    </div>
                </td>
                <td><input type="number" name="items[${i}][quantity]" value="1" min="0" step="0.5" class="form-control form-control-sm text-center" onchange="recalcAmount(${i})"></td>
                <td><input type="number" name="items[${i}][rate]" value="${presetPrice || 0}" step="0.01" class="form-control form-control-sm text-end" onchange="recalcAmount(${i})"></td>
                <td><input type="text" id="amount_${i}" class="form-control form-control-sm text-end fw-semibold" readonly value="${presetPrice ? presetPrice.toFixed(2) : '0.00'}"></td>
                <td><input name="items[${i}][note]" class="form-control form-control-sm" placeholder="Notes"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
            </tr>`;
        document.getElementById('serviceRows').insertAdjacentHTML('beforeend', html);
        if (presetId) {
            const sel = document.querySelector(`[name="items[${i}][charge_id]"]`);
            if (sel) { sel.value = presetId; }
        }
        updateBadges();
        recalcTotal();
    }

    /** Floor accordion bulk-toggle */
    function toggleAllFloors() {
        const items = document.querySelectorAll('#floorAccordion .accordion-collapse');
        if (!items.length) return;
        const allOpen = Array.from(items).every(el => el.classList.contains('show'));
        const icon = document.getElementById('toggleAllIcon');
        const text = document.getElementById('toggleAllText');
        items.forEach(el => {
            const inst = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            if (allOpen) inst.hide(); else inst.show();
        });
        if (icon && text) {
            if (allOpen) { icon.className = 'bi bi-arrows-expand'; text.textContent = 'Expand all'; }
            else         { icon.className = 'bi bi-arrows-collapse'; text.textContent = 'Collapse all'; }
        }
    }

    /** Called from the Quick-add dropdown chips: instantly add a charge row to the table. */
    function addChargeRow(id, code, name, price) {
        addServiceRow(id, code, name, price);
        const target = document.querySelector('[data-bs-target="#tab-services"]');
        if (target && !target.classList.contains('active')) target.click();
    }
    // Backward-compat alias (in case any legacy quick-add still calls it)
    function addServiceByCatalog(id, code, name, price) { addChargeRow(id, code, name, price); }

    /** Live search across the in-table service rows. */
    (function () {
        const search = document.getElementById('srvSearch');
        if (!search) return;
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase().trim();
            document.querySelectorAll('#serviceRows tr').forEach(tr => {
                const sel = tr.querySelector('select');
                const opt = sel?.options[sel.selectedIndex];
                const noteEl = tr.querySelector('input[name*="[note]"]');
                const blob = (
                    (opt?.textContent || '') + ' ' + (noteEl?.value || '')
                ).toLowerCase();
                tr.style.display = !q || blob.includes(q) ? '' : 'none';
            });
        });
    })();
    function addRuleRow() {
        const noRules = document.getElementById('noRulesRow');
        if (noRules) noRules.remove();
        const i = ruleIndex++;
        const bedOpts  = bedTypeOptions.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
        const deptOpts = deptOptions.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
        const html = `
            <tr>
                <td><select name="rules[${i}][bed_type_id]" class="form-select form-select-sm"><option value="">— Any —</option>${bedOpts}</select></td>
                <td><select name="rules[${i}][department_id]" class="form-select form-select-sm"><option value="">— Any —</option>${deptOpts}</select></td>
                <td><input type="number" name="rules[${i}][duration_days]" min="1" max="365" class="form-control form-control-sm text-center"></td>
                <td><select name="rules[${i}][patient_category]" class="form-select form-select-sm"><option value="ANY">Any</option><option value="GENERAL">General</option><option value="CORPORATE">Corporate</option><option value="INSURANCE">Insurance</option><option value="STAFF">Staff</option></select></td>
                <td><input type="number" name="rules[${i}][price]" step="0.01" min="0" class="form-control form-control-sm text-end fw-semibold" required></td>
                <td><input type="date" name="rules[${i}][valid_from]" class="form-control form-control-sm"></td>
                <td><input type="date" name="rules[${i}][valid_to]" class="form-control form-control-sm"></td>
                <td><input name="rules[${i}][notes]" class="form-control form-control-sm"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
            </tr>`;
        document.getElementById('ruleRows').insertAdjacentHTML('beforeend', html);
        updateBadges();
    }
    function removeRow(btn) {
        btn.closest('tr').remove();
        updateBadges();
        recalcTotal();
    }
    function autoFillRate(sel, i) {
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt.dataset.price || 0);
        const row = sel.closest('tr');
        const rateInput = row.querySelector(`[name="items[${i}][rate]"]`);
        if (rateInput && (!rateInput.value || rateInput.value == '0')) rateInput.value = price;
        recalcAmount(i);
    }
    function recalcAmount(i) {
        const qty  = parseFloat(document.querySelector(`[name="items[${i}][quantity]"]`)?.value || 0);
        const rate = parseFloat(document.querySelector(`[name="items[${i}][rate]"]`)?.value || 0);
        const inc  = document.querySelector(`[name="items[${i}][is_included]"]:checked`)?.value === '1';
        const amt  = inc ? qty * rate : 0;
        const amtField = document.getElementById(`amount_${i}`);
        if (amtField) amtField.value = amt.toFixed(2);
        recalcTotal();
    }
    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('[id^="amount_"]').forEach(el => total += parseFloat(el.value || 0));
        const t = document.getElementById('servicesTotal');
        if (t) t.textContent = '৳ ' + total.toFixed(2);
        const s = document.getElementById('summaryTotal');
        if (s) s.textContent = total.toFixed(2);
    }
    function updateBadges() {
        const svc = document.querySelectorAll('#serviceRows tr').length;
        const rul = document.querySelectorAll('#ruleRows tr:not(#noRulesRow)').length;
        document.getElementById('serviceCountBadge').textContent = svc;
        document.getElementById('ruleCountBadge').textContent    = rul;
        const ss = document.getElementById('summaryServices'); if (ss) ss.textContent = svc;
        const sr = document.getElementById('summaryRules');    if (sr) sr.textContent = rul;
    }
    document.querySelectorAll('[data-bs-target="#tab-summary"]').forEach(b => b.addEventListener('click', () => {
        const c = document.querySelector('[name=code]');           if (c) document.getElementById('summaryCode').textContent = c.value || '(auto-generated)';
        const t = document.querySelector('[name=package_type]');   if (t) document.getElementById('summaryType').textContent = t.value || '—';
        const dept = document.querySelector('[name=department_id]'); if (dept) document.getElementById('summaryDept').textContent = dept.value ? dept.options[dept.selectedIndex].text : '—';
        const bed = document.querySelector('[name=bed_type_id]');  if (bed) document.getElementById('summaryBed').textContent = bed.value ? bed.options[bed.selectedIndex].text : '—';
        const v = document.querySelector('[name=validity_days]');  if (v) document.getElementById('summaryValidity').textContent = v.value || '—';
        updateBadges();
        recalcTotal();
    }));
    document.addEventListener('DOMContentLoaded', () => { recalcTotal(); updateBadges(); });
</script>
@endpush
