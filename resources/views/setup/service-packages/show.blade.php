@extends('backend.layouts.master')
@section('title','Package — ' . $package->code)

@section('content')
<div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- ─────────── Hero header ─────────── --}}
    <div class="card border-primary-subtle mb-3">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-info">{{ $package->package_type }}</span>
                        <span class="badge {{ $package->status_badge_class }}">{{ $package->status }}</span>
                        @if($package->requires_approval)
                            <span class="badge bg-warning text-dark">Approval Required</span>
                        @endif
                        @if($package->admission_type)
                            <span class="badge bg-secondary-subtle text-dark border">{{ $package->admission_type }}</span>
                        @endif
                        @if($package->patient_category)
                            <span class="badge bg-light text-dark border">{{ $package->patient_category }}</span>
                        @endif
                    </div>
                    <h1 class="app-page-title mb-1">{{ $package->name }}</h1>
                    <div class="text-muted small"><code>{{ $package->code }}</code>
                        @if($package->department) · {{ $package->department->name }}@endif
                        @if($package->duration_days) · {{ $package->duration_days }} day(s)@endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="display-6 text-primary fw-bold">৳{{ number_format((float) $package->base_price, 0) }}</div>
                    <div class="small text-muted">base price</div>
                    <div class="d-flex gap-2 mt-2">
                        @can('service_packages_edit')
                            <a href="{{ route('setup.service-packages.edit', $package) }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        @endcan
                        <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick stats strip --}}
            @php
                $items          = $package->items;
                $chargeLinked   = $items->where('master_type', 'charge')->count();
                $totalItems     = $items->count();
                $bedVariants    = $package->bedPrices->count();
                $applications   = $package->applications->count();
                $revenuePosted  = $package->patientCharges->sum('net_amount');
            @endphp
            <div class="row g-2 mt-3">
                <div class="col-md col-6">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted">Items linked to Charge</div>
                        <div class="fs-5 fw-semibold">{{ $chargeLinked }}/{{ $totalItems }}
                            <i class="bi bi-receipt text-info ms-1"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted">Bed-type Variants</div>
                        <div class="fs-5 fw-semibold">{{ $bedVariants }}
                            <i class="bi bi-hospital text-primary ms-1"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted">IPD Applications</div>
                        <div class="fs-5 fw-semibold">{{ $applications }}
                            <i class="bi bi-people text-success ms-1"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md col-6">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted">Revenue Posted</div>
                        <div class="fs-5 fw-semibold text-success">৳{{ number_format((float) $revenuePosted, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Tabs ─────────── --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button">
            <i class="bi bi-info-circle"></i> Overview
        </button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-items" type="button">
            <i class="bi bi-receipt"></i> Charges &amp; Items <span class="badge bg-info ms-1">{{ $totalItems }}</span>
        </button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bed" type="button">
            <i class="bi bi-hospital"></i> Bed Pricing <span class="badge bg-primary ms-1">{{ $bedVariants }}</span>
        </button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-apps" type="button">
            <i class="bi bi-people"></i> Applications <span class="badge bg-success ms-1">{{ $applications }}</span>
        </button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-billing" type="button">
            <i class="bi bi-cash-coin"></i> Billing Trail <span class="badge bg-warning ms-1">{{ $package->patientCharges->count() }}</span>
        </button></li>
    </ul>

    <div class="tab-content">

        {{-- ─────────── Overview ─────────── --}}
        <div class="tab-pane fade show active" id="tab-overview">
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="card mb-3">
                        <div class="card-header"><strong>Details</strong></div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 small text-muted">Department</dt>
                                <dd class="col-sm-8">{{ optional($package->department)->name ?? '—' }}</dd>
                                <dt class="col-sm-4 small text-muted">Admission Type</dt>
                                <dd class="col-sm-8">{{ $package->admission_type ?? '—' }}</dd>
                                <dt class="col-sm-4 small text-muted">Default Bed Type</dt>
                                <dd class="col-sm-8">
                                    @if($package->bedType)
                                        <span class="badge bg-light text-dark border">{{ $package->bedType->name }}</span>
                                        @if($package->bedType->is_icu)
                                            <span class="badge bg-danger">{{ $package->bedType->icu_type ?? 'ICU' }}</span>
                                        @endif
                                    @else — @endif
                                </dd>
                                <dt class="col-sm-4 small text-muted">Surgery Type</dt>
                                <dd class="col-sm-8">{{ optional($package->surgeryType)->name ?? '—' }}</dd>
                                <dt class="col-sm-4 small text-muted">Duration</dt>
                                <dd class="col-sm-8">{{ $package->duration_days ? $package->duration_days . ' day(s)' : '—' }}</dd>
                                <dt class="col-sm-4 small text-muted">Patient Category</dt>
                                <dd class="col-sm-8">{{ $package->patient_category ?? 'Any' }}</dd>
                                @if($package->approval_role)
                                    <dt class="col-sm-4 small text-muted">Approval Role</dt>
                                    <dd class="col-sm-8">{{ $package->approval_role }}</dd>
                                @endif
                                <dt class="col-sm-4 small text-muted">Remarks</dt>
                                <dd class="col-sm-8">{{ $package->remarks ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-3 border-success-subtle">
                        <div class="card-header bg-success-subtle"><strong><i class="bi bi-check2-square text-success"></i> Included</strong></div>
                        <div class="card-body">
                            @if($package->included_services_text)
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $package->included_services_text }}</p>
                            @else <span class="text-muted">Not described.</span> @endif
                        </div>
                    </div>

                    <div class="card mb-3 border-danger-subtle">
                        <div class="card-header bg-danger-subtle"><strong><i class="bi bi-x-square text-danger"></i> Excluded</strong></div>
                        <div class="card-body">
                            @if($package->excluded_services_text)
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $package->excluded_services_text }}</p>
                            @else <span class="text-muted">Not described.</span> @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    {{-- Default-bed snapshot --}}
                    @if($package->bedType)
                        @php $defBedAvail = $bedAvailability[$package->bedType->id] ?? null; @endphp
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white">
                                <strong><i class="bi bi-hospital"></i> Bed Capacity (default type)</strong>
                            </div>
                            <div class="card-body">
                                <div class="fs-5 fw-semibold">{{ $package->bedType->name }}</div>
                                @if($defBedAvail)
                                    <div class="d-flex gap-2 mt-2">
                                        <span class="badge bg-light text-dark border fs-6">{{ (int) $defBedAvail->total }} total</span>
                                        <span class="badge bg-success fs-6">{{ (int) $defBedAvail->available }} free</span>
                                    </div>
                                    @php $pct = $defBedAvail->total > 0 ? round((1 - $defBedAvail->available / $defBedAvail->total) * 100) : 0; @endphp
                                    <div class="progress mt-2" style="height:8px;">
                                        <div class="progress-bar bg-{{ $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $pct }}% occupancy across hospital</small>
                                @else
                                    <div class="text-muted small">No beds configured for this type yet.</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header"><strong>Audit</strong></div>
                        <div class="card-body small">
                            <div><strong>Created:</strong> {{ $package->created_at?->format('Y-m-d H:i') }}
                                @if($package->createdBy) by {{ $package->createdBy->name ?? '#' . $package->created_by }}@endif
                            </div>
                            <div><strong>Updated:</strong> {{ $package->updated_at?->format('Y-m-d H:i') }}
                                @if($package->updatedBy) by {{ $package->updatedBy->name ?? '#' . $package->updated_by }}@endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────── Charges & Items ─────────── --}}
        <div class="tab-pane fade" id="tab-items">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-receipt text-info"></i> Items linked to masters
                        <small class="text-muted ms-2">Charge = billable line. Other masters = informational.</small>
                    </strong>
                    <div class="small">
                        <span class="badge bg-success-subtle text-success border">{{ $chargeLinked }} charge-linked</span>
                        <span class="badge bg-light text-dark border">{{ $totalItems - $chargeLinked }} free-text</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Category</th>
                                <th>Item</th>
                                <th>Linked To</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($package->items as $i => $item)
                                @php
                                    $linked   = $item->master_type && $item->master_id ? $item->resolveMaster() : null;
                                    $isCharge = $item->master_type === 'charge';
                                @endphp
                                <tr class="{{ $isCharge ? 'table-success' : '' }}">
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $item->item_category }}</span></td>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td>
                                        @if($item->master_type)
                                            @if($isCharge)
                                                <span class="badge bg-success"><i class="bi bi-receipt"></i> Charge</span>
                                            @else
                                                <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $item->master_type)) }}</span>
                                            @endif
                                            <div class="small mt-1">
                                                @if($linked)
                                                    <i class="bi bi-link-45deg text-muted"></i> {{ $linked->name ?? $linked->charge_name ?? '#' . $item->master_id }}
                                                @else
                                                    <span class="text-warning">#{{ $item->master_id }} (deleted)</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">— free text —</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($item->included_qty, 2), '0'), '.') }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                    <td class="small text-muted">{{ $item->notes ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No items defined yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($totalItems > 0 && $chargeLinked === 0)
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Tip:</strong> None of this package's items are linked to <strong>Charge master</strong>.
                    Linking items to charges lets the bill engine resolve real billable codes per line.
                    Edit the package and use the <em>Link To = Charge</em> option in each row.
                </div>
            @endif
        </div>

        {{-- ─────────── Bed Pricing ─────────── --}}
        <div class="tab-pane fade" id="tab-bed">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="bi bi-hospital text-primary"></i> Bed-type price matrix</strong>
                    <small class="text-muted ms-2">Live bed counts from the bed master.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bed Type</th>
                                <th class="text-end">Variant Price</th>
                                <th class="text-end">vs Base Price</th>
                                <th>Hospital Capacity</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Default bed type row --}}
                            @if($package->bedType)
                                @php
                                    $defAvail = $bedAvailability[$package->bedType->id] ?? null;
                                @endphp
                                <tr class="table-primary">
                                    <td>
                                        <strong>{{ $package->bedType->name }}</strong>
                                        <span class="badge bg-primary ms-1">Default</span>
                                        @if($package->bedType->is_icu)
                                            <span class="badge bg-danger ms-1">{{ $package->bedType->icu_type ?? 'ICU' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end"><strong>৳{{ number_format((float) $package->base_price, 0) }}</strong>
                                        <div class="small text-muted">(uses Base)</div>
                                    </td>
                                    <td class="text-end text-muted small">—</td>
                                    <td>
                                        @if($defAvail)
                                            <span class="badge bg-light text-dark border">{{ (int) $defAvail->total }} total</span>
                                            <span class="badge bg-success">{{ (int) $defAvail->available }} free</span>
                                        @else
                                            <span class="text-muted small">no beds</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @forelse($package->bedPrices as $bp)
                                @php
                                    $variantAvail = $bedAvailability[$bp->bed_type_id] ?? null;
                                    $diff = (float) $bp->price - (float) $package->base_price;
                                @endphp
                                @continue($bp->bed_type_id === $package->bed_type_id) {{-- skip default duplicate --}}
                                <tr>
                                    <td>
                                        {{ optional($bp->bedType)->name ?? '#' . $bp->bed_type_id }}
                                        @if($bp->bedType?->is_icu)
                                            <span class="badge bg-danger ms-1">{{ $bp->bedType->icu_type ?? 'ICU' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end"><strong>৳{{ number_format((float) $bp->price, 0) }}</strong></td>
                                    <td class="text-end">
                                        @if($diff > 0)
                                            <span class="badge bg-warning text-dark">+৳{{ number_format(abs($diff), 0) }}</span>
                                        @elseif($diff < 0)
                                            <span class="badge bg-success">−৳{{ number_format(abs($diff), 0) }}</span>
                                        @else
                                            <span class="text-muted small">same</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($variantAvail)
                                            <span class="badge bg-light text-dark border">{{ (int) $variantAvail->total }} total</span>
                                            <span class="badge bg-success">{{ (int) $variantAvail->available }} free</span>
                                        @else
                                            <span class="text-muted small">no beds</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                @if($bedVariants === 0)
                                    <tr><td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-info-circle"></i>
                                        No bed-type variants defined. Every bed type will use the Base Price.
                                    </td></tr>
                                @endif
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ─────────── Applications ─────────── --}}
        <div class="tab-pane fade" id="tab-apps">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-people text-success"></i> IPD attachments using this package</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>IPD</th>
                                <th>Bed</th>
                                <th class="text-end">Agreed Price</th>
                                <th>Status</th>
                                <th>Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($package->applications as $att)
                                <tr>
                                    <td>{{ $att->ipdAdmission?->patient?->patient_name ?? '—' }}
                                        @if($att->ipdAdmission?->patient?->mrn)
                                            <div class="small text-muted">{{ $att->ipdAdmission->patient->mrn }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($att->ipd_admission_id)
                                            <a href="{{ route('ipd-patients.show', $att->ipd_admission_id) }}">{{ $att->ipdAdmission?->ipd_no ?? '#'.$att->ipd_admission_id }}</a>
                                        @else — @endif
                                    </td>
                                    <td>
                                        @if($att->bedAllocation?->bed)
                                            <span class="badge bg-info">{{ $att->bedAllocation->bed->name }}</span>
                                        @else <span class="text-muted small">—</span> @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>৳{{ number_format((float) ($att->price_override ?? $att->agreed_price), 0) }}</strong>
                                    </td>
                                    <td><span class="badge {{ $att->status_badge_class ?? 'bg-secondary' }}">{{ $att->status }}</span></td>
                                    <td class="small text-muted">{{ optional($att->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Not applied to any IPD admission yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ─────────── Billing Trail ─────────── --}}
        <div class="tab-pane fade" id="tab-billing">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-cash-coin text-warning"></i> Patient charges posted</strong>
                    <span class="badge bg-success fs-6">৳{{ number_format((float) $revenuePosted, 0) }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Module</th>
                                <th>Charge Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($package->patientCharges as $pc)
                                <tr>
                                    <td class="small">{{ optional($pc->date)->format('Y-m-d H:i') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $pc->charge_module }}</span></td>
                                    <td>{{ $pc->charge_item }}
                                        @if($pc->notes)<div class="small text-muted">{{ $pc->notes }}</div>@endif
                                    </td>
                                    <td class="text-end">{{ $pc->quantity }}</td>
                                    <td class="text-end">৳{{ number_format((float) $pc->unit_price, 0) }}</td>
                                    <td class="text-end"><strong>৳{{ number_format((float) $pc->net_amount, 0) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $pc->is_paid ? 'success' : 'warning text-dark' }}">
                                            {{ $pc->is_paid ? 'Paid' : ucfirst($pc->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No charges posted against this package yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
