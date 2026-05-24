<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" id="room_name" name="name" value="{{ old('name', $room->name ?? '') }}"
            class="form-control" required>

        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Floor <span class="text-danger">*</span></label>
        <select id="room_floor_id" name="floor_id" class="form-select" required>
            <option value="">-- Select Floor --</option>
            @foreach ($floors as $f)
                <option value="{{ $f->id }}"
                    {{ (string) old('floor_id', $room->floor_id ?? '') === (string) $f->id ? 'selected' : '' }}>
                    {{ $f->name }}
                </option>
            @endforeach
        </select>

        @error('floor_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="room_is_active" name="is_active" value="1"
                {{ old('is_active', $room->is_active ?? 1) ? 'checked' : '' }}>
            <label class="form-check-label" for="room_is_active">Active</label>
        </div>
    </div>
</div>
