@extends('backend.layouts.master')

@section('title', 'Ambulance Details')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <h1 class="app-page-title">Ambulance #{{ $ambulance->id }} - {{ $ambulance->reg_no }}</h1>
        <a href="{{ route('amb.ambulances.index') }}" class="btn btn-light">Back to List</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div><strong>Registration Number:</strong> {{ $ambulance->reg_no }}</div>
            <div><strong>Type:</strong> {{ $ambulance->type }}</div>
            <div><strong>Ownership:</strong> {{ $ambulance->ownership }}</div>
            <div><strong>Vendor:</strong> {{ $ambulance->vendor ? $ambulance->vendor->name : 'Not outsourced' }}</div>
            <div><strong>Stretcher Capacity:</strong> {{ $ambulance->stretcher_capacity }}</div>
            <div><strong>Attendant Capacity:</strong> {{ $ambulance->attendants_capacity }}</div>
            <div><strong>Oxygen Capacity:</strong> {{ $ambulance->oxygen_capacity ?? 'Not specified' }}</div>
            <div><strong>Status:</strong> {{ $ambulance->status }}</div>
            <div><strong>Fitness Expiry:</strong> {{ $ambulance->fitness_expiry ? $ambulance->fitness_expiry: 'Not available' }}</div>
            <div><strong>Insurance Expiry:</strong> {{ $ambulance->insurance_expiry ? $ambulance->insurance_expiry: 'Not available' }}</div>
            <div><strong>Created At:</strong> {{ $ambulance->created_at}}</div>
            <div><strong>Updated At:</strong> {{ $ambulance->updated_at}}</div>
        </div>
    </div>
</div>
@endsection
