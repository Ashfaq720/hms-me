@extends('backend.layouts.master')
@section('title', $organization->exists ? 'Edit Organization' : 'Add Organization')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $organization->exists ? 'Edit Organization' : 'Add Organization' }}</h1>
    <form method="POST" action="{{ $organization->exists ? route('organizations.update',$organization) : route('organizations.store') }}" class="card p-4 mt-3">
        @csrf @if($organization->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Code *</label><input name="code" class="form-control" value="{{ old('code', $organization->code) }}" required></div>
            <div class="col-md-8"><label class="form-label">Name *</label><input name="name" class="form-control" value="{{ old('name', $organization->name) }}" required></div>
            <div class="col-md-6"><label class="form-label">Legal name</label><input name="legal_name" class="form-control" value="{{ old('legal_name', $organization->legal_name) }}"></div>
            <div class="col-md-3"><label class="form-label">Country (2-letter)</label><input name="country" class="form-control" value="{{ old('country', $organization->country) }}"></div>
            <div class="col-md-3"><label class="form-label">Currency</label><input name="default_currency" class="form-control" value="{{ old('default_currency', $organization->default_currency ?? 'USD') }}"></div>
            <div class="col-md-4"><label class="form-label">Contact email</label><input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $organization->contact_email) }}"></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="contact_phone" class="form-control" value="{{ old('contact_phone', $organization->contact_phone) }}"></div>
            <div class="col-md-4"><label class="form-label">Tax #</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $organization->tax_number) }}"></div>
            <div class="col-md-4"><label class="form-label">Timezone</label><input name="timezone" class="form-control" value="{{ old('timezone', $organization->timezone ?? 'UTC') }}"></div>
            <div class="col-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" id="ia" class="form-check-input" @checked(old('is_active', $organization->is_active ?? true))><label class="form-check-label" for="ia">Active</label></div></div>
        </div>
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
