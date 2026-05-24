@extends('backend.layouts.master')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Roles & Permissions</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Roles</li>
                    </ol>
                </nav>
            </div>

            @hasOnlyPermission('role_create')
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="fi fi-rr-plus me-1"></i> Add New Role
                </a>
            @endHasOnlyPermission
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Roles List</h6>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table display table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>##</th>
                                        <th>Role</th>
                                        {{-- <th>Display Name</th> --}}
                                        <th>Total Users</th>
                                        <th>Permissions</th>
                                        {{-- <th>Priority</th> --}}
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    {{-- <div class="me-2">
                                                        @if ($role->isSystem())
                                                            <i class="fi fi-rr-shield text-warning"></i>
                                                        @else
                                                            <i class="fi fi-rr-user text-info"></i>
                                                        @endif
                                                    </div> --}}
                                                    <div>
                                                        <div class="fw-medium">{{ $role->name ?? 'N/A' }}</div>
                                                        {{-- <small class="text-muted">#{{ $role->id ?? '' }}</small> --}}
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- <td>{{ $role->display_name ?? $role->name }}</td> --}}
                                            <td>
                                                <span class="badge bg-primary rounded-pill">
                                                    {{ $role->getUsersCount() }} users
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary rounded-pill">
                                                    {{ $role->getPermissionsCount() }}
                                                    permissions
                                                </span>
                                            </td>
                                            {{-- <td>
                                                <span
                                                    class="badge bg-{{ $role->priority >= 80 ? 'danger' : ($role->priority >= 60 ? 'warning' : 'primary') }}">
                                                    {{ $role->priority ?? 10 }}
                                                </span>
                                            </td> --}}
                                            <td>
                                                @if (isset($role->isActive) && $role->isActive)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <x-table-actions :viewType="'page'" :editType="'page'" :viewRoute="'roles.show'"
                                                    :editRoute="'roles.edit'" :deleteRoute="'roles.destroy'" :modelId="$role->id" :viewPermission="'role_show'"
                                                    :editPermission="'role_edit'" :deletePermission="'role_delete'" />
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
