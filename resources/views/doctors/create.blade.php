@extends('backend.layouts.master')
@section('title', 'Create Doctor')
@section('content')
    <div class="container">
        <h3 class="mb-3">Create Doctor</h3>

        <form method="POST" action="{{ route('doctors.store') }}" enctype="multipart/form-data">
            @csrf
            @include('doctors._form', ['doctor' => null])
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('doctors.index') }}" class="btn btn-light">Back</a>
            </div>
        </form>
    </div>
@endsection
