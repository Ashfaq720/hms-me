@extends('backend.layouts.master')

@section('title', 'Medical Group')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="row g-4">

            <div class="col-xl-3 col-lg-4 col-md-4">
                @include('backend.layouts.medicine_setup')
            </div>

            <div class="col-xl-9 col-lg-8 col-md-8">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="mb-1 fw-semibold text-dark">Medical Group</h5>
                                <p class="mb-0 text-muted small">Manage all medical groups from here</p>
                            </div>

                            <a data-size="md" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center"
                                data-url="{{ route('admin.medical-groups.create') }}" data-ajax-popup="true"
                                data-title="Create Medical Group" data-bs-toggle="tooltip" title="Create Medical Group">
                                <i class="bi bi-plus-lg me-1"></i> Create Medical Group
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3 text-uppercase fw-semibold small text-muted"
                                            style="width: 80px;">SL</th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted">Medical Group Name</th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted text-center"
                                            style="width: 140px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($medical_groups as $key => $group)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="fw-medium text-dark">{{ $key + 1 }}</span>
                                            </td>

                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                                                        style="width: 34px; height: 34px;">
                                                        <i class="bi bi-diagram-3 text-primary small"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark small">{{ $group->name }}</div>
                                                        <div class="text-muted" style="font-size: 12px;">Medical Group
                                                            Information</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-3 text-center">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <a data-size="md" class="btn btn-sm btn-outline-warning action-btn"
                                                        data-url="{{ route('admin.medical-groups.edit', $group->id) }}"
                                                        data-ajax-popup="true" data-title="Edit Medical Group"
                                                        data-bs-toggle="tooltip" title="Edit Medical Group">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('admin.medical-groups.destroy', $group->id) }}"
                                                        onsubmit="return confirm('Delete this data?')" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger rounded-2"
                                                            type="submit" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
    </div>
@endsection
