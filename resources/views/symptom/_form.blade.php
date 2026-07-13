<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="symptom_name" name="name" value="{{ old('name', $data->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea id="symptom_notes" name="notes" class="form-control" rows="3">{{ old('notes', $data->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
