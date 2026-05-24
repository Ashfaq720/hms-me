@extends('backend.layouts.master')
@section('title','Insight Dashboard')
@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Insight Dashboard</h1>
            <p class="text-muted small mb-0">Real-time KPIs across patients, encounters, billing, stock and insurance (SRS &sect;5.27).</p>
        </div>
        <small class="text-muted">As of {{ now()->toDateTimeString() }}</small>
    </div>

    {{-- Top KPI cards --}}
    <div class="row g-3 mt-2">
        @foreach ([
            ['Patients (total)',        $kpi['patients_total'],          'primary',  'fi-rr-users'],
            ['Open encounters',         $kpi['encounters_open'],         'info',     'fi-rr-stethoscope'],
            ['Encounters today',        $kpi['encounters_today'],        'success',  'fi-rr-calendar'],
            ['OPD today',               $kpi['opd_today'],               'secondary','fi-rr-user-md'],
            ['IPD open',                $kpi['ipd_open'],                'warning',  'fi-rr-bed'],
            ['ER today',                $kpi['er_today'],                'danger',   'fi-rr-ambulance'],
        ] as [$label, $value, $color, $icon])
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card bg-{{ $color }} bg-opacity-10 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-{{ $color }} small">{{ $label }}</div>
                                <h3 class="mb-0">{{ number_format($value) }}</h3>
                            </div>
                            <i class="fi {{ $icon }} fs-2 text-{{ $color }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Financial KPIs --}}
    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Revenue today</div>
                <h3 class="mb-0">{{ number_format($kpi['revenue_today'], 2) }}</h3>
                <small class="text-success">Collected: {{ number_format($kpi['collection_today'], 2) }}</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Revenue this month</div>
                <h3 class="mb-0">{{ number_format($kpi['revenue_month'], 2) }}</h3>
                <small class="text-muted">Bills: {{ $kpi['bills_open'] }} open</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Outstanding due</div>
                <h3 class="mb-0 {{ $kpi['outstanding_due'] > 0 ? 'text-danger' : '' }}">{{ number_format($kpi['outstanding_due'], 2) }}</h3>
                <small class="text-muted">Service-charge revenue ledger: {{ number_format($kpi['service_charge_revenue'], 2) }}</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Inventory KPIs</div>
                <div class="d-flex justify-content-between">
                    <span>Items: <strong>{{ $kpi['stock_items'] }}</strong></span>
                    <span>Movements today: <strong>{{ $kpi['stock_movements_today'] }}</strong></span>
                </div>
                <small class="text-{{ $kpi['near_expiry_batches'] > 0 ? 'warning' : 'muted' }}">
                    Near expiry (30d): {{ $kpi['near_expiry_batches'] }} ·
                    <span class="text-danger">Expired: {{ $kpi['expired_batches'] }}</span>
                </small>
            </div></div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><strong>Revenue by bill type — this month</strong></div>
                <table class="table mb-0">
                    <thead><tr><th>Type</th><th class="text-end">Bills</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @forelse ($revenueByType as $r)
                            <tr>
                                <td><span class="badge bg-info-soft">{{ strtoupper($r->bill_type) }}</span></td>
                                <td class="text-end">{{ $r->cnt }}</td>
                                <td class="text-end">{{ number_format((float) $r->total, 2) }}</td>
                            </tr>
                        @empty <tr><td colspan="3" class="text-center text-muted py-3">No bills this month.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between">
                    <strong>Recent bills</strong>
                    @can('billing.bill.view') <a href="{{ route('billing.bills.index') }}" class="btn btn-sm btn-outline-primary">View all</a> @endcan
                </div>
                <table class="table mb-0">
                    <thead><tr><th>Bill #</th><th>Patient</th><th>Type</th><th class="text-end">Total</th><th class="text-end">Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($recentBills as $b)
                            <tr>
                                <td><a href="{{ route('billing.bills.show',$b) }}"><code>{{ $b->bill_no }}</code></a></td>
                                <td>{{ optional($b->patient)->patient_name }}</td>
                                <td><span class="badge bg-info-soft">{{ strtoupper($b->bill_type) }}</span></td>
                                <td class="text-end">{{ number_format((float) $b->grand_total,2) }}</td>
                                <td class="text-end {{ $b->balance_due > 0 ? 'text-danger' : '' }}">{{ number_format((float) $b->balance_due,2) }}</td>
                                <td>{{ ucwords(str_replace('_',' ',$b->status)) }}</td>
                            </tr>
                        @empty <tr><td colspan="6" class="text-center text-muted py-3">No bills yet. Create an OPD visit to generate one automatically.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><strong>Near-expiry batches (30 days)</strong></div>
                <table class="table mb-0">
                    <thead><tr><th>Item</th><th>Batch</th><th>Expiry</th><th class="text-end">Qty</th></tr></thead>
                    <tbody>
                        @forelse ($criticalBatches as $b)
                            <tr>
                                <td>{{ optional($b->item)->name }}</td>
                                <td><code>{{ $b->batch_no }}</code></td>
                                <td>{{ optional($b->expiry_date)->toDateString() }}</td>
                                <td class="text-end">{{ number_format((float) $b->current_qty, 2) }}</td>
                            </tr>
                        @empty <tr><td colspan="4" class="text-center text-muted py-3">No near-expiry batches.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card mt-3 bg-light border-0">
                <div class="card-body">
                    <h6>Compliance summary</h6>
                    <ul class="mb-0 small">
                        <li>Service-charge postings (immutable): <strong>{{ number_format($kpi['service_charge_postings']) }}</strong></li>
                        <li>Stock movements (immutable): <strong>{{ number_format(\App\Models\Inventory\StockMovement::count()) }}</strong></li>
                        <li>Open insurance claims: <strong>{{ $kpi['claims_open'] }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
