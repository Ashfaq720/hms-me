@php($e = $equipment ?? null)
<div class="row g-3">
    <div class="col-md-3"><label class="form-label">Code *</label><input name="code" class="form-control" required value="{{ old('code', $e->code ?? '') }}"></div>
    <div class="col-md-5"><label class="form-label">Name *</label><input name="name" class="form-control" required value="{{ old('name', $e->name ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Category</label><input name="category" class="form-control" value="{{ old('category', $e->category ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Room</label>
        <select name="ot_room_id" class="form-select">
            <option value="">—</option>
            @foreach($rooms as $r)<option value="{{ $r->id }}" @selected(old('ot_room_id', $e->ot_room_id ?? '') == $r->id)>{{ $r->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Serial No</label><input name="serial_no" class="form-control" value="{{ old('serial_no', $e->serial_no ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['available','in_use','maintenance','retired'] as $s)<option value="{{ $s }}" @selected(old('status', $e->status ?? 'available') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Last Service</label><input type="date" name="last_service_date" class="form-control" value="{{ old('last_service_date', optional($e?->last_service_date)->format('Y-m-d')) }}"></div>
    <div class="col-md-3"><label class="form-label">Next Service</label><input type="date" name="next_service_date" class="form-control" value="{{ old('next_service_date', optional($e?->next_service_date)->format('Y-m-d')) }}"></div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="actEq" @checked(old('is_active', $e->is_active ?? true))><label class="form-check-label" for="actEq">Active</label></div>
    </div>
</div>
