@extends('backend.layouts.master')

@section('title', 'Edit Settings - ' . ucfirst($group))

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Edit Settings - {{ ucfirst($group) }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ ucfirst($group) }}</li>
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
            <div class="card-header">
                <h5 class="card-title mb-0">{{ ucfirst($group) }} Settings</h5>
            </div>
            <div class="card-body">
                @if($settings->isEmpty())
                    <p class="text-muted">No settings found for this group.</p>
                @else
                    <form action="{{ route('settings.group.update', ['group' => $group]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="{{ $group }}">

                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Key</th>
                                    <th style="width: 50%;">Value</th>
                                    <th style="width: 15%;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settings as $setting)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <code>{{ $setting->key }}</code>
                                            @if($setting->description)
                                                <br><small class="text-muted">{{ $setting->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="hidden" name="settings[{{ $setting->key }}][type]" value="{{ $setting->type }}">
                                            @if($setting->type === 'boolean')
                                                <select name="settings[{{ $setting->key }}][value]" class="form-select form-select-sm">
                                                    <option value="1" {{ $setting->value ? 'selected' : '' }}>True</option>
                                                    <option value="0" {{ !$setting->value ? 'selected' : '' }}>False</option>
                                                </select>
                                            @elseif($setting->type === 'text')
                                                <textarea name="settings[{{ $setting->key }}][value]" class="form-control form-control-sm" rows="3">{{ old("settings.{$setting->key}.value", $setting->value) }}</textarea>
                                            @elseif($setting->type === 'json' || $setting->type === 'array')
                                                <textarea name="settings[{{ $setting->key }}][value]" class="form-control form-control-sm" rows="3">{{ is_array($setting->value) || is_object($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                            @else
                                                <input type="text" name="settings[{{ $setting->key }}][value]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("settings.{$setting->key}.value", is_array($setting->value) ? json_encode($setting->value) : $setting->value) }}">
                                            @endif
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $setting->type }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fi fi-rr-disk me-1"></i> Update All
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
