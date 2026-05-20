@extends('backend.layouts.master')

@section('title', 'Ipd Patients')

@section('content')
    <div class="container">

        {{-- Page Head --}}
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Ipd Patient List</h1>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-dark waves-effect waves-light" data-bs-toggle="modal"
                    data-bs-target="#hcIpdCheckinModal" title="Health Card IPD Check-in">
                    <i class="bi bi-credit-card me-1"></i> HC Check-in
                </button>
                <a href="{{ route('ipd-patients.create') }}" class="btn btn-primary waves-effect waves-light">
                    <i class="fi fi-rr-plus me-1"></i> Add Ipd Patient
                </a>
            </div>
            @include('patients._health_card_ipd_checkin_modal')
        </div>

        {{-- Table --}}
        <div class="row mt-1">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <div class="position-relative ms-auto" style="width: 260px; max-width: 100%;">
                            <input type="text" id="ipdSearch"
                                class="form-control form-control-sm ps-4 mb-2"
                                placeholder="Search IPD patients...">
                        </div>
                    </div>

                    <div class="card-body px-2 pt-2 pb-0 gradient-layer" style="min-height: 400px;">
                        <table id="dt_patientsx" class="table table-sm table-hover display table-row-rounded mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-2" style="width:35px;">SN</th>
                                    <th style="width:90px;">Ipd No.</th>
                                    <th>Patient Name</th>
                                    <th style="width:110px;">Mobile No</th>
                                    <th style="width:70px;">Gender</th>
                                    <th>Doctor</th>
                                    <th style="width:120px;">Department</th>
                                    <th style="width:155px;">Admission Date</th>
                                    <th style="width:115px;">Admission Type</th>
                                    <th style="width:90px;">Bed</th>
                                    <th style="width:75px;">Status</th>
                                    <th style="width:50px;" class="text-end pe-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ipdPatients as $index => $ipd)
                                    <tr class="ipd-row" data-index="{{ $index }}">
                                        <td class="ps-2 text-muted">{{ $loop->iteration }}</td>

                                        <td>
                                            <a href="{{ route('ipd-patients.show', $ipd->id) }}"
                                                class="fw-semibold">{{ $ipd->ipd_no ?? '' }}</a>
                                        </td>

                                        <td>
                                            <div class="fw-semibold lh-sm">{{ $ipd->patient?->patient_name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $ipd->patient?->mrn ?? '' }}
                                            </div>
                                        </td>

                                        <td>{{ $ipd->patient?->mobileno }}</td>

                                        <td>{{ $ipd->patient?->gender ?? '-' }}</td>

                                        <td>{{ $ipd->doctor?->name ?? '-' }}</td>

                                        <td>{{ $ipd->department?->name ?? '-' }}</td>

                                        <td>{{ $ipd->admission_date ?? '' }}</td>

                                        <td>{{ $ipd->admission_type ?? '-' }}</td>

                                        <td>
                                            @php $latestAllocation = $ipd->bedAllocations?->first(); @endphp
                                            @if ($latestAllocation && $latestAllocation->bed && ($latestAllocation->status ?? null) !== 'Discharged')
                                                @php $isIcu = ($latestAllocation->allocation_type ?? 'bed') === 'icu'; @endphp
                                                <span class="badge bg-{{ $isIcu ? 'danger' : 'info' }}">
                                                    @if ($isIcu)
                                                        <i class="bi bi-heart-pulse"></i>
                                                    @endif
                                                    {{ $latestAllocation->bed->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">No bed assigned</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span
                                                class="badge bg-{{ $ipd->status == 'Admitted' ? 'success' : 'warning' }}">{{ $ipd->status ?? '' }}</span>
                                        </td>

                                        <td class="text-end pe-2">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border-0 px-1 py-0" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fi fi-rr-menu-dots-vertical"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="{{ route('ipd-patients.show', $ipd->id) }}"
                                                            class="dropdown-item">
                                                            <i class="fi fi-rr-eye me-2"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('ipd-patients.prescriptions.create', $ipd->id) }}"
                                                            class="dropdown-item">
                                                            <i class="bi bi-plus me-2"></i> Add Prescription
                                                        </a>
                                                    </li>
                                                    @php $latestPrescription = $ipd->prescriptions?->first(); @endphp
                                                    @if ($latestPrescription)
                                                        <li>
                                                            <a href="{{ route('ipd-patients.prescriptions.pdf', [$ipd->id, $latestPrescription->id]) }}"
                                                                class="dropdown-item" target="_blank">
                                                                <i class="bi bi-download me-2"></i> E Prescription
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($ipd->status == 'Admitted')
                                                        @php $currentIsIcu = ($latestAllocation->allocation_type ?? 'bed') === 'icu'; @endphp
                                                        <li>
                                                            <a href="javascript:void(0);" class="dropdown-item"
                                                                data-url="{{ route('ipd-patients.bed-transfer', $ipd->id) }}"
                                                                data-size="xl" data-ajax-popup="true"
                                                                data-title="{{ $currentIsIcu ? 'Transfer ICU → Bed' : 'Bed Transfer' }}"
                                                                data-bs-toggle="tooltip" title="">
                                                                <i class="fi fi-rr-exchange me-2"></i>
                                                                {{ $currentIsIcu ? 'Transfer to Bed' : 'Bed Transfer' }}
                                                            </a>
                                                        </li>

                                                        @if (!$currentIsIcu)
                                                            <li>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item text-danger"
                                                                    data-url="{{ route('ipd-patients.icu-transfer', $ipd->id) }}"
                                                                    data-size="xl" data-ajax-popup="true"
                                                                    data-title="Transfer to ICU / CCU">
                                                                    <i class="bi bi-heart-pulse me-2"></i> Transfer to ICU /
                                                                    CCU
                                                                </a>
                                                            </li>
                                                        @endif

                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('ipd-patients.edit', $ipd->id) }}"
                                                                class="dropdown-item">
                                                                <i class="fi fi-rr-edit me-2"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form method="POST"
                                                                action="{{ route('ipd-patients.discharge-request', $ipd->id) }}"
                                                                onsubmit="return confirm('Discharge Request for this Ipd patient?')">
                                                                @csrf
                                                                @method('PUT')
                                                                <button class="dropdown-item text-warning" type="submit">
                                                                    <i class="fi fi-rr-sign-out-alt me-2"></i> Discharge
                                                                    Request
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if ($ipd->status == 'Discharge in Process')
                                                        <li>
                                                            <button class="dropdown-item text-danger" type="button"
                                                                disabled>
                                                                <i class="fi fi-rr-sign-out-alt me-2"></i> Discharge
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            No Ipd admission found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>

                    @if (count($ipdPatients))
                        <div
                            class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <span>Rows per page:</span>
                                <select id="ipdPerPage" class="form-select form-select-sm" style="width:auto;">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span id="ipdRangeInfo"></span>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="ipdPagination"></ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <script>
        (function() {
            const rows = Array.from(document.querySelectorAll('.ipd-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('ipdPerPage');
            const pagination = document.getElementById('ipdPagination');
            const rangeInfo = document.getElementById('ipdRangeInfo');
            const searchInput = document.getElementById('ipdSearch');
            const tbody = rows[0].parentNode;
            const colCount = rows[0].children.length;
            const noMatchRow = document.createElement('tr');
            noMatchRow.id = 'ipdNoMatchRow';
            noMatchRow.style.display = 'none';
            noMatchRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted py-4">No matching patients</td>`;
            tbody.appendChild(noMatchRow);

            const rowText = rows.map(r => r.textContent.toLowerCase().replace(/\s+/g, ' ').trim());
            let filtered = rows.slice();
            let currentPage = 1;

            function applyFilter() {
                const q = (searchInput.value || '').toLowerCase().trim();
                if (!q) {
                    filtered = rows.slice();
                } else {
                    filtered = rows.filter((_, i) => rowText[i].includes(q));
                }
                currentPage = 1;
            }

            function render() {
                const perPage = parseInt(perPageSel.value, 10);
                const total = filtered.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                if (currentPage > totalPages) currentPage = totalPages;
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, total);

                rows.forEach(r => { r.style.display = 'none'; });
                filtered.slice(start, end).forEach(r => { r.style.display = ''; });
                noMatchRow.style.display = total === 0 ? '' : 'none';

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

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    applyFilter();
                    render();
                });
            }
            render();
        })();
    </script>
@endsection
