@extends('backend.layouts.master')
@section('title', 'Create Patient')
@section('content')
<div class="container">
    <h3 class="mb-3">Create Patient</h3>

    <form method="POST" action="{{ route('patients.store') }}" enctype="multipart/form-data">
        @csrf
        @include('patients._form', ['patient' => null])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('patients.index') }}" class="btn btn-light">Back</a>
        </div>
    </form>
</div>
@endsection
