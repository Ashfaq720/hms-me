@extends('backend.layouts.master')

@section('title', $icuType ?? null ? $icuType . ' Nursing Notes' : 'Nursing Notes')

@section('content')
    <style>
        .nn-page {
            padding: 0 4px;
        }

        .nn-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .nn-head__title {
            font-size: 1.55rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .nn-head__sub {
            color: #64748b;
            font-size: .9rem;
            margin-top: 2px;
        }

        .nn-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .nn-stat {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 16px;
            min-width: 130px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .nn-stat__label {
            font-size: .65rem;
            color: #64748b;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .nn-stat__value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }

        .nn-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 14px;
        }

        .nn-filters select,
        .nn-filters input[type="date"],
        .nn-filters input[type="text"] {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 7px 32px 7px 14px;
            font-size: .82rem;
            color: #334155;
            font-weight: 500;
            min-width: 140px;
            cursor: pointer;
        }

        .nn-filters select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M8 11.5 3.5 7l1-1L8 9.5 11.5 6l1 1z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
        }

        .nn-filters select:hover,
        .nn-filters input[type="date"]:hover {
            border-color: #cbd5e1;
        }

        .nn-filters select.is-active,
        .nn-filters input.is-active {
            border-color: #6366f1;
            color: #4338ca;
            background-color: #eef2ff;
        }

        .nn-filters .nn-search-inline {
            min-width: 280px;
            flex: 1;
            padding: 7px 14px;
            border-radius: 999px;
        }

        .nn-refresh {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 7px 16px;
            font-size: .82rem;
            color: #334155;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .nn-refresh:hover {
            border-color: #2563eb;
            color: #1d4ed8;
        }

        .nn-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .nn-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nn-table thead th {
            background: #f8fafc;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #64748b;
            padding: 11px 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }

        .nn-table thead th.num {
            text-align: right;
        }

        .nn-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: .85rem;
            color: #1e293b;
        }

        .nn-table tbody td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .nn-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .nn-table tbody tr:hover {
            background: #fafbff;
        }

        .nn-row-num {
            color: #64748b;
        }

        .nn-time {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: .8rem;
            color: #0f172a;
        }

        .nn-time small {
            display: block;
            color: #94a3b8;
            font-size: .7rem;
            margin-top: 1px;
        }

        .nn-case-id {
            color: #2563eb;
            font-weight: 600;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: .8rem;
            text-decoration: none;
        }

        .nn-case-id:hover {
            text-decoration: underline;
        }

        .nn-patient {
            font-weight: 600;
            color: #0f172a;
        }

        .nn-patient small {
            display: block;
            color: #94a3b8;
            font-weight: 500;
            font-size: .72rem;
            margin-top: 1px;
        }

        .nn-bed {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: .8rem;
            color: #475569;
        }

        .nn-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }

        .nn-pill--shift-morning {
            background: #dcfce7;
            color: #166534;
        }

        .nn-pill--shift-evening {
            background: #fef3c7;
            color: #92400e;
        }

        .nn-pill--shift-night {
            background: #ede9fe;
            color: #5b21b6;
        }

        .nn-pill--mute {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .nn-pain-low {
            color: #16a34a;
            font-weight: 700;
        }

        .nn-pain-mid {
            color: #d97706;
            font-weight: 700;
        }

        .nn-pain-high {
            color: #dc2626;
            font-weight: 700;
        }

        .nn-pain-zero {
            color: #64748b;
            font-weight: 700;
        }

        .nn-remarks {
            display: inline-block;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #475569;
            font-size: .8rem;
        }

        .nn-eye {
            color: #64748b;
            font-size: 1rem;
            padding: 4px 8px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .nn-eye:hover {
            color: #2563eb;
            background: #eef2ff;
        }

        .nn-empty {
            padding: 60px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .nn-empty i {
            font-size: 2.4rem;
            opacity: .35;
            display: block;
            margin-bottom: 10px;
        }

        .nn-foot {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
        }

        .nn-foot .form-select-sm {
            width: auto;
        }

        .nn-muted-cell {
            color: #94a3b8;
        }
    </style>

    <div class="nn-page container-fluid">
        <div class="nn-head">
            <div>
                <h1 class="nn-head__title">Nursing Notes</h1>
                <div class="nn-head__sub">All nursing observations logged across {{ $icuType ?: 'ICU/CCU' }} admissions</div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('icu.nursing-notes.manage') }}" class="nn-filters" id="nnFilterForm">
            <input type="hidden" name="q" value="{{ $search }}">
            <input type="hidden" name="icu_type" value="{{ $icuType }}">

            <select name="patient_id" class="{{ $patientId ? 'is-active' : '' }}"
                onchange="document.getElementById('nnFilterForm').submit()">
                <option value="">All Patients</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" @selected((string) $patientId === (string) $p->id)>
                        {{ $p->patient_name }}
                    </option>
                @endforeach
            </select>

            <select name="bed_id" class="{{ $bedId ? 'is-active' : '' }}"
                onchange="document.getElementById('nnFilterForm').submit()">
                <option value="">All Beds</option>
                @foreach ($beds as $b)
                    <option value="{{ $b->id }}" @selected((string) $bedId === (string) $b->id)>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>

            <select name="shift" class="{{ $shift ? 'is-active' : '' }}"
                onchange="document.getElementById('nnFilterForm').submit()">
                <option value="">All Shifts</option>
                @foreach (['Morning', 'Evening', 'Night'] as $s)
                    <option value="{{ $s }}" @selected($shift === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <input type="{{ $startDate ? 'date' : 'text' }}" name="start_date" value="{{ $startDate }}"
                class="{{ $startDate ? 'is-active' : '' }}"
                placeholder="Start date"
                onfocus="this.type='date'"
                onblur="if(!this.value)this.type='text'"
                onchange="document.getElementById('nnFilterForm').submit()">

            <input type="{{ $endDate ? 'date' : 'text' }}" name="end_date" value="{{ $endDate }}"
                class="{{ $endDate ? 'is-active' : '' }}"
                placeholder="End date"
                onfocus="this.type='date'"
                onblur="if(!this.value)this.type='text'"
                onchange="document.getElementById('nnFilterForm').submit()">

            <input type="text" name="q_inline" value="{{ $search }}" class="nn-search-inline"
                placeholder="Search by Patient Name / Case ID / PID..."
                onkeydown="if(event.key==='Enter'){event.preventDefault();document.querySelector('#nnFilterForm input[name=q]').value=this.value;document.getElementById('nnFilterForm').submit();}">

            <a href="{{ request()->fullUrl() }}" class="nn-refresh">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </form>

        <div class="nn-card">
            <div class="table-responsive">
                <table class="nn-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Observation Time</th>
                            <th>ICU Case ID</th>
                            <th>Patient</th>
                            <th>Bed</th>
                            <th>Shift</th>
                            <th>Consciousness</th>
                            <th class="num">Pain</th>
                            <th>Resp. Support</th>
                            <th>Oxygen</th>
                            <th>Position</th>
                            <th>General</th>
                            <th>Remarks</th>
                            <th>Enter By</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notes as $note)
                            @php
                                $a = $note->admission;
                                $patient = $a?->patient;
                                $age = $patient?->dob ? \Carbon\Carbon::parse($patient->dob)->age : null;
                                $genderShort = match (strtolower((string) $patient?->gender)) {
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other',
                                    default => '—',
                                };
                                $ageGender = $age !== null ? $age . ' Y / ' . $genderShort : $patient?->gender ?? '—';

                                $shiftClass = match ($note->shift) {
                                    'Morning' => 'nn-pill--shift-morning',
                                    'Evening' => 'nn-pill--shift-evening',
                                    'Night' => 'nn-pill--shift-night',
                                    default => 'nn-pill--mute',
                                };

                                $pain = $note->pain_score;
                                $painCls =
                                    $pain === null
                                        ? 'nn-pain-zero'
                                        : ($pain <= 3
                                            ? 'nn-pain-low'
                                            : ($pain <= 6
                                                ? 'nn-pain-mid'
                                                : 'nn-pain-high'));
                            @endphp
                            <tr class="nn-row" data-index="{{ $loop->index }}">
                                <td class="nn-row-num">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="nn-time">
                                        {{ $note->observation_time->format('h:i A') }}
                                        <small>{{ $note->observation_time->format('d M Y') }}</small>
                                    </span>
                                </td>
                                <td>
                                    @if ($a)
                                        <a href="{{ route('icu.admissions.nursing-notes.index', $a->id) }}"
                                            class="nn-case-id">
                                            {{ $a->icu_case_id }}
                                        </a>
                                    @else
                                        <span class="nn-muted-cell">{{ $note->icu_case_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="nn-patient">
                                        {{ $patient?->patient_name ?? '—' }}
                                        <small>{{ $ageGender }}</small>
                                    </span>
                                </td>
                                <td><span class="nn-bed">{{ $a?->bed?->name ?? '—' }}</span></td>
                                <td>
                                    @if ($note->shift)
                                        <span class="nn-pill {{ $shiftClass }}">{{ $note->shift }}</span>
                                    @else
                                        <span class="nn-pill nn-pill--mute">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->consciousness_level)
                                        {{ $note->consciousness_level }}
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($pain !== null)
                                        <span class="{{ $painCls }}">{{ $pain }}/10</span>
                                    @else
                                        <span class="nn-pain-zero">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->respiratory_support)
                                        {{ $note->respiratory_support }}
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->oxygen_flow)
                                        {{ $note->oxygen_flow }}
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->position)
                                        {{ $note->position }}
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->general_condition)
                                        {{ $note->general_condition }}
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($note->remarks)
                                        <span class="nn-remarks" title="{{ $note->remarks }}">{{ $note->remarks }}</span>
                                    @else
                                        <span class="nn-muted-cell">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $note->enteredBy?->name ?? '—' }}
                                </td>
                                <td style="text-align:right;">
                                    @if ($a)
                                        <a href="{{ route('icu.admissions.nursing-notes.index', $a->id) }}" class="nn-eye"
                                            title="Add nursing notes">
                                            <i class="bi bi-plus"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14">
                                    <div class="nn-empty">
                                        <i class="bi bi-journal-text"></i>
                                        No nursing notes recorded for the current filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($notes->count())
                <div class="nn-foot">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Rows per page:</span>
                        <select id="nnPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="6" selected>6</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span id="nnRangeInfo"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="nnPagination"></ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function() {
            const rows = Array.from(document.querySelectorAll('.nn-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('nnPerPage');
            const pagination = document.getElementById('nnPagination');
            const rangeInfo = document.getElementById('nnRangeInfo');
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

                rangeInfo.textContent = `Showing ${total ? start + 1 : 0} to ${end} of ${total} notes`;

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
