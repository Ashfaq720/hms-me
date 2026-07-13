@extends('backend.layouts.master')
@section('title', 'Edit Package — ' . $package->name)
@section('content')
<div class="container-fluid py-3" style="padding-bottom:80px !important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-pencil-square text-warning"></i> Edit Package — {{ $package->name }}
                <span class="badge bg-light text-muted ms-1">{{ $package->code }}</span>
            </h4>
            <small class="text-muted">Change basic info, services or pricing across 4 tabs</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('packages.show', $package->id) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('packages.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="bi bi-exclamation-triangle"></i> Please fix:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('packages.update', $package->id) }}" id="packageForm">
        @csrf @method('PUT')
        @include('packages._form')
    </form>
</div>

{{-- Sticky action bar --}}
<div class="position-fixed bottom-0 start-0 end-0 bg-white shadow-lg border-top py-2 px-4 d-flex justify-content-end gap-2"
     style="z-index:1030;">
    <a href="{{ route('packages.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-x"></i> Cancel
    </a>
    <button type="submit" form="packageForm" class="btn btn-primary btn-sm px-4">
        <i class="bi bi-check2"></i> Update Package
    </button>
</div>
@endsection
