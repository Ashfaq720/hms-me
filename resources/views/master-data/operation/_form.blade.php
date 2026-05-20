<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="operation_name" name="name" value="{{ old('name', $operation->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Charge <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="operation_charge" name="charge" value="{{ old('charge', $operation->charge ?? '') }}"
            class="form-control" required>
        @error('charge')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
