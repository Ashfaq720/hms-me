@extends('backend.layouts.master')

@section('title', 'Modules Management')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Modules Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Modules</li>
                    </ol>
                </nav>
            </div>

            @hasOnlyPermission('module_create')
                <a href="{{ route('modules.create') }}" class="btn btn-primary">
                    <i class="fi fi-rr-plus me-1"></i> Add New Module
                </a>
            @endHasOnlyPermission
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Modules List</h6>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table display table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>##</th>
                                        <th>Module</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Permissions</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if ($module->icon)
                                                            <i class="{{ $module->icon }}"
                                                                style="color: {{ $module->color ?? '#6c757d' }}"></i>
                                                        @else
                                                            <i class="fi fi-rr-apps text-info"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $module->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">#{{ $module->id ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code
                                                    class="text-muted">{{ $module->slug ?? Str::slug($module->name) }}</code>
                                            </td>
                                            <td>{{ Str::limit($module->description ?? 'No description', 50) }}</td>
                                            <td>
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $module->getPermissionsCount() }}
                                                    permissions
                                                </span>
                                            </td>
                                            <td>
                                                @if (isset($module->isActive) && $module->isActive)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $module->createdAt ? format_date($module->createdAt) : 'N/A' }}
                                            </td>
                                            <td class="text-end">
                                                <x-table-actions :viewType="'page'" :editType="'page'" :viewRoute="'modules.show'"
                                                    :editRoute="'modules.edit'" :deleteRoute="'modules.destroy'" :modelId="$module->id" :viewPermission="'module_show'"
                                                    :editPermission="'module_edit'" :deletePermission="'module_delete'" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @include('backend.components.delete-modal')
    </div>
@endsection
