@extends('backend.layouts.master')

@section('title', 'New Surgery Request')

@section('content')
<div class="container-fluid">
    <div class="app-page-head d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">New Surgery Request</h1>
        <a href="{{ route('ot.surgery-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('ot.surgery-requests.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                @include('ot.surgery-requests._form')
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" name="save_as" value="draft" class="btn btn-secondary">Save Draft</button>
                <button type="submit" name="save_as" value="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </div>
    </form>
</div>
@endsection
