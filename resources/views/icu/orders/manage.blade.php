@extends('backend.layouts.master')

@section('title', ($icuType ?? null) ? $icuType . ' Order Management' : 'Order Management')

@section('content')
    <style>
        .om-page { padding: 0 4px; }

        .om-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .om-head__title { font-size: 1.55rem; font-weight: 700; color: #0f172a; margin: 0; }
        .om-head__sub { color: #64748b; font-size: .9rem; margin-top: 2px; }
        .om-search {
            position: relative;
            min-width: 320px;
        }
        .om-search input {
            background: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 9px 14px 9px 38px;
            width: 100%;
            font-size: .88rem;
        }
        .om-search input:focus { background: #fff; border-color: #cbd5e1; outline: none; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .om-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .om-search kbd {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            font-size: .68rem; background: #e2e8f0; color: #475569;
            padding: 2px 6px; border-radius: 4px; font-weight: 600;
        }

        .om-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 22px; }
        @media (max-width: 1100px) { .om-stats { grid-template-columns: repeat(2, 1fr); } }
        .om-stat {
            position: relative;
            background: #fff;
            border-radius: 12px;
            padding: 16px 18px 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            overflow: hidden;
        }
        .om-stat__label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .om-stat__value { font-size: 2.1rem; font-weight: 700; color: #0f172a; line-height: 1; }
        .om-stat__foot { font-size: .78rem; color: #64748b; margin-top: 6px; }
        .om-stat__icon {
            position: absolute; top: 14px; right: 14px;
            width: 30px; height: 30px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .om-stat--stat       { background: #fef2f2; border-color: #fecaca; }
        .om-stat--stat .om-stat__label { color: #b91c1c; }
        .om-stat--stat .om-stat__icon  { background: rgba(220,38,38,.12); color: #b91c1c; }

        .om-stat--urgent     { background: #fffbeb; border-color: #fde68a; }
        .om-stat--urgent .om-stat__label { color: #b45309; }
        .om-stat--urgent .om-stat__icon  { background: rgba(217,119,6,.14); color: #b45309; }

        .om-stat--routine    { background: #eff6ff; border-color: #bfdbfe; }
        .om-stat--routine .om-stat__label { color: #1d4ed8; }
        .om-stat--routine .om-stat__icon  { background: rgba(37,99,235,.12); color: #1d4ed8; }

        .om-stat--completed  { background: #ecfdf5; border-color: #a7f3d0; }
        .om-stat--completed .om-stat__label { color: #047857; }
        .om-stat--completed .om-stat__icon  { background: rgba(5,150,105,.12); color: #047857; }

        .om-stat--total      { background: #f5f3ff; border-color: #ddd6fe; }
        .om-stat--total .om-stat__label { color: #6d28d9; }
        .om-stat--total .om-stat__icon  { background: rgba(124,58,237,.12); color: #6d28d9; }

        .om-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .om-filters select {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 7px 32px 7px 14px;
            font-size: .82rem;
            color: #334155;
            font-weight: 500;
            min-width: 140px;
            cursor: pointer;
            -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M8 11.5 3.5 7l1-1L8 9.5 11.5 6l1 1z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
        }
        .om-filters select:hover { border-color: #cbd5e1; }
        .om-filters select.is-active { border-color: #6366f1; color: #4338ca; background-color: #eef2ff; }

        .om-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15,23,42,.04);
            overflow: hidden;
        }
        .om-card__head {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
        }
        .om-card__title { font-weight: 700; color: #0f172a; font-size: .95rem; margin: 0; }
        .om-sort-label { color: #64748b; font-size: .8rem; }
        .om-sort-label b { color: #2563eb; font-weight: 600; }

        .om-table { width: 100%; border-collapse: collapse; }
        .om-table thead th {
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
        .om-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: .85rem;
            color: #1e293b;
        }
        .om-table tbody tr:last-child td { border-bottom: 0; }
        .om-table tbody tr:hover { background: #fafbff; }
        .om-row--inprogress { background: #eef4ff; }
        .om-row--inprogress:hover { background: #e6edff; }

        .om-order-id { color: #64748b; font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: .78rem; }
        .om-order-title { font-weight: 600; color: #0f172a; }
        .om-order-sub { color: #64748b; font-size: .76rem; margin-top: 1px; }

        .om-type {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }
        .om-type--medication { background: #dbeafe; color: #1d4ed8; }
        .om-type--lab        { background: #ede9fe; color: #6d28d9; }
        .om-type--radiology  { background: #fef3c7; color: #b45309; }
        .om-type--procedure  { background: #dcfce7; color: #166534; }
        .om-type--default    { background: #f1f5f9; color: #475569; }

        .om-priority {
            font-weight: 700;
            font-size: .76rem;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .om-priority--stat    { color: #dc2626; }
        .om-priority--urgent  { color: #d97706; }
        .om-priority--routine { color: #2563eb; }

        .om-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 600;
        }
        .om-status::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
        }
        .om-status--ordered      { background: #fef3c7; color: #92400e; }
        .om-status--ordered::before      { background: #d97706; }
        .om-status--acknowledged { background: #dbeafe; color: #1e40af; }
        .om-status--acknowledged::before { background: #2563eb; }
        .om-status--inprogress   { background: #ede9fe; color: #5b21b6; }
        .om-status--inprogress::before   { background: #7c3aed; }
        .om-status--completed    { background: #dcfce7; color: #166534; }
        .om-status--completed::before    { background: #16a34a; }
        .om-status--cancelled    { background: #fee2e2; color: #b91c1c; }
        .om-status--cancelled::before    { background: #dc2626; }
        .om-status--onhold       { background: #e2e8f0; color: #475569; }
        .om-status--onhold::before       { background: #64748b; }
        .om-status--modified     { background: #cffafe; color: #0e7490; }
        .om-status--modified::before     { background: #0891b2; }

        .om-elapsed {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: .72rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
        }
        .om-elapsed::before { content: '⏱'; }
        .om-elapsed--mute { background: transparent; color: #94a3b8; }
        .om-elapsed--mute::before { content: ''; }

        .om-actions { display: inline-flex; gap: 6px; flex-wrap: nowrap; }
        .om-btn {
            border: 1.4px solid;
            background: transparent;
            padding: 4px 11px;
            border-radius: 6px;
            font-size: .73rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all .15s ease;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .om-btn--start       { border-color: #16a34a; color: #16a34a; }
        .om-btn--start:hover { background: #16a34a; color: #fff; }
        .om-btn--complete    { border-color: #d97706; color: #d97706; }
        .om-btn--complete:hover { background: #d97706; color: #fff; }
        .om-btn--ack         { border-color: #2563eb; color: #2563eb; }
        .om-btn--ack:hover   { background: #2563eb; color: #fff; }
        .om-btn--module      { border-color: #6d28d9; color: #6d28d9; }
        .om-btn--module:hover { background: #6d28d9; color: #fff; }
        .om-btn--cancel      { border-color: #dc2626; color: #dc2626; }
        .om-btn--cancel:hover { background: #dc2626; color: #fff; }
        .om-btn:disabled     { opacity: .35; cursor: not-allowed; }
        .om-btn--ghost       { border: 0; color: #64748b; padding: 4px 8px; font-size: .95rem; }
        .om-btn--ghost:hover { color: #2563eb; }

        .om-empty {
            padding: 60px 20px;
            text-align: center;
            color: #94a3b8;
        }
        .om-empty i { font-size: 2.4rem; opacity: .35; display: block; margin-bottom: 10px; }

        .om-foot {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
        }
        .om-foot .form-select-sm { width: auto; }
    </style>

    <div class="om-page container-fluid">
        <div class="om-head">
            <div>
                <h1 class="om-head__title">{{ $icuType ? $icuType . ' Order Management' : 'Order Management' }}</h1>
                <div class="om-head__sub">Create, manage and track all {{ $icuType ?: 'ICU/CCU' }} clinical orders</div>
            </div>
            <form method="GET" action="{{ route('icu.orders.manage') }}" class="om-search" id="omSearchForm">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Search orders by ID, drug, lab..." autocomplete="off">
                <input type="hidden" name="status"   value="{{ $status }}">
                <input type="hidden" name="type"     value="{{ $type }}">
                <input type="hidden" name="priority" value="{{ $priority }}">
                <input type="hidden" name="date"     value="{{ $dateKey }}">
                <input type="hidden" name="icu_type" value="{{ $icuType }}">
                <kbd>Ctrl /</kbd>
            </form>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <div class="om-stats">
            <div class="om-stat om-stat--stat">
                <div class="om-stat__icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="om-stat__label">STAT</div>
                <div class="om-stat__value">{{ str_pad($stats['stat'], 2, '0', STR_PAD_LEFT) }}</div>
                <div class="om-stat__foot">Requires Immediate Action</div>
            </div>
            <div class="om-stat om-stat--urgent">
                <div class="om-stat__icon"><i class="bi bi-clock-fill"></i></div>
                <div class="om-stat__label">URGENT</div>
                <div class="om-stat__value">{{ str_pad($stats['urgent'], 2, '0', STR_PAD_LEFT) }}</div>
                <div class="om-stat__foot">Needs Attention</div>
            </div>
            <div class="om-stat om-stat--routine">
                <div class="om-stat__icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div class="om-stat__label">ROUTINE</div>
                <div class="om-stat__value">{{ str_pad($stats['routine'], 2, '0', STR_PAD_LEFT) }}</div>
                <div class="om-stat__foot">Normal Priority</div>
            </div>
            <div class="om-stat om-stat--completed">
                <div class="om-stat__icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="om-stat__label">COMPLETED</div>
                <div class="om-stat__value">{{ str_pad($stats['completed'], 2, '0', STR_PAD_LEFT) }}</div>
                @php
                    $rangeLabel = match ($dateKey) {
                        'today' => 'Today',
                        '7d'    => 'Last 7 days',
                        '30d'   => 'Last 30 days',
                        default => 'All time',
                    };
                @endphp
                <div class="om-stat__foot">{{ $rangeLabel }}</div>
            </div>
            <div class="om-stat om-stat--total">
                <div class="om-stat__icon"><i class="bi bi-list-ul"></i></div>
                <div class="om-stat__label">TOTAL ORDERS</div>
                <div class="om-stat__value">{{ str_pad($stats['total'], 2, '0', STR_PAD_LEFT) }}</div>
                <div class="om-stat__foot">{{ $rangeLabel }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('icu.orders.manage') }}" class="om-filters" id="omFilterForm">
            <input type="hidden" name="q" value="{{ $search }}">
            <input type="hidden" name="icu_type" value="{{ $icuType }}">
            <select name="status" class="{{ $status ? 'is-active' : '' }}" onchange="document.getElementById('omFilterForm').submit()">
                <option value="">All Status</option>
                @foreach (['Ordered', 'Acknowledged', 'InProgress', 'Completed', 'Cancelled', 'OnHold', 'Modified'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="type" class="{{ $type ? 'is-active' : '' }}" onchange="document.getElementById('omFilterForm').submit()">
                <option value="">All Type</option>
                @foreach (['Medication', 'Lab', 'Radiology', 'Procedure', 'NursingCare', 'DietFluid', 'Monitoring'] as $t)
                    <option value="{{ $t }}" @selected($type === $t)>{{ $t === 'Lab' ? 'Pathology' : $t }}</option>
                @endforeach
            </select>
            <select name="priority" class="{{ $priority ? 'is-active' : '' }}" onchange="document.getElementById('omFilterForm').submit()">
                <option value="">All Priority</option>
                @foreach (['STAT', 'Urgent', 'Routine'] as $p)
                    <option value="{{ $p }}" @selected($priority === $p)>{{ $p }}</option>
                @endforeach
            </select>
            <select name="date" class="{{ $dateKey !== 'all' ? 'is-active' : '' }}" onchange="document.getElementById('omFilterForm').submit()">
                <option value="all"   @selected($dateKey === 'all')>All Orders</option>
                <option value="today" @selected($dateKey === 'today')>Today's Orders</option>
                <option value="7d"    @selected($dateKey === '7d')>Last 7 days</option>
                <option value="30d"   @selected($dateKey === '30d')>Last 30 days</option>
            </select>
        </form>

        <div class="om-card">
            <div class="om-card__head">
                <h6 class="om-card__title">All Patient Orders</h6>
                <div class="om-sort-label">Sort by: <b>Priority &middot; Newest First <i class="bi bi-arrow-down-up"></i></b></div>
            </div>
            <div class="table-responsive">
                <table class="om-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Patient Name</th>
                            <th>Order Details</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Ordered By</th>
                            <th>Time Elapsed</th>
                            <th>Order Time</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $o)
                            @php
                                $typeClass = match ($o->order_type) {
                                    'Medication' => 'om-type--medication',
                                    'Lab'        => 'om-type--lab',
                                    'Radiology'  => 'om-type--radiology',
                                    'Procedure'  => 'om-type--procedure',
                                    default      => 'om-type--default',
                                };
                                $typeLabel = $o->order_type === 'Lab' ? 'Pathology' : $o->order_type;

                                $prioClass = match ($o->priority) {
                                    'STAT'    => 'om-priority--stat',
                                    'Urgent'  => 'om-priority--urgent',
                                    default   => 'om-priority--routine',
                                };

                                $statusKey = strtolower($o->status);
                                $statusLabel = match ($o->status) {
                                    'InProgress' => 'In Progress',
                                    'OnHold'     => 'On Hold',
                                    default      => $o->status,
                                };

                                $isOpenOrder   = $o->status === 'Ordered';
                                $minutesElapsed = $o->created_at ? (int) $o->created_at->diffInMinutes(now()) : null;
                                $showElapsed   = $isOpenOrder && $minutesElapsed !== null;
                                if ($showElapsed) {
                                    $hoursPart = intdiv($minutesElapsed, 60);
                                    $minsPart  = $minutesElapsed % 60;
                                    $elapsedLabel = $hoursPart > 0
                                        ? "{$hoursPart} hr {$minsPart} min"
                                        : "{$minsPart} min";
                                } else {
                                    $elapsedLabel = null;
                                }

                                $moduleRoute = null;
                                $moduleLabel = null;
                                if ($o->icu_admission_id) {
                                    switch ($o->order_type) {
                                        case 'Medication':
                                            $moduleRoute = route('icu.admissions.medicine-orders.index', $o->icu_admission_id);
                                            $moduleLabel = 'Medicine Order';
                                            break;
                                        case 'Lab':
                                            $moduleRoute = route('icu.admissions.pathology-orders.index', $o->icu_admission_id);
                                            $moduleLabel = 'Pathology Order';
                                            break;
                                        case 'Radiology':
                                            $moduleRoute = route('icu.admissions.radiology-orders.index', $o->icu_admission_id);
                                            $moduleLabel = 'Radiology Order';
                                            break;
                                        case 'Procedure':
                                            $moduleRoute = route('icu.admissions.procedure-orders.index', $o->icu_admission_id);
                                            $moduleLabel = 'Procedure Order';
                                            break;
                                    }
                                }
                            @endphp
                            <tr class="om-row {{ $o->status === 'InProgress' ? 'om-row--inprogress' : '' }}" data-index="{{ $loop->index }}">
                                <td><span class="om-order-id">ORD-{{ str_pad($o->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td>{{ $o->patient?->patient_name ?? '—' }}</td>
                                <td>
                                    <div class="om-order-title">{{ $o->order_title }}</div>
                                    @if ($o->order_details)
                                        <div class="om-order-sub">{{ \Illuminate\Support\Str::limit($o->order_details, 60) }}</div>
                                    {{-- @elseif ($o->patient)
                                        <div class="om-order-sub">{{ $o->patient->patient_name }}</div> --}}
                                    @endif
                                </td>
                                <td><span class="om-type {{ $typeClass }}">{{ $typeLabel }}</span></td>
                                <td><span class="om-priority {{ $prioClass }}">{{ strtoupper($o->priority) }}</span></td>
                                <td>{{ $o->frequency ?: '' }}</td>
                                <td><span class="om-status om-status--{{ $statusKey }}">{{ $statusLabel }}</span></td>
                                <td>{{ $o->doctor?->name ?? '—' }}</td>
                                <td>
                                    @if ($showElapsed)
                                        <span class="om-elapsed">{{ $elapsedLabel }}</span>
                                    @else
                                        <span class="om-elapsed om-elapsed--mute">---</span>
                                    @endif
                                </td>
                                <td>{{ $o->start_time?->format('d M Y, h:i A') }}</td>
                                <td style="text-align:right;">
                                    <div class="om-actions">
                                        @if (in_array($o->status, ['Ordered', 'Acknowledged', 'OnHold']))
                                            <form method="POST" action="{{ route('icu.admissions.orders.start', [$o->icu_admission_id, $o->id]) }}" class="d-inline">
                                                @csrf<button type="submit" class="om-btn om-btn--start">Start</button>
                                            </form>
                                        @else
                                            <button type="button" class="om-btn om-btn--start" disabled>Start</button>
                                        @endif

                                        @if (in_array($o->status, ['Acknowledged', 'InProgress']))
                                            <form method="POST" action="{{ route('icu.admissions.orders.complete', [$o->icu_admission_id, $o->id]) }}" class="d-inline">
                                                @csrf<button type="submit" class="om-btn om-btn--complete">Complete</button>
                                            </form>
                                        @else
                                            <button type="button" class="om-btn om-btn--complete" disabled>Complete</button>
                                        @endif

                                        @if ($o->status === 'Ordered')
                                            <form method="POST" action="{{ route('icu.admissions.orders.acknowledge', [$o->icu_admission_id, $o->id]) }}" class="d-inline">
                                                @csrf<button type="submit" class="om-btn om-btn--ack">Acknowledge</button>
                                            </form>
                                        @elseif ($o->requires_doctor_ack && $o->status === 'Completed' && ! $o->doctor_acknowledged_at)
                                            <form method="POST" action="{{ route('icu.admissions.orders.doctor-ack', [$o->icu_admission_id, $o->id]) }}" class="d-inline">
                                                @csrf<button type="submit" class="om-btn om-btn--ack">Doctor Ack</button>
                                            </form>
                                        @else
                                            <button type="button" class="om-btn om-btn--ack" disabled>Acknowledge</button>
                                        @endif

                                        @if ($moduleRoute)
                                            <a href="{{ $moduleRoute }}" class="om-btn om-btn--module">{{ $moduleLabel }}</a>
                                        @endif

                                        @if ($o->icu_admission_id)
                                            <a href="{{ route('icu.admissions.show', $o->icu_admission_id) }}" class="om-btn om-btn--ghost" title="View admission">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="om-empty">
                                        <i class="bi bi-clipboard-x"></i>
                                        No orders match the current filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->count())
                <div class="om-foot">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Rows per page:</span>
                        <select id="omOrdersPerPage" class="form-select form-select-sm" style="width:auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span id="omOrdersRangeInfo"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="omOrdersPagination"></ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const rows = Array.from(document.querySelectorAll('.om-row'));
            if (!rows.length) return;
            const perPageSel = document.getElementById('omOrdersPerPage');
            const pagination = document.getElementById('omOrdersPagination');
            const rangeInfo = document.getElementById('omOrdersRangeInfo');
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

        // Ctrl+/ focuses the search bar
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                const input = document.querySelector('#omSearchForm input[name="q"]');
                if (input) input.focus();
            }
        });
    </script>
@endsection
