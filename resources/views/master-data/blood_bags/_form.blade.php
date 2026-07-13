<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Bag Type <span class="text-danger">*</span></label>
        <select id="bb_bag_type" name="bag_type" class="form-select" required>
            @foreach (['SINGLE', 'DOUBLE', 'TRIPLE'] as $t)
                <option value="{{ $t }}" @selected(old('bag_type', $item->bag_type ?? 'SINGLE') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('bag_type')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Volume (ml) <span class="text-danger">*</span></label>
        <input type="number" id="bb_bag_volume" name="volume_ml" value="{{ old('volume_ml', $item->volume_ml ?? '') }}"
            class="form-control" min="50" required>
        @error('volume_ml')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Active</label>
        <select id="bb_bag_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>

    <div class="col-md-12">
        <label class="form-label">Allowed Components <span class="text-danger">*</span></label>
        <select id="bb_bag_components" name="component_ids[]" class="form-select" multiple size="6" required>
            @foreach ($components as $c)
                <option value="{{ $c->id }}">{{ $c->component_name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Hold Ctrl / Cmd to select multiple.</small>
        @error('component_ids')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12" id="bb_bag_lock_msg" style="display:none;">
        <div class="alert alert-warning mb-0">This blood bag is locked/used and cannot be edited.</div>
    </div>
</div>
