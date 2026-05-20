@extends('backend.layouts.master')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Package</h3>

    <form method="POST" action="{{ route('packages.update', $package->id) }}" id="packageForm">
        @csrf
        @method('PUT')

        @include('packages._form', [
            'package'  => $package,
            'services' => $services
        ])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('packages.index') }}" class="btn btn-light">Back</a>
        </div>
    </form>
</div>
@endsection
