<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Component Name <span class="text-danger">*</span></label>
        <input type="text" id="bb_cmp_name" name="component_name"
            value="{{ old('component_name', $item->component_name ?? '') }}" class="form-control" required>
        @error('component_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Derived From <span class="text-danger">*</span></label>
        <select id="bb_cmp_derived" name="derived_from" class="form-select" required>
            <option value="WHOLE_BLOOD" @selected(old('derived_from', $item->derived_from ?? 'WHOLE_BLOOD') === 'WHOLE_BLOOD')>WHOLE_BLOOD</option>
            <option value="COMPONENT" @selected(old('derived_from', $item->derived_from ?? '') === 'COMPONENT')>COMPONENT</option>
        </select>
        @error('derived_from')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Shelf Life <span class="text-danger">*</span></label>
        <input type="number" id="bb_cmp_shelf_value" name="shelf_life_value"
            value="{{ old('shelf_life_value', $item->shelf_life_value ?? '') }}" class="form-control" min="1"
            required>
        @error('shelf_life_value')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Shelf Life Unit <span class="text-danger">*</span></label>
        <select id="bb_cmp_shelf_unit" name="shelf_life_unit" class="form-select" required>
            <option value="HOURS" @selected(old('shelf_life_unit', $item->shelf_life_unit ?? '') === 'HOURS')>HOURS</option>
            <option value="DAYS" @selected(old('shelf_life_unit', $item->shelf_life_unit ?? 'DAYS') === 'DAYS')>DAYS</option>
        </select>
        @error('shelf_life_unit')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Storage Requirement <span class="text-danger">*</span></label>
        <select id="bb_cmp_storage" name="storage_requirement" class="form-select" required>
            @foreach (['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER'] as $s)
                <option value="{{ $s }}" @selected(old('storage_requirement', $item->storage_requirement ?? 'REFRIGERATOR') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('storage_requirement')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Min Volume (ml)</label>
        <input type="number" id="bb_cmp_min_vol" name="min_volume_ml"
            value="{{ old('min_volume_ml', $item->min_volume_ml ?? '') }}" class="form-control" min="1">
        @error('min_volume_ml')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Max Volume (ml)</label>
        <input type="number" id="bb_cmp_max_vol" name="max_volume_ml"
            value="{{ old('max_volume_ml', $item->max_volume_ml ?? '') }}" class="form-control" min="{{ old('min_volume_ml', $item->min_volume_ml ?? 1) }}">
        @error('max_volume_ml')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Active</label>
        <select id="bb_cmp_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>
</div>
