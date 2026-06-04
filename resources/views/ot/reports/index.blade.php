@extends('backend.layouts.master')
@section('title','OT Reports')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Reports &amp; Analytics</h1>
    <div class="row g-3">
        @foreach([
            ['surgeries','Surgery Log','bi-clipboard-check','primary'],
            ['utilization','Room Utilization','bi-pie-chart','info'],
            ['cancellations','Cancellations','bi-x-circle','danger'],
            ['consumables','Consumables Usage','bi-box-seam','warning'],
            ['revenue','Revenue Summary','bi-cash-coin','success'],
            ['audit','Audit Trail','bi-shield-check','secondary'],
        ] as [$slug, $label, $icon, $color])
            <div class="col-md-4">
                <a href="{{ route('ot.reports.' . $slug) }}" class="card text-decoration-none text-dark h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi {{ $icon }} fs-1 text-{{ $color }}"></i>
                        <div><h5 class="mb-0">{{ $label }}</h5></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
