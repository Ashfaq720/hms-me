<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">ABO <span class="text-danger">*</span></label>
        <select id="bb_bg_abo" name="abo_group" class="form-select" required>
            <option value="">Select</option>
            @foreach (['A', 'B', 'AB', 'O'] as $abo)
                <option value="{{ $abo }}" @selected(old('abo_group', $item->abo_group ?? '') == $abo)>{{ $abo }}</option>
            @endforeach
        </select>
        @error('abo_group')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Rh <span class="text-danger">*</span></label>
        <select id="bb_bg_rh" name="rh_factor" class="form-select" required>
            <option value="">Select</option>
            <option value="POS" @selected(old('rh_factor', $item->rh_factor ?? '') === 'POS')>Positive (+)</option>
            <option value="NEG" @selected(old('rh_factor', $item->rh_factor ?? '') === 'NEG')>Negative (-)</option>
        </select>
        @error('rh_factor')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Display Name <span class="text-danger">*</span></label>
        <input type="text" id="bb_bg_display" name="display_name"
            value="{{ old('display_name', $item->display_name ?? '') }}" class="form-control" required
            placeholder="e.g. O Positive">
        @error('display_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Active</label>
        <select id="bb_bg_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>

    <div class="col-12" id="bb_bg_lock_msg" style="display:none;">
        <div class="alert alert-warning mb-0">
            This blood group is locked/used and cannot be edited.
        </div>
    </div>
</div>
