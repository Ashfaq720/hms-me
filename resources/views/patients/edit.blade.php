@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Patient</h3>

    <form method="POST" action="{{ route('patients.update', $patient) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('patients._form', ['patient' => $patient])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-warning">Update</button>
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
