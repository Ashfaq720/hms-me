@extends('backend.layouts.master')
@section('title','Edit Package — ' . $package->code)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0">Edit Package — {{ $package->code }}</h1>
            <small class="text-muted">{{ $package->name }}</small>
        </div>
        <a href="{{ route('setup.service-packages.show', $package) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('setup.service-packages.update', $package) }}">
        @csrf @method('PUT')
        @include('setup.service-packages._form')
    </form>
</div>
@endsection
