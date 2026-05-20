@extends('backend.layouts.master')

@section('title', 'Settings')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">System Settings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
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

        {{-- Quick Links --}}
        <div class="row g-3 mb-4">
            <!-- Settings -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        {{-- <div class="icon-circle mb-3"> --}}
                            <i class="fi fi-rr-settings text-primary fs-2"></i>
                        {{-- </div> --}}
                        <h5 class="card-title">Settings</h5>
                        <p class="card-text">Manage all general, buisness and system settings.</p>
                        {{-- <small class="text-muted d-block mb-2">
                            {{ isset($allSettings) ? $allSettings->flatten()->count() . ' total settings' : '' }}
                        </small> --}}
                        <a href="{{ route('settings.all') }}" class="btn btn-primary btn-sm">Manage Settings</a>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        {{-- <div class="icon-circle mb-3"> --}}
                            <i class="fi fi-rr-users text-success fs-2"></i>
                        {{-- </div> --}}
                        <h5 class="card-title">User Management</h5>
                        <p class="card-text">Manage users, roles, and permissions.</p>
                        <div class="mt-auto">
                            <a href="{{ route('users.index') }}" class="btn btn-success btn-sm">Manage Users</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        {{-- <div class="icon-circle mb-3"> --}}
                            <i class="fi fi-rr-shield text-warning fs-2"></i>
                        {{-- </div> --}}
                        <h5 class="card-title">Roles & Permissions</h5>
                        <p class="card-text">Configure user roles and permission settings.</p>
                        <div class="mt-auto">
                            <a href="{{ route('roles.index') }}" class="btn btn-warning btn-sm">Manage Roles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules -->
            <div class="col-lg-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        {{-- <div class="icon-circle mb-3"> --}}
                            <i class="fi fi-rr-apps text-info fs-2"></i>
                        {{-- </div> --}}
                        <h5 class="card-title">Modules</h5>
                        <p class="card-text">Manage application modules and components.</p>
                        <div class="mt-auto">
                            <a href="{{ route('modules.index') }}" class="btn btn-info btn-sm">Manage Modules</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
