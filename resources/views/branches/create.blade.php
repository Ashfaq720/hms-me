@extends('backend.layouts.master')
@section('title', $branch->exists ? 'Edit Branch' : 'Add Branch')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $branch->exists ? 'Edit Branch' : 'Add Branch' }}</h1>
    <form method="POST" action="{{ $branch->exists ? route('branches.update',$branch) : route('branches.store') }}" class="card p-4 mt-3">
        @csrf @if($branch->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Organization *</label>
                <select name="organization_id" class="form-select" required>
                    @foreach ($organizations as $o)
                        <option value="{{ $o->id }}" @selected(old('organization_id', $branch->organization_id) == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Code *</label><input name="code" class="form-control" value="{{ old('code', $branch->code) }}" required></div>
            <div class="col-md-3"><label class="form-label">Type</label>
                <select name="type" class="form-select">
                    @foreach (['hospital','clinic','diagnostic','pharmacy'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $branch->type ?? 'hospital') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8"><label class="form-label">Name *</label><input name="name" class="form-control" value="{{ old('name', $branch->name) }}" required></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}"></div>
            <div class="col-md-6"><label class="form-label">Address</label><input name="address_line1" class="form-control" value="{{ old('address_line1', $branch->address_line1) }}"></div>
            <div class="col-md-3"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city', $branch->city) }}"></div>
            <div class="col-md-3"><label class="form-label">Country</label><input name="country" class="form-control" value="{{ old('country', $branch->country) }}"></div>
            <div class="col-md-3"><label class="form-label">MRN prefix</label><input name="mrn_prefix" class="form-control" value="{{ old('mrn_prefix', $branch->mrn_prefix ?? 'MRN') }}"></div>
            <div class="col-md-3"><label class="form-label">Invoice prefix</label><input name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $branch->invoice_prefix ?? 'INV') }}"></div>
            <div class="col-md-3"><label class="form-label">Health card prefix</label><input name="health_card_prefix" class="form-control" value="{{ old('health_card_prefix', $branch->health_card_prefix ?? 'HC') }}"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" id="ia" class="form-check-input" @checked(old('is_active', $branch->is_active ?? true))><label class="form-check-label" for="ia">Active</label></div></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
