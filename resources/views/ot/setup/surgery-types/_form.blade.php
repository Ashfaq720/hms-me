@php($i = $item ?? null)
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="{{ old('name', $i->name ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $i->code ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">—</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $i->category_id ?? '') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Duration (min) *</label><input type="number" name="standard_duration_minutes" class="form-control" required min="5" value="{{ old('standard_duration_minutes', $i->standard_duration_minutes ?? 60) }}"></div>
    <div class="col-md-3"><label class="form-label">Standard Charge</label><input type="number" step="0.01" name="standard_charge" class="form-control" value="{{ old('standard_charge', $i->standard_charge ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">Surgeon Fee</label><input type="number" step="0.01" name="surgeon_charge" class="form-control" value="{{ old('surgeon_charge', $i->surgeon_charge ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">Anesthesia Fee</label><input type="number" step="0.01" name="anesthesia_charge" class="form-control" value="{{ old('anesthesia_charge', $i->anesthesia_charge ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">OT Room Charge</label><input type="number" step="0.01" name="ot_room_charge" class="form-control" value="{{ old('ot_room_charge', $i->ot_room_charge ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">Recovery Charge</label><input type="number" step="0.01" name="recovery_charge" class="form-control" value="{{ old('recovery_charge', $i->recovery_charge ?? 0) }}"></div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="actSt" @checked(old('is_active', $i->is_active ?? true))><label class="form-check-label" for="actSt">Active</label></div>
    </div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $i->notes ?? '') }}</textarea></div>
</div>
