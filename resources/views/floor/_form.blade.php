<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="floor_name" name="name" value="{{ old('name', $floor->name ?? '') }}"
            class="form-control" required>

        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
