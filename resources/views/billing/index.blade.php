@extends('backend.layouts.master')

@section('title', 'Billing')

@section('content')
    @php
        $fmt = fn($n) => number_format((float) $n, 2);

        $statCards = [
            [
                'label' => 'TOTAL BILLING',
                'value' => $fmt($stats['total_billing_sum']),
                'sub' => 'Across ' . number_format($stats['total_billing_count']) . ' Billing',
                'icon' => 'bi-bag',
                'color' => 'primary',
            ],
            [
                'label' => "TODAY'S BILLING",
                'value' => $fmt($stats['today_billing_sum']),
                'sub' => number_format($stats['today_billing_count']) . ' Transactions',
                'icon' => 'bi-bag-check',
                'color' => 'success',
            ],
            [
                'label' => "TODAY'S OPD BILLING",
                'value' => $fmt($stats['today_opd_sum']),
                'sub' => number_format($stats['today_opd_count']) . ' Payment',
                'icon' => 'bi-graph-up-arrow',
                'color' => 'info',
            ],
            [
                'label' => "TODAY'S Ipd BILLING",
                'value' => $fmt($stats['today_ipd_sum']),
                'sub' => number_format($stats['today_ipd_count']) . ' Payment',
                'icon' => 'bi-brightness-high',
                'color' => 'warning',
            ],
            [
                'label' => 'PENDING PAYMENT',
                'value' => number_format($stats['pending_count']),
                'sub' => 'All department',
                'icon' => 'bi-shield-exclamation',
                'color' => 'purple',
            ],
            [
                'label' => 'TOTAL REVENUE',
                'value' => $fmt($stats['revenue_sum']),
                'sub' => 'Total Payment',
                'icon' => 'bi-exclamation-triangle',
                'color' => 'danger',
            ],
        ];

        $typeColorMap = [
            'payment' => 'success',
            'refund' => 'warning',
            'adjustment' => 'primary',
            'advance' => 'info',
        ];

        $statusColorMap = [
            'successed' => 'info',
            'paid' => 'info',
            'pending' => 'warning',
            'failed' => 'danger',
            'canceled' => 'purple',
        ];
    @endphp

    <style>
        .billing-stat-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .billing-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .billing-stat-label {
            font-size: .65rem;
            letter-spacing: .04em;
            color: #6c757d;
            font-weight: 800;
        }

        .billing-stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.1;
            color: #1a1a1a;
        }

        .billing-stat-sub {
            font-size: .65rem;
            color: #8a8a8a;
        }

        .bg-purple-subtle {
            background-color: #efe6ff !important;
        }

        .text-purple {
            color: #7c3aed !important;
        }

        .bg-purple {
            background-color: #7c3aed !important;
            color: #fff !important;
        }

        .badge-soft-success {
            background-color: #d1fae5;
            color: #047857;
        }

        .badge-soft-primary {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .badge-soft-info {
            background-color: #1d9bf0;
            color: #fff;
        }

        .badge-soft-warning {
            background-color: #f97316;
            color: #fff;
        }

        .badge-soft-purple {
            background-color: #a855f7;
            color: #fff;
        }

        .badge-pill-type {
            font-weight: 700;
            padding: .4rem .7rem;
            border-radius: 6px;
            font-size: .7rem;
            letter-spacing: .03em;
        }

        .badge-pill-status {
            font-weight: 700;
            padding: .35rem .75rem;
            border-radius: 6px;
            font-size: .7rem;
            letter-spacing: .04em;
        }

        /* .billing-table thead th {
                                        background: #fff; color: #6b7280; font-weight: 600;
                                        font-size: .78rem; letter-spacing: .02em; border-bottom: 1px solid #e5e7eb;
                                    }
                                    .billing-table tbody td { vertical-align: middle; font-size: .85rem; }
                                    .billing-table tbody tr { border-bottom: 1px solid #f1f3f5; } */
        .billing-bill-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        .billing-bill-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="container-fluid py-3">

        {{-- Header Row --}}
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Billing</h2>
                <div class="text-muted small">
                    All Pharmacy Operations, Transactions, Inventory, and History - Unified in One Place
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 small">
                <span class="d-inline-flex align-items-center text-muted">
                    <i class="bi bi-calendar3 me-2"></i>
                    <span id="billingToday"></span>
                </span>
                <span class="d-inline-flex align-items-center text-muted">
                    <i class="bi bi-clock me-2"></i>
                    <span id="billingClock"></span>
                </span>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="row g-3 mb-4">
            @foreach ($statCards as $card)
                <div class="col-12 col-sm-6 col-md-6 col-xl-3">
                    <div class="card billing-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="billing-stat-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }}">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="billing-stat-label text-uppercase text-truncate">{{ $card['label'] }}</div>
                                <div class="billing-stat-value">{{ $card['value'] }}</div>
                                <div class="billing-stat-sub">{{ $card['sub'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- All Billing Table --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">All Billing</h5>
                        <div class="text-muted small">
                            <strong>{{ number_format($transactions->count()) }}</strong> transactions
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Export
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th class="ps-4">Date &amp; Time</th>
                                <th>Bill No</th>
                                <th>Bill Type</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>VAT</th>
                                <th>Tax</th>
                                <th>Discount</th>
                                <th>Paid Amount</th>
                                <th>Payment Status</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $i => $tx)
                                @php
                                    $paymentDate = $tx->payment_date
                                        ? \Illuminate\Support\Carbon::parse($tx->payment_date)
                                        : null;
                                    $doctorName =
                                        $tx->opdPatient?->doctor?->name ?? ($tx->ipdPatient?->doctor?->name ?? '—');
                                    $patientName = $tx->patient?->patient_name ?? '—';
                                    $typeKey = strtolower((string) $tx->type);
                                    $statusKey = strtolower((string) $tx->status);
                                    $typeColor = $typeColorMap[$typeKey] ?? 'primary';
                                    $statusColor = $statusColorMap[$statusKey] ?? 'primary';
                                @endphp
                                <tr class="billing-row" data-index="{{ $i }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $paymentDate ? $paymentDate->format('d/m/Y') : '—' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $paymentDate ? $paymentDate->format('h:i A') : '' }}</div>
                                    </td>
                                    <td>
                                        <a href="#" class="billing-bill-link">{{ $tx->invoice_no ?? '—' }}</a>
                                    </td>
                                    <td>
                                        <span class="badge-pill-type badge-soft-{{ $typeColor }}">
                                            {{ strtoupper($tx->type ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $patientName }}</td>
                                    <td class="fw-semibold">{{ $doctorName }}</td>
                                    <td>{{ strtoupper($tx->section ?? '—') }}</td>
                                    @php
                                        $amt      = (float) $tx->amount;
                                        $vatPct   = (float) $tx->vat;
                                        $taxPct   = (float) $tx->tax;
                                        $discPct  = (float) $tx->discount;
                                        $vatAmt   = $amt * $vatPct / 100;
                                        $taxAmt   = $amt * $taxPct / 100;
                                        $subAmt   = $amt + $vatAmt + $taxAmt;
                                        $discAmt  = $subAmt * $discPct / 100;
                                    @endphp
                                    <td class="fw-semibold">{{ number_format($amt, 2) }}</td>
                                    <td>{{ number_format($vatAmt, 2) }}({{ rtrim(rtrim(number_format($vatPct, 2, '.', ''), '0'), '.') }}%)</td>
                                    <td>{{ number_format($taxAmt, 2) }}({{ rtrim(rtrim(number_format($taxPct, 2, '.', ''), '0'), '.') }}%)</td>
                                    <td>{{ number_format($discAmt, 2) }}({{ rtrim(rtrim(number_format($discPct, 2, '.', ''), '0'), '.') }}%)</td>
                                    <td class="fw-semibold">{{ number_format((float) $tx->net_amount, 2) }}</td>
                                    <td>
                                        <span class="badge-pill-status badge-soft-{{ $statusColor }}">
                                            {{ strtoupper($tx->status ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-info" title="View Details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentDetailsModal{{ $tx->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-danger py-4">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @foreach ($transactions as $payment)
                        @php
                            $files = $payment->files ? (json_decode($payment->files, true) ?: []) : [];
                        @endphp
                        <div class="modal fade" id="paymentDetailsModal{{ $payment->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Transaction Details — {{ $payment->invoice_no }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                <tr>
                                                    <th style="width:35%">Invoice No</th>
                                                    <td>{{ $payment->invoice_no ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Date</th>
                                                    <td>{{ format_datetime($payment->payment_date) ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Type</th>
                                                    <td>{{ ucfirst($payment->type ?? '-') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Section</th>
                                                    <td>{{ ucfirst($payment->section ?? '-') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Via</th>
                                                    <td>{{ ucfirst($payment->payment_via ?? '-') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Amount</th>
                                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>VAT(%)</th>
                                                    <td>{{ number_format($payment->vat, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Tax(%)</th>
                                                    <td>{{ number_format($payment->tax, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Discount(%)</th>
                                                    <td>{{ number_format($payment->discount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Net Amount</th>
                                                    <td class="fw-semibold">{{ number_format($payment->net_amount, 2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        @php
                                                            $map2 = [
                                                                'successed' => 'success',
                                                                'pending' => 'warning',
                                                                'failed' => 'danger',
                                                                'canceled' => 'secondary',
                                                            ];
                                                            $cls2 = $map2[$payment->status] ?? 'secondary';
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $cls2 }}">{{ ucfirst($payment->status) }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Received By</th>
                                                    <td>{{ $payment->received_by ?? '-' }}</td>
                                                </tr>
                                                @if ($payment->payment_via === 'card')
                                                    <tr>
                                                        <th>Card No</th>
                                                        <td>{{ $payment->card_no ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Card Type</th>
                                                        <td>{{ ucfirst($payment->card_type ?? '-') }}</td>
                                                    </tr>
                                                @elseif ($payment->payment_via === 'cheque')
                                                    <tr>
                                                        <th>Cheque Name</th>
                                                        <td>{{ $payment->cheque_name ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cheque No</th>
                                                        <td>{{ $payment->cheque_no ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cheque Date</th>
                                                        <td>{{ format_datetime($payment->cheque_date) ?? '-' }}</td>
                                                    </tr>
                                                @elseif ($payment->payment_via === 'mfs')
                                                    <tr>
                                                        <th>MFS Type</th>
                                                        <td>{{ ucfirst($payment->mfs_type ?? '-') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>MFS No</th>
                                                        <td>{{ $payment->mfs_no ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>MFS Transaction ID</th>
                                                        <td>{{ $payment->mfs_transaction_id ?? '-' }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <th>Notes</th>
                                                    <td>{{ $payment->notes ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Attachments</th>
                                                    <td>
                                                        @if (count($files))
                                                            <ul class="mb-0 ps-3">
                                                                @foreach ($files as $f)
                                                                    <li><a href="{{ asset('storage/' . $f) }}"
                                                                            target="_blank">{{ basename($f) }}</a></li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Created At</th>
                                                    <td>{{ format_datetime($payment->created_at) ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Updated At</th>
                                                    <td>{{ format_datetime($payment->updated_at) ?? '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Rows per page:</span>
                    <select id="billingPerPage" class="form-select form-select-sm" style="width:auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span id="billingRangeInfo"></span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="billingPagination"></ul>
                </nav>
            </div>
        </div>

    </div>

    <script>
        (function() {
            function updateClock() {
                const now = new Date();
                const dayOpts = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit'
                };
                const dateEl = document.getElementById('billingToday');
                const clockEl = document.getElementById('billingClock');
                if (dateEl) dateEl.textContent = now.toLocaleDateString('en-US', dayOpts);
                if (clockEl) {
                    let h = now.getHours();
                    const m = now.getMinutes().toString().padStart(2, '0');
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    clockEl.textContent = `${h.toString().padStart(2,'0')}:${m} ${ampm}`;
                }
            }
            updateClock();
            setInterval(updateClock, 30000);
        })();

        (function() {
            const rows = Array.from(document.querySelectorAll('.billing-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('billingPerPage');
            const pagination = document.getElementById('billingPagination');
            const rangeInfo = document.getElementById('billingRangeInfo');
            let currentPage = 1;

            function render() {
                const perPage = parseInt(perPageSel.value, 10);
                const total = rows.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                if (currentPage > totalPages) currentPage = totalPages;
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, total);

                rows.forEach((r, i) => {
                    r.style.display = (i >= start && i < end) ? '' : 'none';
                });

                rangeInfo.textContent = `Showing ${total ? start + 1 : 0}–${end} of ${total}`;

                let html = '';
                const mkItem = (label, page, disabled, active) =>
                    `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${page}">${label}</a>
                     </li>`;
                html += mkItem('&laquo;', currentPage - 1, currentPage === 1, false);
                for (let p = 1; p <= totalPages; p++) {
                    if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1) {
                        html += mkItem(p, p, false, p === currentPage);
                    } else if (Math.abs(p - currentPage) === 2) {
                        html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                    }
                }
                html += mkItem('&raquo;', currentPage + 1, currentPage === totalPages, false);
                pagination.innerHTML = html;

                pagination.querySelectorAll('a.page-link').forEach(a => {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        const p = parseInt(a.dataset.page, 10);
                        if (!isNaN(p) && p >= 1 && p <= totalPages) {
                            currentPage = p;
                            render();
                        }
                    });
                });
            }

            perPageSel.addEventListener('change', () => {
                currentPage = 1;
                render();
            });
            render();
        })();
    </script>
@endsection
