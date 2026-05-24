@extends('backend.layouts.master')

@section('title', 'Create Role')

@section('content')
    <div class="container pb-5 mb-5">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Create Role</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('roles.index') }}">Roles</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                <i class="fi fi-rr-arrow-left me-1"></i> Back to Roles
            </a>
        </div>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Role Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Name Field -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description Field -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority Field -->
                            {{-- <div class="mb-3">
                                <label for="priority" class="form-label">Priority Level</label>
                                <select class="form-select select2 @error('priority') is-invalid @enderror" id="priority"
                                    name="priority">
                                    <option value="">Select Priority</option>
                                    <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>1 - Highest
                                    </option>
                                    <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>2 - High</option>
                                    <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>3 - Medium
                                    </option>
                                    <option value="4" {{ old('priority') == '4' ? 'selected' : '' }}>4 - Low</option>
                                    <option value="5" {{ old('priority') == '5' ? 'selected' : '' }}>5 - Lowest
                                    </option>
                                </select>
                                <div class="form-text">Higher priority roles have precedence over lower priority ones</div>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            <!-- Color Field -->
                            {{-- <div class="mb-3">
                                <label for="color" class="form-label">Role Color</label>
                                <input type="color"
                                    class="form-control form-control-color @error('color') is-invalid @enderror"
                                    id="color" name="color" value="{{ old('color', '#6c757d') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            <!-- Active Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Role
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Role Information -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Role Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <div class="small text-muted">
                                <h6>Best Practices:</h6>
                                <ul class="mb-0">
                                    <li>Use descriptive role names</li>
                                    <li>Assign minimal required permissions</li>
                                    <li>Set appropriate priority levels</li>
                                    <li>Review permissions regularly</li>
                                    <li>Document role responsibilities</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Assignment -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-underline card-header-tabs" id="myTab" role="tablist">
                                @if (isset($modules) && $modules->count() > 0)
                                    @foreach ($modules as $index => $module)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                id="module-{{ $module->id }}-tab" data-bs-toggle="tab"
                                                data-bs-target="#module-{{ $module->id }}" type="button" role="tab"
                                                aria-controls="module-{{ $module->id }}"
                                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                                tabindex="{{ $index === 0 ? '0' : '-1' }}">
                                                <i class="fi fi-rr-{{ $module->icon ?? 'apps' }} me-1"></i>
                                                {{ $module->name }}
                                                <span
                                                    class="badge bg-secondary ms-1">{{ $module->permissions->count() }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                @foreach ($modules as $index => $module)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                        id="module-{{ $module->id }}" role="tabpanel"
                                        aria-labelledby="module-{{ $module->id }}-tab" tabindex="0">

                                        @if ($module->permissions->count() > 0)
                                            <div class="permissions-grid">
                                                @foreach ($module->permissions as $permission)
                                                    <div class="permission-item">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-check" type="checkbox"
                                                                id="permission_{{ $permission->id }}" name="permissions[]"
                                                                value="{{ $permission->id }}"
                                                                {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-medium"
                                                                for="permission_{{ $permission->id }}">
                                                                {{ $permission->name }}
                                                                @if ($permission->description)
                                                                    <br><small
                                                                        class="text-muted">{{ $permission->description }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fi fi-rr-info text-muted mb-2" style="font-size: 2rem;"></i>
                                                <p class="text-muted mb-0">No permissions available for this module</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-check me-1"></i> Create Role
                        </button>
                        {{-- <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a> --}}
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAllCheckbox = document.getElementById('select_all');
            const permissionCheckboxes = document.querySelectorAll('.permission-check');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }

            // Update select all state when individual checkboxes change
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedCount = document.querySelectorAll('.permission-check:checked')
                        .length;
                    const totalCount = permissionCheckboxes.length;

                    if (checkedCount === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCount === totalCount) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                });
            });

            // Auto-generate role name from display name
            const nameInput = document.getElementById('name');
            nameInput.addEventListener('input', function() {
                // You can add auto-slug generation here if needed
            });
        });
    </script>
