@extends('backend.layouts.master')
@section('title','Room Status')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">OT Room Status</h1>
    <div class="row g-3">
        @foreach($rooms as $room)
            @php $color = match($room->status){'available'=>'success','cleaning'=>'warning','occupied'=>'danger',default=>'secondary'}; @endphp
            <div class="col-md-3 col-sm-6">
                <div class="card border-{{ $color }}">
                    <div class="card-body text-center">
                        <h5>{{ $room->name }}</h5>
                        <span class="badge bg-{{ $color }} text-uppercase">{{ $room->status }}</span>
                        <hr>
                        <div class="small">Today: <strong>{{ $room->today_count }}</strong> cases</div>
                        @if($room->running)<div class="text-danger small mt-1">Running: {{ optional($room->running->surgeryRequest?->patient)->patient_name }}</div>@endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
