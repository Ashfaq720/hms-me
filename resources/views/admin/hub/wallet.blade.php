@extends('backend.layouts.master')
@section('title', 'Doctor Wallet')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-wallet2 text-success"></i> Doctor Wallet — Commission Tracking</h4>
            <small class="text-muted">Auto-accrued commission per encounter, settled via payroll</small>
        </div>
        <a href="{{ route('admin.hub.reports') }}" class="btn btn-sm btn-outline-primary">← Reports Hub</a>
    </div>

    {{-- KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3"><small class="text-primary">Total Transactions</small><h4 class="mb-0">{{ $stats['total_txns'] }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3"><small class="text-success">Gross Revenue</small><h4 class="mb-0">৳ {{ number_format($stats['gross_revenue'], 0) }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3"><small class="text-warning">Commission Due</small><h4 class="mb-0">৳ {{ number_format($stats['commission_due'], 0) }}</h4></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3"><small class="text-info">Commission Paid</small><h4 class="mb-0">৳ {{ number_format($stats['commission_paid'], 0) }}</h4></div></div>
    </div>

    <div class="row g-3">
        {{-- Per-doctor summary --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h6 class="mb-0">By Doctor</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>Doctor</th><th class="text-center">Txns</th><th class="text-end">Gross</th><th class="text-end">Commission</th></tr></thead>
                        <tbody>
                        @forelse ($byEmployee as $r)
                            <tr>
                                <td><strong>{{ trim($r->name) ?: 'Unknown' }}</strong><br><small class="text-muted">{{ $r->employee_code }}</small></td>
                                <td class="text-center">{{ $r->txns }}</td>
                                <td class="text-end">৳ {{ number_format((float) $r->gross, 0) }}</td>
                                <td class="text-end text-success">৳ {{ number_format((float) $r->commission, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No commission accrued yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h6 class="mb-0">Wallet Transactions ({{ $txns->total() }})</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>#</th><th>Doctor</th><th>Source</th><th class="text-end">Gross</th><th class="text-center">%</th><th class="text-end">Commission</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($txns as $t)
                            <tr>
                                <td>{{ $t->id }}</td>
                                <td>{{ trim($t->employee_name ?? '') ?: '—' }}</td>
                                <td><small>{{ $t->source }}</small></td>
                                <td class="text-end">৳ {{ number_format((float) $t->gross_amount, 2) }}</td>
                                <td class="text-center">{{ $t->commission_percent }}%</td>
                                <td class="text-end text-success">৳ {{ number_format((float) $t->commission_amount, 2) }}</td>
                                <td>
                                    @php $col = ['accrued' => 'warning text-dark', 'paid' => 'success', 'cancelled' => 'danger'][$t->status] ?? 'secondary'; @endphp
                                    <span class="badge bg-{{ $col }}">{{ ucfirst($t->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">
                                No transactions yet. Doctor commission auto-accrues when bills are finalized.
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $txns->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
