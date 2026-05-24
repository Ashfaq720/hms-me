<div class="row">
<div class="col-md-4">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}"
        class="form-control" required>
    @error('name')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Quantity <span class="text-danger">*</span></label>
    <input type="number" name="quantity" value="{{ old('quantity', $service->quantity ?? '') }}"
        class="form-control" min="1" required>
    @error('quantity')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Rate <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="rate" value="{{ old('rate', $service->rate ?? '') }}"
        class="form-control" required>
    @error('rate')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description ?? '') }}</textarea>
    @error('description')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select" required>
        <option value="">Select Status</option>
        <option value="1" {{ old('status', $service->status ?? '') == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status', $service->status ?? '') == 0 ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('status')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

</div>
