@extends('backend.layouts.master')
@section('title', $warehouse->exists ? 'Edit Warehouse' : 'Add Warehouse')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $warehouse->exists ? 'Edit Warehouse' : 'Add Warehouse' }}</h1>
    <form method="POST" action="{{ $warehouse->exists ? route('inventory.warehouses.update', $warehouse) : route('inventory.warehouses.store') }}" class="card p-4 mt-3">
        @csrf @if($warehouse->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Code *</label><input name="code" class="form-control" value="{{ old('code', $warehouse->code) }}" required></div>
            <div class="col-md-8"><label class="form-label">Name *</label><input name="name" class="form-control" value="{{ old('name', $warehouse->name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Type *</label>
                <select name="type" class="form-select" required>
                    @foreach (['main','sub_store','pharmacy','ot','icu','lab','blood_bank'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $warehouse->type ?? 'main') === $t)>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location', $warehouse->location) }}"></div>
            <div class="col-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" id="ia" class="form-check-input" @checked(old('is_active', $warehouse->is_active ?? true))><label class="form-check-label" for="ia">Active</label></div></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
