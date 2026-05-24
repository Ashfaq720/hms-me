@extends('backend.layouts.master')

@section('title', $icuType . ' Mortality')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-nowrap gap-3 align-items-center justify-content-between">
            <div class="flex-shrink-0">
                <h1 class="app-page-title mb-1">Mortality Summary</h1>
                <div class="text-muted small">Create, manage and track all {{ $icuType }} clinical orders</div>
            </div>

            <form method="GET" class="d-flex flex-nowrap align-items-center gap-2">
                <input type="hidden" name="icu_type" value="{{ $icuType }}">

                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" value="{{ $search }}" class="form-control"
                        placeholder="Search case / patient / mobile...">
                </div>

                <input type="date" name="from" value="{{ $from?->format('Y-m-d') }}"
                    class="form-control form-control-sm" style="width:auto;">
                <span class="text-muted small">to</span>
                <input type="date" name="to" value="{{ $to?->format('Y-m-d') }}"
                    class="form-control form-control-sm" style="width:auto;">

                <button class="btn btn-sm btn-primary text-nowrap">
                    <i class="bi bi-funnel me-1"></i>Search
                </button>
                <a href="{{ route('icu.mortality.index', ['icu_type' => $icuType]) }}"
                    class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>

        <div class="card border shadow-sm rounded-3 mt-2">
            <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-heartbreak text-danger me-2"></i>Mortality Patients List
                </h6>
                <span class="badge bg-primary">Total {{ $admissions->count() }}</span>
            </div>

            <div class="card-body p-0">
                @if ($admissions->isEmpty())
                    <div class="text-center text-muted py-4">No mortality records found.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0 mortality-table">
                            <thead class="table-light">
                                <tr class="small">
                                    <th class="text-center" style="width:48px;">SN</th>
                                    <th>Case ID</th>
                                    <th>Patient</th>
                                    <th>Mobile</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th class="text-center">Type</th>
                                    <th>Source</th>
                                    <th>Bed</th>
                                    <th>Isolation</th>
                                    <th class="text-center">Vent</th>
                                    <th>Admitted</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admissions->values() as $i => $adm)
                                    @php
                                        $p = $adm->patient;
                                        $age = $p?->dob ? calculateAgeFromDob($p->dob) : '-';
                                    @endphp
                                    <tr class="mortality-row small" data-index="{{ $i }}">
                                        <td class="text-center fw-semibold text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <a href="{{ route('icu.admissions.mortality.show', $adm->id) }}"
                                                class="fw-semibold d-block text-primary">{{ $adm->icu_case_id }}</a>
                                            @if ($p?->mrn)
                                                <div class="text-muted" style="font-size: 11px;">{{ $p->mrn }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $p?->patient_name ?? '-' }}</div>
                                        </td>
                                        <td>{{ $p?->mobileno ?? '-' }}</td>
                                        <td>{{ $p?->gender ?? '-' }}</td>
                                        <td>{{ $age }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-info">{{ $adm->icu_type }}</span>
                                        </td>
                                        <td>{{ strtoupper($adm->source_type ?? '-') }}</td>
                                        <td>{{ $adm->bed?->name ?? '-' }}</td>
                                        <td>
                                            @if ($adm->isolation_type && $adm->isolation_type !== 'None')
                                                <span
                                                    class="badge bg-warning-subtle text-warning">{{ $adm->isolation_type }}</span>
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($adm->ventilator_required)
                                                <span class="text-warning fw-semibold">Yes</span>
                                            @else
                                                <span class="text-muted">No</span>
                                            @endif
                                        </td>
                                        <td class="text-muted text-nowrap">
                                            {{ $adm->admission_time?->format('Y-m-d H:i') ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge"
                                                style="background:#fde2e2;color:#c81e1e;border:1px solid #f5b5b5;">Death</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('icu.admissions.mortality.show', $adm->id) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
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
                            <select id="mortalityPerPage" class="form-select form-select-sm" style="width:auto;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span id="mortalityRangeInfo"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="mortalityPagination"></ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const rows = Array.from(document.querySelectorAll('.mortality-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('mortalityPerPage');
            const pagination = document.getElementById('mortalityPagination');
            const rangeInfo = document.getElementById('mortalityRangeInfo');
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
