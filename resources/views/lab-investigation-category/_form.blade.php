<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="category_name" name="name" value="{{ old('name', $data->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select id="category_type_id" name="type_id" class="form-select" required>
            <option value="" disabled {{ old('type_id', $data->type_id ?? '') ? '' : 'selected' }}>Select Type</option>
            @foreach ($types as $f)
                <option value="{{ $f->id }}"
                    {{ old('type_id', $data->type_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}
                </option>
            @endforeach
        </select>
        @error('type_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea id="category_notes" name="notes" class="form-control" rows="3">{{ old('notes', $data->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
