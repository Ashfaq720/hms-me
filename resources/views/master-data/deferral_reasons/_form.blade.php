<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Deferral Reason <span class="text-danger">*</span></label>
        <input type="text" id="bb_def_reason" name="deferral_reason"
            value="{{ old('deferral_reason', $item->deferral_reason ?? '') }}" class="form-control" required>
        @error('deferral_reason')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Deferral Type <span class="text-danger">*</span></label>
        <select id="bb_def_type" name="deferral_type" class="form-select" required>
            <option value="TEMP" @selected(old('deferral_type', $item->deferral_type ?? 'TEMP') === 'TEMP')>TEMP</option>
            <option value="PERM" @selected(old('deferral_type', $item->deferral_type ?? '') === 'PERM')>PERM</option>
        </select>
        @error('deferral_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Default Duration (days)</label>
        <input type="number" id="bb_def_duration" name="default_duration_days"
            value="{{ old('default_duration_days', $item->default_duration_days ?? '') }}" class="form-control"
            min="1">
        @error('default_duration_days')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Regulatory Reference</label>
        <input type="text" id="bb_def_ref" name="regulatory_reference"
            value="{{ old('regulatory_reference', $item->regulatory_reference ?? '') }}" class="form-control">
        @error('regulatory_reference')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Active</label>
        <select id="bb_def_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>

    <div class="col-12" id="bb_def_lock_msg" style="display:none;">
        <div class="alert alert-warning mb-0">This deferral reason is locked/used and cannot be edited.</div>
    </div>
</div>
