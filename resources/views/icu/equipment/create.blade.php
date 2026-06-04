@extends('backend.layouts.master')

@php
    $unitLabel = ($icuType ?? null) ?: 'ICU';
    $unitQuery = ($icuType ?? null) ? ['icu_type' => $icuType] : [];
@endphp

@section('title', 'Add ' . $unitLabel . ' Equipment')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <h1 class="app-page-title">Add {{ $unitLabel }} Equipment</h1>
            <a href="{{ route('icu.equipment.index', $unitQuery) }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        <form method="POST" action="{{ route('icu.equipment.store') }}" class="mt-2">
            @csrf
            @if ($icuType ?? null)
                <input type="hidden" name="icu_type" value="{{ $icuType }}">
            @endif
            @include('icu.equipment._form')
            <div class="text-end mt-3">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection
