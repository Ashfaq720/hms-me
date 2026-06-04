@extends('backend.layouts.master')
@section('title', $meta['label'])
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-{{ $meta['icon'] }} text-{{ $meta['colour'] }}"></i>
                {{ $meta['label'] }}
            </h4>
            <small class="text-muted">{{ $totals['count'] }} bills · ৳ {{ number_format($totals['grand'], 2) }} billed · ৳ {{ number_format($totals['paid'], 2) }} paid · ৳ {{ number_format($totals['due'], 2) }} outstanding</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Bill no or patient…" style="width:200px;">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All status</option>
                    @foreach (['draft', 'partially_paid', 'paid', 'final', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body py-2">
                    <small class="text-primary">Total Bills</small>
                    <h5 class="mb-0">{{ $totals['count'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body py-2">
                    <small class="text-info">Billed Amount</small>
                    <h5 class="mb-0">৳ {{ number_format($totals['grand'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body py-2">
                    <small class="text-success">Collected</small>
                    <h5 class="mb-0">৳ {{ number_format($totals['paid'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm {{ $totals['due'] > 0.01 ? 'bg-danger' : 'bg-light' }} bg-opacity-10">
                <div class="card-body py-2">
                    <small class="text-{{ $totals['due'] > 0.01 ? 'danger' : 'muted' }}">Outstanding</small>
                    <h5 class="mb-0">৳ {{ number_format($totals['due'], 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Bill No</th><th>Date</th><th>Patient</th><th>Type</th>
                        <th class="text-end">Grand ৳</th><th class="text-end">Paid ৳</th><th class="text-end">Due ৳</th>
                        <th>Status</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($bills as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td><strong>{{ $b->bill_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($b->bill_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($b->patient)->patient_name ?? '—' }}<br>
                            <small class="text-muted">{{ optional($b->patient)->mrn }}</small></td>
                        <td><span class="badge bg-{{ $meta['colour'] }} bg-opacity-15 text-{{ $meta['colour'] }}">{{ $b->bill_type }}</span></td>
                        <td class="text-end">{{ number_format($b->grand_total, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($b->paid_total, 2) }}</td>
                        <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger' : '' }}">{{ number_format($b->balance_due, 2) }}</td>
                        <td>
                            @php $cls = ['paid'=>'success','final'=>'success','partially_paid'=>'warning text-dark','draft'=>'secondary','cancelled'=>'danger'][$b->status] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $cls }}">{{ ucfirst(str_replace('_', ' ', $b->status)) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('billing.bills.show', $b->id) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('billing.category.pdf', $b->id) }}" class="btn btn-outline-danger" title="PDF" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                <a href="{{ route('billing.bills.print', $b->id) }}" class="btn btn-outline-secondary" title="Print" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">
                        No {{ strtolower($meta['label']) }} bills found.
                        <br><small>Bills are auto-assembled from encounter postings when bill_type matches: <code>{{ implode(', ', $meta['types']) }}</code></small>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $bills->links() }}</div>
    </div>
</div>
@endsection
