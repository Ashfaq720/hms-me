@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Service</h3>

    <form method="POST" action="{{ route('services.update', $service) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('services._form', ['service' => $service])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-warning">Update</button>
            <a href="{{ route('services.show', $service) }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
