<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="bed_group_name" name="name" value="{{ old('name', default: $data->name ?? '') }}"
            class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Rent <span class="text-danger">*</span></label>
        <input type="number" id="bed_rent" name="rent" value="{{ old('rent', default: $data->rent ?? '') }}"
            class="form-control" required>
        @error('rent')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Bed Type <span class="text-danger">*</span></label>
        <select id="bed_type_id" name="bed_type_id" class="form-select" required>
            <option value="" disabled {{ old('bed_type_id', $data->bed_type_id ?? '') ? '' : 'selected' }}>Select
                Bed Type
            </option>
            @foreach ($bedTypes as $f)
                <option value="{{ $f->id }}"
                    {{ old('bed_type_id', $data->bed_type_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}</option>
            @endforeach
        </select>
        @error('bed_type_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Bed Group <span class="text-danger">*</span></label>
        <select id="bed_group_id" name="bed_group_id" class="form-select" required>
            <option value="" disabled {{ old('bed_group_id', $data->bed_group_id ?? '') ? '' : 'selected' }}>
                Select Bed Group
            </option>
            @foreach ($bedGroups as $f)
                <option value="{{ $f->id }}"
                    {{ old('bed_group_id', $data->bed_group_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}</option>
            @endforeach
        </select>
        @error('bed_group_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Available</label>
        <select id="bed_is_reserved" name="is_reserved" class="form-select">
            <option value="0" @selected(old('is_reserved', $data->is_reserved ?? 0) == 0)>Yes</option>
            <option value="1" @selected(old('is_reserved', $data->is_reserved ?? 0) == 1)>No</option>
        </select>
        @error('is_reserved')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
