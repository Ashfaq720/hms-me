@extends('backend.layouts.master')

@section('title', 'Create Setting')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Create Setting - {{ ucfirst($group) }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
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
                <form action="{{ route('settings.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="group" value="{{ $group }}">
                    <div class="mb-3">
                        <label class="form-label">Key</label>
                        <input type="text" name="key" class="form-control" value="{{ old('key') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="text" name="value" class="form-control" value="{{ old('value') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select select2" data-placeholder="--Select Type--" required>
                            <option value=""></option>
                            <option value="string">String</option>
                            <option value="integer">Integer</option>
                            <option value="float">Float</option>
                            <option value="boolean">Boolean</option>
                            <option value="json">JSON</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
