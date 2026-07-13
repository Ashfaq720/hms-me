@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h3 class="mb-3">Create Service</h3>

    <form method="POST" action="{{ route('services.store') }}" enctype="multipart/form-data">
        @csrf
        @include('services._form', ['service' => null])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('services.index') }}" class="btn btn-light">Back</a>
        </div>
    </form>
</div>
@endsection
