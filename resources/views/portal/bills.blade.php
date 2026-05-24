@extends('portal.layout')
@section('title', 'My Bills')

@section('content')
<h4 class="mb-3"><i class="bi bi-receipt"></i> My Bills</h4>

<div class="card portal-card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill No</th><th>Date</th><th>Type</th>
                    <th class="text-end">Grand</th><th class="text-end">Paid</th><th class="text-end">Due</th>
                    <th>Status</th><th>Items</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bills as $b)
                    <tr>
                        <td><strong>{{ $b->bill_no }}</strong></td>
                        <td><small>{{ \Carbon\Carbon::parse($b->bill_date)->format('Y-m-d') }}</small></td>
                        <td><span class="badge bg-secondary">{{ strtoupper($b->bill_type) }}</span></td>
                        <td class="text-end">৳ {{ number_format($b->grand_total, 2) }}</td>
                        <td class="text-end text-success">৳ {{ number_format($b->paid_total, 2) }}</td>
                        <td class="text-end {{ $b->balance_due > 0.01 ? 'text-danger fw-bold' : '' }}">৳ {{ number_format($b->balance_due, 2) }}</td>
                        <td><span class="badge bg-{{ $b->status === 'paid' ? 'success' : 'warning text-dark' }}">{{ $b->status }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#items-{{ $b->id }}">
                                <i class="bi bi-list"></i> {{ $b->items->count() }} items
                            </button>
                        </td>
                    </tr>
                    <tr id="items-{{ $b->id }}" class="collapse">
                        <td colspan="8" class="p-0 bg-light">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Total</th></tr></thead>
                                <tbody>
                                    @foreach ($b->items as $item)
                                        <tr>
                                            <td><small>{{ $item->description }}</small></td>
                                            <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                            <td class="text-end">৳ {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end">৳ {{ number_format($item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> No bills yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($bills->hasPages())
        <div class="p-2">{{ $bills->links() }}</div>
    @endif
</div>
@endsection
