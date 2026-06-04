@extends('backend.layouts.master')
@section('title','New Service Package')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">New Service Package</h1>
        <a href="{{ route('setup.service-packages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('setup.service-packages.store') }}">
        @csrf
        @include('setup.service-packages._form')
    </form>
</div>
@endsection
