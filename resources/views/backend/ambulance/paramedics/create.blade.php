@extends('backend.layouts.master')

@section('title', 'Add Paramedic')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Add Paramedic</h1>
        <a href="{{ route('amb.paramedics.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <form action="{{ route('amb.paramedics.store') }}" method="POST" class="mt-4">
        @csrf

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="nid">National ID</label>
            <input type="text" class="form-control @error('nid') is-invalid @enderror" id="nid" name="nid" value="{{ old('nid') }}" required>
            @error('nid')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="certification">Certification</label>
            <select class="form-control @error('certification') is-invalid @enderror" id="certification" name="certification" required>
                <option value="BLS" @selected(old('certification') == 'BLS')>Basic Life Support (BLS)</option>
                <option value="ACLS" @selected(old('certification') == 'ACLS')>Advanced Cardiac Life Support (ACLS)</option>
            </select>
            @error('certification')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="cert_expiry">Certification Expiry</label>
            <input type="date" class="form-control @error('cert_expiry') is-invalid @enderror" id="cert_expiry" name="cert_expiry" value="{{ old('cert_expiry') }}">
            @error('cert_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="ACTIVE" @selected(old('status') == 'ACTIVE')>Active</option>
                <option value="SUSPENDED" @selected(old('status') == 'SUSPENDED')>Suspended</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Paramedic</button>
    </form>
</div>
@endsection
