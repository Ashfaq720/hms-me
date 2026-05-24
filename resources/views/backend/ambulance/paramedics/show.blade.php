@extends('backend.layouts.master')

@section('title', 'Paramedic Details')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Paramedic #{{ $paramedic->id }} - {{ $paramedic->name }}</h1>
        <a href="{{ route('amb.paramedics.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div><strong>Name:</strong> {{ $paramedic->name }}</div>
            <div><strong>National ID:</strong> {{ $paramedic->nid }}</div>
            <div><strong>Phone Number:</strong> {{ $paramedic->phone ?? 'Not provided' }}</div>
            <div><strong>Certification:</strong> {{ $paramedic->certification }}</div>
            <div><strong>Certification Expiry:</strong> {{ $paramedic->cert_expiry ? $paramedic->cert_expiry : 'Not available' }}</div>
            <div><strong>Status:</strong> {{ $paramedic->status }}</div>
            <div><strong>Created At:</strong> {{ $paramedic->created_at->format('Y-m-d') }}</div>
            <div><strong>Updated At:</strong> {{ $paramedic->updated_at->format('Y-m-d') }}</div>
        </div>
    </div>
</div>
@endsection
