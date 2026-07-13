@extends('backend.layouts.master')

@section('title', $room->exists ? 'Edit Room' : 'New Room')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-3">
            @include('backend.layouts.bed_setup')
        </div>
        <div class="col-lg-9 col-md-8">
            <h4 class="mb-3">
                <i class="bi bi-door-closed text-success"></i>
                {{ $room->exists ? 'Edit Room — ' . $room->room_no : 'New Room' }}
            </h4>

            <form method="POST" action="{{ $room->exists ? route('rooms.update', $room) : route('rooms.store') }}">
                @csrf
                @if ($room->exists) @method('PUT') @endif

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light"><strong>📍 Location</strong></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Floor</label>
                            <select name="floor_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($floors as $f)
                                    <option value="{{ $f->id }}" @selected(old('floor_id', $room->floor_id) == $f->id)>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ward / Bed Group <span class="text-danger">*</span></label>
                            <select name="bed_group_id" required class="form-select">
                                <option value="">— select —</option>
                                @foreach ($bedGroups as $g)
                                    <option value="{{ $g->id }}" @selected(old('bed_group_id', $room->bed_group_id) == $g->id)>
                                        {{ $g->name }} @if($g->code)({{ $g->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Room No <span class="text-danger">*</span></label>
                            <input type="text" name="room_no" value="{{ old('room_no', $room->room_no) }}" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Name (optional)</label>
                            <input type="text" name="name" value="{{ old('name', $room->name) }}" class="form-control" placeholder="e.g. Sunshine Suite">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light"><strong>🏷️ Class · Capacity · Rent</strong></div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Room Class <span class="text-danger">*</span></label>
                            <select name="room_class" required class="form-select">
                                @foreach (\App\Models\Room::CLASSES as $code => $label)
                                    <option value="{{ $code }}" @selected(old('room_class', $room->room_class ?? 'general') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Capacity (Beds) <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" value="{{ old('capacity', $room->capacity ?? 1) }}" class="form-control" min="1" max="20" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Daily Room Rent (৳)<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="room_rent" value="{{ old('room_rent', $room->room_rent ?? 0) }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSw" {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActiveSw">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light"><strong>🛋️ Amenities &amp; Equipment</strong></div>
                    <div class="card-body row g-2">
                        @php
                            $amenities = [
                                ['has_ac', '❄️ AC'],
                                ['has_attached_bath', '🚿 Attached Bath'],
                                ['has_tv', '📺 TV'],
                                ['has_fridge', '🧊 Fridge'],
                                ['has_sofa_cum_bed', '🛋️ Sofa-cum-Bed (Attendant)'],
                                ['has_oxygen_outlet', '🫁 Oxygen Outlet'],
                                ['has_central_monitor', '📡 Central Monitor'],
                            ];
                        @endphp
                        @foreach ($amenities as [$field, $label])
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" {{ old($field, $room->$field ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light"><strong>📝 Notes</strong></div>
                    <div class="card-body">
                        <textarea name="description" rows="2" class="form-control" placeholder="Any extra remarks…">{{ old('description', $room->description) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-success"><i class="bi bi-save"></i> Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
