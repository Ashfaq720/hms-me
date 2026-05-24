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

        @php
            // Treat the setting as a file/image when the type column says so,
            // OR when the key looks like a logo / favicon / banner / image.
            $isFileType  = in_array($setting->type, ['file', 'image'], true)
                        || preg_match('/(logo|favicon|banner|image|photo|picture)/i', $setting->key);
            $isImageType = $setting->type === 'image'
                        || preg_match('/(logo|favicon|banner|image|photo|picture)/i', $setting->key);
            $currentPath = $setting->value;
            $currentUrl  = $currentPath
                ? (\Illuminate\Support\Str::startsWith($currentPath, ['http://', 'https://'])
                    ? $currentPath
                    : asset($currentPath))
                : null;
        @endphp

        <div class="card mt-4">
            <div class="card-body">
                <form action="{{ route('settings.update', $setting) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="group" value="{{ old('group', $setting->group) }}">

                    <div class="mb-3">
                        <label class="form-label">Key</label>
                        <input type="text" name="key" class="form-control"
                               value="{{ old('key', $setting->key) }}" required>
                        <small class="form-text text-muted">
                            Programmatic identifier. Avoid renaming if other code reads it.
                        </small>
                    </div>

                    {{-- ────────── Value field — branches on detected type ────────── --}}
                    @if($isFileType)
                        <div class="mb-3">
                            <label class="form-label d-block">
                                Current {{ $isImageType ? 'Image' : 'File' }}
                            </label>
                            @if($currentUrl)
                                @if($isImageType)
                                    <div class="mb-2 p-2 border rounded bg-light d-inline-block">
                                        <img src="{{ $currentUrl }}"
                                             alt="{{ $setting->key }}"
                                             style="max-height: 100px; max-width: 240px;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div style="display:none;" class="text-muted small">
                                            Preview unavailable — current value: <code>{{ $currentPath }}</code>
                                        </div>
                                    </div>
                                @endif
                                <div class="small text-muted">
                                    Current path: <code>{{ $currentPath }}</code>
                                </div>
                            @else
                                <div class="text-muted small">No file uploaded yet.</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Upload New {{ $isImageType ? 'Image' : 'File' }}
                            </label>
                            <input type="file" name="upload_file" class="form-control"
                                   accept="{{ $isImageType ? 'image/*,.ico' : '*' }}">
                            <small class="form-text text-muted">
                                Leave empty to keep the current file.
                                @if($isImageType) Recommended: PNG / JPG / SVG / ICO, under 2 MB. @endif
                            </small>
                        </div>

                        {{-- Preserve the current value when no new file is uploaded --}}
                        <input type="hidden" name="value" value="{{ old('value', $setting->value) }}">
                    @else
                        <div class="mb-3">
                            <label class="form-label">Value</label>
                            @if($setting->type === 'boolean')
                                <select name="value" class="form-select">
                                    <option value="1" @selected($setting->value == '1' || $setting->value === true)>True</option>
                                    <option value="0" @selected($setting->value == '0' || $setting->value === false)>False</option>
                                </select>
                            @elseif(in_array($setting->type, ['json', 'array'], true))
                                <textarea name="value" rows="6" class="form-control font-monospace" required>{{ old('value', $setting->value) }}</textarea>
                                <small class="form-text text-muted">Valid JSON.</small>
                            @elseif(in_array($setting->type, ['integer', 'float'], true))
                                <input type="number"
                                       step="{{ $setting->type === 'float' ? 'any' : '1' }}"
                                       name="value" class="form-control"
                                       value="{{ old('value', $setting->value) }}" required>
                            @else
                                <input type="text" name="value" class="form-control"
                                       value="{{ old('value', $setting->value) }}" required>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select select2" data-placeholder="--Select Type--" required>
                            <option value="string"  {{ $setting->type === 'string' ? 'selected' : '' }}>String</option>
                            <option value="integer" {{ $setting->type === 'integer' ? 'selected' : '' }}>Integer</option>
                            <option value="float"   {{ $setting->type === 'float' ? 'selected' : '' }}>Float</option>
                            <option value="boolean" {{ $setting->type === 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="json"    {{ $setting->type === 'json' ? 'selected' : '' }}>JSON</option>
                            <option value="file"    {{ $setting->type === 'file' ? 'selected' : '' }}>File</option>
                            <option value="image"   {{ $setting->type === 'image' ? 'selected' : '' }}>Image</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $setting->description) }}</textarea>
                    </div>

                    <div class="d-flex gap-3 justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit">
                            <i class="fi fi-rr-save me-1"></i> Update Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
