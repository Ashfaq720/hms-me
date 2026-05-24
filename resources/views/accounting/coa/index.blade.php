@extends('backend.layouts.master')
@section('title','Chart of Accounts')
@section('content')
@php
    // Pre-compute depth for indentation (parent_id → depth)
    $depthOf = [];
    $resolveDepth = function ($acc) use (&$resolveDepth, &$depthOf, $accounts) {
        if (isset($depthOf[$acc->id])) return $depthOf[$acc->id];
        if (! $acc->parent_id) return $depthOf[$acc->id] = 0;
        $parent = $accounts->firstWhere('id', $acc->parent_id);
        return $depthOf[$acc->id] = $parent ? $resolveDepth($parent) + 1 : 0;
    };
    foreach ($accounts as $a) $resolveDepth($a);

    $typeMeta = [
        'asset'     => ['icon' => 'wallet2',         'colour' => 'info',      'label' => 'Assets'],
        'liability' => ['icon' => 'cash-coin',       'colour' => 'warning',   'label' => 'Liabilities'],
        'equity'    => ['icon' => 'pie-chart',       'colour' => 'secondary', 'label' => 'Equity'],
        'income'    => ['icon' => 'graph-up-arrow',  'colour' => 'success',   'label' => 'Income'],
        'expense'   => ['icon' => 'graph-down-arrow','colour' => 'danger',    'label' => 'Expenses'],
    ];
    $typeOrder = ['asset', 'liability', 'equity', 'income', 'expense'];

    // Per-type subtotals
    $typeBalances = [];
    foreach ($typeOrder as $t) {
        $typeBalances[$t] = ['dr' => 0, 'cr' => 0];
        foreach ($grouped[$t] ?? [] as $a) {
            $b = $balances[$a->id] ?? null;
            if ($b) { $typeBalances[$t]['dr'] += $b->debit_total; $typeBalances[$t]['cr'] += $b->credit_total; }
        }
    }
    $totalDr = array_sum(array_column($typeBalances, 'dr'));
    $totalCr = array_sum(array_column($typeBalances, 'cr'));
@endphp

