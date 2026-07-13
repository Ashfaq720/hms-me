@extends('backend.layouts.master')

@section('title', 'Create Module')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Create Module</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('modules.index') }}">Modules</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary">
                <i class="fi fi-rr-arrow-left me-1"></i> Back to Modules
            </a>
        </div>

        <form method="POST" action="{{ route('modules.store') }}">
            @csrf

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
                                    class="form-control @error('name') is-invalid @enderror" required
                                    placeholder="Enter module name" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Enter module description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Icon -->
                            <div class="mb-3">
                                <label for="icon" class="form-label">Icon Class</label>
                                <input type="text" id="icon" name="icon"
                                    class="form-control @error('icon') is-invalid @enderror" placeholder="fi fi-rr-apps"
                                    value="{{ old('icon') }}">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Module Color -->
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="color" id="color" name="color"
                                    class="form-control form-control-color @error('color') is-invalid @enderror"
                                    value="{{ old('color', '#6c757d') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Module
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fi fi-rr-check me-1"></i> Create Module
                                </button>
                                <a href="{{ route('modules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Module Permissions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Module Permissions</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm mb-0" id="permission_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;">No.</th>
                                        <th>Permission</th>
                                        <th style="width: 64px;">
                                            <button type="button" class="btn btn-sm btn-success btn-add-row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><input type="text" name="modules[0][name]"
                                                class="form-control form-control-sm" required
                                                placeholder="Enter permission name"></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i
                                                    class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let rowCount = 1;

        $('.btn-add-row').click(function() {
            rowCount++;

            let newRow = `
        <tr>
            <td>${rowCount}</td>
            <td><input type="text" name="modules[${rowCount}][name]" class="form-control form-control-sm" required placeholder="Enter permission name"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;

            $('#permission_table tbody').append(newRow);
        });

        // Remove Row
        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
        });
    </script>
@endpush
