@extends('backend.layouts.master')

@section('title', ($icuType ?? null) ? $icuType . ' Intake / Output' : 'Intake / Output')

@section('content')
    <style>
        .io-page { padding: 0 4px; }

        .io-head {
            display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between;
            gap: 16px; margin-bottom: 18px;
        }
        .io-head__title { font-size: 1.55rem; font-weight: 700; color: #0f172a; margin: 0; }
        .io-head__sub { color: #64748b; font-size: .9rem; margin-top: 2px; }
        .io-search { position: relative; min-width: 320px; }
        .io-search input {
            background: #f1f5f9; border: 1px solid transparent;
            border-radius: 10px; padding: 9px 14px 9px 38px;
            width: 100%; font-size: .88rem;
        }
        .io-search input:focus {
            background: #fff; border-color: #cbd5e1; outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }
        .io-search i {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%); color: #94a3b8;
        }
        .io-search kbd {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            font-size: .68rem; background: #e2e8f0; color: #475569;
            padding: 2px 6px; border-radius: 4px; font-weight: 600;
        }

        .io-filters {
            display: flex; flex-wrap: wrap; gap: 10px;
            align-items: center; margin-bottom: 14px;
        }
        .io-filters select,
        .io-filters input[type="date"],
        .io-filters input[type="text"] {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 999px; padding: 7px 32px 7px 14px;
            font-size: .82rem; color: #334155; font-weight: 500;
            min-width: 140px; cursor: pointer;
        }
        .io-filters select {
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M8 11.5 3.5 7l1-1L8 9.5 11.5 6l1 1z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
        }
        .io-filters select:hover,
        .io-filters input[type="date"]:hover { border-color: #cbd5e1; }
        .io-filters select.is-active,
        .io-filters input.is-active { border-color: #6366f1; color: #4338ca; background-color: #eef2ff; }
        .io-filters .io-search-inline {
            min-width: 280px; flex: 1; padding: 7px 14px;
            border-radius: 999px;
        }
        .io-refresh {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 999px; padding: 7px 16px;
            font-size: .82rem; color: #334155; font-weight: 600;
            text-decoration: none; cursor: pointer;
        }
        .io-refresh:hover { border-color: #2563eb; color: #1d4ed8; }

        .io-card {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04);
            overflow: hidden;
        }

        .io-table { width: 100%; border-collapse: collapse; }
        .io-table thead th {
            background: #f8fafc; font-size: .65rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase; color: #64748b;
            padding: 11px 14px; border-bottom: 1px solid #e2e8f0;
            text-align: left; white-space: nowrap;
        }
        .io-table thead th.num { text-align: right; }
        .io-table tbody td {
            padding: 13px 14px; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle; font-size: .85rem; color: #1e293b;
        }
        .io-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .io-table tbody tr:last-child td { border-bottom: 0; }
        .io-table tbody tr:hover { background: #fafbff; }

        .io-row-num { color: #64748b; }
        .io-case-id {
            color: #2563eb; font-weight: 600;
            font-family: 'JetBrains Mono','Courier New',monospace;
            font-size: .8rem; text-decoration: none;
        }
        .io-case-id:hover { text-decoration: underline; }
        .io-patient { font-weight: 600; color: #0f172a; }
        .io-bed {
            font-family: 'JetBrains Mono','Courier New',monospace;
            font-size: .8rem; color: #475569;
        }

        .io-pill {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 999px;
            font-size: .72rem; font-weight: 600;
        }
        .io-pill--yes { background: #dcfce7; color: #166534; }
        .io-pill--no  { background: #fee2e2; color: #b91c1c; }
        .io-pill--shift-morning { background: #dcfce7; color: #166534; }
        .io-pill--shift-evening { background: #fef3c7; color: #92400e; }
        .io-pill--shift-night   { background: #ede9fe; color: #5b21b6; }
        .io-pill--mute  { background: #f1f5f9; color: #94a3b8; }

        .io-balance-pos { color: #16a34a; font-weight: 700; }
        .io-balance-neg { color: #dc2626; font-weight: 700; }
        .io-balance-zero { color: #64748b; font-weight: 700; }

        .io-eye {
            color: #64748b; font-size: 1rem; padding: 4px 8px;
            border-radius: 6px; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .io-eye:hover { color: #2563eb; background: #eef2ff; }

        .io-empty {
            padding: 60px 20px; text-align: center; color: #94a3b8;
        }
        .io-empty i { font-size: 2.4rem; opacity: .35; display: block; margin-bottom: 10px; }

        .io-foot {
            display: flex; flex-wrap: wrap;
            justify-content: space-between; align-items: center;
            gap: 12px; padding: 12px 18px;
            border-top: 1px solid #f1f5f9; background: #fafafa;
        }
        .io-foot .form-select-sm { width: auto; }
    </style>

    <div class="io-page container-fluid">
        <div class="io-head">
            <div>
                <h1 class="io-head__title">Intake / Output</h1>
                <div class="io-head__sub">Track fluid balance across all {{ $icuType ?: 'ICU/CCU' }} patients</div>
            </div>
            {{-- <form method="GET" action="{{ route('icu.intake-output.manage') }}" class="io-search" id="ioSearchTopForm">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Search orders by ID, drug, lab..." autocomplete="off">
                <input type="hidden" name="date"       value="{{ $date }}">
                <input type="hidden" name="shift"      value="{{ $shift }}">
                <input type="hidden" name="patient_id" value="{{ $patientId }}">
                <input type="hidden" name="bed_id"     value="{{ $bedId }}">
                <input type="hidden" name="icu_type"   value="{{ $icuType }}">
                <kbd>Ctrl /</kbd>
            </form> --}}
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <form method="GET" action="{{ route('icu.intake-output.manage') }}" class="io-filters" id="ioFilterForm">
            <input type="hidden" name="q"        value="{{ $search }}">
            <input type="hidden" name="icu_type" value="{{ $icuType }}">

            <select name="patient_id" class="{{ $patientId ? 'is-active' : '' }}"
                    onchange="document.getElementById('ioFilterForm').submit()">
                <option value="">All Patients</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" @selected((string) $patientId === (string) $p->id)>
                        {{ $p->patient_name }}
                    </option>
                @endforeach
            </select>

            <select name="bed_id" class="{{ $bedId ? 'is-active' : '' }}"
                    onchange="document.getElementById('ioFilterForm').submit()">
                <option value="">All Beds</option>
                @foreach ($beds as $b)
                    <option value="{{ $b->id }}" @selected((string) $bedId === (string) $b->id)>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>

            <select name="shift" class="{{ $shift ? 'is-active' : '' }}"
                    onchange="document.getElementById('ioFilterForm').submit()">
                <option value="">All Shifts</option>
                @foreach (['Morning', 'Evening', 'Night'] as $s)
                    <option value="{{ $s }}" @selected($shift === $s)>{{ $s }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ $date }}"
                   class="{{ $date !== now()->toDateString() ? 'is-active' : '' }}"
                   onchange="document.getElementById('ioFilterForm').submit()">

            <input type="text" name="q_inline" value="{{ $search }}"
                   class="io-search-inline"
                   placeholder="Search by Patient Name / Case ID / PID..."
                   onkeydown="if(event.key==='Enter'){event.preventDefault();document.querySelector('#ioFilterForm input[name=q]').value=this.value;document.getElementById('ioFilterForm').submit();}">

            <a href="{{ request()->fullUrl() }}" class="io-refresh">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </form>

        <div class="io-card">
            <div class="table-responsive">
                <table class="io-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ICU Case ID</th>
                            <th>Patient Name</th>
                            <th>Age / Gender</th>
                            <th>Bed</th>
                            <th>Ventilator</th>
                            <th>Shift</th>
                            <th>Last Observation</th>
                            <th class="num">Intake (ml)</th>
                            <th class="num">Output (ml)</th>
                            <th class="num">Balance (ml)</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            @php
                                $a = $r->admission;
                                $patient = $a->patient;
                                $age = $patient?->dob ? \Carbon\Carbon::parse($patient->dob)->age : null;
                                $genderShort = match (strtolower((string) $patient?->gender)) {
                                    'male'   => 'Male',
                                    'female' => 'Female',
                                    'other'  => 'Other',
                                    default  => '—',
                                };
                                $ageGender = $age !== null ? $age . ' Y / ' . $genderShort : ($patient?->gender ?? '—');

                                $shiftLbl   = $r->shift_label;
                                $shiftClass = match ($shiftLbl) {
                                    'Morning' => 'io-pill--shift-morning',
                                    'Evening' => 'io-pill--shift-evening',
                                    'Night'   => 'io-pill--shift-night',
                                    default   => 'io-pill--mute',
                                };

                                $balCls = $r->balance > 0 ? 'io-balance-pos' : ($r->balance < 0 ? 'io-balance-neg' : 'io-balance-zero');
                                $balPrefix = $r->balance > 0 ? '+' : '';
                            @endphp
                            <tr class="io-row" data-index="{{ $loop->index }}">
                                <td class="io-row-num">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('icu.admissions.intake-output.index', $a->id) }}" class="io-case-id">
                                        {{ $a->icu_case_id }}
                                    </a>
                                </td>
                                <td><span class="io-patient">{{ $patient?->patient_name ?? '—' }}</span></td>
                                <td>{{ $ageGender }}</td>
                                <td><span class="io-bed">{{ $a->bed?->name ?? '—' }}</span></td>
                                <td>
                                    <span class="io-pill {{ $a->ventilator_required ? 'io-pill--yes' : 'io-pill--no' }}">
                                        {{ $a->ventilator_required ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($shiftLbl)
                                        <span class="io-pill {{ $shiftClass }}">{{ $shiftLbl }}</span>
                                    @else
                                        <span class="io-pill io-pill--mute">—</span>
                                    @endif
                                </td>
                                <td>{{ $r->last_entry_time?->format('h:i A') ?? '—' }}</td>
                                <td class="num">{{ $r->intake ? number_format($r->intake) : '—' }}</td>
                                <td class="num">{{ $r->output ? number_format($r->output) : '—' }}</td>
                                <td class="num">
                                    @if ($r->intake || $r->output)
                                        <span class="{{ $balCls }}">{{ $balPrefix }}{{ number_format($r->balance) }}</span>
                                    @else
                                        <span class="io-balance-zero">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('icu.admissions.intake-output.index', ['admissionId' => $a->id, 'date' => $date]) }}"
                                       class="io-eye" title="View intake/output chart">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <div class="io-empty">
                                        <i class="bi bi-droplet"></i>
                                        No active admissions match the current filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows->count())
                <div class="io-foot">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Rows per page:</span>
                        <select id="ioPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="6" selected>6</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span id="ioRangeInfo"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="ioPagination"></ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.io-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('ioPerPage');
            const pagination = document.getElementById('ioPagination');
            const rangeInfo  = document.getElementById('ioRangeInfo');
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

                rangeInfo.textContent = `Showing ${total ? start + 1 : 0} to ${end} of ${total} patients`;

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

        // Ctrl+/ focuses the top search bar
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                const input = document.querySelector('#ioSearchTopForm input[name="q"]');
                if (input) input.focus();
            }
        });
    </script>
@endsection
