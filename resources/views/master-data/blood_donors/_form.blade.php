<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="bb_donor_name" name="name"
            value="{{ old('name', $item->name ?? '') }}" class="form-control" required>
        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
        <input type="date" id="bb_donor_dob" name="dob"
            value="{{ old('dob', isset($item->dob) ? $item->dob->format('Y-m-d') : '') }}" class="form-control" required>
        @error('dob')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Gender <span class="text-danger">*</span></label>
        <select id="bb_donor_gender" name="gender" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="MALE" @selected(old('gender', $item->gender ?? '') === 'MALE')>Male</option>
            <option value="FEMALE" @selected(old('gender', $item->gender ?? '') === 'FEMALE')>Female</option>
            <option value="OTHER" @selected(old('gender', $item->gender ?? '') === 'OTHER')>Other</option>
        </select>
        @error('gender')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Blood Group <span class="text-danger">*</span></label>
        <select id="bb_donor_blood_group" name="blood_group_id" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach ($bloodGroups as $bg)
                <option value="{{ $bg->id }}" @selected(old('blood_group_id', $item->blood_group_id ?? '') == $bg->id)>
                    {{ $bg->display_name }}
                </option>
            @endforeach
        </select>
        @error('blood_group_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Father's Name</label>
        <input type="text" id="bb_donor_father" name="father_name"
            value="{{ old('father_name', $item->father_name ?? '') }}" class="form-control">
        @error('father_name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Contact No <span class="text-danger">*</span></label>
        <input type="text" id="bb_donor_contact" name="contact_no"
            value="{{ old('contact_no', $item->contact_no ?? '') }}" class="form-control" required>
        @error('contact_no')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label">Address</label>
        <textarea id="bb_donor_address" name="address" class="form-control" rows="2">{{ old('address', $item->address ?? '') }}</textarea>
        @error('address')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Active</label>
        <select id="bb_donor_active" name="is_active" class="form-select">
            <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>No</option>
        </select>
    </div>
</div>
