@extends('backend.layouts.master')

@section('title', 'Company')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="row g-4">

            <div class="col-xl-3 col-lg-4 col-md-4">
                @include('backend.layouts.medicine_setup')
            </div>

            <div class="col-xl-9 col-lg-8 col-md-8">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="mb-1 fw-semibold text-dark">Company</h5>
                                <p class="mb-0 text-muted small">Manage all companies from here</p>
                            </div>

                            <a data-size="md"
                               class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center"
                               data-url="{{ route('admin.companies.create') }}"
                               data-ajax-popup="true"
                               data-title="Create Company"
                               data-bs-toggle="tooltip"
                               title="Create Company">
                                <i class="bi bi-plus-lg me-1"></i> Create Company
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3 text-uppercase fw-semibold small text-muted" style="width: 80px;">SL</th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted">Company Name</th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted text-center" style="width: 140px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($companies as $key => $company)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="fw-medium text-dark">{{ $key + 1 }}</span>
                                            </td>

                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                                                        style="width: 34px; height: 34px;">
                                                        <i class="bi bi-building text-primary small"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark small">{{ $company->name }}</div>
                                                        <div class="text-muted" style="font-size: 12px;">Company Information</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-3 text-center">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <a data-size="md"
                                                       class="btn btn-sm btn-outline-warning action-btn"
                                                       data-url="{{ route('admin.companies.edit', $company->id) }}"
                                                       data-ajax-popup="true"
                                                       data-title="Edit Company"
                                                       data-bs-toggle="tooltip"
                                                       title="Edit Company">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <form method="POST"
                                                          action="{{ route('admin.companies.destroy', $company->id) }}"
                                                          onsubmit="return confirm('Delete this data?')"
                                                          class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger rounded-2" type="submit" title="Delete">
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
