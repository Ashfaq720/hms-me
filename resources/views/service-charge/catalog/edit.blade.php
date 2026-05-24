@extends('backend.layouts.master')

@section('title', 'Edit Service')

@section('content')
    <div class="container">
        <h1 class="app-page-title">Edit Service: {{ $catalog->name }}</h1>

        <form action="{{ route('service-charge.catalog.update', $catalog) }}" method="POST" class="card p-4 mt-3">
            @csrf
            @method('PUT')
            @include('service-charge.catalog._form')
            <div class="mt-4 d-flex gap-2 justify-content-end">
                <a href="{{ route('service-charge.catalog.show', $catalog) }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection
