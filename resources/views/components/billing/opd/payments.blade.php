@php
    $opdNoLabelPay = $opdPatient->token_no ?? ($opdPatient->serial_no ?? ('OPD-' . $opdPatient->id));
@endphp

<div class="tab-pane fade" id="payment" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">All Payments</h5>
                    <div class="text-muted small" id="opdbPaymentsRangeInfoTop">
                        Showing 1-{{ $totalTransactions }} of {{ number_format($totalTransactions) }}
                        transactions
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="opdb-pill opdb-pill-soft-success fw-bold" style="font-size:1.2rem;">
                        Total Payments: &#2547;&nbsp;{{ $fmt($transactions->sum('net_amount')) }}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm opdb-btn-add" data-bs-toggle="modal"
                        data-bs-target="#opdbAddPaymentModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Payment
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table opdb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Invoice No</th>
                            <th>Type</th>
                            <th>Section</th>
                            <th>Payment Via</th>
                            <th>Amount (BDT)</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $i => $t)
                            @php
                                $payDate = $t->payment_date
                                    ? \Illuminate\Support\Carbon::parse($t->payment_date)->format('d M Y')
                                    : '—';
                                $typeColor = $typePill[$t->type] ?? 'primary';
                                $statusColor = $statusPill[$t->status] ?? 'info';
                                $paymentRowData = [
                                    'id' => $t->id,
                                    'invoice_no' => $t->invoice_no,
                                    'type' => $t->type,
                                    'section' => $t->section,
                                    'payment_via' => $t->payment_via,
                                    'payment_date_input' => $t->payment_date
                                        ? \Illuminate\Support\Carbon::parse($t->payment_date)->format('Y-m-d\TH:i')
                                        : '',
                                    'payment_date_display' => $t->payment_date
                                        ? \Illuminate\Support\Carbon::parse($t->payment_date)->format('d M Y, h:i A')
                                        : '—',
                                    'amount' => (float) $t->amount,
                                    'vat' => (float) $t->vat,
                                    'tax' => (float) $t->tax,
                                    'discount' => (float) $t->discount,
                                    'net_amount' => (float) $t->net_amount,
                                    'status' => $t->status,
                                    'received_by' => $t->received_by,
                                    'notes' => $t->notes,
                                    'card_no' => $t->card_no,
                                    'card_type' => $t->card_type,
                                    'cheque_name' => $t->cheque_name,
                                    'cheque_no' => $t->cheque_no,
                                    'cheque_date' => $t->cheque_date
                                        ? \Illuminate\Support\Carbon::parse($t->cheque_date)->format('Y-m-d')
                                        : '',
                                    'mfs_type' => $t->mfs_type,
                                    'mfs_no' => $t->mfs_no,
                                    'mfs_transaction_id' => $t->mfs_transaction_id,
                                    'files' => $t->files ? (json_decode($t->files, true) ?: []) : [],
                                    'type_color' => $typeColor,
                                    'status_color' => $statusColor,
                                    'update_url' => route('opd-patients.payments.update', [$opdPatient->id, $t->id]),
                                    'destroy_url' => route('opd-patients.payments.destroy', [$opdPatient->id, $t->id]),
                                ];
                            @endphp
                            <tr class="opdb-payment-row" data-index="{{ $i }}"
                                data-payment="{{ base64_encode(json_encode($paymentRowData)) }}">
                                <td class="ps-4">{{ $payDate }}</td>
                                <td class="fw-semibold">{{ $t->invoice_no ?? '—' }}</td>
                                <td>
                                    <span class="opdb-pill opdb-pill-soft-{{ $typeColor }}">
                                        {{ ucfirst($t->type ?? '—') }}
                                    </span>
                                </td>
                                <td>{{ strtoupper($t->section ?? '—') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $t->payment_via ?? '—')) }}</td>
                                <td>{{ $fmt($t->amount) }}</td>
                                <td>{{ $fmt($t->discount) }}</td>
                                <td>{{ $fmt((float) $t->vat + (float) $t->tax) }}</td>
                                <td class="fw-bold">{{ $fmt($t->net_amount) }}</td>
                                <td>
                                    <span class="opdb-pill opdb-pill-soft-{{ $statusColor }}">
                                        {{ ucfirst($t->status ?? '—') }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="opdb-action-icon view" data-action="view"
                                        title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="opdb-action-icon edit" data-action="edit"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="opdb-action-icon delete" data-action="delete"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="bi bi-wallet2 fs-3 d-block mb-2"></i>
                                    No payments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3 small text-muted">
                <span><strong>{{ $totalTransactions }}</strong> Transactions</span>
                <span class="d-inline-flex align-items-center gap-2">
                    <span>Rows per page:</span>
                    <select id="opdbPaymentsPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="opdbPaymentsRangeInfo"></span>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="opdbPaymentsPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade opdb-pay-modal" id="opdbAddPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable opdb-pay-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('opd-patients.payments.store', $opdPatient->id) }}" method="POST"
                enctype="multipart/form-data" id="opdbAddPaymentForm">
                @csrf

                <div class="opdb-pay-header px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="opdb-pay-header-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white fw-bold">Add Payment</h5>
                                <div class="text-white-50 small">
                                    {{ $opdPatient->patient->patient_name ?? 'Patient' }}
                                    <span class="opacity-75">· {{ $opdNoLabelPay }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-4 opdb-pay-body">
                    <input type="hidden" name="source" value="billing">

                    {{-- Payment Info Section --}}
                    <div class="opdb-pay-section mb-3">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-info-circle me-1"></i> Payment Info
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Type</label>
                                <select name="type" class="form-select">
                                    @foreach (['payment', 'advance', 'refund', 'adjustment'] as $t)
                                        <option value="{{ $t }}" @selected($t === 'payment')>
                                            {{ ucfirst($t) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">
                                    Payment Via <span class="text-danger">*</span>
                                </label>
                                <select name="payment_via" id="opdbPayVia" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach (['cash', 'card', 'cheque', 'mfs', 'other'] as $v)
                                        <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">
                                    Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="payment_date" class="form-control"
                                    value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Received By</label>
                                <input type="text" name="received_by" class="form-control"
                                    placeholder="Cashier / Staff name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" class="form-select" required>
                                    @foreach (['successed', 'pending', 'failed', 'canceled'] as $s)
                                        <option value="{{ $s }}" @selected($s === 'successed')>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Breakdown Section --}}
                    <div class="opdb-pay-section opdb-pay-section-pricing mb-3">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-calculator me-1"></i> Amount Breakdown
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">&#2547;</span>
                                    <input type="number" step="0.01" min="0" name="amount"
                                        id="opdbPayAmt" class="form-control" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">VAT</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="vat" id="opdbPayVat" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Tax</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="tax" id="opdbPayTax" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Discount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="discount" id="opdbPayDis" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="opdb-pay-net">
                                    <span class="text-muted small">
                                        Net = (Amount + VAT + Tax) − Discount
                                    </span>
                                    <span class="fw-bold opdb-pay-net-amount">
                                        &#2547;&nbsp;<span id="opdbPayNet">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbPayCardFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-credit-card me-1"></i> Card Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card No</label>
                                <input type="text" name="card_no" class="form-control"
                                    placeholder="**** **** **** 1234">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card Type</label>
                                <select name="card_type" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['visa', 'master', 'american_express', 'other'] as $c)
                                        <option value="{{ $c }}">
                                            {{ ucfirst(str_replace('_', ' ', $c)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Cheque Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbPayChequeFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-receipt me-1"></i> Cheque Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Name</label>
                                <input type="text" name="cheque_name" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque No</label>
                                <input type="text" name="cheque_no" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- MFS Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbPayMfsFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-phone me-1"></i> MFS Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS Type</label>
                                <select name="mfs_type" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['bkash', 'nagad', 'rocket', 'other'] as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS No</label>
                                <input type="text" name="mfs_no" class="form-control" placeholder="01XXXXXXXXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Transaction ID</label>
                                <input type="text" name="mfs_transaction_id" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Notes & Files Section --}}
                    <div class="opdb-pay-section">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-paperclip me-1"></i> Notes &amp; Attachments
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Notes</label>
                                <textarea name="notes" class="form-control opdb-pay-notes" rows="2"
                                    placeholder="Any note for this payment…"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Files</label>
                                <input type="file" name="files[]" class="form-control" multiple>
                                <div class="form-text">
                                    Upload receipt / cheque scan / supporting documents.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0 px-4 pb-4 pt-0 mt-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn opdb-pay-save-btn px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .opdb-pay-modal .opdb-pay-dialog {
        max-width: 820px;
    }

    .opdb-pay-modal .opdb-pay-dialog .modal-content {
        max-height: calc(100vh - 3rem);
    }

    .opdb-pay-modal .opdb-pay-body {
        max-height: calc(100vh - 16rem);
        overflow-y: auto;
        background: #f6f8fb;
    }

    .opdb-pay-modal .opdb-pay-header {
        background: linear-gradient(135deg, #0d518d 0%, #0ea5e9 55%, #6366f1 100%);
        color: #fff;
    }

    .opdb-pay-modal .opdb-pay-header-icon {
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

    .opdb-pay-modal .opdb-pay-section {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }

    .opdb-pay-modal .opdb-pay-section-pricing {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-color: #d1fae5;
    }

    .opdb-pay-modal .opdb-pay-section-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #475569;
        margin-bottom: .85rem;
    }

    .opdb-pay-modal .form-label {
        margin-bottom: .35rem;
    }

    .opdb-pay-modal .form-control,
    .opdb-pay-modal .form-select,
    .opdb-pay-modal .input-group-text {
        border-radius: 8px;
    }

    .opdb-pay-modal .input-group>.form-control,
    .opdb-pay-modal .input-group>.input-group-text {
        border-radius: 0;
    }

    .opdb-pay-modal .input-group> :first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .opdb-pay-modal .input-group> :last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .opdb-pay-modal .form-control:focus,
    .opdb-pay-modal .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 .2rem rgba(16, 185, 129, .15);
    }

    .opdb-pay-modal .opdb-pay-net {
        background: #ecfdf5;
        border: 1px dashed #6ee7b7;
        border-radius: 10px;
        padding: .65rem 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .opdb-pay-modal .opdb-pay-net-amount {
        color: #065f46;
        font-size: 1.05rem;
    }

    .opdb-pay-modal .opdb-pay-notes {
        resize: vertical;
        min-height: 70px;
    }

    .opdb-pay-modal .opdb-pay-save-btn {
        background: linear-gradient(135deg, #10b981 0%, #0ea5e9 100%);
        color: #fff;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .opdb-pay-modal .opdb-pay-save-btn:hover,
    .opdb-pay-modal .opdb-pay-save-btn:focus {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, .35);
    }
</style>

<style>
    .opdb-payment-row .opdb-action-icon {
        cursor: pointer;
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
</style>

{{-- View Payment Modal --}}
<div class="modal fade opdb-view-modal" id="opdbViewPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="opdb-view-header px-4 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="opdb-view-header-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 text-white fw-bold">Payment Details</h5>
                            <div class="text-white-50 small">Full breakdown of this transaction</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 opdb-view-body">
                <div class="opdb-view-amount-card mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="opdb-view-eyebrow mb-1">Net Amount</div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="opdb-view-currency">&#2547;</span>
                                <span class="opdb-view-amount" id="opdbViewPayNet">—</span>
                            </div>
                        </div>
                        <div class="text-sm-end">
                            <div class="opdb-view-eyebrow mb-1">
                                <i class="bi bi-clock me-1"></i> Payment Date
                            </div>
                            <div class="fw-semibold text-dark" id="opdbViewPayDate">—</div>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section mb-3">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-info-circle me-1"></i> Payment Information
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Invoice No</div>
                            <div class="opdb-view-value" id="opdbViewPayInvoice">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Type</div>
                            <span class="opdb-pill opdb-pill-soft-primary" id="opdbViewPayType">—</span>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Status</div>
                            <span class="opdb-pill opdb-pill-soft-info" id="opdbViewPayStatus">—</span>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Section</div>
                            <div class="opdb-view-value" id="opdbViewPaySection">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Payment Via</div>
                            <div class="opdb-view-value" id="opdbViewPayVia">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Received By</div>
                            <div class="opdb-view-value" id="opdbViewPayReceivedBy">—</div>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section mb-3">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-calculator me-1"></i> Amount Breakdown
                    </div>
                    <div class="opdb-view-breakdown">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Amount</span>
                            <span class="fw-semibold text-dark">&#2547;&nbsp;<span
                                    id="opdbViewPayAmount">—</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">VAT (%)</span>
                            <span class="fw-semibold text-dark"><span id="opdbViewPayVat">—</span>%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Tax (%)</span>
                            <span class="fw-semibold text-dark"><span id="opdbViewPayTax">—</span>%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted">Discount (%)</span>
                            <span class="fw-semibold text-dark"><span id="opdbViewPayDiscount">—</span>%</span>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section mb-3 d-none" id="opdbViewPayCardSection">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-credit-card me-1"></i> Card Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="opdb-view-label">Card No</div>
                            <div class="opdb-view-value" id="opdbViewPayCardNo">—</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="opdb-view-label">Card Type</div>
                            <div class="opdb-view-value" id="opdbViewPayCardType">—</div>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section mb-3 d-none" id="opdbViewPayChequeSection">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-receipt me-1"></i> Cheque Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Cheque Name</div>
                            <div class="opdb-view-value" id="opdbViewPayChequeName">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Cheque No</div>
                            <div class="opdb-view-value" id="opdbViewPayChequeNo">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Cheque Date</div>
                            <div class="opdb-view-value" id="opdbViewPayChequeDate">—</div>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section mb-3 d-none" id="opdbViewPayMfsSection">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-phone me-1"></i> MFS Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="opdb-view-label">MFS Type</div>
                            <div class="opdb-view-value" id="opdbViewPayMfsType">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">MFS No</div>
                            <div class="opdb-view-value" id="opdbViewPayMfsNo">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="opdb-view-label">Transaction ID</div>
                            <div class="opdb-view-value" id="opdbViewPayMfsTxn">—</div>
                        </div>
                    </div>
                </div>

                <div class="opdb-view-section">
                    <div class="opdb-view-section-title">
                        <i class="bi bi-journal-text me-1"></i> Notes
                    </div>
                    <div class="opdb-view-notes" id="opdbViewPayNotes">—</div>
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

{{-- Edit Payment Modal --}}
<div class="modal fade opdb-pay-modal" id="opdbEditPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable opdb-pay-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="opdbEditPaymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="source" value="billing">

                <div class="opdb-pay-header px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="opdb-pay-header-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white fw-bold">Edit Payment</h5>
                                <div class="text-white-50 small">
                                    {{ $opdPatient->patient->patient_name ?? 'Patient' }}
                                    <span class="opacity-75">· {{ $opdNoLabelPay }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-4 opdb-pay-body">
                    {{-- Payment Info Section --}}
                    <div class="opdb-pay-section mb-3">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-info-circle me-1"></i> Payment Info
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Type</label>
                                <select name="type" id="opdbEditPayType" class="form-select">
                                    @foreach (['payment', 'advance', 'refund', 'adjustment'] as $t)
                                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">
                                    Payment Via <span class="text-danger">*</span>
                                </label>
                                <select name="payment_via" id="opdbEditPayVia" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach (['cash', 'card', 'cheque', 'mfs', 'other'] as $v)
                                        <option value="{{ $v }}">{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">
                                    Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="payment_date" id="opdbEditPayDate"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Received By</label>
                                <input type="text" name="received_by" id="opdbEditPayReceivedBy"
                                    class="form-control" placeholder="Cashier / Staff name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="opdbEditPayStatus" class="form-select" required>
                                    @foreach (['successed', 'pending', 'failed', 'canceled'] as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Breakdown Section --}}
                    <div class="opdb-pay-section opdb-pay-section-pricing mb-3">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-calculator me-1"></i> Amount Breakdown
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted">&#2547;</span>
                                    <input type="number" step="0.01" min="0" name="amount"
                                        id="opdbEditPayAmt" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">VAT</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="vat"
                                        id="opdbEditPayVat" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Tax</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="tax"
                                        id="opdbEditPayTax" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Discount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="discount"
                                        id="opdbEditPayDis" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="opdb-pay-net">
                                    <span class="text-muted small">
                                        Net = (Amount + VAT + Tax) − Discount
                                    </span>
                                    <span class="fw-bold opdb-pay-net-amount">
                                        &#2547;&nbsp;<span id="opdbEditPayNet">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbEditPayCardFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-credit-card me-1"></i> Card Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card No</label>
                                <input type="text" name="card_no" id="opdbEditPayCardNo" class="form-control"
                                    placeholder="**** **** **** 1234">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card Type</label>
                                <select name="card_type" id="opdbEditPayCardType" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['visa', 'master', 'american_express', 'other'] as $c)
                                        <option value="{{ $c }}">
                                            {{ ucfirst(str_replace('_', ' ', $c)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Cheque Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbEditPayChequeFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-receipt me-1"></i> Cheque Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Name</label>
                                <input type="text" name="cheque_name" id="opdbEditPayChequeName"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque No</label>
                                <input type="text" name="cheque_no" id="opdbEditPayChequeNo" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Date</label>
                                <input type="date" name="cheque_date" id="opdbEditPayChequeDate"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- MFS Details (conditional) --}}
                    <div class="opdb-pay-section mb-3 d-none" id="opdbEditPayMfsFields">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-phone me-1"></i> MFS Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS Type</label>
                                <select name="mfs_type" id="opdbEditPayMfsType" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['bkash', 'nagad', 'rocket', 'other'] as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS No</label>
                                <input type="text" name="mfs_no" id="opdbEditPayMfsNo" class="form-control"
                                    placeholder="01XXXXXXXXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Transaction ID</label>
                                <input type="text" name="mfs_transaction_id" id="opdbEditPayMfsTxn"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Notes & Files Section --}}
                    <div class="opdb-pay-section">
                        <div class="opdb-pay-section-title">
                            <i class="bi bi-paperclip me-1"></i> Notes &amp; Attachments
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Notes</label>
                                <textarea name="notes" id="opdbEditPayNotes" class="form-control opdb-pay-notes" rows="2"
                                    placeholder="Any note for this payment…"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Add More Files</label>
                                <input type="file" name="files[]" class="form-control" multiple>
                                <div class="form-text">
                                    Existing files are kept; new uploads are appended.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0 px-4 pb-4 pt-0 mt-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn opdb-pay-save-btn px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Delete Form --}}
<form id="opdbDeletePaymentForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="source" value="billing">
</form>

<script>
    (function() {
        const modal = document.getElementById('opdbAddPaymentModal');
        if (!modal) return;

        const via = modal.querySelector('#opdbPayVia');
        const cardF = modal.querySelector('#opdbPayCardFields');
        const chequeF = modal.querySelector('#opdbPayChequeFields');
        const mfsF = modal.querySelector('#opdbPayMfsFields');

        const toggleVia = () => {
            cardF.classList.toggle('d-none', via.value !== 'card');
            chequeF.classList.toggle('d-none', via.value !== 'cheque');
            mfsF.classList.toggle('d-none', via.value !== 'mfs');
        };
        via.addEventListener('change', toggleVia);

        const amt = modal.querySelector('#opdbPayAmt');
        const vat = modal.querySelector('#opdbPayVat');
        const tax = modal.querySelector('#opdbPayTax');
        const dis = modal.querySelector('#opdbPayDis');
        const net = modal.querySelector('#opdbPayNet');
        const num = el => parseFloat(el.value) || 0;
        const recalc = () => {
            const a = num(amt);
            const sub = a + (a * num(vat) / 100) + (a * num(tax) / 100);
            const n = sub - (sub * num(dis) / 100);
            net.textContent = n.toFixed(2);
        };
        [amt, vat, tax, dis].forEach(el => el.addEventListener('input', recalc));
        recalc();
    })();

    (function() {
        const viewModalEl = document.getElementById('opdbViewPaymentModal');
        const editModalEl = document.getElementById('opdbEditPaymentModal');
        const deleteForm = document.getElementById('opdbDeletePaymentForm');
        if (!viewModalEl || !editModalEl || !deleteForm) {
            console.error('opdb-payments: modal elements missing', {
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
        const cap = s => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '—');
        const human = s => (s ? cap(String(s).replace(/_/g, ' ')) : '—');

        const view = {
            net: document.getElementById('opdbViewPayNet'),
            date: document.getElementById('opdbViewPayDate'),
            invoice: document.getElementById('opdbViewPayInvoice'),
            type: document.getElementById('opdbViewPayType'),
            status: document.getElementById('opdbViewPayStatus'),
            section: document.getElementById('opdbViewPaySection'),
            payVia: document.getElementById('opdbViewPayVia'),
            receivedBy: document.getElementById('opdbViewPayReceivedBy'),
            amount: document.getElementById('opdbViewPayAmount'),
            vat: document.getElementById('opdbViewPayVat'),
            tax: document.getElementById('opdbViewPayTax'),
            discount: document.getElementById('opdbViewPayDiscount'),
            notes: document.getElementById('opdbViewPayNotes'),
            cardSection: document.getElementById('opdbViewPayCardSection'),
            cardNo: document.getElementById('opdbViewPayCardNo'),
            cardType: document.getElementById('opdbViewPayCardType'),
            chequeSection: document.getElementById('opdbViewPayChequeSection'),
            chequeName: document.getElementById('opdbViewPayChequeName'),
            chequeNo: document.getElementById('opdbViewPayChequeNo'),
            chequeDate: document.getElementById('opdbViewPayChequeDate'),
            mfsSection: document.getElementById('opdbViewPayMfsSection'),
            mfsType: document.getElementById('opdbViewPayMfsType'),
            mfsNo: document.getElementById('opdbViewPayMfsNo'),
            mfsTxn: document.getElementById('opdbViewPayMfsTxn'),
        };

        const editForm = document.getElementById('opdbEditPaymentForm');
        const edit = {
            type: document.getElementById('opdbEditPayType'),
            via: document.getElementById('opdbEditPayVia'),
            date: document.getElementById('opdbEditPayDate'),
            receivedBy: document.getElementById('opdbEditPayReceivedBy'),
            status: document.getElementById('opdbEditPayStatus'),
            amount: document.getElementById('opdbEditPayAmt'),
            vat: document.getElementById('opdbEditPayVat'),
            tax: document.getElementById('opdbEditPayTax'),
            discount: document.getElementById('opdbEditPayDis'),
            net: document.getElementById('opdbEditPayNet'),
            notes: document.getElementById('opdbEditPayNotes'),
            cardFields: document.getElementById('opdbEditPayCardFields'),
            cardNo: document.getElementById('opdbEditPayCardNo'),
            cardType: document.getElementById('opdbEditPayCardType'),
            chequeFields: document.getElementById('opdbEditPayChequeFields'),
            chequeName: document.getElementById('opdbEditPayChequeName'),
            chequeNo: document.getElementById('opdbEditPayChequeNo'),
            chequeDate: document.getElementById('opdbEditPayChequeDate'),
            mfsFields: document.getElementById('opdbEditPayMfsFields'),
            mfsType: document.getElementById('opdbEditPayMfsType'),
            mfsNo: document.getElementById('opdbEditPayMfsNo'),
            mfsTxn: document.getElementById('opdbEditPayMfsTxn'),
        };

        function toggleEditVia() {
            edit.cardFields.classList.toggle('d-none', edit.via.value !== 'card');
            edit.chequeFields.classList.toggle('d-none', edit.via.value !== 'cheque');
            edit.mfsFields.classList.toggle('d-none', edit.via.value !== 'mfs');
        }

        function recalcEdit() {
            const a = parseFloat(edit.amount.value) || 0;
            const v = parseFloat(edit.vat.value) || 0;
            const t = parseFloat(edit.tax.value) || 0;
            const d = parseFloat(edit.discount.value) || 0;
            const sub = a + (a * v / 100) + (a * t / 100);
            const n = sub - (sub * d / 100);
            edit.net.textContent = n.toFixed(2);
        }

        edit.via.addEventListener('change', toggleEditVia);
        [edit.amount, edit.vat, edit.tax, edit.discount].forEach(el =>
            el.addEventListener('input', recalcEdit)
        );

        function pillClass(base, color) {
            const el = base;
            el.className = 'opdb-pill opdb-pill-soft-' + (color || 'info');
        }

        function populateView(d) {
            view.net.textContent = fmt(d.net_amount);
            view.date.textContent = d.payment_date_display || '—';
            view.invoice.textContent = d.invoice_no || '—';
            view.type.textContent = cap(d.type);
            pillClass(view.type, d.type_color);
            view.status.textContent = cap(d.status);
            pillClass(view.status, d.status_color);
            view.section.textContent = d.section ? d.section.toUpperCase() : '—';
            view.payVia.textContent = human(d.payment_via);
            view.receivedBy.textContent = d.received_by || '—';
            view.amount.textContent = fmt(d.amount);
            view.vat.textContent = fmt(d.vat);
            view.tax.textContent = fmt(d.tax);
            view.discount.textContent = fmt(d.discount);
            view.notes.textContent = d.notes || '—';

            const isCard = d.payment_via === 'card';
            const isCheque = d.payment_via === 'cheque';
            const isMfs = d.payment_via === 'mfs';
            view.cardSection.classList.toggle('d-none', !isCard);
            view.chequeSection.classList.toggle('d-none', !isCheque);
            view.mfsSection.classList.toggle('d-none', !isMfs);
            if (isCard) {
                view.cardNo.textContent = d.card_no || '—';
                view.cardType.textContent = human(d.card_type);
            }
            if (isCheque) {
                view.chequeName.textContent = d.cheque_name || '—';
                view.chequeNo.textContent = d.cheque_no || '—';
                view.chequeDate.textContent = d.cheque_date || '—';
            }
            if (isMfs) {
                view.mfsType.textContent = human(d.mfs_type);
                view.mfsNo.textContent = d.mfs_no || '—';
                view.mfsTxn.textContent = d.mfs_transaction_id || '—';
            }
        }

        function populateEdit(d) {
            editForm.setAttribute('action', d.update_url);
            edit.type.value = d.type || 'payment';
            edit.via.value = d.payment_via || '';
            edit.date.value = d.payment_date_input || '';
            edit.receivedBy.value = d.received_by || '';
            edit.status.value = d.status || 'successed';
            edit.amount.value = d.amount ?? 0;
            edit.vat.value = d.vat ?? 0;
            edit.tax.value = d.tax ?? 0;
            edit.discount.value = d.discount ?? 0;
            edit.notes.value = d.notes || '';
            edit.cardNo.value = d.card_no || '';
            edit.cardType.value = d.card_type || '';
            edit.chequeName.value = d.cheque_name || '';
            edit.chequeNo.value = d.cheque_no || '';
            edit.chequeDate.value = d.cheque_date || '';
            edit.mfsType.value = d.mfs_type || '';
            edit.mfsNo.value = d.mfs_no || '';
            edit.mfsTxn.value = d.mfs_transaction_id || '';
            toggleEditVia();
            recalcEdit();
        }

        function decodeRow(row) {
            try {
                return JSON.parse(atob(row.dataset.payment || ''));
            } catch (e) {
                console.error('opdb-payment-row: failed to decode data-payment', e);
                return null;
            }
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.opdb-payment-row .opdb-action-icon');
            if (!btn) return;
            const row = btn.closest('.opdb-payment-row');
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
                if (!confirm('Delete this payment? This action cannot be undone.')) return;
                deleteForm.setAttribute('action', data.destroy_url);
                deleteForm.submit();
            }
        });
    })();
</script>
