@extends('backend.layouts.master')
@section('title', 'Record Stock Movement')
@section('content')
<div class="container">
    <h1 class="app-page-title">Record Stock Movement</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.movements.store') }}" class="card p-4 mt-3">
        @csrf

        <div class="row g-3">
            {{-- Item --}}
            <div class="col-md-6">
                <label class="form-label">Item <span class="text-danger">*</span></label>
                <select name="inventory_item_id" class="form-select select2" data-placeholder="Select item" required>
                    <option value=""></option>
                    @foreach ($items as $i)
                        <option value="{{ $i->id }}" @selected(old('inventory_item_id') == $i->id)>
                            {{ $i->name }} ({{ $i->code }})
                        </option>
                    @endforeach
                </select>
                @error('inventory_item_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Warehouse --}}
            <div class="col-md-6">
                <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                <select name="warehouse_id" class="form-select select2" data-placeholder="Select warehouse" required>
                    <option value=""></option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>
                            {{ $w->name }} ({{ $w->code }})
                        </option>
                    @endforeach
                </select>
                @error('warehouse_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Direction --}}
            <div class="col-md-4">
                <label class="form-label">Direction <span class="text-danger">*</span></label>
                <select name="direction" class="form-select" required>
                    @foreach (['in'=>'In (receive)','out'=>'Out (dispense)','adjustment_in'=>'Adjustment in','adjustment_out'=>'Adjustment out'] as $v=>$label)
                        <option value="{{ $v }}" @selected(old('direction') === $v)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('direction') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Quantity --}}
            <div class="col-md-4">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" step="0.0001" min="0.0001" name="quantity"
                       class="form-control" value="{{ old('quantity') }}" required>
                @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Reason --}}
            <div class="col-md-4">
                <label class="form-label">Reason <span class="text-danger">*</span></label>
                <select name="reason" class="form-select" required>
                    @foreach (['grn'=>'Goods Received','pharmacy_dispense'=>'Pharmacy Dispense','ipd_dispense'=>'IPD Dispense','opd_dispense'=>'OPD Dispense','ot_consumption'=>'OT Consumption','icu_consumption'=>'ICU Consumption','lab_consumption'=>'Lab Consumption','return'=>'Return','damaged'=>'Damaged','expired'=>'Expired','transfer'=>'Transfer','stock_count'=>'Stock Count','opening'=>'Opening Balance','other'=>'Other'] as $v=>$label)
                        <option value="{{ $v }}" @selected(old('reason') === $v)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('reason') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Unit cost --}}
            <div class="col-md-4">
                <label class="form-label">Unit Cost</label>
                <input type="number" step="0.01" min="0" name="unit_cost"
                       class="form-control" value="{{ old('unit_cost') }}"
                       placeholder="Cost per unit (BDT)">
                <small class="text-muted">For inbound: cost paid. For outbound: read from FIFO batch.</small>
            </div>

            {{-- Selling price (only meaningful on receive) --}}
            <div class="col-md-4">
                <label class="form-label">Selling Price</label>
                <input type="number" step="0.01" min="0" name="selling_price"
                       class="form-control" value="{{ old('selling_price') }}"
                       placeholder="MRP / sell rate (BDT)">
                <small class="text-muted">Used on inbound to set the batch's selling_price.</small>
            </div>

            {{-- Reference no --}}
            <div class="col-md-4">
                <label class="form-label">Reference No.</label>
                <input name="reference_no" class="form-control" value="{{ old('reference_no') }}"
                       placeholder="GRN-001 / PO-2026-…">
            </div>

            {{-- Batch no --}}
            <div class="col-md-4">
                <label class="form-label">Batch No</label>
                <input name="batch_no" class="form-control" value="{{ old('batch_no') }}"
                       placeholder="Required on receive (else auto-generated)">
            </div>

            {{-- Expiry --}}
            <div class="col-md-4">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
            </div>

            {{-- Storage location --}}
            <div class="col-md-4">
                <label class="form-label">Storage Location</label>
                <input name="storage_location" class="form-control" value="{{ old('storage_location') }}"
                       placeholder="Rack / Shelf / Bin">
            </div>

            {{-- Remarks --}}
            <div class="col-12">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"
                          placeholder="Any context (donor, supplier note, audit reason…)">{{ old('remarks') }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Record Movement
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('select.select2').each(function () {
                if (jQuery(this).hasClass('select2-hidden-accessible')) return;
                jQuery(this).select2({
                    width: '100%',
                    theme: 'bootstrap-5',
                    placeholder: jQuery(this).data('placeholder') || 'Select',
                    allowClear: true,
                });
            });
        }
    });
</script>
@endpush
@endsection
