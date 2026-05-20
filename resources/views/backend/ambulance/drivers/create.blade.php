@extends('backend.layouts.master')

@section('title', 'Add Driver')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Add Driver</h1>
        <a href="{{ route('amb.drivers.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <form action="{{ route('amb.drivers.store') }}" method="POST" class="mt-4">
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
            <label for="license_number">License Number</label>
            <input type="text" class="form-control @error('license_number') is-invalid @enderror" id="license_number" name="license_number" value="{{ old('license_number') }}">
            @error('license_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="license_expiry">License Expiry</label>
            <input type="date" class="form-control @error('license_expiry') is-invalid @enderror" id="license_expiry" name="license_expiry" value="{{ old('license_expiry') }}">
            @error('license_expiry')
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

        <button type="submit" class="btn btn-primary mt-4">Save Driver</button>
    </form>
</div>
@endsection
