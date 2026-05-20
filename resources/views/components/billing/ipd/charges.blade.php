<div class="tab-pane fade show active" id="charges" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">All Charges List</h5>
                    <div class="text-muted small" id="ipdbChargesRangeInfoTop">
                        Showing 1-{{ $totalCharges }} of {{ number_format($totalCharges) }} transactions
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="ipdb-pill ipdb-pill-soft-warning fw-bold" style="font-size:1.2rem;">
                        Total Charges: &#2547;&nbsp;{{ $fmt($charges->sum('net_amount')) }}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm ipdb-btn-add" data-bs-toggle="modal"
                        data-bs-target="#ipdbAddChargesModal">
                        <i class="bi bi-plus-lg me-1"></i> Add New Charges
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table ipdb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Date & Time</th>
                            <th>Category</th>
                            <th>Charges Name</th>
                            <th>Qty / Days</th>
                            <th>Price</th>
                            <th>Vat</th>
                            <th>Tax</th>
                            <th>Net Amount</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($charges as $i => $c)
                            @php
                                $catName =
                                    $c->charge?->chargeCategory?->name ??
                                    ucfirst(str_replace('_', ' ', $c->charge_module ?? 'Charge'));
                                $catColor = $pillColor($catName);
                                $chargeName = $c->charge_item ?: $c->charge?->charge_name ?? '—';
                                $sub = $c->remarks ?: ($c->notes ?: $c->charge?->description ?? '');
                                $chargeRowData = [
                                    'id' => $c->id,
                                    'charge_id' => $c->charge_id,
                                    'doctor_id' => $c->doctor_id,
                                    'department_id' => $c->department_id,
                                    'quantity' => $c->quantity,
                                    'unit_price' => (float) $c->unit_price,
                                    'vat' => (float) $c->vat,
                                    'tax' => (float) $c->tax,
                                    'net_amount' => (float) $c->net_amount,
                                    'notes' => $c->notes ?: $c->remarks,
                                    'date_input' => $c->date ? $c->date->format('Y-m-d\TH:i') : '',
                                    'date_display' => $c->date ? $c->date->format('d M Y, h:i A') : '—',
                                    'category' => $catName,
                                    'charge_name' => $chargeName,
                                    'update_url' => route('ipd-patients.charges.update', [$ipdPatient->id, $c->id]),
                                    'destroy_url' => route('ipd-patients.charges.destroy', [$ipdPatient->id, $c->id]),
                                ];
                            @endphp
                            <tr class="ipdb-charge-row" data-index="{{ $i }}"
                                data-charge="{{ base64_encode(json_encode($chargeRowData)) }}">
                                <td class="ps-4">
                                    @if ($c->date)
                                        <div class="fw-semibold">
                                            {{ $c->date->format('d/m/Y') }}</div>
                                        <div class="text-muted small">
                                            {{ $c->date->format('h:i A') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="ipdb-pill ipdb-pill-soft-{{ $catColor }}">
                                        {{ $catName }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ipdb-charges-name">{{ $chargeName }}</div>
                                    @if ($sub)
                                        <div class="ipdb-charges-sub">
                                            {{ \Illuminate\Support\Str::limit($sub, 60) }}</div>
                                    @endif
                                </td>
                                <td>{{ $c->quantity }}</td>
                                <td>{{ $fmt($c->unit_price) }}</td>
                                <td>{{ $fmt((float) $c->vat) }}</td>
                                <td>{{ $fmt((float) $c->tax) }}</td>
                                <td class="fw-bold">{{ $fmt($c->net_amount) }}</td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="ipdb-action-icon view" data-action="view"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="ipdb-action-icon edit" data-action="edit"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="ipdb-action-icon delete" data-action="delete"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No charges added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3 small text-muted">
                <span class="d-inline-flex align-items-center gap-2">
                    <span>Rows per page:</span>
                    <select id="ipdbChargesPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="ipdbChargesRangeInfo"></span>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="ipdbChargesPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Add Charges Modal --}}
<div class="modal fade" id="ipdbAddChargesModal" tabindex="-1" aria-labelledby="ipdbAddChargesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('ipd-patients.charges.store', $ipdPatient->id) }}" method="POST"
                id="ipdbAddChargesForm">
                @csrf
                <input type="hidden" name="source" value="billing">
                <div class="modal-header">
                    <h5 class="modal-title" id="ipdbAddChargesModalLabel">
                        Add Charges — {{ $ipdPatient->patient->patient_name ?? 'Patient' }}
                        <span class="text-muted small ms-2">({{ $ipdPatient->ipd_no }})</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Shared Doctor & Department --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="ipdb_charge_doctor_id" class="form-label">Doctor</label>
                            <select name="doctor_id" id="ipdb_charge_doctor_id" class="form-select">
                                <option value="">-- Select Doctor --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected($ipdPatient->doctor_id == $doctor->id)>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ipdb_charge_department_id" class="form-label">Department</label>
                            <select name="department_id" id="ipdb_charge_department_id" class="form-select">
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected($ipdPatient->department_id == $dept->id)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Charge Rows Table --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="ipdbChargesTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:260px">Charge Item <span class="text-danger">*</span></th>
                                    <th style="min-width:180px">Date <span class="text-danger">*</span></th>
                                    <th style="width:80px">Qty <span class="text-danger">*</span></th>
                                    <th style="width:110px">Unit Price</th>
                                    <th style="width:90px">Tax (%)</th>
                                    <th style="width:120px">Net Amount</th>
                                    <th style="min-width:160px">Notes</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="ipdbChargeRows">
                                <tr class="ipdb-charge-input-row" data-index="0">
                                    <td>
                                        <select name="items[0][charge_id]"
                                            class="form-select form-select-sm ipdb-charge-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach ($availableCharges as $charge)
                                                <option value="{{ $charge->id }}"
                                                    data-price="{{ $charge->standard_charge }}"
                                                    data-tax="{{ $charge->tax ?? 0 }}"
                                                    data-name="{{ $charge->charge_name }}">
                                                    {{ $charge->charge_name }}
                                                    ({{ optional($charge->chargeCategory)->name }})
                                                    - &#2547; {{ number_format($charge->standard_charge, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="datetime-local" name="items[0][date]"
                                            class="form-control form-control-sm"
                                            value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]"
                                            class="form-control form-control-sm ipdb-qty-input" value="1"
                                            min="1" required>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light ipdb-unit-price" readonly
                                            value="0.00">
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light ipdb-tax-rate" readonly
                                            value="0.00">
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control form-control-sm bg-light fw-semibold ipdb-row-net"
                                            readonly value="0.00">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][notes]"
                                            class="form-control form-control-sm" placeholder="Optional">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger ipdb-remove-row"
                                            title="Remove" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                    <td>
                                        <input type="text" id="ipdbGrandTotal"
                                            class="form-control form-control-sm bg-light fw-bold" readonly
                                            value="0.00">
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="ipdbAddChargeRowBtn">
                            <i class="bi bi-plus-circle"></i> Add Row
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save All Charges</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .ipdb-charge-row .ipdb-action-icon {
        cursor: pointer;
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
</style>

{{-- View Charge Modal --}}
<div class="modal fade ipdb-view-modal" id="ipdbViewChargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="ipdb-view-header px-4 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ipdb-view-header-icon">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-bold">Charge Details</h5>
                            <div class="text-white-50 small">Full breakdown of this charge entry</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 ipdb-view-body">
                <div class="ipdb-view-amount-card mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="ipdb-view-eyebrow mb-1">Net Amount</div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="ipdb-view-currency">&#2547;</span>
                                <span class="ipdb-view-amount" id="ipdbViewNetAmount">—</span>
                            </div>
                        </div>
                        <div class="text-sm-end">
                            <div class="ipdb-view-eyebrow mb-1">
                                <i class="bi bi-clock me-1"></i> Date &amp; Time
                            </div>
                            <div class="fw-semibold text-dark" id="ipdbViewDate">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-info-circle me-1"></i> Charge Information
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Category</div>
                            <span class="ipdb-pill ipdb-pill-soft-info" id="ipdbViewCategory">—</span>
                        </div>
                        <div class="col-sm-5">
                            <div class="ipdb-view-label">Charge Name</div>
                            <div class="ipdb-view-value" id="ipdbViewChargeName">—</div>
                        </div>
                        <div class="col-sm-3">
                            <div class="ipdb-view-label">Quantity</div>
                            <div class="ipdb-view-value" id="ipdbViewQty">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-calculator me-1"></i> Pricing Breakdown
                    </div>
                    <div class="ipdb-view-breakdown">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Unit Price</span>
                            <span class="fw-semibold text-dark">&#2547;&nbsp;<span
                                    id="ipdbViewUnitPrice">—</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">VAT</span>
                            <span class="fw-semibold text-dark">&#2547;&nbsp;<span id="ipdbViewVat">—</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted">Tax</span>
                            <span class="fw-semibold text-dark">&#2547;&nbsp;<span id="ipdbViewTax">—</span></span>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-journal-text me-1"></i> Notes
                    </div>
                    <div class="ipdb-view-notes" id="ipdbViewNotes">—</div>
                </div>
            </div>

            <div class="modal-footer bg-white border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .ipdb-view-modal .ipdb-view-header {
        background: linear-gradient(135deg, #0d518d 0%, #0ea5e9 55%, #6366f1 100%);
        color: #fff;
    }

    .ipdb-view-modal .ipdb-view-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        backdrop-filter: blur(4px);
    }

    .ipdb-view-modal .ipdb-view-body {
        background: #f6f8fb;
    }

    .ipdb-view-modal .ipdb-view-amount-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .ipdb-view-modal .ipdb-view-currency {
        font-size: 1.1rem;
        color: #64748b;
        font-weight: 600;
    }

    .ipdb-view-modal .ipdb-view-amount {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        color: #0f172a;
        letter-spacing: -.01em;
    }

    .ipdb-view-modal .ipdb-view-eyebrow {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
    }

    .ipdb-view-modal .ipdb-view-section {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }

    .ipdb-view-modal .ipdb-view-section-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #475569;
        margin-bottom: .85rem;
    }

    .ipdb-view-modal .ipdb-view-label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        margin-bottom: .3rem;
    }

    .ipdb-view-modal .ipdb-view-value {
        font-weight: 600;
        color: #0f172a;
    }

    .ipdb-view-modal .ipdb-view-breakdown {
        font-size: .95rem;
    }

    .ipdb-view-modal .ipdb-view-notes {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: .85rem 1rem;
        min-height: 54px;
        color: #475569;
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>

{{-- Edit Charge Modal --}}
<div class="modal fade ipdb-edit-modal" id="ipdbEditChargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable ipdb-edit-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="ipdbEditChargeForm" method="POST">
                @csrf
                @method('PUT')

                <div class="ipdb-edit-header px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ipdb-edit-header-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white fw-bold">Edit Charge</h5>
                                <div class="text-white-50 small">
                                    {{ $ipdPatient->patient->patient_name ?? 'Patient' }}
                                    <span class="opacity-75">· {{ $ipdPatient->ipd_no }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-4 ipdb-edit-body">
                    <input type="hidden" name="source" value="billing">

                    {{-- Charge Details Section --}}
                    <div class="ipdb-edit-section mb-3">
                        <div class="ipdb-edit-section-title">
                            <i class="bi bi-info-circle me-1"></i> Charge Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Charge Item <span class="text-danger">*</span>
                                </label>
                                <select name="charge_id" id="ipdbEditChargeId" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($availableCharges as $charge)
                                        <option value="{{ $charge->id }}"
                                            data-price="{{ $charge->standard_charge }}"
                                            data-tax="{{ $charge->tax ?? 0 }}">
                                            {{ $charge->charge_name }}
                                            ({{ optional($charge->chargeCategory)->name }})
                                            - &#2547; {{ number_format($charge->standard_charge, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="date" id="ipdbEditDate" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- Assignment Section --}}
                    <div class="ipdb-edit-section mb-3">
                        <div class="ipdb-edit-section-title">
                            <i class="bi bi-person-badge me-1"></i> Assignment
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Doctor</label>
                                <select name="doctor_id" id="ipdbEditDoctorId" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Department</label>
                                <select name="department_id" id="ipdbEditDepartmentId" class="form-select">
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Pricing Section --}}
                    <div class="ipdb-edit-section ipdb-edit-section-pricing mb-3">
                        <div class="ipdb-edit-section-title">
                            <i class="bi bi-calculator me-1"></i> Pricing
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Quantity <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="quantity" id="ipdbEditQty" class="form-control"
                                    min="1" required>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Unit Price</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">&#2547;</span>
                                    <input type="text" id="ipdbEditUnitPrice"
                                        class="form-control bg-light border-start-0" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Tax</label>
                                <div class="input-group">
                                    <input type="text" id="ipdbEditTaxRate"
                                        class="form-control bg-light border-end-0" readonly>
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Net Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text ipdb-edit-net-symbol">&#2547;</span>
                                    <input type="text" id="ipdbEditNetAmount"
                                        class="form-control ipdb-edit-net-input fw-bold border-start-0" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes Section --}}
                    <div class="ipdb-edit-section">
                        <div class="ipdb-edit-section-title">
                            <i class="bi bi-journal-text me-1"></i> Notes
                        </div>
                        <textarea name="notes" id="ipdbEditNotes" class="form-control ipdb-edit-notes" rows="3"
                            placeholder="Add any optional notes about this charge…"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0 px-4 pb-4 pt-0 mt-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn ipdb-edit-save-btn px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Charge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .ipdb-edit-modal .ipdb-edit-dialog {
        max-width: 760px;
    }

    .ipdb-edit-modal .ipdb-edit-dialog .modal-content {
        max-height: calc(100vh - 3rem);
    }

    .ipdb-edit-modal .ipdb-edit-body {
        max-height: calc(100vh - 16rem);
        overflow-y: auto;
    }

    .ipdb-edit-modal .ipdb-edit-header {
        background: linear-gradient(135deg, #0d518d 0%, #0ea5e9 55%, #6366f1 100%);
        color: #fff;
    }

    .ipdb-edit-modal .ipdb-edit-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, .18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        backdrop-filter: blur(4px);
    }

    .ipdb-edit-modal .ipdb-edit-body {
        background: #f6f8fb;
    }

    .ipdb-edit-modal .ipdb-edit-section {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }

    .ipdb-edit-modal .ipdb-edit-section-pricing {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-color: #e0e7ff;
    }

    .ipdb-edit-modal .ipdb-edit-section-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #475569;
        margin-bottom: .85rem;
    }

    .ipdb-edit-modal .form-label {
        margin-bottom: .35rem;
    }

    .ipdb-edit-modal .form-control,
    .ipdb-edit-modal .form-select,
    .ipdb-edit-modal .input-group-text {
        border-radius: 8px;
    }

    .ipdb-edit-modal .input-group>.form-control,
    .ipdb-edit-modal .input-group>.input-group-text {
        border-radius: 0;
    }

    .ipdb-edit-modal .input-group> :first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .ipdb-edit-modal .input-group> :last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .ipdb-edit-modal .form-control:focus,
    .ipdb-edit-modal .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 .2rem rgba(99, 102, 241, .15);
    }

    .ipdb-edit-modal .ipdb-edit-net-symbol {
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
        border-color: #a7f3d0;
    }

    .ipdb-edit-modal .ipdb-edit-net-input {
        background: #ecfdf5 !important;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .ipdb-edit-modal .ipdb-edit-notes {
        resize: vertical;
        min-height: 80px;
    }

    .ipdb-edit-modal .ipdb-edit-save-btn {
        background: linear-gradient(135deg, #0d518d 0%, #0ea5e9 55%, #6366f1 100%);
        color: #fff;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .ipdb-edit-modal .ipdb-edit-save-btn:hover,
    .ipdb-edit-modal .ipdb-edit-save-btn:focus {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, .35);
    }
</style>

{{-- Hidden Delete Form --}}
<form id="ipdbDeleteChargeForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="source" value="billing">
</form>

<script>
    (function() {
        const modalEl = document.getElementById('ipdbAddChargesModal');
        if (!modalEl) return;

        const tbody = document.getElementById('ipdbChargeRows');
        const addBtn = document.getElementById('ipdbAddChargeRowBtn');
        const grandEl = document.getElementById('ipdbGrandTotal');
        const optionsHtml = tbody.querySelector('.ipdb-charge-select').innerHTML;
        const defaultDate = @json(now()->format('Y-m-d\TH:i'));
        let rowIndex = 1;

        function calcRow(row) {
            const sel = row.querySelector('.ipdb-charge-select');
            const opt = sel.options[sel.selectedIndex];
            const price = parseFloat(opt?.dataset?.price) || 0;
            const tax = parseFloat(opt?.dataset?.tax) || 0;
            const qty = parseInt(row.querySelector('.ipdb-qty-input').value) || 1;
            const amount = price * qty;
            const taxAmt = Math.round(amount * tax / 100 * 100) / 100;
            const net = amount + taxAmt;
            row.querySelector('.ipdb-unit-price').value = price.toFixed(2);
            row.querySelector('.ipdb-tax-rate').value = tax.toFixed(2);
            row.querySelector('.ipdb-row-net').value = net.toFixed(2);
            calcGrandTotal();
        }

        function calcGrandTotal() {
            let total = 0;
            tbody.querySelectorAll('.ipdb-row-net').forEach(el => {
                total += parseFloat(el.value) || 0;
            });
            grandEl.value = total.toFixed(2);
        }

        function toggleRemoveButtons() {
            const rows = tbody.querySelectorAll('.ipdb-charge-input-row');
            rows.forEach(r => {
                r.querySelector('.ipdb-remove-row').disabled = rows.length <= 1;
            });
        }

        addBtn.addEventListener('click', function() {
            const i = rowIndex++;
            const tr = document.createElement('tr');
            tr.classList.add('ipdb-charge-input-row');
            tr.dataset.index = i;
            tr.innerHTML = `
                <td>
                    <select name="items[${i}][charge_id]" class="form-select form-select-sm ipdb-charge-select" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="datetime-local" name="items[${i}][date]"
                        class="form-control form-control-sm" value="${defaultDate}" required>
                </td>
                <td>
                    <input type="number" name="items[${i}][quantity]"
                        class="form-control form-control-sm ipdb-qty-input" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm bg-light ipdb-unit-price" readonly value="0.00">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm bg-light ipdb-tax-rate" readonly value="0.00">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm bg-light fw-semibold ipdb-row-net" readonly value="0.00">
                </td>
                <td>
                    <input type="text" name="items[${i}][notes]" class="form-control form-control-sm" placeholder="Optional">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger ipdb-remove-row" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
            tbody.appendChild(tr);
            toggleRemoveButtons();
        });

        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.ipdb-remove-row');
            if (!btn) return;
            btn.closest('.ipdb-charge-input-row').remove();
            toggleRemoveButtons();
            calcGrandTotal();
        });

        tbody.addEventListener('change', function(e) {
            if (e.target.classList.contains('ipdb-charge-select')) {
                calcRow(e.target.closest('.ipdb-charge-input-row'));
            }
        });
        tbody.addEventListener('input', function(e) {
            if (e.target.classList.contains('ipdb-qty-input')) {
                calcRow(e.target.closest('.ipdb-charge-input-row'));
            }
        });

        calcRow(tbody.querySelector('.ipdb-charge-input-row'));
    })();

    (function() {
        const viewModalEl = document.getElementById('ipdbViewChargeModal');
        const editModalEl = document.getElementById('ipdbEditChargeModal');
        const deleteForm = document.getElementById('ipdbDeleteChargeForm');
        if (!viewModalEl || !editModalEl || !deleteForm) {
            console.error('ipdb-charges: modal elements missing', {
                viewModalEl,
                editModalEl,
                deleteForm
            });
            return;
        }

        function showModal(el) {
            if (window.bootstrap && window.bootstrap.Modal) {
                (window.bootstrap.Modal.getOrCreateInstance ?
                    window.bootstrap.Modal.getOrCreateInstance(el) :
                    new window.bootstrap.Modal(el)).show();
            } else if (window.jQuery && window.jQuery.fn.modal) {
                window.jQuery(el).modal('show');
            } else {
                el.classList.add('show');
                el.style.display = 'block';
                el.removeAttribute('aria-hidden');
            }
        }

        const fmt = n => (parseFloat(n) || 0).toFixed(2);

        const view = {
            date: document.getElementById('ipdbViewDate'),
            category: document.getElementById('ipdbViewCategory'),
            chargeName: document.getElementById('ipdbViewChargeName'),
            qty: document.getElementById('ipdbViewQty'),
            unitPrice: document.getElementById('ipdbViewUnitPrice'),
            vat: document.getElementById('ipdbViewVat'),
            tax: document.getElementById('ipdbViewTax'),
            netAmount: document.getElementById('ipdbViewNetAmount'),
            notes: document.getElementById('ipdbViewNotes'),
        };

        const editForm = document.getElementById('ipdbEditChargeForm');
        const edit = {
            charge: document.getElementById('ipdbEditChargeId'),
            date: document.getElementById('ipdbEditDate'),
            doctor: document.getElementById('ipdbEditDoctorId'),
            dept: document.getElementById('ipdbEditDepartmentId'),
            qty: document.getElementById('ipdbEditQty'),
            unitPrice: document.getElementById('ipdbEditUnitPrice'),
            taxRate: document.getElementById('ipdbEditTaxRate'),
            netAmount: document.getElementById('ipdbEditNetAmount'),
            notes: document.getElementById('ipdbEditNotes'),
        };

        function recalcEdit() {
            const opt = edit.charge.options[edit.charge.selectedIndex];
            const price = parseFloat(opt?.dataset?.price) || 0;
            const taxRate = parseFloat(opt?.dataset?.tax) || 0;
            const qty = parseInt(edit.qty.value) || 1;
            const amount = price * qty;
            const taxAmt = Math.round(amount * taxRate / 100 * 100) / 100;
            edit.unitPrice.value = price.toFixed(2);
            edit.taxRate.value = taxRate.toFixed(2);
            edit.netAmount.value = (amount + taxAmt).toFixed(2);
        }

        edit.charge.addEventListener('change', recalcEdit);
        edit.qty.addEventListener('input', recalcEdit);

        function populateView(d) {
            view.date.textContent = d.date_display || '—';
            view.category.textContent = d.category || '—';
            view.chargeName.textContent = d.charge_name || '—';
            view.qty.textContent = d.quantity ?? '—';
            view.unitPrice.textContent = fmt(d.unit_price);
            view.vat.textContent = fmt(d.vat);
            view.tax.textContent = fmt(d.tax);
            view.netAmount.textContent = fmt(d.net_amount);
            view.notes.textContent = d.notes || '—';
        }

        function populateEdit(d) {
            editForm.setAttribute('action', d.update_url);
            edit.charge.value = d.charge_id ?? '';
            edit.date.value = d.date_input || '';
            edit.doctor.value = d.doctor_id ?? '';
            edit.dept.value = d.department_id ?? '';
            edit.qty.value = d.quantity ?? 1;
            edit.notes.value = d.notes || '';
            recalcEdit();
        }

        function decodeRow(row) {
            try {
                return JSON.parse(atob(row.dataset.charge || ''));
            } catch (e) {
                console.error('ipdb-charge-row: failed to decode data-charge', e);
                return null;
            }
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.ipdb-charge-row .ipdb-action-icon');
            if (!btn) return;
            const row = btn.closest('.ipdb-charge-row');
            if (!row) return;
            const data = decodeRow(row);
            if (!data) return;

            const action = btn.dataset.action;
            if (action === 'view') {
                populateView(data);
                showModal(viewModalEl);
            } else if (action === 'edit') {
                populateEdit(data);
                showModal(editModalEl);
            } else if (action === 'delete') {
                if (!confirm('Delete this charge? This action cannot be undone.')) return;
                deleteForm.setAttribute('action', data.destroy_url);
                deleteForm.submit();
            }
        });
    })();
</script>
