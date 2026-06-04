@extends('backend.layouts.master')
@section('title', 'Stock Ledger')
@section('content')
<div class="container">
    <div class="app-page-head d-flex justify-content-between">
        <div>
            <h1 class="app-page-title">Stock Ledger</h1>
            <p class="text-muted small mb-0">Immutable record of every IN/OUT/ADJUSTMENT (SRS &sect;5.20).</p>
        </div>
        @can('inventory.stock.adjust')
            <a href="{{ route('inventory.movements.create') }}" class="btn btn-primary">Record Adjustment</a>
        @endcan
    </div>

    @if (session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif

    <form class="row g-2 mt-3">
        <div class="col-md-4">
            <select name="item_id" class="form-select"><option value="">All items</option>
                @foreach ($items as $i)
                    <option value="{{ $i->id }}" @selected(request('item_id') == $i->id)>{{ $i->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="warehouse_id" class="form-select"><option value="">All warehouses</option>
                @foreach ($warehouses as $w) <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option> @endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="text" name="reason" class="form-control" placeholder="Reason..." value="{{ request('reason') }}"></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
    </form>

    <div class="card mt-3"><table class="table mb-0">
        <thead><tr>
            <th>When</th><th>Item</th><th>Warehouse</th><th>Direction</th>
            <th class="text-end">Qty</th><th class="text-end">Balance after</th><th>Reason</th><th>Ref</th><th>By</th>
        </tr></thead>
        <tbody>
            @forelse ($movements as $m)
                <tr>
                    <td>{{ optional($m->performed_at)->toDateTimeString() }}</td>
                    <td>{{ optional($m->item)->name }}</td>
                    <td>{{ optional($m->warehouse)->name }}</td>
                    <td><span class="badge bg-{{ str_starts_with($m->direction,'out') ? 'warning' : 'success' }}">{{ $m->direction }}</span></td>
                    <td class="text-end">{{ number_format((float) $m->quantity, 4) }}</td>
                    <td class="text-end">{{ number_format((float) $m->balance_after, 4) }}</td>
                    <td>{{ $m->reason }}</td>
                    <td>{{ $m->reference_no }}</td>
                    <td>#{{ $m->performed_by }}</td>
                </tr>
            @empty <tr><td colspan="9" class="text-center text-muted py-3">No movements.</td></tr> @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $movements->links() }}</div>
</div>
@endsection
