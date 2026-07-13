<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Location Name <span class="text-danger">*</span></label>
        <input type="text" id="bb_sl_name" name="location_name"
            value="{{ old('location_name', $item->location_name ?? '') }}" class="form-control" required>
        @error('location_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Location Type <span class="text-danger">*</span></label>
        <select id="bb_sl_type" name="location_type" class="form-select" required>
            @foreach (['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER'] as $t)
                <option value="{{ $t }}" @selected(old('location_type', $item->location_type ?? 'BLOOD_BANK') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('location_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Capacity (units) <span class="text-danger">*</span></label>
        <input type="number" id="bb_sl_capacity" name="capacity_units"
            value="{{ old('capacity_units', $item->capacity_units ?? 0) }}" class="form-control" min="0"
            required>
        @error('capacity_units')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Temperature Monitoring Required</label>
        <select id="bb_sl_monitor" name="temperature_monitoring_required" class="form-select">
            <option value="1" @selected(old('temperature_monitoring_required', $item->temperature_monitoring_required ?? 0) == 1)>Yes</option>
            <option value="0" @selected(old('temperature_monitoring_required', $item->temperature_monitoring_required ?? 0) == 0)>No</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Device ID (optional)</label>
        <input type="text" id="bb_sl_device" name="device_id" value="{{ old('device_id', $item->device_id ?? '') }}"
            class="form-control">
        @error('device_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select id="bb_sl_status" name="status" class="form-select">
            <option value="ACTIVE" @selected(old('status', $item->status ?? 'ACTIVE') === 'ACTIVE')>ACTIVE</option>
            <option value="MAINTENANCE" @selected(old('status', $item->status ?? '') === 'MAINTENANCE')>MAINTENANCE</option>
        </select>
    </div>
</div>