<div class="container-fluid py-3 coa-page">

    {{-- ── HEADER ── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-diagram-3"></i> Chart of Accounts</h4>
            <small class="text-muted">{{ $kpi['total'] }} accounts · {{ $kpi['postable'] }} postable · {{ $kpi['with_postings'] }} with postings</small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div class="input-group input-group-sm" style="width:260px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" id="coaSearch" class="form-control" placeholder="Search code or name…">
            </div>
            <div class="btn-group btn-group-sm" role="group" id="coaTypeFilter">
                <button class="btn btn-outline-dark active" data-filter="all">All</button>
                @foreach ($typeOrder as $t)
                    <button class="btn btn-outline-{{ $typeMeta[$t]['colour'] }}" data-filter="{{ $t }}">
                        <i class="bi bi-{{ $typeMeta[$t]['icon'] }}"></i> {{ $typeMeta[$t]['label'] }}
                        <span class="badge bg-{{ $typeMeta[$t]['colour'] }} ms-1">{{ ($kpi['by_type'][$t] ?? 0) }}</span>
                    </button>
                @endforeach
            </div>
            <button class="btn btn-sm btn-outline-secondary" id="toggleZeroBalances" title="Hide accounts with no postings">
                <i class="bi bi-eye-slash"></i> Hide zero-balance
            </button>
            <a href="{{ route('accounting.journal.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-journal-text"></i> Journal
            </a>
            @can('accounting.coa.manage')
                <a href="{{ route('accounting.coa.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Account
                </a>
            @endcan
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success py-2">{{ session('success') }}</div> @endif

    {{-- ── KPI strip ── --}}
    <div class="row g-2 mb-3">
        @foreach ($typeOrder as $t)
            @php $meta = $typeMeta[$t]; @endphp
            <div class="col-md col-6">
                <div class="card border-0 shadow-sm h-100 type-summary-card" style="border-left:4px solid var(--bs-{{ $meta['colour'] }}) !important;">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-{{ $meta['colour'] }} fw-semibold">
                                <i class="bi bi-{{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                            </small>
                            <span class="badge bg-{{ $meta['colour'] }} bg-opacity-10 text-{{ $meta['colour'] }}">{{ ($kpi['by_type'][$t] ?? 0) }}</span>
                        </div>
                        <div class="small mt-1 text-muted">
                            Dr ৳ {{ number_format($typeBalances[$t]['dr'], 0) }}
                            <br>Cr ৳ {{ number_format($typeBalances[$t]['cr'], 0) }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── UNIFIED TREE TABLE ── --}}
    <div class="card border-0 shadow-sm coa-tree-card">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle coa-tree" id="coaTable">
                <thead class="table-light sticky-top" style="top:0;z-index:5;">
                    <tr>
                        <th style="width:14%;">Code</th>
                        <th>Account</th>
                        <th style="width:11%;">Type</th>
                        <th style="width:9%;" class="text-center">Postable</th>
                        <th style="width:13%;" class="text-end">Debit ৳</th>
                        <th style="width:13%;" class="text-end">Credit ৳</th>
                        <th style="width:7%;" class="text-center">Trx</th>
                        <th style="width:7%;" class="text-center">Status</th>
                        <th style="width:7%;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($typeOrder as $type)
                    @continue (! $grouped->has($type) || $grouped[$type]->isEmpty())
                    @php $meta = $typeMeta[$type]; @endphp
                    {{-- Section header row --}}
                    <tr class="coa-section-row table-{{ $meta['colour'] }} bg-opacity-10 fw-semibold" data-type="{{ $type }}" data-section-row="1">
                        <td colspan="9" class="py-2">
                            <i class="bi bi-{{ $meta['icon'] }}"></i> {{ strtoupper($meta['label']) }}
                            <span class="text-muted small ms-2">{{ $grouped[$type]->count() }} accounts</span>
                        </td>
                    </tr>
                    @foreach ($grouped[$type]->sortBy('code') as $a)
                        @php
                            $b = $balances[$a->id] ?? null;
                            $depth = $depthOf[$a->id] ?? 0;
                            $hasBalance = $b && ($b->debit_total > 0 || $b->credit_total > 0);
                        @endphp
                        <tr class="coa-row" data-type="{{ $type }}"
                            data-name="{{ strtolower($a->code . ' ' . $a->name) }}"
                            data-has-balance="{{ $hasBalance ? '1' : '0' }}">
                            <td>
                                <code class="text-dark">{{ $a->code }}</code>
                            </td>
                            <td>
                                <span style="display:inline-block; padding-left:{{ $depth * 18 }}px;">
                                    @if (! $a->is_postable)
                                        <i class="bi bi-folder2-open text-{{ $meta['colour'] }}"></i>
                                        <strong>{{ $a->name }}</strong>
                                    @else
                                        @if ($depth > 0)<i class="bi bi-arrow-return-right text-muted small"></i>@endif
                                        {{ $a->name }}
                                    @endif
                                </span>
                                @if ($a->category)
                                    <span class="badge bg-light text-muted ms-1">{{ $a->category }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $meta['colour'] }} bg-opacity-10 text-{{ $meta['colour'] }}">
                                    {{ $meta['label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($a->is_postable)
                                    <i class="bi bi-check-circle-fill text-success" title="Postable"></i>
                                @else
                                    <i class="bi bi-dash-circle text-muted" title="Header / non-postable"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($b && $b->debit_total > 0)
                                    <span class="fw-semibold">{{ number_format($b->debit_total, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($b && $b->credit_total > 0)
                                    <span class="fw-semibold">{{ number_format($b->credit_total, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($b)
                                    <a href="{{ route('accounting.journal.index') }}?coa={{ $a->id }}"
                                        class="badge bg-info bg-opacity-15 text-info text-decoration-none">
                                        {{ $b->posting_count }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($a->is_active)
                                    <span class="badge bg-success bg-opacity-15 text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-15 text-muted">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('accounting.coa.manage')
                                    <a href="{{ route('accounting.coa.edit', $a) }}"
                                        class="btn btn-sm btn-link p-0" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
                <tfoot class="table-light fw-bold sticky-bottom" style="bottom:0;">
                    <tr>
                        <td colspan="4" class="text-end">Grand total</td>
                        <td class="text-end" id="grandDr">৳ {{ number_format($totalDr, 2) }}</td>
                        <td class="text-end" id="grandCr">৳ {{ number_format($totalCr, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                    @if (abs($totalDr - $totalCr) > 0.01)
                        <tr class="text-danger">
                            <td colspan="9" class="text-end small">Unbalanced by ৳ {{ number_format(abs($totalDr - $totalCr), 2) }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Empty state when search matches nothing --}}
    <div id="coaEmpty" class="text-center py-5 text-muted d-none">
        <i class="bi bi-search display-4"></i>
        <p class="mt-2">No accounts match your filter.</p>
    </div>
</div>

@push('styles')
<style>
    .coa-page .type-summary-card { transition: transform .15s ease; }
    .coa-page .type-summary-card:hover { transform: translateY(-2px); }
    .coa-tree code { font-size: .85rem; }
    .coa-tree .coa-section-row td { font-size: .8rem; letter-spacing: .5px; }
    .coa-tree-card { max-height: calc(100vh - 280px); overflow: hidden; }
    .coa-tree-card .table-responsive { max-height: calc(100vh - 280px); overflow-y: auto; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const search = document.getElementById('coaSearch');
    const filterBtns = document.querySelectorAll('#coaTypeFilter button');
    const hideZero = document.getElementById('toggleZeroBalances');
    const rows = document.querySelectorAll('#coaTable .coa-row');
    const sections = document.querySelectorAll('#coaTable [data-section-row]');
    const empty = document.getElementById('coaEmpty');
    let currentType = 'all';
    let hidingZero = false;

    function apply() {
        const q = (search.value || '').toLowerCase().trim();
        let visible = 0;
        const visibleByType = {};
        rows.forEach(r => {
            const matchesText = !q || r.dataset.name.includes(q);
            const matchesType = currentType === 'all' || r.dataset.type === currentType;
            const matchesBal = !hidingZero || r.dataset.hasBalance === '1';
            const show = matchesText && matchesType && matchesBal;
            r.style.display = show ? '' : 'none';
            if (show) {
                visible++;
                visibleByType[r.dataset.type] = (visibleByType[r.dataset.type] || 0) + 1;
            }
        });
        // Hide section header if no children visible in that section
        sections.forEach(s => {
            const t = s.dataset.type;
            s.style.display = visibleByType[t] ? '' : 'none';
        });
        empty.classList.toggle('d-none', visible !== 0);
    }

    search.addEventListener('input', apply);
    filterBtns.forEach(b => b.addEventListener('click', () => {
        filterBtns.forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        currentType = b.dataset.filter;
        apply();
    }));
    hideZero.addEventListener('click', () => {
        hidingZero = ! hidingZero;
        hideZero.classList.toggle('active', hidingZero);
        hideZero.innerHTML = hidingZero
            ? '<i class="bi bi-eye"></i> Show all'
            : '<i class="bi bi-eye-slash"></i> Hide zero-balance';
        apply();
    });
})();
</script>
@endpush
@endsection
