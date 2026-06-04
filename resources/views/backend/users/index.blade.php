@extends('backend.layouts.master')

@section('title', 'Users Management')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Users Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>
            </div>
            
            @hasOnlyPermission('user_create')
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fi fi-rr-plus me-1"></i> Add New User
                </a>
            @endHasOnlyPermission
        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">Users List</h6>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table display table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>##</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs rounded-circle">
                                                        <img src="{{ asset('backend/assets/images/avatar/avatar1.webp') }}"
                                                            alt="{{ $user->name ?? 'User' }}">
                                                    </div>
                                                    <div class="ms-2">
                                                        <div class="fw-medium">{{ $user->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">#{{ $user->id ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email ?? 'N/A' }}</td>
                                            <td>{{ $user->phone ?? '-' }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ $role->display_name ?? $role->name }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if (isset($user->is_active) && $user->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at ? format_date($user->created_at) : 'N/A' }}</td>
                                            <td class="text-end">
                                                <x-table-actions
                                                    :viewType="'page'"
                                                    :editType="'page'"
                                                    :viewRoute="'users.show'"
                                                    :editRoute="'users.edit'"
                                                    :deleteRoute="'users.destroy'"
                                                    :modelId="$user->id"
                                                    :viewPermission="'user_show'"
                                                    :editPermission="'user_edit'"
                                                    :deletePermission="'user_delete'"
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
        </div>
        
        @include('backend.components.delete-modal')
    </div>
@endsection