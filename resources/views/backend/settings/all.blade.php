@extends('backend.layouts.master')

@section('title', 'All Settings')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">All Settings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">All Settings</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $groupIcons = [
                'company'        => ['icon' => 'fi fi-rr-building', 'color' => 'primary'],
                'business_hours' => ['icon' => 'fi fi-rr-clock', 'color' => 'info'],
                'social'         => ['icon' => 'fi fi-rr-share', 'color' => 'success'],
                'system'         => ['icon' => 'fi fi-rr-settings-sliders', 'color' => 'warning'],
                'general'        => ['icon' => 'fi fi-rr-settings', 'color' => 'secondary'],
            ];
        @endphp

        {{-- All Settings in One Card --}}
        @isset($allSettings)
            @if ($allSettings->isNotEmpty())
                <div class="card" id="settingsCard">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                            @foreach($allSettings as $group => $settings)
                                @php
                                    $meta = $groupIcons[$group] ?? ['icon' => 'fi fi-rr-settings', 'color' => 'secondary'];
                                    $groupId = Str::slug($group);
                                @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="tab-{{ $groupId }}"
                                            data-bs-toggle="tab"
                                            data-bs-target="#pane-{{ $groupId }}"
                                            type="button"
                                            role="tab"
                                            aria-controls="pane-{{ $groupId }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i class="{{ $meta['icon'] }} text-{{ $meta['color'] }} me-1"></i>
                                        {{ ucwords(str_replace('_', ' ', $group)) }}
                                        <span class="badge bg-{{ $meta['color'] }} ms-1">{{ $settings->count() }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="settingsTabContent">
                            @foreach($allSettings as $group => $settings)
                                @php
                                    $meta = $groupIcons[$group] ?? ['icon' => 'fi fi-rr-settings', 'color' => 'secondary'];
                                    $groupId = Str::slug($group);
                                @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="pane-{{ $groupId }}"
                                     role="tabpanel"
                                     aria-labelledby="tab-{{ $groupId }}">

                                    @if($settings->isEmpty())
                                        <p class="text-muted mb-0">No settings found for this group.</p>
                                    @else
                                        <form action="{{ route('settings.group.update', ['group' => $group]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="group" value="{{ $group }}">

                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 5%;">#</th>
                                                            <th style="width: 25%;">Key</th>
                                                            <th style="width: 40%;">Value</th>
                                                            <th style="width: 10%;">Type</th>
                                                            <th style="width: 10%;">Status</th>
                                                            <th style="width: 10%;" class="text-end">Action</th>
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
                                                                        <textarea name="settings[{{ $setting->key }}][value]" class="form-control form-control-sm" rows="2">{{ old("settings.{$setting->key}.value", $setting->value) }}</textarea>
                                                                    @elseif($setting->type === 'json' || $setting->type === 'array')
                                                                        <textarea name="settings[{{ $setting->key }}][value]" class="form-control form-control-sm" rows="2">{{ is_array($setting->value) || is_object($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                                                    @else
                                                                        <input type="text" name="settings[{{ $setting->key }}][value]"
                                                                            class="form-control form-control-sm"
                                                                            value="{{ old("settings.{$setting->key}.value", is_array($setting->value) ? json_encode($setting->value) : $setting->value) }}">
                                                                    @endif
                                                                </td>
                                                                <td><span class="badge bg-secondary">{{ $setting->type }}</span></td>
                                                                <td>
                                                                    @if ($setting->isActive)
                                                                        <span class="badge bg-success">Active</span>
                                                                    @else
                                                                        <span class="badge bg-danger">Inactive</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">
                                                                    <x-table-actions :viewType="'page'" :editType="'page'" :viewRoute="'settings.show'"
                                                                        :editRoute="'settings.edit'" :deleteRoute="'settings.destroy'" :modelId="$setting->id" :viewPermission="'setting_show'"
                                                                        :editPermission="'setting_edit'" :deletePermission="'setting_delete'" />
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <a href="{{ route('settings.group.create', ['group' => $group]) }}" class="btn btn-outline-{{ $meta['color'] }} btn-sm">
                                                    <i class="fi fi-rr-plus me-1"></i> Add Setting
                                                </a>
                                                <button type="submit" class="btn btn-{{ $meta['color'] }} btn-sm">
                                                    <i class="fi fi-rr-disk me-1"></i> Save {{ ucwords(str_replace('_', ' ', $group)) }}
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fi fi-rr-settings text-muted fs-1 mb-3 d-block"></i>
                        <p class="text-muted mb-0">No settings found.</p>
                    </div>
                </div>
            @endif
        @endisset
    </div>
@endsection

@push('styles')
    <style>
        #settingsTab .nav-link {
            color: var(--bs-body-color);
            border: none;
            padding: 0.75rem 1rem;
        }
        #settingsTab .nav-link.active {
            font-weight: 600;
            border-bottom: 2px solid var(--bs-primary);
        }
    </style>
@endpush
