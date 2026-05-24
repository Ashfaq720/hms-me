@extends('backend.layouts.master')

@section('title', 'Settings - Group')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Settings - {{ ucfirst($group) }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('settings.index') }}">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($group) }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ ucfirst($group) }} Settings</h5>
                <div>
                    <a href="{{ route('settings.group.create', ['group' => $group]) }}" class="btn btn-primary btn-sm">Create New</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><code>{{ $setting->key }}</code></td>
                                    <td>{{ Str::limit($setting->value, 60) }}</td>
                                    <td><span class="badge bg-secondary">{{ $setting->type }}</span></td>
                                    <td>
                                        @if($setting->isActive)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <x-table-actions
                                            :viewType="'page'"
                                            :editType="'page'"
                                            :viewRoute="'settings.show'"
                                            :editRoute="'settings.edit'"
                                            :deleteRoute="'settings.destroy'"
                                            :modelId="$setting->id"
                                            :viewPermission="'setting_show'"
                                            :editPermission="'setting_edit'"
                                            :deletePermission="'setting_delete'"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
