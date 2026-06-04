@extends('backend.layouts.master')

@section('title', 'Module Details - ' . $module->name)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Module Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('modules.index') }}">Modules</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $module->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('modules.edit', $module->id) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit me-1"></i> Edit Module
                </a>
                <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Modules
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Module Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Module Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Module Name</label>
                                <h5 class="mb-0">{{ $module->name }}</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Status</label>
                                <div>
                                    <span class="badge {{ $module->isActive ? 'bg-success' : 'bg-danger' }}">
                                        {{ $module->isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            @if ($module->description)
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted mb-1">Description</label>
                                    <p class="mb-0">{{ $module->description }}</p>
                                </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Created At</label>
                                <p class="mb-0">{{ format_date($module->createdAt) }}</p>
                                <small class="text-muted">{{ $module->createdAt->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Last Updated</label>
                                <p class="mb-0">{{ format_date($module->updatedAt) }}</p>
                                <small class="text-muted">{{ $module->updatedAt->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Module Permissions -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Module Permissions</h5>
                        <span class="badge bg-primary">{{ $module->getPermissionsCount() }} Permissions</span>
                    </div>
                    <div class="card-body">
                        @if ($module->hasPermissions())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Permission Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($module->permissions as $index => $permission)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $permission }}</strong></td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No permissions assigned to this module</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Module Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h3 class="mb-0">{{ $module->getPermissionsCount() }}</h3>
                                <small class="text-muted">Total Permissions</small>
                            </div>
                            <div class="text-primary">
                                <i class="fi fi-rr-shield-check" style="font-size: 32px;"></i>
                            </div>
                        </div>
                        @if (method_exists($module, 'roles'))
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <h3 class="mb-0">{{ $module->roles()->count() ?? 0 }}</h3>
                                    <small class="text-muted">Roles Using Module</small>
                                </div>
                                <div class="text-success">
                                    <i class="fi fi-rr-users-alt" style="font-size: 32px;"></i>
                                </div>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Module Status</small>
                                <p class="mb-0">
                                    <span class="badge {{ $module->isActive ? 'bg-success' : 'bg-danger' }}">
                                        {{ $module->isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Module Info -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Module Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="mb-2">
                                <strong>Module ID:</strong>
                                <span class="text-muted">#{{ $module->id }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Created:</strong>
                                <br><span class="text-muted">{{ $module->createdAt->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="mb-2">
                                <strong>Last Updated:</strong>
                                <br><span class="text-muted">{{ $module->updatedAt->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
