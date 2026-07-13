@extends('backend.layouts.master')

@section('title', 'Add Service')

@section('content')
    <div class="container">
        <h1 class="app-page-title">Add Service Catalog Entry</h1>

        <form action="{{ route('service-charge.catalog.store') }}" method="POST" class="card p-4 mt-3">
            @csrf
            @include('service-charge.catalog._form')
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <a href="{{ route('service-charge.catalog.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
