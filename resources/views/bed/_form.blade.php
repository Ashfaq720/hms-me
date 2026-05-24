@php
    $rooms     = $rooms     ?? \App\Models\Room::with('bedGroup')->where('is_active', true)->orderBy('room_no')->get();
    $packages  = $packages  ?? \App\Models\Package::where('is_active', true)->orderBy('name')->get(['id', 'name', 'package_type']);
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Bed Name / Code <span class="text-danger">*</span></label>
        <input type="text" id="bed_group_name" name="name" value="{{ old('name', $data->name ?? '') }}" class="form-control" placeholder="e.g. A-101" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Bed No</label>
        <input type="text" name="bed_no" value="{{ old('bed_no', $data->bed_no ?? '') }}" class="form-control" placeholder="01">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach (\App\Models\Bed::STATUSES as $code => $label)
                <option value="{{ $code }}" @selected(old('status', $data->status ?? 'available') === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Bed Type <span class="text-danger">*</span></label>
        <select id="bed_type_id" name="bed_type_id" class="form-select" required>
            <option value="" disabled {{ old('bed_type_id', $data->bed_type_id ?? '') ? '' : 'selected' }}>Select Bed Type</option>
            @foreach ($bedTypes as $f)
                <option value="{{ $f->id }}" data-rent="{{ $f->base_rent ?? '' }}"
                    {{ old('bed_type_id', $data->bed_type_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }} @if($f->base_rent ?? 0)— ৳{{ number_format($f->base_rent, 0) }}/day @endif
                </option>
            @endforeach
        </select>
        @error('bed_type_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Bed Group / Ward <span class="text-danger">*</span></label>
        <select id="bed_group_id" name="bed_group_id" class="form-select" required>
            <option value="" disabled {{ old('bed_group_id', $data->bed_group_id ?? '') ? '' : 'selected' }}>Select Ward</option>
            @foreach ($bedGroups as $f)
                <option value="{{ $f->id }}" {{ old('bed_group_id', $data->bed_group_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->name }}
                </option>
            @endforeach
        </select>
        @error('bed_group_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Room <small class="text-muted">(optional — leave blank if bed is direct in ward)</small></label>
        <select name="room_id" class="form-select">
            <option value="">— none —</option>
            @foreach ($rooms as $r)
                <option value="{{ $r->id }}"
                        data-room-rent="{{ $r->room_rent }}"
                        data-bed-group="{{ $r->bed_group_id }}"
                        {{ old('room_id', $data->room_id ?? '') == $r->id ? 'selected' : '' }}>
                    {{ $r->room_no }} · {{ \App\Models\Room::CLASSES[$r->room_class] ?? '—' }}
                    · ৳ {{ number_format($r->room_rent, 0) }}/day · {{ optional($r->bedGroup)->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Bed Rent (৳/day) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" id="bed_rent" name="rent" value="{{ old('rent', $data->rent ?? 0) }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Amenity Charge (৳/day)</label>
        <input type="number" step="0.01" name="amenity_charge" value="{{ old('amenity_charge', $data->amenity_charge ?? 0) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Nursing Charge (৳/day)</label>
        <input type="number" step="0.01" name="nursing_charge" value="{{ old('nursing_charge', $data->nursing_charge ?? 0) }}" class="form-control">
    </div>

    <div class="col-md-8">
        <label class="form-label">Default Package <small class="text-muted">(auto-suggested in IPD admission)</small></label>
        <select name="default_package_id" class="form-select">
            <option value="">— none —</option>
            @foreach ($packages as $p)
                <option value="{{ $p->id }}" @selected(old('default_package_id', $data->default_package_id ?? '') == $p->id)>
                    [{{ $p->package_type }}] {{ $p->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Available?</label>
        <select id="bed_is_reserved" name="is_reserved" class="form-select">
            <option value="0" @selected(old('is_reserved', $data->is_reserved ?? 0) == 0)>Yes (open)</option>
            <option value="1" @selected(old('is_reserved', $data->is_reserved ?? 0) == 1)>No (reserved)</option>
        </select>
    </div>
</div>
