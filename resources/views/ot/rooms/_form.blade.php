@php($r = $room ?? null)
<div class="row g-3">
    <div class="col-md-3"><label class="form-label">Code *</label><input name="code" class="form-control" required value="{{ old('code', $r->code ?? '') }}"></div>
    <div class="col-md-5"><label class="form-label">Name *</label><input name="name" class="form-control" required value="{{ old('name', $r->name ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Type</label>
        <select name="type" class="form-select">
            <option value="">—</option>
            @foreach(['Major','Minor','Day Care','Emergency','Endoscopy','Cath Lab'] as $t)
                <option value="{{ $t }}" @selected(old('type', $r->type ?? '') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Floor</label>
        <select name="floor_id" class="form-select">
            <option value="">—</option>
            @foreach(($floors ?? \App\Models\Floor::orderBy('name')->get()) as $f)
                <option value="{{ $f->id }}" @selected(old('floor_id', $r->floor_id ?? '') == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Block</label><input name="block" class="form-control" value="{{ old('block', $r->block ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['available','occupied','cleaning','maintenance','reserved'] as $st)
                <option value="{{ $st }}" @selected(old('status', $r->status ?? 'available') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_emergency" value="0"><input class="form-check-input" type="checkbox" name="is_emergency" value="1" id="emRm" @checked(old('is_emergency', $r->is_emergency ?? false))><label class="form-check-label" for="emRm">Emergency OT</label></div>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="actRm" @checked(old('is_active', $r->is_active ?? true))><label class="form-check-label" for="actRm">Active</label></div>
    </div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description', $r->description ?? '') }}</textarea></div>
</div>
