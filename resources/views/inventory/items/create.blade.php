@extends('backend.layouts.master')
@php
    $preset = $preset ?? null;
    $titleMap = [
        'consumable' => 'Add Consumable',
        'asset'      => 'Add Equipment / Asset',
    ];
    $title = $item->exists
        ? 'Edit Item: ' . $item->name
        : ($titleMap[$preset] ?? 'Add Inventory Item');
@endphp
@section('title', $title)
@section('content')
<div class="container">
    <h1 class="app-page-title">{{ $title }}</h1>

    @if (! $item->exists && $preset === 'consumable')
        <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Creating a <strong>Consumable</strong>: the "Consumable" flag is pre-checked.
            Pharmacy medicines should be added via <a href="{{ route('admin.pharmacy.drug-master') }}">Drug Master</a> instead.
        </div>
    @elseif (! $item->exists && $preset === 'asset')
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-tools me-1"></i>
            Creating an <strong>Equipment / Asset</strong>: the "Asset" flag is pre-checked.
            Stock isn't batched for assets — each unit is tracked individually.
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $item->exists ? route('inventory.items.update', $item) : route('inventory.items.store') }}"
          class="card p-4 mt-3">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        {{-- Preserve preset across validation failures --}}
        @if ($preset)
            <input type="hidden" name="preset" value="{{ $preset }}">
        @endif

        @include('inventory.items._form')

        <div class="mt-4 d-flex gap-2 justify-content-end">
            <a href="{{ route('inventory.items.index', $preset ? ['type' => $preset === 'consumable' ? 'consumable' : 'asset'] : []) }}"
               class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Save
            </button>
        </div>
    </form>
</div>
@endsection
