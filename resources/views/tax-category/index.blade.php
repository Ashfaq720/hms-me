@extends('backend.layouts.master')

@section('title', 'Tax Category')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <div class="row g-4">

            {{-- LEFT: Setup Menu --}}
            <div class="col-xl-3 col-lg-4 col-md-4">
                @include('backend.layouts.charges_setup')
            </div>

            {{-- RIGHT: Content --}}
            <div class="col-xl-9 col-lg-8 col-md-8">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

                    {{-- Header --}}
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h5 class="mb-1 fw-semibold text-dark">Tax Category</h5>
                                <p class="mb-0 text-muted small">Manage all Tax Categorys from here</p>
                            </div>

                            <a data-size="md" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center"
                                data-url="{{ route('admin.tax-categories.create') }}" data-ajax-popup="true"
                                data-title="Create Tax Category" data-bs-toggle="tooltip" title="Create Tax Category">
                                <i class="bi bi-plus-lg me-1"></i> Create Tax Category
                            </a>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="dt_basic" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3 text-uppercase fw-semibold small text-muted"
                                            style="width: 80px;">
                                            SL
                                        </th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted">
                                            Tax Category Name
                                        </th>
                                        <th class="py-3 text-uppercase fw-semibold small text-muted text-center"
                                            style="width: 140px;">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taxCategory ?? [] as $key => $t)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="fw-medium text-dark">{{ $key + 1 }}</span>
                                            </td>

                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                                                        style="width: 34px; height: 34px;">
                                                        <i class="bi bi-grid text-primary small"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark small">
                                                            {{ $t->name ?? '' }}
                                                        </div>
                                                        <div class="text-muted" style="font-size: 12px;">
                                                            Tax Category Information
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-3 text-center">
                                                <div class="d-inline-flex align-items-center gap-1">

                                                    <a data-size="md" class="btn btn-sm btn-outline-warning action-btn"
                                                        data-url="{{ route('admin.tax-categories.edit', $t->id) }}"
                                                        data-ajax-popup="true" data-title="Edit Tax Category"
                                                        data-bs-toggle="tooltip" title="Edit Tax Category">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('admin.tax-categories.destroy', $t->id) }}"
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
