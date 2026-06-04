@extends('backend.layouts.master')
@section('title', $item->name)
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="app-page-title">{{ $item->name }}</h1>
        <div>
            @can('inventory.item.manage')
                <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-primary">Edit</a>
            @endcan
            <a href="{{ route('inventory.items.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Master</h5>
                <dl class="row mb-0">
                    <dt class="col-5">Code</dt><dd class="col-7"><code>{{ $item->code }}</code></dd>
                    <dt class="col-5">Category</dt><dd class="col-7">{{ $item->category }}</dd>
                    <dt class="col-5">Generic</dt><dd class="col-7">{{ $item->generic_name }}</dd>
                    <dt class="col-5">Brand</dt><dd class="col-7">{{ $item->brand }}</dd>
                    <dt class="col-5">UOM</dt><dd class="col-7">{{ $item->uom }}</dd>
                    <dt class="col-5">Reorder level</dt><dd class="col-7">{{ $item->reorder_level }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Batches on hand</h5>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Batch</th><th>Expiry</th><th>Warehouse</th><th class="text-end">Qty</th></tr></thead>
                    <tbody>
                        @forelse ($item->batches as $b)
                            <tr>
                                <td>{{ $b->batch_no }}</td>
                                <td>{{ optional($b->expiry_date)->toDateString() }}</td>
                                <td>{{ optional($b->warehouse)->name }}</td>
                                <td class="text-end">{{ number_format((float) $b->current_qty, 4) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No batches.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>

    <div class="card mt-3"><div class="card-header"><strong>Recent stock movements (immutable ledger)</strong></div>
        <table class="table mb-0">
            <thead><tr><th>When</th><th>Direction</th><th class="text-end">Qty</th><th class="text-end">Balance</th><th>Reason</th><th>Ref</th></tr></thead>
            <tbody>
                @forelse ($item->movements as $m)
                    <tr>
                        <td>{{ optional($m->performed_at)->toDateTimeString() }}</td>
                        <td><span class="badge bg-{{ str_starts_with($m->direction,'out') ? 'warning' : 'success' }}">{{ $m->direction }}</span></td>
                        <td class="text-end">{{ number_format((float) $m->quantity, 4) }}</td>
                        <td class="text-end">{{ number_format((float) $m->balance_after, 4) }}</td>
                        <td>{{ $m->reason }}</td>
                        <td>{{ $m->reference_no }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No movements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
