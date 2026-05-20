@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h3 class="mb-3">Create Package</h3>

    <form method="POST" action="{{ route('packages.store') }}" id="packageForm">
        @csrf

        @include('packages._form', [
            'package'  => null,
            'services' => $services
        ])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('packages.index') }}" class="btn btn-light">Back</a>
        </div>
    </form>
</div>
@endsection
