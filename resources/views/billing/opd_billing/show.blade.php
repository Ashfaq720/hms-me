@extends('backend.layouts.master')

@section('title', 'OPD Patients Billing')

@section('content')
    @php
        $fmt = fn($n) => number_format((float) $n, 2);

        $patientName = $opdPatient->patient?->patient_name ?? '—';
        $patientAge =  calculateAgeFromDob($opdPatient->patient?->dob) ?? '—';
        $patientGender = $opdPatient->patient?->gender ?? '—';
        $caseId = $opdPatient->case_id ?? '—';
        $patientPhone = $opdPatient->patient?->mobileno ?? '—';
        $opdNo = $opdPatient->token_no ?? ($opdPatient->serial_no ?? ('OPD-' . $opdPatient->id));
        $visitDate = $opdPatient->visit_date
            ? \Illuminate\Support\Carbon::parse($opdPatient->visit_date)->format('d M Y, h:i A')
            : ($opdPatient->date ? $opdPatient->date->format('d M Y, h:i A') : '—');
        $doctorName = $opdPatient->doctor?->name ?? '—';
        $doctorDept = $opdPatient->department?->name ?? '—';
        $status = $opdPatient->status ?? '-';

        // Stable color picker for category pills based on category name
        $pillPalette = ['primary', 'purple', 'success', 'warning', 'info', 'danger', 'orange'];
        $pillColor = function ($name) use ($pillPalette) {
            if (!$name) {
                return 'primary';
            }
            return $pillPalette[crc32(strtolower($name)) % count($pillPalette)];
        };

        $charges = $opdPatient->charges ?? collect();
        $totalCharges = $charges->count();
        $transactions = $opdPatient->transactions ?? collect();
        $totalTransactions = $transactions->count();

        // Pill color for transaction status
        $statusPill = [
            'succeeded' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'cancelled' => 'danger',
        ];
        // Pill color for transaction type
        $typePill = [
            'payment' => 'success',
            'refund' => 'danger',
            'adjustment' => 'info',
            'advance' => 'primary',
        ];
    @endphp

    <style>
        .opdb-stat-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .opdb-tabs {
            border-bottom: 1px solid #e5e7eb;
        }

        .opdb-tabs .nav-link {
            color: #6b7280;
            font-weight: 600;
            font-size: .9rem;
            padding: .75rem 1.25rem;
            border: 0;
            border-bottom: 2px solid transparent;
            background: transparent;
        }

        .opdb-tabs .nav-link.active {
            color: #2563eb;
            background: #dbeafe;
            border-bottom: 2px solid #2563eb;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }

        .opdb-pill {
            font-weight: 700;
            padding: .3rem .65rem;
            border-radius: 6px;
            font-size: .7rem;
            letter-spacing: .02em;
            display: inline-block;
        }

        .opdb-pill-soft-primary {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .opdb-pill-soft-purple {
            background-color: #ede9fe;
            color: #6d28d9;
        }

        .opdb-pill-soft-success {
            background-color: #d1fae5;
            color: #047857;
        }

        .opdb-pill-soft-warning {
            background-color: #fef3c7;
            color: #b45309;
        }

        .opdb-pill-soft-info {
            background-color: #cffafe;
            color: #0e7490;
        }

        .opdb-pill-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .opdb-pill-soft-orange {
            background-color: #ffedd5;
            color: #c2410c;
        }

        .opdb-btn-add {
            background: #fbbf24;
            color: #111827;
            font-weight: 600;
            border: 0;
        }

        .opdb-btn-add:hover {
            background: #f59e0b;
            color: #111827;
        }

        .opdb-table thead th {
            background: #fff;
            color: #6b7280;
            font-weight: 600;
            font-size: .72rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            border-top: 0;
            padding-top: .9rem;
            padding-bottom: .9rem;
        }

        .opdb-table tbody td {
            vertical-align: middle;
            font-size: .85rem;
            border-color: #f1f3f5;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .opdb-status-badge {
            background: #d1fae5;
            color: #047857;
            font-weight: 600;
            padding: .45rem .9rem;
            border-radius: 8px;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .opdb-patient-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            background: #e5e7eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .opdb-info-label {
            font-size: .7rem;
            letter-spacing: .04em;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }

        .opdb-info-value {
            font-size: .88rem;
            font-weight: 600;
            color: #111827;
            line-height: 1.3;
        }

        .opdb-info-sub {
            font-size: .75rem;
            color: #6b7280;
        }

        .opdb-action-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            font-size: .8rem;
            margin-right: .25rem;
            transition: all .15s;
        }

        .opdb-action-icon:hover {
            color: #111827;
            border-color: #9ca3af;
        }

        .opdb-action-icon.view {
            color: #0ea5e9;
            border-color: #bae6fd;
        }

        .opdb-action-icon.edit {
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .opdb-action-icon.delete {
            color: #ef4444;
            border-color: #fecaca;
        }

        .opdb-clear-btn {
            background: #fff;
            border: 1px solid #fecaca;
            color: #ef4444;
            font-weight: 600;
            padding: .4rem .9rem;
            border-radius: 8px;
            font-size: .8rem;
        }

        .opdb-clear-btn:hover {
            background: #fee2e2;
        }

        .opdb-charges-name {
            font-weight: 600;
            color: #111827;
            line-height: 1.25;
        }

        .opdb-charges-sub {
            font-size: .72rem;
            color: #9ca3af;
            margin-top: .15rem;
        }
    </style>

    <div class="container-fluid py-3">

        {{-- Header Row --}}
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
            <div>
                <h2 class="fw-bold mb-1">OPD Charges</h2>
                <div class="text-muted small">All OPD Charges are here</div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3 small">
                <span class="d-inline-flex align-items-center text-muted">
                    <i class="bi bi-calendar3 me-2"></i>
                    <span id="opdbToday"></span>
                </span>
                <span class="d-inline-flex align-items-center text-muted">
                    <i class="bi bi-clock me-2"></i>
                    <span id="opdbClock"></span>
                </span>
            </div>
        </div>

        {{-- Patient Info Card --}}
        <div class="card opdb-stat-card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-auto d-flex align-items-center gap-3">
                        <span class="opdb-patient-avatar">
                            {{ strtoupper(substr($patientName, 0, 1)) }}
                        </span>
                        <div>
                            <div class="fw-bold" style="font-size:1.05rem;">{{ $patientName }}</div>
                            <div class="opdb-info-sub">{{ $patientAge }} / {{ $patientGender }}</div>
                            <div class="opdb-info-sub">Case ID: {{ $caseId }}</div>
                            <div class="opdb-info-sub">Phone: {{ $patientPhone }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="opdb-info-label">OPD No</div>
                        <div class="opdb-info-value">{{ $opdNo }}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="opdb-info-label">Visit Date</div>
                        <div class="opdb-info-value">{{ $visitDate }}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="opdb-info-label">Doctor</div>
                        <div class="opdb-info-value">{{ $doctorName }}</div>
                        <div class="opdb-info-sub">{{ $doctorDept }}</div>
                    </div>
                    <div class="col-6 col-md-auto ms-md-auto text-md-end">
                        <span class="opdb-status-badge">
                            <i class="bi bi-check-circle-fill"></i> {{ $status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body py-0 px-3">
                <ul class="nav opdb-tabs" id="opdbChargeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="charges-tab" data-bs-toggle="tab" data-bs-target="#charges"
                            type="button" role="tab">Charges</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment"
                            type="button" role="tab">Payment</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="refund-tab" data-bs-toggle="tab" data-bs-target="#refund"
                            type="button" role="tab">Refund</button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content">

            {{-- Charges Tab --}}
            @include('components.billing.opd.charges')

            {{-- Payment Tab --}}

            @include('components.billing.opd.payments')
            {{-- Refund Tab --}}
            <div class="tab-pane fade" id="refund" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-arrow-counterclockwise fs-1 d-block mb-2"></i>
                        Refund details will appear here.
                    </div>
                </div>
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
                const dateEl = document.getElementById('opdbToday');
                const clockEl = document.getElementById('opdbClock');
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
            const rows = Array.from(document.querySelectorAll('.opdb-charge-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('opdbChargesPerPage');
            const pagination = document.getElementById('opdbChargesPagination');
            const rangeInfo = document.getElementById('opdbChargesRangeInfo');
            const rangeInfoTop = document.getElementById('opdbChargesRangeInfoTop');
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
                if (rangeInfoTop) {
                    rangeInfoTop.textContent =
                        `Showing ${total ? start + 1 : 0}-${end} of ${total.toLocaleString()} transactions`;
                }

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

        (function() {
            const rows = Array.from(document.querySelectorAll('.opdb-payment-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('opdbPaymentsPerPage');
            const pagination = document.getElementById('opdbPaymentsPagination');
            const rangeInfo = document.getElementById('opdbPaymentsRangeInfo');
            const rangeInfoTop = document.getElementById('opdbPaymentsRangeInfoTop');
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
                if (rangeInfoTop) {
                    rangeInfoTop.textContent =
                        `Showing ${total ? start + 1 : 0}-${end} of ${total.toLocaleString()} transactions`;
                }

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
