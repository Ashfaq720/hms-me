@extends('backend.layouts.master')

@section('title', 'Driver Details')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Driver #{{ $driver->id }} - {{ $driver->name }}</h1>
        <a href="{{ route('amb.drivers.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div><strong>Name:</strong> {{ $driver->name }}</div>
            <div><strong>National ID:</strong> {{ $driver->nid }}</div>
            <div><strong>Phone Number:</strong> {{ $driver->phone ?? 'Not provided' }}</div>
            <div><strong>License Number:</strong> {{ $driver->license_number ?? 'Not provided' }}</div>
            <div><strong>License Type:</strong> {{ $driver->license_type ?? 'Not provided' }}</div>
            <div><strong>License Expiry:</strong> {{ $driver->license_expiry ? $driver->license_expiry : 'Not available' }}</div>
            <div><strong>Status:</strong> {{ $driver->status }}</div>
            <div><strong>Created At:</strong> {{ $driver->created_at->format('Y-m-d') }}</div>
            <div><strong>Updated At:</strong> {{ $driver->updated_at->format('Y-m-d') }}</div>
        </div>
    </div>
</div>
@endsection
