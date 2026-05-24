<div class="tab-pane fade" id="opd-transaction" role="tabpanel">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-1">OPD Transactions History (All Activities - Real-time)</h5>
                    <div class="text-muted small">
                        Total <strong>{{ number_format($totalTransactions) }}</strong> transactions
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm opdb-search" style="width: 260px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input id="opdbSearch" type="text" class="form-control border-start-0 ps-0"
                            placeholder="Search invoice, patient, doctor...">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table opdb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Date &amp; Time</th>
                            <th>Invoice No</th>
                            <th>Type</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Case Ref.</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Net Amount</th>
                            <th>Payment Via</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opdTransactions as $i => $tx)
                            @php
                                $typeKey = strtolower($tx->type ?? '');
                                $typeColor = match ($typeKey) {
                                    'payment' => 'success',
                                    'refund' => 'danger',
                                    'adjustment' => 'warning',
                                    'advance' => 'primary',
                                    default => 'secondary',
                                };

                                $statusKey = strtolower($tx->status ?? '');
                                $statusColor = match ($statusKey) {
                                    'successed', 'success' => 'info',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    'canceled', 'cancelled' => 'purple',
                                    default => 'warning',
                                };
                            @endphp
                            <tr class="opdb-row" data-index="{{ $i }}">
                                <td class="ps-4">
                                    @if ($tx->payment_date)
                                        <div class="fw-semibold">{{ $tx->payment_date->format('d/m/Y') }}</div>
                                        <div class="text-muted small">{{ $tx->payment_date->format('h:i A') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="opdb-bill-link">{{ $tx->invoice_no ?? '-' }}</a>
                                </td>
                                <td>
                                    <span class="opdb-pill opdb-pill-soft-{{ $typeColor }}">
                                        {{ strtoupper($tx->type ?? '-') }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $tx->patient?->patient_name ?? '-' }}</td>
                                <td>{{ $tx->opdPatient?->doctor?->name ?? '-' }}</td>
                                <td>{{ $tx->case_id ? "C#{$tx->case_id}" : '-' }}</td>
                                <td>{{ number_format((float) $tx->amount, 2) }}</td>
                                <td>{{ number_format((float) $tx->discount, 2) }}</td>
                                <td>{{ number_format((float) $tx->net_amount, 2) }}</td>
                                <td>{{ strtoupper($tx->payment_via ?? '-') }}</td>
                                <td>
                                    <span class="opdb-pill opdb-pill-{{ $statusColor }}">
                                        {{ strtoupper($tx->status ?? '-') }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        @if ($tx->opd_patient_id)
                                            <a href="{{ route('opd-patients.show', $tx->opd_patient_id) }}"
                                               class="btn btn-outline-primary" title="View OPD">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('opd-patients.bill', $tx->opd_patient_id) }}"
                                               class="btn btn-outline-danger" target="_blank" title="PDF / Print Bill">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary" disabled title="No OPD linkage">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No OPD transactions found.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="opdbNoResults" style="display:none;">
                            <td colspan="12" class="text-center text-muted py-5">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No transactions match your search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($totalTransactions > 0)
            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Rows per page:</span>
                    <select id="opdbPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="opdbRangeInfo"></span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="opdbPagination"></ul>
                </nav>
            </div>
        @endif
    </div>
</div>

<style>
    .opdb-pill-soft-secondary {
        background-color: #e5e7eb;
        color: #374151;
    }
    .opdb-pill-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    .opdb-pill-soft-warning {
        background-color: #fef3c7;
        color: #92400e;
    }
</style>
