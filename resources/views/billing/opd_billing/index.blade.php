@extends('backend.layouts.master')

@section('title', 'OPD Patients Billing')

@section('content')
    @php
        $fmt = fn($n) => number_format((float) $n, 2);

        $stats = [
            [
                'label' => 'TOTAL BILL',
                'value' => number_format(12458260),
                'icon' => 'bi-bag',
                'color' => 'primary',
            ],
            [
                'label' => 'TOTAL REVENUE',
                'value' => number_format(1250000),
                'icon' => 'bi-currency-dollar',
                'color' => 'success',
            ],
            [
                'label' => 'TOTAL PENDING',
                'value' => number_format(250000),
                'icon' => 'bi-clock-history',
                'color' => 'warning',
            ],
        ];

        $totalTransactions = $opdTransactions->count();
        $totalOpdPatients  = $opdPatients->count();
    @endphp

    <style>
        .opdb-stat-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .opdb-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .opdb-stat-label {
            font-size: .7rem;
            letter-spacing: .05em;
            color: #9ca3af;
            font-weight: 700;
        }

        .opdb-stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            color: #111827;
        }

        .opdb-tabs {
            border-bottom: 1px solid #e5e7eb;
        }

        .opdb-tabs .nav-link {
            color: #6b7280;
            font-weight: 600;
            font-size: .9rem;
            padding: .55rem 1.1rem;
            margin: .35rem .25rem;
            border: 0;
            border-radius: 8px;
            background: transparent;
            transition: background-color .15s ease, color .15s ease;
        }

        .opdb-tabs .nav-link:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .opdb-tabs .nav-link.active {
            color: #fff;
            background: #2563eb;
        }

        .opdb-bill-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }

        .opdb-bill-link:hover {
            text-decoration: underline;
        }

        .opdb-pill {
            font-weight: 700;
            padding: .35rem .7rem;
            border-radius: 6px;
            font-size: .7rem;
            letter-spacing: .04em;
            display: inline-block;
        }

        .opdb-pill-soft-success {
            background-color: #d1fae5;
            color: #047857;
        }

        .opdb-pill-soft-primary {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .opdb-pill-success {
            background-color: #10b981;
            color: #fff;
        }

        .opdb-pill-info {
            background-color: #1d9bf0;
            color: #fff;
        }

        .opdb-pill-warning {
            background-color: #f97316;
            color: #fff;
        }

        .opdb-pill-purple {
            background-color: #8b5cf6;
            color: #fff;
        }

        .opdb-pill-danger {
            background-color: #ef4444;
            color: #fff;
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
            font-size: .78rem;
            letter-spacing: .02em;
            border-bottom: 1px solid #e5e7eb;
            border-top: 0;
        }

        .opdb-table tbody td {
            vertical-align: middle;
            font-size: .85rem;
            border-color: #f1f3f5;
        }

        .opdb-action-btn {
            background: transparent;
            border: 0;
            color: #6b7280;
            padding: .25rem .5rem;
        }

        .opdb-action-btn:hover {
            color: #111827;
        }
    </style>

    <div class="container-fluid py-3">

        {{-- Header Row --}}
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">OPD Billing</h2>
                <div class="text-muted small">
                    All OPD, Transactions and History - Unified in One Place
                </div>
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

        {{-- Stat Cards --}}
        <div class="row g-3 mb-3">
            @foreach ($stats as $card)
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card opdb-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="opdb-stat-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }}">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="opdb-stat-label text-uppercase">{{ $card['label'] }}</div>
                                <div class="opdb-stat-value">{{ $card['value'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body py-0 px-3">
                <ul class="nav opdb-tabs" id="opdbTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link  active" id="all-opd-tab" data-bs-toggle="tab" data-bs-target="#all-opd"
                            type="button" role="tab">
                            All OPD
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="opd-transaction-tab" data-bs-toggle="tab"
                            data-bs-target="#opd-transaction" type="button" role="tab">
                            <i class="bi bi-funnel me-1"></i> OPD Transaction
                        </button>
                    </li>

                </ul>
            </div>
        </div>

        <div class="tab-content">

            @include('components.billing.opd.all-opd-list')
            {{-- OPD Transaction Tab --}}
            @include('components.billing.opd.all-transactions')
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

        (function () {
            const rows = Array.from(document.querySelectorAll('.opdb-patient-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('opdbPatientsPerPage');
            const pagination = document.getElementById('opdbPatientsPagination');
            const rangeInfo = document.getElementById('opdbPatientsRangeInfo');
            const searchInput = document.getElementById('opdbPatientsSearch');
            const noResults = document.getElementById('opdbPatientsNoResults');
            const rowText = rows.map(r => r.textContent.toLowerCase().replace(/\s+/g, ' '));
            let currentPage = 1;

            function render() {
                const q = (searchInput?.value || '').trim().toLowerCase();
                const filtered = q
                    ? rows.filter((r, i) => rowText[i].includes(q))
                    : rows;

                const perPage = parseInt(perPageSel.value, 10);
                const total = filtered.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                if (currentPage > totalPages) currentPage = totalPages;
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, total);

                rows.forEach(r => { r.style.display = 'none'; });
                filtered.slice(start, end).forEach(r => { r.style.display = ''; });

                if (noResults) noResults.style.display = (q && total === 0) ? '' : 'none';

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

            perPageSel.addEventListener('change', () => { currentPage = 1; render(); });
            if (searchInput) {
                searchInput.addEventListener('input', () => { currentPage = 1; render(); });
            }
            render();
        })();

        (function () {
            const rows = Array.from(document.querySelectorAll('.opdb-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('opdbPerPage');
            const pagination = document.getElementById('opdbPagination');
            const rangeInfo = document.getElementById('opdbRangeInfo');
            const searchInput = document.getElementById('opdbSearch');
            const noResults = document.getElementById('opdbNoResults');
            const rowText = rows.map(r => r.textContent.toLowerCase().replace(/\s+/g, ' '));
            let currentPage = 1;

            function render() {
                const q = (searchInput?.value || '').trim().toLowerCase();
                const filtered = q
                    ? rows.filter((r, i) => rowText[i].includes(q))
                    : rows;

                const perPage = parseInt(perPageSel.value, 10);
                const total = filtered.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                if (currentPage > totalPages) currentPage = totalPages;
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, total);

                rows.forEach(r => { r.style.display = 'none'; });
                filtered.slice(start, end).forEach(r => { r.style.display = ''; });

                if (noResults) noResults.style.display = (q && total === 0) ? '' : 'none';

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

            perPageSel.addEventListener('change', () => { currentPage = 1; render(); });
            if (searchInput) {
                searchInput.addEventListener('input', () => { currentPage = 1; render(); });
            }
            render();
        })();
    </script>
@endsection
