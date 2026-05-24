@extends('backend.layouts.master')
@section('title', $item->exists ? 'Edit Item' : 'Add Item')
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $item->exists ? 'Edit Item: '.$item->name : 'Add Inventory Item' }}</h1>
    <form method="POST" action="{{ $item->exists ? route('inventory.items.update', $item) : route('inventory.items.store') }}" class="card p-4 mt-3">
        @csrf
        @if ($item->exists) @method('PUT') @endif
        @include('inventory.items._form')
        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('inventory.items.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
