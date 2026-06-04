@extends('backend.layouts.master')
@section('title', $payer->exists ? 'Edit Payer' : 'Add Payer')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $payer->exists ? 'Edit Payer' : 'Add Insurance Payer' }}</h1>
    <form method="POST" action="{{ $payer->exists ? route('insurance.payers.update',$payer) : route('insurance.payers.store') }}" class="card p-4 mt-3">
        @csrf @if($payer->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Code *</label><input name="code" class="form-control" value="{{ old('code',$payer->code) }}" required></div>
            <div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" value="{{ old('name',$payer->name) }}" required></div>
            <div class="col-md-3"><label class="form-label">Type *</label>
                <select name="type" class="form-select" required>
                    @foreach (['insurance','corporate','government','tpa','self'] as $t)
                        <option value="{{ $t }}" @selected(old('type',$payer->type ?? 'insurance') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Contact person</label><input name="contact_person" class="form-control" value="{{ old('contact_person',$payer->contact_person) }}"></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone',$payer->phone) }}"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$payer->email) }}"></div>
            <div class="col-md-3"><label class="form-label">Default disc %</label><input type="number" step="0.01" name="default_discount_percent" class="form-control" value="{{ old('default_discount_percent',$payer->default_discount_percent ?? 0) }}"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="pre_auth_required" value="0"><input type="checkbox" name="pre_auth_required" value="1" id="par" class="form-check-input" @checked(old('pre_auth_required',$payer->pre_auth_required ?? false))><label class="form-check-label" for="par">Pre-auth required</label></div></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" id="ia" class="form-check-input" @checked(old('is_active',$payer->is_active ?? true))><label class="form-check-label" for="ia">Active</label></div></div>
            <div class="col-12"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control">{{ old('address',$payer->address) }}</textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('insurance.payers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
