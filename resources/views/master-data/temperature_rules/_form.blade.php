<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Component <span class="text-danger">*</span></label>
        <select id="bb_tr_component_id" name="component_id" class="form-select" required>
            <option value="">Select Component</option>
            @foreach ($components as $c)
                <option value="{{ $c->id }}" @selected(old('component_id', $item->component_id ?? '') == $c->id)>
                    {{ $c->component_name }}
                </option>
            @endforeach
        </select>
        @error('component_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Min Temp (°C) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="bb_tr_min" name="min_temp"
            value="{{ old('min_temp', $item->min_temp ?? '') }}" class="form-control" required>
        @error('min_temp')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Max Temp (°C) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="bb_tr_max" name="max_temp"
            value="{{ old('max_temp', $item->max_temp ?? '') }}" class="form-control" required>
        @error('max_temp')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Monitoring Required</label>
        <select id="bb_tr_monitor" name="monitoring_required" class="form-select">
            <option value="1" @selected(old('monitoring_required', $item->monitoring_required ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('monitoring_required', $item->monitoring_required ?? 1) == 0)>No</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Active</label>
        <select id="bb_tr_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>
</div>
