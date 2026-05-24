@extends('backend.layouts.master')
@section('title','Package Categories')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="app-page-title mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Package Categories</h1>
            <small class="text-muted">All package types from the spec — click a card to drill into the master.</small>
        </div>
        <a href="{{ route('package-management.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    @php
        $typeIcons = [
            'IPD'             => 'bi-hospital',
            'OPD'             => 'bi-person-walking',
            'OT'              => 'bi-scissors',
            'ICU'             => 'bi-heart-pulse',
            'CCU'             => 'bi-heart',
            'Diagnostic'      => 'bi-clipboard-pulse',
            'Health Checkup'  => 'bi-shield-check',
            'Procedure'       => 'bi-bandaid',
        ];
        $typeColors = [
            'IPD' => 'primary', 'OPD' => 'info', 'OT' => 'warning',
            'ICU' => 'danger',  'CCU' => 'danger', 'Diagnostic' => 'success',
            'Health Checkup' => 'success', 'Procedure' => 'secondary',
        ];
    @endphp

    <div class="row g-3">
        @foreach($categories as $cat)
            @php
                $icon  = $typeIcons[$cat['type']]  ?? 'bi-box-seam';
                $color = $typeColors[$cat['type']] ?? 'secondary';
            @endphp
            <div class="col-md-6 col-lg-4 col-xl-3">
                <a href="{{ route('setup.service-packages.index', ['package_type' => $cat['type']]) }}"
                   class="text-decoration-none">
                    <div class="card h-100 border-start border-4 border-{{ $color }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                                </div>
                                <span class="badge bg-{{ $color }}">{{ $cat['type'] }}</span>
                            </div>
                            <h5 class="mb-1 text-dark">{{ $cat['type'] }} Packages</h5>
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><strong class="text-dark">{{ $cat['total'] }}</strong> total</span>
                                <span class="text-success">{{ $cat['active'] }} active</span>
                                @if($cat['inactive'] > 0)
                                    <span class="text-secondary">{{ $cat['inactive'] }} inactive</span>
                                @endif
                            </div>
                            @if($cat['total'] > 0)
                                <hr class="my-2">
                                <div class="small text-muted">
                                    <div>Avg price: <strong>৳{{ number_format((float) $cat['avg_price'], 0) }}</strong></div>
                                    <div>Range: ৳{{ number_format((float) $cat['min_price'], 0) }} — ৳{{ number_format((float) $cat['max_price'], 0) }}</div>
                                </div>
                            @else
                                <hr class="my-2">
                                <div class="small text-muted">No packages yet.
                                    @can('service_packages_create')
                                        <a href="{{ route('setup.service-packages.create') }}">Create one →</a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
