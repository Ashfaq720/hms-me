@extends('backend.layouts.master')
@section('title', 'Package Reports')
@section('content')
<div class="container-fluid py-3">
    <h4 class="mb-3"><i class="bi bi-file-earmark-bar-graph"></i> Package Reports</h4>

    <div class="row g-2 mb-4">
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3"><small class="text-primary">Total Packages</small><h4 class="mb-0">{{ $stats['total_packages'] }}</h4></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3"><small class="text-success">Active</small><h4 class="mb-0">{{ $stats['active_packages'] }}</h4></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-info bg-opacity-10 p-3"><small class="text-info">Enrolments</small><h4 class="mb-0">{{ $stats['total_enrolments'] }}</h4></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3"><small class="text-warning">Active Now</small><h4 class="mb-0">{{ $stats['active_enrolments'] }}</h4></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3"><small class="text-success">Revenue ৳</small><h6 class="mb-0">{{ number_format($stats['total_revenue'], 0) }}</h6></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm bg-danger bg-opacity-10 p-3"><small class="text-danger">Outstanding ৳</small><h6 class="mb-0">{{ number_format($stats['outstanding'], 0) }}</h6></div></div>
    </div>

    <div class="row g-3">
        @php $reports = [
            ['department',  'Department-wise Report', 'bi-diagram-3',         'Which department uses packages most'],
            ['utilization', 'Utilization Report',     'bi-graph-up',          'Allowed vs consumed vs extras per service'],
            ['revenue',     'Revenue Report',         'bi-cash-stack',        'Revenue + outstanding per package'],
            ['expiry',      'Expiry Alerts',          'bi-exclamation-triangle','Enrolments expiring in 7 days'],
        ]; @endphp
        @foreach ($reports as [$route, $title, $icon, $desc])
            <div class="col-md-3">
                <a href="{{ route('packages.reports.' . $route) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="{{ $icon }} display-6 text-primary"></i>
                            <h6 class="mt-2 mb-1">{{ $title }}</h6>
                            <small class="text-muted">{{ $desc }}</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
