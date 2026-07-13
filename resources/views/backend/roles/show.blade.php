@extends('backend.layouts.master')

@section('title', 'Role Details - ' . $role->name)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Role Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('roles.index') }}">Roles</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $role->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit me-1"></i> Edit Role
                </a>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Roles
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Role Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Role Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-3 rounded" style="background-color: {{ $role->color ?? '#6c757d' }}20;">
                                        <i class="fi fi-rr-shield" style="font-size: 48px; color: {{ $role->color ?? '#6c757d' }};"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1">{{ $role->name }}</h3>
                                        <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($role->priority)
                                            <span class="badge bg-info">Priority: {{ $role->priority }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($role->description)
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted mb-1">Description</label>
                                <p class="mb-0">{{ $role->description }}</p>
                            </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Role Name</label>
                                <p class="mb-0">{{ $role->name }}</p>
                            </div>

                            @if($role->priority)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Priority Level</label>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ $role->priority }}</span>
                                    @if($role->priority == 1)
                                        <small class="text-muted">(Highest)</small>
                                    @elseif($role->priority == 5)
                                        <small class="text-muted">(Lowest)</small>
                                    @endif
                                </p>
                            </div>
                            @endif

                            @if($role->color)
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Role Color</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded" style="width: 40px; height: 40px; background-color: {{ $role->color }};"></div>
                                    <code>{{ $role->color }}</code>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Status</label>
                                <p class="mb-0">
                                    <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Created At</label>
                                <p class="mb-0">{{ format_date($role->created_at) }}</p>
                                <small class="text-muted">{{ $role->created_at->diffForHumans() }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Last Updated</label>
                                <p class="mb-0">{{ format_date($role->updated_at) }}</p>
                                <small class="text-muted">{{ $role->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Role Permissions -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Assigned Permissions</h5>
                        <span class="badge bg-primary">{{ $role->permissions->count() }} Permissions</span>
                    </div>
                    <div class="card-body">
                        @if($role->permissions && $role->permissions->count() > 0)
                            @php
                                $permissionsByModule = $role->permissions->groupBy('module.name');
                            @endphp

                            @foreach($permissionsByModule as $moduleName => $modulePermissions)
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fi fi-rr-apps me-1"></i>
                                        {{ $moduleName ?? 'System Permissions' }}
                                        <span class="badge bg-primary">{{ $modulePermissions->count() }}</span>
                                    </h6>
                                    <div class="row">
                                        @foreach($modulePermissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-start gap-2">
                                                    <i class="fi fi-rr-check-circle text-success mt-1"></i>
                                                    <div>
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No permissions assigned to this role</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Role Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h3 class="mb-0">{{ $role->users->count() ?? 0 }}</h3>
                                <small class="text-muted">Total Users</small>
                            </div>
                            <div class="text-primary">
                                <i class="fi fi-rr-users-alt" style="font-size: 32px;"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h3 class="mb-0">{{ $role->permissions->count() }}</h3>
                                <small class="text-muted">Total Permissions</small>
                            </div>
                            <div class="text-success">
                                <i class="fi fi-rr-shield-check" style="font-size: 32px;"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Role Status</small>
                                <p class="mb-0">
                                    <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Users with this Role -->
                @if($role->users && $role->users->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Users with this Role</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach($role->users->take(5) as $user)
                                    <div class="list-group-item px-0 py-2 border-0">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                                    class="rounded-circle" width="32" height="32">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; font-size: 14px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <strong class="d-block">{{ $user->name }}</strong>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($role->users->count() > 5)
                                    <div class="list-group-item px-0 py-2 border-0">
                                        <small class="text-muted">+{{ $role->users->count() - 5 }} more users</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Role Info -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Role Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="mb-2">
                                <strong>Role ID:</strong>
                                <span class="text-muted">#{{ $role->id }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Guard:</strong>
                                <span class="text-muted">{{ $role->guard_name ?? 'web' }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Created:</strong>
                                <br><span class="text-muted">{{ $role->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Last Updated:</strong>
                                <br><span class="text-muted">{{ $role->updated_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
