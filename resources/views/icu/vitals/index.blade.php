@extends('backend.layouts.master')

@section('title', 'Vitals — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Vital Signs</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }} ({{ $admission->bed?->name ?? '-' }})
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('icu.admissions.thresholds.index', $admission->id) }}"
                    class="btn btn-sm btn-outline-primary">Thresholds</a>
                <a href="{{ route('icu.admissions.alerts.index', $admission->id) }}"
                    class="btn btn-sm btn-outline-warning">Alerts</a>
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        @if ($latest)
            @php
                $sevColor = match ($latest->severity) {
                    'Critical' => 'danger',
                    'Warning'  => 'warning',
                    default    => 'success',
                };
            @endphp
            <div class="card mt-2 border-{{ $sevColor }}-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Latest Reading
                            <span class="badge bg-{{ $sevColor }} ms-2">{{ $latest->severity }}</span>
                        </h6>
                        <small class="text-muted">{{ $latest->recorded_at?->format('Y-m-d H:i') }}</small>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col"><div class="text-muted small">HR</div><div class="fs-4">{{ $latest->heart_rate ?? '-' }}</div></div>
                        <div class="col"><div class="text-muted small">BP</div><div class="fs-4">{{ $latest->systolic_bp ?? '-' }}/{{ $latest->diastolic_bp ?? '-' }}</div></div>
                        <div class="col"><div class="text-muted small">SpO₂</div><div class="fs-4">{{ $latest->spo2 ?? '-' }}%</div></div>
                        <div class="col"><div class="text-muted small">RR</div><div class="fs-4">{{ $latest->respiratory_rate ?? '-' }}</div></div>
                        <div class="col"><div class="text-muted small">Temp</div><div class="fs-4">{{ $latest->temperature ?? '-' }}°C</div></div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Manual Entry</h6>
                <form method="POST" action="{{ route('icu.admissions.vitals.store', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Recorded At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="recorded_at"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-1"><label class="form-label small">HR</label>
                        <input type="number" name="heart_rate" class="form-control form-control-sm"></div>
                    <div class="col-md-1"><label class="form-label small">SBP</label>
                        <input type="number" name="systolic_bp" class="form-control form-control-sm"></div>
                    <div class="col-md-1"><label class="form-label small">DBP</label>
                        <input type="number" name="diastolic_bp" class="form-control form-control-sm"></div>
                    <div class="col-md-1"><label class="form-label small">SpO₂</label>
                        <input type="number" step="0.1" name="spo2" class="form-control form-control-sm"></div>
                    <div class="col-md-1"><label class="form-label small">RR</label>
                        <input type="number" name="respiratory_rate" class="form-control form-control-sm"></div>
                    <div class="col-md-1"><label class="form-label small">Temp °C</label>
                        <input type="number" step="0.1" name="temperature" class="form-control form-control-sm"></div>
                    <div class="col-md-3">
                        <label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Save Vitals</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body p-2">
                <div class="d-flex align-items-center justify-content-between px-2 pt-2">
                    <h6 class="card-title mb-0">History</h6>
                    <span class="badge bg-primary">Total {{ $logs->count() }}</span>
                </div>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:140px;">Time</th>
                            <th>HR</th><th>BP</th><th>SpO₂</th><th>RR</th><th>Temp</th>
                            <th style="width:100px;">Severity</th>
                            <th style="width:80px;">Source</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $i => $l)
                            @php
                                $c = match ($l->severity) {
                                    'Critical' => 'danger',
                                    'Warning'  => 'warning',
                                    default    => 'success',
                                };
                            @endphp
                            <tr class="vitals-history-row" data-index="{{ $i }}">
                                <td class="ps-2"><small>{{ $l->recorded_at?->format('Y-m-d H:i') }}</small></td>
                                <td>{{ $l->heart_rate ?? '-' }}</td>
                                <td>{{ $l->systolic_bp ?? '-' }}/{{ $l->diastolic_bp ?? '-' }}</td>
                                <td>{{ $l->spo2 ?? '-' }}</td>
                                <td>{{ $l->respiratory_rate ?? '-' }}</td>
                                <td>{{ $l->temperature ?? '-' }}</td>
                                <td><span class="badge bg-{{ $c }}">{{ $l->severity }}</span></td>
                                <td><small>{{ $l->source_type }}</small></td>
                                <td><small>{{ $l->remarks ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No vitals recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->count())
                <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Rows per page:</span>
                        <select id="vitalsHistoryPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span id="vitalsHistoryRangeInfo"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="vitalsHistoryPagination"></ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.vitals-history-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('vitalsHistoryPerPage');
            const pagination = document.getElementById('vitalsHistoryPagination');
            const rangeInfo = document.getElementById('vitalsHistoryRangeInfo');
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

            perPageSel.addEventListener('change', () => { currentPage = 1; render(); });
            render();
        })();
    </script>
@endsection
