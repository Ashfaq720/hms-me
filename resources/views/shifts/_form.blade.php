<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Shift Name <span class="text-danger">*</span></label>
        <input type="text" id="shift_name" name="name" value="{{ old('name', $shift->name ?? '') }}"
            class="form-control" required>

        @error('name')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Time From</label>
        <input type="time" id="shift_time_from" name="time_from"
            value="{{ old('time_from', isset($shift->time_from) ? \Illuminate\Support\Str::substr($shift->time_from, 0, 5) : '') }}"
            class="form-control">

        @error('time_from')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Time To</label>
        <input type="time" id="shift_time_to" name="time_to"
            value="{{ old('time_to', isset($shift->time_to) ? \Illuminate\Support\Str::substr($shift->time_to, 0, 5) : '') }}"
            class="form-control">

        @error('time_to')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
