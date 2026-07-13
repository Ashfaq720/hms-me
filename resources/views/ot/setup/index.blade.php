@extends('backend.layouts.master')
@section('title','OT Setup')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Setup / Master Configuration</h1>
    <div class="row g-3">
        @foreach([
            ['rooms','OT Rooms','bi-door-closed','primary',$counts['rooms']],
            ['equipments','Equipment','bi-tools','info',$counts['equipments']],
            ['surgery-categories','Surgery Categories','bi-tags','secondary',$counts['surgery_categories']],
            ['anesthesia-types','Anesthesia Types','bi-droplet','warning',$counts['anesthesia_types']],
            ['surgery-types','Surgery Types','bi-clipboard-pulse','success',$counts['surgery_types']],
            ['consumables','Consumables / Implants','bi-box-seam','danger',$counts['consumables']],
        ] as [$slug, $label, $icon, $color, $count])
            <div class="col-md-4">
                <a href="{{ route('ot.setup.' . $slug . '.index') }}" class="card text-decoration-none text-dark h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi {{ $icon }} fs-1 text-{{ $color }}"></i>
                        <div>
                            <h5 class="mb-0">{{ $label }}</h5>
                            <small class="text-muted">{{ $count }} item(s)</small>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