@endpush


@push('styles')
    <style>
        /* ========== ROLE CREATION INTERFACE STYLING ========== */

        /* Container and Layout */
        .role-create-container {
            background: linear-gradient(135deg, #f8f9fe 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        /* Page Header Enhancements */
        .app-page-head {
            background: linear-gradient(135deg, #4361ee 0%, #3451dc 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
        }

        .app-page-head .app-page-title {
            color: white;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .app-page-head .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .app-page-head .breadcrumb-item.active {
            color: white;
        }

        /* Enhanced Card Styling */
        .role-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.15);
        }

        .role-card .card-header {
            background: linear-gradient(135deg, #f8f9fe 0%, #eef2ff 100%);
            border-bottom: 2px solid #e4e7f0;
            padding: 1.5rem;
        }

        .role-card .card-header .card-title {
            color: #2d3561;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .role-card .card-body {
            padding: 1.5rem;
        }

        /* Form Elements Enhancement */
        .form-group-enhanced {
            margin-bottom: 1.5rem;
        }

        .form-label-enhanced {
            color: #2d3561;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control-enhanced {
            border: 2px solid #e4e7f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-enhanced:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-select-enhanced {
            border: 2px solid #e4e7f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="none" stroke="%23343a40" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 5l6 6 6-6"/></svg>');
        }

        /* Permissions Tabs Styling */
        .permissions-tabs .nav-tabs {
            border-bottom: 2px solid #e4e7f0;
            padding: 0 1rem;
        }

        .permissions-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            font-weight: 600;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .permissions-tabs .nav-link:hover {
            color: #4361ee;
            background-color: rgba(67, 97, 238, 0.05);
        }

        .permissions-tabs .nav-link.active {
            color: #4361ee;
            background-color: rgba(67, 97, 238, 0.1);
            border-bottom-color: #4361ee;
        }

        .permissions-tabs .nav-link .badge {
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        /* Permission Items Grid */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .permission-item {
            background: white;
            border: 2px solid #e4e7f0;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .permission-item:hover {
            border-color: #4361ee;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.1);
        }

        .permission-item.selected {
            border-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .permission-name {
            color: #2d3561;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .permission-description {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0;
        }

        /* Custom Checkbox Styling */
        .form-check-input-custom {
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #cbd0dd;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .form-check-input-custom:checked {
            background-color: #4361ee;
            border-color: #4361ee;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>');
        }

        .form-check-input-custom:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        /* Action Buttons */
        .btn-primary-enhanced {
            background: linear-gradient(135deg, #4361ee 0%, #3451dc 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
        }

        .btn-primary-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            background: linear-gradient(135deg, #3451dc 0%, #2a42c7 100%);
        }

        .btn-outline-enhanced {
            border: 2px solid #e4e7f0;
            color: #6b7280;
            background: white;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-outline-enhanced:hover {
            border-color: #4361ee;
            color: #4361ee;
            transform: translateY(-2px);
        }

        /* Priority Level Visual Enhancement */
        .priority-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .priority-1 {
            background-color: #ef4444;
        }

        .priority-2 {
            background-color: #f59e0b;
        }

        .priority-3 {
            background-color: #10b981;
        }

        .priority-4 {
            background-color: #3b82f6;
        }

        .priority-5 {
            background-color: #6b7280;
        }

        /* Role Color Preview */
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 2px solid #e4e7f0;
            display: inline-block;
            vertical-align: middle;
            margin-left: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .role-create-container {
                padding: 1rem 0;
            }

            .app-page-head {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .permissions-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .permission-item {
                padding: 0.75rem;
            }
        }

        /* Animation Keyframes */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }

        /* Loading States */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e4e7f0;
            border-top: 4px solid #4361ee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Footer spacing fix */
        .app-wrapper {
            min-height: calc(100vh - 200px);
            padding-bottom: 2rem;
        }

        .container.pb-5.mb-5 {
            margin-bottom: 5rem !important;
            padding-bottom: 3rem !important;
        }
    </style>
@endpush
