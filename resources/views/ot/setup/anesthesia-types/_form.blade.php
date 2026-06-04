@php($i = $item ?? null)
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="{{ old('name', $i->name ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $i->code ?? '') }}"></div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="actAt" @checked(old('is_active', $i->is_active ?? true))><label class="form-check-label" for="actAt">Active</label></div>
    </div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $i->description ?? '') }}</textarea></div>
</div>
