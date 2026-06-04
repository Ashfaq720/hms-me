@php($i = $item ?? null)
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required value="{{ old('name', $i->name ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Code</label><input name="code" class="form-control" value="{{ old('code', $i->code ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Type *</label>
        <select name="type" class="form-select" required>
            @foreach(['consumable','implant','instrument','medicine'] as $t)
                <option value="{{ $t }}" @selected(old('type', $i->type ?? 'consumable') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Unit</label><input name="unit" class="form-control" value="{{ old('unit', $i->unit ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Rate</label><input type="number" step="0.01" name="rate" class="form-control" value="{{ old('rate', $i->rate ?? 0) }}"></div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_implant" value="0"><input class="form-check-input" type="checkbox" name="is_implant" value="1" id="impl" @checked(old('is_implant', $i->is_implant ?? false))><label class="form-check-label" for="impl">Implant</label></div>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="actCn" @checked(old('is_active', $i->is_active ?? true))><label class="form-check-label" for="actCn">Active</label></div>
    </div>
</div>
