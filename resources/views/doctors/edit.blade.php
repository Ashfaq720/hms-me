@extends('backend.layouts.master')
@section('title', 'Edit Doctor')
@section('content')
    <div class="container">
        <h3 class="mb-3">Edit Doctor</h3>

        <form method="POST" action="{{ route('doctors.update', $doctor) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('doctors._form', ['doctor' => $doctor])
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-warning">Update</button>
                <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
@endsection
