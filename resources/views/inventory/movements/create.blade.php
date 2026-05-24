@extends('backend.layouts.master')
@section('title', 'Record Stock Movement')
@section('content')
<div class="container">
    <h1 class="app-page-title">Record Stock Movement</h1>
    <form method="POST" action="{{ route('inventory.movements.store') }}" class="card p-4 mt-3">
        @csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Item *</label>
                <select name="inventory_item_id" class="form-select" required>
                    <option value="">Select item</option>
                    @foreach ($items as $i) <option value="{{ $i->id }}">{{ $i->name }} ({{ $i->code }})</option> @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Warehouse *</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">Select warehouse</option>
                    @foreach ($warehouses as $w) <option value="{{ $w->id }}">{{ $w->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Direction *</label>
                <select name="direction" class="form-select" required>
                    <option value="in">In (receive)</option>
                    <option value="out">Out (dispense)</option>
                    <option value="adjustment_in">Adjustment in</option>
                    <option value="adjustment_out">Adjustment out</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Quantity *</label>
                <input type="number" step="0.0001" name="quantity" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Unit cost</label>
                <input type="number" step="0.01" name="unit_cost" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Reason *</label>
                <select name="reason" class="form-select" required>
                    @foreach (['grn','pharmacy_dispense','ipd_dispense','opd_dispense','ot_consumption','icu_consumption','lab_consumption','return','damaged','expired','transfer','stock_count','opening','other'] as $r)
                        <option value="{{ $r }}">{{ str_replace('_',' ',$r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Batch no</label>
                <input name="batch_no" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Expiry date</label>
                <input type="date" name="expiry_date" class="form-control"></div>
            <div class="col-12"><label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"></textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Record</button>
        </div>
    </form>
</div>
@endsection
