@extends('backend.layouts.master')

@section('title', 'Edit Setting')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Edit Setting - {{ $setting->key }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('settings.index') }}" class="btn btn-light btn-outline-enhanced">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Settings
                </a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <form action="{{ route('settings.update', $setting) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="group" value="{{ old('group', $setting->group) }}">
                    <div class="mb-3">
                        <label class="form-label">Key</label>
                        <input type="text" name="key" class="form-control" value="{{ old('key', $setting->key) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="text" name="value" class="form-control" value="{{ old('value', $setting->value) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select select2" data-placeholder="--Select Type--" required>
                            <option value="string" {{ $setting->type === 'string' ? 'selected' : '' }}>String</option>
                            <option value="integer" {{ $setting->type === 'integer' ? 'selected' : '' }}>Integer</option>
                            <option value="float" {{ $setting->type === 'float' ? 'selected' : '' }}>Float</option>
                            <option value="boolean" {{ $setting->type === 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="json" {{ $setting->type === 'json' ? 'selected' : '' }}>JSON</option>
                            <option value="file" {{ $setting->type === 'file' ? 'selected' : '' }}>File</option>
                            <option value="image" {{ $setting->type === 'image' ? 'selected' : '' }}>Image</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $setting->description) }}</textarea>
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
