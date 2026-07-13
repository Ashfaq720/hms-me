@extends('backend.layouts.master')
@section('title','Consumables — ' . $schedule->schedule_no)
@section('content')
<div class="container-fluid">
    <div class="app-page-head mb-3 d-flex justify-content-between">
        <h1 class="app-page-title">Consumables — {{ $schedule->schedule_no }}</h1>
        <a href="{{ route('ot.schedules.show', $schedule->id) }}" class="btn btn-outline-secondary">Back</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-3">
        <div class="col-md-7"><div class="card">
            <div class="card-header"><strong>Items Used</strong></div>
            <div class="table-responsive"><table class="table mb-0">
                <thead class="table-light"><tr><th>Item</th><th>Type</th><th>Qty</th><th>Rate</th><th>Amount</th><th></th></tr></thead>
                <tbody>
                    @forelse($schedule->consumableUsages as $u)
                        <tr>
                            <td>{{ $u->item_name }}</td>
                            <td>{{ $u->type }}</td>
                            <td>{{ $u->quantity }} {{ $u->unit }}</td>
                            <td>{{ number_format($u->rate, 2) }}</td>
                            <td>{{ number_format($u->amount, 2) }}</td>
                            <td class="text-end">
                                @if(! $u->is_billed)
                                    <form action="{{ route('ot.consumables.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">×</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No items recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><th colspan="4" class="text-end">Total</th><th>{{ number_format($schedule->consumableUsages->sum('amount'), 2) }}</th><th></th></tr></tfoot>
            </table></div>
        </div></div>

        <div class="col-md-5"><div class="card">
            <div class="card-header"><strong>Add Usage</strong></div>
            <form action="{{ route('ot.consumables.store', $schedule->id) }}" method="POST" class="card-body">@csrf
                <div class="mb-2"><label class="form-label">Select from Master</label>
                    <select class="form-select" onchange="(()=>{const o=this.options[this.selectedIndex]; if(!o.value)return; document.querySelector('[name=item_name]').value=o.dataset.name; document.querySelector('[name=item_code]').value=o.dataset.code||''; document.querySelector('[name=rate]').value=o.dataset.rate; document.querySelector('[name=ot_consumable_id]').value=o.value;})()">
                        <option value="">— or type custom below —</option>
                        @foreach($consumables as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-code="{{ $c->code }}" data-rate="{{ $c->rate }}">{{ $c->name }} ({{ $c->code }}) — {{ number_format($c->rate, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="ot_consumable_id">
                <div class="row g-2">
                    <div class="col-12"><label class="form-label">Item Name *</label><input name="item_name" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Item Code</label><input name="item_code" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            @foreach(['consumable','implant','instrument','medicine'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-4"><label class="form-label">Qty *</label><input type="number" step="0.01" name="quantity" class="form-control" value="1" required></div>
                    <div class="col-4"><label class="form-label">Unit</label><input name="unit" class="form-control"></div>
                    <div class="col-4"><label class="form-label">Rate *</label><input type="number" step="0.01" name="rate" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
                </div>
                <button class="btn btn-primary w-100 mt-2">Add</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
