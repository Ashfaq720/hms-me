@extends('backend.layouts.master')

@section('title', 'Edit User')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Edit User</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('users.index') }}">Users</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info">
                    <i class="fi fi-rr-eye me-1"></i> View Profile
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Users
                </a>
            </div>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Name Field -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Field -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department/Position Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control @error('department') is-invalid @enderror"
                                            id="department" name="department"
                                            value="{{ old('department', $user->department) }}">
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position</label>
                                        <input type="text" class="form-control @error('position') is-invalid @enderror"
                                            id="position" name="position" value="{{ old('position', $user->position) }}">
                                        @error('position')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Current Profile Image -->
                            @if ($user->avatar)
                                <div class="mb-3">
                                    <label class="form-label">Current Profile Image</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                            class="rounded-circle" width="60" height="60">
                                        <div>
                                            <p class="mb-0">{{ basename($user->avatar) }}</p>
                                            <small class="text-muted">Current profile image</small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Profile Image -->
                            <div class="mb-3">
                                <label for="avatar" class="form-label">
                                    {{ $user->avatar ? 'Change Profile Image' : 'Upload Profile Image' }}
                                </label>
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                    id="avatar" name="avatar" accept="image/*">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active User
                                    </label>
                                </div>
                            </div>

                            <!-- Email Verification -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_verified"
                                        name="email_verified" value="1"
                                        {{ old('email_verified', $user->hasVerifiedEmail()) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_verified">
                                        Email Verified
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Roles Assignment -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Roles & Permissions</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Assign Roles</label>
                                @foreach ($roles as $role)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                            id="role_{{ $role->id }}" name="roles[]"
                                            value="{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            <span class="fw-medium">{{ $role->name }}</span>
                                            @if ($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Additional Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                            id="date_of_birth" name="date_of_birth"
                            value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <!-- Current Roles -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Current Roles</h6>
                        </div>
                        <div class="card-body">
                            @if ($user->roles->count() > 0)
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary me-1 mb-1">{{ $role->name }}</span>
                                @endforeach
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
                        <div class="card-body">
                            <div class="small text-muted">
                                <p class="mb-1"><strong>Created:</strong> {{ format_date($user->created_at) }}</p>
                                <p class="mb-1"><strong>Last Updated:</strong> {{ format_date($user->updated_at) }}
                                </p>
                                @if ($user->email_verified_at)
                                    <p class="mb-1 text-success"><strong>Email Verified:</strong>
                                        {{ format_date($user->email_verified_at) }}</p>
                                @else
                                    <p class="mb-1 text-warning"><strong>Email:</strong> Not verified</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fi fi-rr-check me-1"></i> Update User
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview uploaded image
            const avatarInput = document.getElementById('avatar');
            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // You can add image preview functionality here
                            console.log('Image selected:', file.name);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('password_confirmation').value;

                    if (password && password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return false;
                    }
                });
            }
        });
    </script>
@endpush
