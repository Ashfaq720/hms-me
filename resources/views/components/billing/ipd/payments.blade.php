<div class="tab-pane fade" id="payment" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">All Payments</h5>
                    <div class="text-muted small" id="ipdbPaymentsRangeInfoTop">
                        Showing 1-{{ $totalTransactions }} of {{ number_format($totalTransactions) }}
                        transactions
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="ipdb-pill ipdb-pill-soft-success fw-bold" style="font-size:1.2rem;">
                        Total Payments: &#2547;&nbsp;{{ $fmt($transactions->sum('net_amount')) }}
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm ipdb-btn-add" data-bs-toggle="modal"
                        data-bs-target="#ipdbAddPaymentModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Payment
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table ipdb-table align-middle mb-0">
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
                                    'update_url' => route('ipd-patients.payments.update', [$ipdPatient->id, $t->id]),
                                    'destroy_url' => route('ipd-patients.payments.destroy', [$ipdPatient->id, $t->id]),
                                ];
                            @endphp
                            <tr class="ipdb-payment-row" data-index="{{ $i }}"
                                data-payment="{{ base64_encode(json_encode($paymentRowData)) }}">
                                <td class="ps-4">{{ $payDate }}</td>
                                <td class="fw-semibold">{{ $t->invoice_no ?? '—' }}</td>
                                <td>
                                    <span class="ipdb-pill ipdb-pill-soft-{{ $typeColor }}">
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
                                    <span class="ipdb-pill ipdb-pill-soft-{{ $statusColor }}">
                                        {{ ucfirst($t->status ?? '—') }}
                                    </span>
                                </td>
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
                    <select id="ipdbPaymentsPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="ipdbPaymentsRangeInfo"></span>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="ipdbPaymentsPagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade ipdb-pay-modal" id="ipdbAddPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable ipdb-pay-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('ipd-patients.payments.store', $ipdPatient->id) }}" method="POST"
                enctype="multipart/form-data" id="ipdbAddPaymentForm">
                @csrf

                <div class="ipdb-pay-header px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ipdb-pay-header-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white fw-bold">Add Payment</h5>
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

                <div class="modal-body p-4 ipdb-pay-body">
                    <input type="hidden" name="source" value="billing">

                    {{-- Payment Info Section --}}
                    <div class="ipdb-pay-section mb-3">
                        <div class="ipdb-pay-section-title">
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
                                <select name="payment_via" id="ipdbPayVia" class="form-select" required>
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
                    <div class="ipdb-pay-section ipdb-pay-section-pricing mb-3">
                        <div class="ipdb-pay-section-title">
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
                                        id="ipdbPayAmt" class="form-control" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">VAT</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="vat" id="ipdbPayVat" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Tax</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="tax" id="ipdbPayTax" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Discount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="discount" id="ipdbPayDis" class="form-control" value="0">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="ipdb-pay-net">
                                    <span class="text-muted small">
                                        Net = (Amount + VAT + Tax) − Discount
                                    </span>
                                    <span class="fw-bold ipdb-pay-net-amount">
                                        &#2547;&nbsp;<span id="ipdbPayNet">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Details (conditional) --}}
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbPayCardFields">
                        <div class="ipdb-pay-section-title">
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
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbPayChequeFields">
                        <div class="ipdb-pay-section-title">
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
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbPayMfsFields">
                        <div class="ipdb-pay-section-title">
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
                    <div class="ipdb-pay-section">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-paperclip me-1"></i> Notes &amp; Attachments
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Notes</label>
                                <textarea name="notes" class="form-control ipdb-pay-notes" rows="2"
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
                    <button type="submit" class="btn ipdb-pay-save-btn px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .ipdb-pay-modal .ipdb-pay-dialog {
        max-width: 820px;
    }

    .ipdb-pay-modal .ipdb-pay-dialog .modal-content {
        max-height: calc(100vh - 3rem);
    }

    .ipdb-pay-modal .ipdb-pay-body {
        max-height: calc(100vh - 16rem);
        overflow-y: auto;
        background: #f6f8fb;
    }

    .ipdb-pay-modal .ipdb-pay-header {
        background: linear-gradient(135deg, #0d518d 0%, #0ea5e9 55%, #6366f1 100%);
        color: #fff;
    }

    .ipdb-pay-modal .ipdb-pay-header-icon {
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

    .ipdb-pay-modal .ipdb-pay-section {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 1rem 1.25rem;
    }

    .ipdb-pay-modal .ipdb-pay-section-pricing {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-color: #d1fae5;
    }

    .ipdb-pay-modal .ipdb-pay-section-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #475569;
        margin-bottom: .85rem;
    }

    .ipdb-pay-modal .form-label {
        margin-bottom: .35rem;
    }

    .ipdb-pay-modal .form-control,
    .ipdb-pay-modal .form-select,
    .ipdb-pay-modal .input-group-text {
        border-radius: 8px;
    }

    .ipdb-pay-modal .input-group>.form-control,
    .ipdb-pay-modal .input-group>.input-group-text {
        border-radius: 0;
    }

    .ipdb-pay-modal .input-group> :first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .ipdb-pay-modal .input-group> :last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .ipdb-pay-modal .form-control:focus,
    .ipdb-pay-modal .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 .2rem rgba(16, 185, 129, .15);
    }

    .ipdb-pay-modal .ipdb-pay-net {
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

    .ipdb-pay-modal .ipdb-pay-net-amount {
        color: #065f46;
        font-size: 1.05rem;
    }

    .ipdb-pay-modal .ipdb-pay-notes {
        resize: vertical;
        min-height: 70px;
    }

    .ipdb-pay-modal .ipdb-pay-save-btn {
        background: linear-gradient(135deg, #10b981 0%, #0ea5e9 100%);
        color: #fff;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .ipdb-pay-modal .ipdb-pay-save-btn:hover,
    .ipdb-pay-modal .ipdb-pay-save-btn:focus {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, .35);
    }
</style>

<style>
    .ipdb-payment-row .ipdb-action-icon {
        cursor: pointer;
        position: relative;
        z-index: 1;
        pointer-events: auto;
    }
</style>

{{-- View Payment Modal --}}
<div class="modal fade ipdb-view-modal" id="ipdbViewPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="ipdb-view-header px-4 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ipdb-view-header-icon">
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

            <div class="modal-body p-4 ipdb-view-body">
                <div class="ipdb-view-amount-card mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="ipdb-view-eyebrow mb-1">Net Amount</div>
                            <div class="d-flex align-items-baseline gap-1">
                                <span class="ipdb-view-currency">&#2547;</span>
                                <span class="ipdb-view-amount" id="ipdbViewPayNet">—</span>
                            </div>
                        </div>
                        <div class="text-sm-end">
                            <div class="ipdb-view-eyebrow mb-1">
                                <i class="bi bi-clock me-1"></i> Payment Date
                            </div>
                            <div class="fw-semibold text-dark" id="ipdbViewPayDate">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-info-circle me-1"></i> Payment Information
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Invoice No</div>
                            <div class="ipdb-view-value" id="ipdbViewPayInvoice">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Type</div>
                            <span class="ipdb-pill ipdb-pill-soft-primary" id="ipdbViewPayType">—</span>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Status</div>
                            <span class="ipdb-pill ipdb-pill-soft-info" id="ipdbViewPayStatus">—</span>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Section</div>
                            <div class="ipdb-view-value" id="ipdbViewPaySection">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Payment Via</div>
                            <div class="ipdb-view-value" id="ipdbViewPayVia">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Received By</div>
                            <div class="ipdb-view-value" id="ipdbViewPayReceivedBy">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-calculator me-1"></i> Amount Breakdown
                    </div>
                    <div class="ipdb-view-breakdown">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Amount</span>
                            <span class="fw-semibold text-dark">&#2547;&nbsp;<span
                                    id="ipdbViewPayAmount">—</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">VAT (%)</span>
                            <span class="fw-semibold text-dark"><span id="ipdbViewPayVat">—</span>%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Tax (%)</span>
                            <span class="fw-semibold text-dark"><span id="ipdbViewPayTax">—</span>%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted">Discount (%)</span>
                            <span class="fw-semibold text-dark"><span id="ipdbViewPayDiscount">—</span>%</span>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3 d-none" id="ipdbViewPayCardSection">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-credit-card me-1"></i> Card Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="ipdb-view-label">Card No</div>
                            <div class="ipdb-view-value" id="ipdbViewPayCardNo">—</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="ipdb-view-label">Card Type</div>
                            <div class="ipdb-view-value" id="ipdbViewPayCardType">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3 d-none" id="ipdbViewPayChequeSection">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-receipt me-1"></i> Cheque Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Cheque Name</div>
                            <div class="ipdb-view-value" id="ipdbViewPayChequeName">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Cheque No</div>
                            <div class="ipdb-view-value" id="ipdbViewPayChequeNo">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Cheque Date</div>
                            <div class="ipdb-view-value" id="ipdbViewPayChequeDate">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section mb-3 d-none" id="ipdbViewPayMfsSection">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-phone me-1"></i> MFS Details
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">MFS Type</div>
                            <div class="ipdb-view-value" id="ipdbViewPayMfsType">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">MFS No</div>
                            <div class="ipdb-view-value" id="ipdbViewPayMfsNo">—</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="ipdb-view-label">Transaction ID</div>
                            <div class="ipdb-view-value" id="ipdbViewPayMfsTxn">—</div>
                        </div>
                    </div>
                </div>

                <div class="ipdb-view-section">
                    <div class="ipdb-view-section-title">
                        <i class="bi bi-journal-text me-1"></i> Notes
                    </div>
                    <div class="ipdb-view-notes" id="ipdbViewPayNotes">—</div>
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
<div class="modal fade ipdb-pay-modal" id="ipdbEditPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable ipdb-pay-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="ipdbEditPaymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="source" value="billing">

                <div class="ipdb-pay-header px-4 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ipdb-pay-header-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0 text-white fw-bold">Edit Payment</h5>
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

                <div class="modal-body p-4 ipdb-pay-body">
                    {{-- Payment Info Section --}}
                    <div class="ipdb-pay-section mb-3">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-info-circle me-1"></i> Payment Info
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Type</label>
                                <select name="type" id="ipdbEditPayType" class="form-select">
                                    @foreach (['payment', 'advance', 'refund', 'adjustment'] as $t)
                                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">
                                    Payment Via <span class="text-danger">*</span>
                                </label>
                                <select name="payment_via" id="ipdbEditPayVia" class="form-select" required>
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
                                <input type="datetime-local" name="payment_date" id="ipdbEditPayDate"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Received By</label>
                                <input type="text" name="received_by" id="ipdbEditPayReceivedBy"
                                    class="form-control" placeholder="Cashier / Staff name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="ipdbEditPayStatus" class="form-select" required>
                                    @foreach (['successed', 'pending', 'failed', 'canceled'] as $s)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Breakdown Section --}}
                    <div class="ipdb-pay-section ipdb-pay-section-pricing mb-3">
                        <div class="ipdb-pay-section-title">
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
                                        id="ipdbEditPayAmt" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">VAT</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="vat"
                                        id="ipdbEditPayVat" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Tax</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="tax"
                                        id="ipdbEditPayTax" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="form-label fw-semibold small text-secondary">Discount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="discount"
                                        id="ipdbEditPayDis" class="form-control">
                                    <span class="input-group-text bg-white text-muted">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="ipdb-pay-net">
                                    <span class="text-muted small">
                                        Net = (Amount + VAT + Tax) − Discount
                                    </span>
                                    <span class="fw-bold ipdb-pay-net-amount">
                                        &#2547;&nbsp;<span id="ipdbEditPayNet">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Details (conditional) --}}
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbEditPayCardFields">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-credit-card me-1"></i> Card Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card No</label>
                                <input type="text" name="card_no" id="ipdbEditPayCardNo" class="form-control"
                                    placeholder="**** **** **** 1234">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-secondary">Card Type</label>
                                <select name="card_type" id="ipdbEditPayCardType" class="form-select">
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
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbEditPayChequeFields">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-receipt me-1"></i> Cheque Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Name</label>
                                <input type="text" name="cheque_name" id="ipdbEditPayChequeName"
                                    class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque No</label>
                                <input type="text" name="cheque_no" id="ipdbEditPayChequeNo" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Cheque Date</label>
                                <input type="date" name="cheque_date" id="ipdbEditPayChequeDate"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- MFS Details (conditional) --}}
                    <div class="ipdb-pay-section mb-3 d-none" id="ipdbEditPayMfsFields">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-phone me-1"></i> MFS Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS Type</label>
                                <select name="mfs_type" id="ipdbEditPayMfsType" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach (['bkash', 'nagad', 'rocket', 'other'] as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">MFS No</label>
                                <input type="text" name="mfs_no" id="ipdbEditPayMfsNo" class="form-control"
                                    placeholder="01XXXXXXXXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-secondary">Transaction ID</label>
                                <input type="text" name="mfs_transaction_id" id="ipdbEditPayMfsTxn"
                                    class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Notes & Files Section --}}
                    <div class="ipdb-pay-section">
                        <div class="ipdb-pay-section-title">
                            <i class="bi bi-paperclip me-1"></i> Notes &amp; Attachments
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-secondary">Notes</label>
                                <textarea name="notes" id="ipdbEditPayNotes" class="form-control ipdb-pay-notes" rows="2"
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
                    <button type="submit" class="btn ipdb-pay-save-btn px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Delete Form --}}
