@extends('backend.layouts.master')

@section('title', 'Edit Paramedic')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Edit Paramedic #{{ $paramedic->id }}</h1>
        <a href="{{ route('amb.paramedics.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <form action="{{ route('amb.paramedics.update', $paramedic) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $paramedic->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="nid">National ID</label>
            <input type="text" class="form-control @error('nid') is-invalid @enderror" id="nid" name="nid" value="{{ old('nid', $paramedic->nid) }}" required>
            @error('nid')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $paramedic->phone) }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="certification">Certification</label>
            <select class="form-control @error('certification') is-invalid @enderror" id="certification" name="certification" required>
                <option value="BLS" @selected(old('certification', $paramedic->certification) == 'BLS')>Basic Life Support (BLS)</option>
                <option value="ACLS" @selected(old('certification', $paramedic->certification) == 'ACLS')>Advanced Cardiac Life Support (ACLS)</option>
            </select>
            @error('certification')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="cert_expiry">Certification Expiry</label>
            <input type="date" class="form-control @error('cert_expiry') is-invalid @enderror" id="cert_expiry" name="cert_expiry" value="{{ old('cert_expiry', $paramedic->cert_expiry) }}">
            @error('cert_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="ACTIVE" @selected(old('status', $paramedic->status) == 'ACTIVE')>Active</option>
                <option value="SUSPENDED" @selected(old('status', $paramedic->status) == 'SUSPENDED')>Suspended</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">Update Paramedic</button>
    </form>
</div>
@endsection
