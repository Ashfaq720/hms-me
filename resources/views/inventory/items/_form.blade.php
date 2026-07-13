<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Code *</label>
        <input name="code" class="form-control" value="{{ old('code', $item->code) }}" required>
        @error('code') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-md-8"><label class="form-label">Name *</label>
        <input name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
    </div>
    <div class="col-md-4"><label class="form-label">Category</label>
        <input name="category" class="form-control" value="{{ old('category', $item->category) }}">
    </div>
    <div class="col-md-4"><label class="form-label">Generic Name</label>
        <input name="generic_name" class="form-control" value="{{ old('generic_name', $item->generic_name) }}">
    </div>
    <div class="col-md-4"><label class="form-label">Brand</label>
        <input name="brand" class="form-control" value="{{ old('brand', $item->brand) }}">
    </div>
    <div class="col-md-3"><label class="form-label">SKU</label>
        <input name="sku" class="form-control" value="{{ old('sku', $item->sku) }}">
    </div>
    <div class="col-md-3"><label class="form-label">Barcode</label>
        <input name="barcode" class="form-control" value="{{ old('barcode', $item->barcode) }}">
    </div>
    <div class="col-md-3"><label class="form-label">UOM *</label>
        <input name="uom" class="form-control" value="{{ old('uom', $item->uom ?? 'unit') }}" required>
    </div>
    <div class="col-md-3"><label class="form-label">Tax %</label>
        <input type="number" step="0.01" name="tax_percent" class="form-control" value="{{ old('tax_percent', $item->tax_percent ?? 0) }}">
    </div>
    <div class="col-md-3"><label class="form-label">Reorder level</label>
        <input type="number" step="0.0001" name="reorder_level" class="form-control" value="{{ old('reorder_level', $item->reorder_level ?? 0) }}">
    </div>
    <div class="col-md-3"><label class="form-label">Reorder qty</label>
        <input type="number" step="0.0001" name="reorder_quantity" class="form-control" value="{{ old('reorder_quantity', $item->reorder_quantity ?? 0) }}">
    </div>
    <div class="col-md-6"><label class="form-label">Storage condition</label>
        <input name="storage_condition" class="form-control" value="{{ old('storage_condition', $item->storage_condition) }}">
    </div>
    <div class="col-12 d-flex gap-3 flex-wrap">
        <div class="form-check">
            <input type="hidden" name="is_controlled" value="0">
            <input type="checkbox" name="is_controlled" value="1" id="ic" class="form-check-input"
                @checked(old('is_controlled', $item->is_controlled ?? false))>
            <label for="ic" class="form-check-label">Controlled drug</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_consumable" value="0">
            <input type="checkbox" name="is_consumable" value="1" id="ico" class="form-check-input"
                @checked(old('is_consumable', $item->is_consumable ?? true))>
            <label for="ico" class="form-check-label">Consumable</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_asset" value="0">
            <input type="checkbox" name="is_asset" value="1" id="ia" class="form-check-input"
                @checked(old('is_asset', $item->is_asset ?? false))>
            <label for="ia" class="form-check-label">Asset</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="iact" class="form-check-input"
                @checked(old('is_active', $item->is_active ?? true))>
            <label for="iact" class="form-check-label">Active</label>
        </div>
    </div>
    <div class="col-12"><label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $item->description) }}</textarea>
    </div>
</div>
