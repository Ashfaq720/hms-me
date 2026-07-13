<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Ward / Group Name <span class="text-danger">*</span></label>
        <input type="text" id="bed_group_name" name="name" value="{{ old('name', $data->name ?? '') }}" class="form-control" placeholder="e.g. Ward-A, Cabin Block 1" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" id="bed_group_code" name="code" value="{{ old('code', $data->code ?? '') }}" class="form-control" placeholder="WA1">
    </div>
    <div class="col-md-3">
        <label class="form-label">Floor <span class="text-danger">*</span></label>
        <select id="floor_id" name="floor_id" class="form-select" required>
            <option value="" disabled {{ old('floor_id', $data->floor_id ?? '') ? '' : 'selected' }}>Select Floor</option>
            @foreach ($floors as $f)
                <option value="{{ $f->id }}" {{ old('floor_id', $data->floor_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }} @if($f->building) — {{ $f->building }} @endif
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Group Type</label>
        <select id="bed_group_type" name="group_type" class="form-select">
            @foreach (\App\Models\BedGroup::GROUP_TYPES as $code => $label)
                <option value="{{ $code }}" @selected(old('group_type', $data->group_type ?? 'ward') === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Gender Preference</label>
        <select id="bed_group_gender" name="gender_preference" class="form-select">
            @foreach (['any' => 'Any', 'male' => 'Male only', 'female' => 'Female only', 'mixed' => 'Mixed'] as $code => $label)
                <option value="{{ $code }}" @selected(old('gender_preference', $data->gender_preference ?? 'any') === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Notes</label>
        <input type="text" id="bed_group_notes" name="notes" value="{{ old('notes', $data->notes ?? '') }}" class="form-control" placeholder="optional">
    </div>
</div>
