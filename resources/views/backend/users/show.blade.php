@extends('backend.layouts.master')

@section('title', 'User Profile - ' . $user->name)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">User Profile</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('users.index') }}">Users</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit me-1"></i> Edit User
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="row">
            <!-- MAIN CONTENT: User Information -->
            <div class="col-md-8">

                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Personal Information</h5>
                    </div>

                    <div class="card-body">

                        <!-- Avatar & Basic Info -->
                        <div class="row mb-3">
                            <div class="col-md-3">

                                @if ($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                        class="rounded-circle img-thumbnail" width="120" height="120">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                        style="width:120px; height:120px; font-size:48px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                            </div>

                            <div class="col-md-9">
                                <h3 class="mb-1">{{ $user->name }}</h3>
                                <p class="text-muted mb-2">{{ $user->email }}</p>

                                @if ($user->position)
                                    <p class="mb-1"><strong>Position:</strong> {{ $user->position }}</p>
                                @endif

                                @if ($user->department)
                                    <p class="mb-1"><strong>Department:</strong> {{ $user->department }}</p>
                                @endif

                                <div class="mt-2">
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                    @if ($user->hasVerifiedEmail())
                                        <span class="badge bg-success">Email Verified</span>
                                    @else
                                        <span class="badge bg-warning">Email Not Verified</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Detailed Info -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Full Name</label>
                                <p class="mb-0">{{ $user->name }}</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted mb-1">Email Address</label>
                                <p class="mb-0">{{ $user->email }}</p>
                            </div>

                            @if ($user->phone)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted mb-1">Phone Number</label>
                                    <p class="mb-0">{{ $user->phone }}</p>
                                </div>
                            @endif

                            @if ($user->department)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted mb-1">Department</label>
                                    <p class="mb-0">{{ $user->department }}</p>
                                </div>
                            @endif

                            @if ($user->position)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted mb-1">Position</label>
                                    <p class="mb-0">{{ $user->position }}</p>
                                </div>
                            @endif

                            @if ($user->date_of_birth)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted mb-1">Date of Birth</label>
                                    <p class="mb-0">{{ format_date($user->date_of_birth) }}</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                @if ($user->bio || $user->address)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Additional Information</h5>
                        </div>

                        <div class="card-body">

                            @if ($user->bio)
                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1">Bio</label>
                                    <p class="mb-0">{{ $user->bio }}</p>
                                </div>
                            @endif

                            @if ($user->address)
                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1">Address</label>
                                    <p class="mb-0">{{ $user->address }}</p>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

            </div>

            <!-- Permissions -->
            <div class="col-md-4">
                <!-- Permissions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Permissions</h6>
                    </div>

                    <div class="card-body">
                        @php $permissions = $user->getAllPermissions(); @endphp

                        @if ($permissions->count() > 0)
                            <div class="list-group list-group-flush">

                                @foreach ($permissions->take(10) as $permission)
                                    <div class="list-group-item px-0 py-1 border-0">
                                        <small>
                                            <i class="fi fi-rr-check-circle text-success me-1"></i>
                                            {{ $permission->name }}
                                        </small>
                                    </div>
                                @endforeach

                                @if ($permissions->count() > 10)
                                    <div class="list-group-item px-0 py-1 border-0">
                                        <small class="text-muted">
                                            +{{ $permissions->count() - 10 }} more permissions
                                        </small>
                                    </div>
                                @endif

                            </div>
                        @else
                            <p class="text-muted mb-0">No permissions granted</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <!-- Assigned Roles -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Assigned Roles</h6>
                    </div>

                    <div class="card-body">
                        @if ($user->roles->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No roles assigned</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Account Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Account Information</h6>
                    </div>

                    <div class="card-body small">

                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="{{ $user->is_active ? 'text-success' : 'text-danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <strong>Created:</strong>
                            <span class="text-muted">{{ format_date($user->created_at) }}</span>
                            <br>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="mb-2">
                            <strong>Last Updated:</strong>
                            <span class="text-muted">{{ format_date($user->updated_at) }}</span>
                            <br>
                            <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                        </div>

                        @if ($user->email_verified_at)
                            <div class="mb-2">
                                <strong>Email Verified:</strong>
                                <span class="text-success">{{ format_date($user->email_verified_at) }}</span>
                            </div>
                        @else
                            <div class="mb-2">
                                <strong>Email:</strong>
                                <span class="text-warning">Not verified</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
