@extends('backend.layouts.master')

@section('title', $icuType . ' Code Blue')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-nowrap gap-3 align-items-center justify-content-between">
            <div class="flex-shrink-0">
                <h1 class="app-page-title mb-1">{{ $icuType }} Code Blue</h1>
                <div class="text-muted small">Create, manage and track all {{ $icuType }} clinical orders</div>
            </div>

            <form method="GET" class="d-flex flex-nowrap align-items-center gap-2">
                <input type="hidden" name="icu_type" value="{{ $icuType }}">

                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control"
                        placeholder="Search...">
                </div>

                {{-- <select name="status" class="form-select form-select-sm" style="width:auto;">
                    <option value="">All Status</option>
                    @foreach (['Activated', 'TeamNotified', 'ResponseStarted', 'InProgress', 'Stabilized', 'Closed'] as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select> --}}

                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                    class="form-control form-control-sm" style="width:auto;">
                <span class="text-muted small">to</span>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                    class="form-control form-control-sm" style="width:auto;">

                <button class="btn btn-sm btn-primary text-nowrap">
                    <i class="bi bi-funnel me-1"></i>Search
                </button>
                <a href="{{ route('icu.code-blue.index', ['icu_type' => $icuType]) }}"
                    class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>

        {{-- <div class="row g-2 mt-1 mb-2">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="text-muted small">Total Events</div>
                        <div class="fs-5 fw-bold">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="text-muted small">Open</div>
                        <div class="fs-5 fw-bold text-danger">{{ $stats['open'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="text-muted small">Stabilized</div>
                        <div class="fs-5 fw-bold text-success">{{ $stats['stabilized'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="text-muted small">Closed</div>
                        <div class="fs-5 fw-bold text-secondary">{{ $stats['closed'] }}</div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="card border shadow-sm rounded-3">
            <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>Currently Code Blue Patient
                </h6>
                <span class="badge bg-primary">Total {{ $rows->count() }}</span>
            </div>

            <div class="card-body p-0">
                @if ($rows->isEmpty())
                    <div class="text-center text-muted py-4">No Code Blue events for the selected range.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0 codeblue-table">
                            <thead class="table-light">
                                <tr class="small">
                                    <th class="text-center" style="width:36px;">#</th>
                                    <th>Case</th>
                                    <th>Patient</th>
                                    <th class="text-center">Age / Gender</th>
                                    <th>Bed</th>
                                    <th>Isolation</th>
                                    <th>Infection</th>
                                    <th>Activated</th>
                                    <th class="text-end" style="width:120px;">Fluid Balance (mL)</th>
                                    <th class="text-center" style="width:56px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows->values() as $i => $row)
                                    <tr class="codeblue-row small" data-index="{{ $i }}">
                                        <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <a href="{{ route('icu.admissions.emergency.show', [$row->admission_id, $row->id]) }}"
                                                class="fw-semibold d-block">{{ $row->icu_case_id }}</a>
                                            <div class="text-muted" style="font-size: 11px;">{{ $row->event_no ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $row->patient_name }}</div>
                                            @if ($row->mrn)
                                                <div class="text-muted" style="font-size: 11px;"> {{ $row->mrn ?? '-' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $row->age ? $row->age . 'Y' : '-' }}@if ($row->gender)
                                                / {{ $row->gender }}
                                            @endif
                                        </td>
                                        <td>{{ $row->bed_name ?? '-' }}</td>
                                        <td>
                                            @if ($row->isolation_type && $row->isolation_type !== 'None')
                                                <span
                                                    class="badge bg-info-subtle text-info">{{ $row->isolation_type }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $row->infection_name ?? '-' }}</div>
                                            @if ($row->infection_state)
                                                <div class="text-muted" style="font-size: 11px;">
                                                    {{ $row->infection_state }}</div>
                                            @endif
                                        </td>
                                        <td class="text-muted text-nowrap">
                                            {{ $row->activated_at?->format('d-m-Y h:i A') ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-between" style="font-size: 11px;">
                                                <span class="text-muted">In</span>
                                                <span>{{ number_format($row->intake_ml) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between" style="font-size: 11px;">
                                                <span class="text-muted">Out</span>
                                                <span>{{ number_format($row->output_ml) }}</span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between fw-semibold border-top pt-1 mt-1 {{ $row->balance_ml >= 0 ? 'text-success' : 'text-danger' }}">
                                                <span>Bal</span>
                                                <span>{{ ($row->balance_ml >= 0 ? '+' : '') . number_format($row->balance_ml) }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('icu.admissions.emergency.show', [$row->admission_id, $row->id]) }}"
                                                class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <span>Rows per page:</span>
                            <select id="codeBluePerPage" class="form-select form-select-sm" style="width:auto;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span id="codeBlueRangeInfo"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="codeBluePagination"></ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const rows = Array.from(document.querySelectorAll('.codeblue-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('codeBluePerPage');
            const pagination = document.getElementById('codeBluePagination');
            const rangeInfo = document.getElementById('codeBlueRangeInfo');
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
