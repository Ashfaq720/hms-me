<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="bed_group_name" name="name" value="{{ old('name', default: $data->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Floor <span class="text-danger">*</span></label>
        <select id="floor_id" name="floor_id" class="form-select" required>
            <option value="" disabled {{ old('floor_id', $data->floor_id ?? '') ? '' : 'selected' }}>Select Floor
            </option>
            @foreach ($floors as $f)
                <option value="{{ $f->id }}"
                    {{ old('floor_id', $data->floor_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}</option>
            @endforeach
        </select>
        @error('floor_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
