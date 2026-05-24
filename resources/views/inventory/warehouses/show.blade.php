@extends('backend.layouts.master')
@section('title', $warehouse->name)
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $warehouse->name }}</h1>
    <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-outline-secondary mb-3">Back</a>
    <div class="card"><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-3">Code</dt><dd class="col-9"><code>{{ $warehouse->code }}</code></dd>
            <dt class="col-3">Type</dt><dd class="col-9">{{ $warehouse->type }}</dd>
            <dt class="col-3">Location</dt><dd class="col-9">{{ $warehouse->location }}</dd>
        </dl>
    </div></div>
</div>
@endsection
