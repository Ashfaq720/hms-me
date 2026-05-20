@extends('backend.layouts.master')

@section('title', 'Edit Module')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Edit Module</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('modules.index') }}">Modules</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit {{ $module->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('modules.show', $module->id) }}" class="btn btn-outline-info">
                    <i class="fi fi-rr-eye me-1"></i> View Details
                </a>
                <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary">
                    <i class="fi fi-rr-arrow-left me-1"></i> Back to Modules
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('modules.update', $module->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Module Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Module Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Module Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $module->name) }}" required placeholder="Enter module name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Enter module description">{{ old('description', $module->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Icon -->
                            <div class="mb-3">
                                <label for="icon" class="form-label">Icon Class</label>
                                <input type="text" id="icon" name="icon"
                                    class="form-control @error('icon') is-invalid @enderror" placeholder="fi fi-rr-apps"
                                    value="{{ old('icon', $module->icon) }}">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Color -->
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="color" id="color" name="color"
                                    class="form-control form-control-color @error('color') is-invalid @enderror"
                                    value="{{ old('color', $module->color ?? '#6c757d') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $module->isActive) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Module
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fi fi-rr-check me-1"></i> Update Module
                                </button>
                                <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Module Permissions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Module Permissions</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm mb-0" id="edit_permission_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">No.</th>
                                        <th>Permission</th>
                                        <th style="width: 64px;">
                                            <button type="button" class="btn btn-sm btn-success btn-add-row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($eloquentModule->permissions as $index => $permission)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="modules[{{ $index }}][id]"
                                                    value="{{ $permission->id }}">
                                                <input type="text" name="modules[{{ $index }}][name]"
                                                    class="form-control form-control-sm" value="{{ $permission->name }}"
                                                    required placeholder="Enter permission name">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-row">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
            
        <div class="row">
            <div class="col-md-6">
                <!-- Current Permissions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Current Permissions</h6>
                    </div>
                    <div class="card-body">
                        @if ($eloquentModule->permissions->count() > 0)
                            @foreach ($eloquentModule->permissions as $permission)
                                <span class="badge bg-primary me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No permissions assigned</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <!-- Module Information -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Module Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <p class="mb-1"><strong>Created:</strong> {{ format_date($module->createdAt) }}
                            </p>
                            <p class="mb-1"><strong>Last Updated:</strong>
                                {{ format_date($module->updatedAt) }}</p>
                            <p class="mb-1"><strong>Permissions:</strong> {{ $module->getPermissionsCount() }}</p>
                            @if ($module->slug)
                                <p class="mb-1"><strong>Slug:</strong> <code>{{ $module->slug }}</code></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let editRowCount = {{ $eloquentModule->permissions->count() }};

            $('.btn-add-row').click(function() {
                editRowCount++;
                const rowIndex = Date.now(); // unique index for new entries

                const newRow = `
                <tr>
                    <td>${editRowCount}</td>
                    <td>
                        <input type="text" name="modules[${rowIndex}][name]" class="form-control form-control-sm" required placeholder="Enter permission name">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#edit_permission_table tbody').append(newRow);
            });

            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endpush