<form id="ipdbDeletePaymentForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
    <input type="hidden" name="source" value="billing">
</form>

<script>
    (function() {
        const modal = document.getElementById('ipdbAddPaymentModal');
        if (!modal) return;

        const via = modal.querySelector('#ipdbPayVia');
        const cardF = modal.querySelector('#ipdbPayCardFields');
        const chequeF = modal.querySelector('#ipdbPayChequeFields');
        const mfsF = modal.querySelector('#ipdbPayMfsFields');

        const toggleVia = () => {
            cardF.classList.toggle('d-none', via.value !== 'card');
            chequeF.classList.toggle('d-none', via.value !== 'cheque');
            mfsF.classList.toggle('d-none', via.value !== 'mfs');
        };
        via.addEventListener('change', toggleVia);

        const amt = modal.querySelector('#ipdbPayAmt');
        const vat = modal.querySelector('#ipdbPayVat');
        const tax = modal.querySelector('#ipdbPayTax');
        const dis = modal.querySelector('#ipdbPayDis');
        const net = modal.querySelector('#ipdbPayNet');
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
        const viewModalEl = document.getElementById('ipdbViewPaymentModal');
        const editModalEl = document.getElementById('ipdbEditPaymentModal');
        const deleteForm = document.getElementById('ipdbDeletePaymentForm');
        if (!viewModalEl || !editModalEl || !deleteForm) {
            console.error('ipdb-payments: modal elements missing', {
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
            net: document.getElementById('ipdbViewPayNet'),
            date: document.getElementById('ipdbViewPayDate'),
            invoice: document.getElementById('ipdbViewPayInvoice'),
            type: document.getElementById('ipdbViewPayType'),
            status: document.getElementById('ipdbViewPayStatus'),
            section: document.getElementById('ipdbViewPaySection'),
            payVia: document.getElementById('ipdbViewPayVia'),
            receivedBy: document.getElementById('ipdbViewPayReceivedBy'),
            amount: document.getElementById('ipdbViewPayAmount'),
            vat: document.getElementById('ipdbViewPayVat'),
            tax: document.getElementById('ipdbViewPayTax'),
            discount: document.getElementById('ipdbViewPayDiscount'),
            notes: document.getElementById('ipdbViewPayNotes'),
            cardSection: document.getElementById('ipdbViewPayCardSection'),
            cardNo: document.getElementById('ipdbViewPayCardNo'),
            cardType: document.getElementById('ipdbViewPayCardType'),
            chequeSection: document.getElementById('ipdbViewPayChequeSection'),
            chequeName: document.getElementById('ipdbViewPayChequeName'),
            chequeNo: document.getElementById('ipdbViewPayChequeNo'),
            chequeDate: document.getElementById('ipdbViewPayChequeDate'),
            mfsSection: document.getElementById('ipdbViewPayMfsSection'),
            mfsType: document.getElementById('ipdbViewPayMfsType'),
            mfsNo: document.getElementById('ipdbViewPayMfsNo'),
            mfsTxn: document.getElementById('ipdbViewPayMfsTxn'),
        };

        const editForm = document.getElementById('ipdbEditPaymentForm');
        const edit = {
            type: document.getElementById('ipdbEditPayType'),
            via: document.getElementById('ipdbEditPayVia'),
            date: document.getElementById('ipdbEditPayDate'),
            receivedBy: document.getElementById('ipdbEditPayReceivedBy'),
            status: document.getElementById('ipdbEditPayStatus'),
            amount: document.getElementById('ipdbEditPayAmt'),
            vat: document.getElementById('ipdbEditPayVat'),
            tax: document.getElementById('ipdbEditPayTax'),
            discount: document.getElementById('ipdbEditPayDis'),
            net: document.getElementById('ipdbEditPayNet'),
            notes: document.getElementById('ipdbEditPayNotes'),
            cardFields: document.getElementById('ipdbEditPayCardFields'),
            cardNo: document.getElementById('ipdbEditPayCardNo'),
            cardType: document.getElementById('ipdbEditPayCardType'),
            chequeFields: document.getElementById('ipdbEditPayChequeFields'),
            chequeName: document.getElementById('ipdbEditPayChequeName'),
            chequeNo: document.getElementById('ipdbEditPayChequeNo'),
            chequeDate: document.getElementById('ipdbEditPayChequeDate'),
            mfsFields: document.getElementById('ipdbEditPayMfsFields'),
            mfsType: document.getElementById('ipdbEditPayMfsType'),
            mfsNo: document.getElementById('ipdbEditPayMfsNo'),
            mfsTxn: document.getElementById('ipdbEditPayMfsTxn'),
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
            el.className = 'ipdb-pill ipdb-pill-soft-' + (color || 'info');
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
                console.error('ipdb-payment-row: failed to decode data-payment', e);
                return null;
            }
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.ipdb-payment-row .ipdb-action-icon');
            if (!btn) return;
            const row = btn.closest('.ipdb-payment-row');
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
