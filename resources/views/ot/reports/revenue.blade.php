@extends('backend.layouts.master')
@section('title','OT Revenue')
@section('content')
<div class="container-fluid">
    <h1 class="app-page-title mb-3">Revenue Summary</h1>
    @include('ot.reports._filter')
    <div class="row g-3">
        <div class="col-md-6"><div class="card"><div class="card-body">
            <h6 class="text-muted">Surgeries Performed</h6>
            <h2>{{ $surgeryCount }}</h2>
        </div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body">
            <h6 class="text-muted">Consumables Billed</h6>
            <h2>{{ number_format($usages, 2) }}</h2>
        </div></div></div>
    </div>
    <p class="text-muted small mt-3">Note: this is consumables-billed only. Surgeon/anesthesia/room charges flow through PatientCharge once posted.</p>
</div>
@endsection
