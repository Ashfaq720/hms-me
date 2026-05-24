@extends('backend.layouts.master')

@section('title', 'Lab Investigations')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Lab Investigation Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.lab_investigation_setup')
            </div>

            {{-- RIGHT: Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Lab Investigations</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#formModal" data-mode="create" data-title="Create Lab Investigation"
                        data-action="{{ route('lab-investigations.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Lab Investigation
                    </button>
                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Lab Investigation List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                            <th>Type</th>
                                            <th>Category</th>
                                            <th>Sample Type</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($data as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td><div class="fw-bold">{{ $p->name ?? '' }}</div></td>
                                                <td>{{ $p->short_name ?? '' }}</td>
                                                <td>{{ $p->category?->type?->name ?? '' }}</td>
                                                <td>{{ $p->category?->name ?? '' }}</td>
                                                <td>{{ $p->sample_type ?? '' }}</td>
                                                <td>{{ $p->price ?? 0 }}</td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-info"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-name="{{ $p->name }}"
                                                            data-short_name="{{ $p->short_name }}"
                                                            data-type="{{ $p->category?->type?->name ?? '' }}"
                                                            data-category="{{ $p->category?->name ?? '' }}"
                                                            data-department="{{ $p->department }}"
                                                            data-sample_type="{{ $p->sample_type }}"
                                                            data-report_time_hours="{{ $p->report_time_hours }}"
                                                            data-normal_range="{{ $p->normal_range }}"
                                                            data-unit="{{ $p->unit }}"
                                                            data-method="{{ $p->method }}"
                                                            data-preparation="{{ $p->preparation }}"
                                                            data-description="{{ $p->description }}"
                                                            data-price="{{ $p->price }}"
                                                            data-sort_order="{{ $p->sort_order }}"
                                                            data-notes="{{ $p->notes }}" title="Show">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>

                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#formModal"
                                                            data-mode="edit" data-title="Edit Lab Investigation"
                                                            data-action="{{ route('lab-investigations.update', $p->id) }}"
                                                            data-name="{{ $p->name }}"
                                                            data-short_name="{{ $p->short_name }}"
                                                            data-category_id="{{ $p->category_id }}"
                                                            data-department="{{ $p->department }}"
                                                            data-sample_type="{{ $p->sample_type }}"
                                                            data-report_time_hours="{{ $p->report_time_hours }}"
                                                            data-normal_range="{{ $p->normal_range }}"
                                                            data-unit="{{ $p->unit }}"
                                                            data-method="{{ $p->method }}"
                                                            data-preparation="{{ $p->preparation }}"
                                                            data-description="{{ $p->description }}"
                                                            data-price="{{ $p->price }}"
                                                            data-sort_order="{{ $p->sort_order }}"
                                                            data-notes="{{ $p->notes }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('lab-investigations.destroy', $p) }}"
                                                            onsubmit="return confirm('Delete this data?')"
                                                            class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h5 class="modal-title" id="formModalTitle">Lab Investigation</h5>
                    </div>

                    <form id="formEl" method="POST" action="{{ route('lab-investigations.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="formMethod" value="">

                        <div class="modal-body">
                            @include('lab-investigation._form', ['data' => null, 'categories' => $categories])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="formSubmitBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Show Modal -->
        <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h5 class="modal-title" id="showModalTitle">Lab Investigation Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped mb-0">
                            <tbody>
                                <tr><th style="width:35%">Name</th><td id="show_name"></td></tr>
                                <tr><th>Short Name</th><td id="show_short_name"></td></tr>
                                <tr><th>Type</th><td id="show_type"></td></tr>
                                <tr><th>Category</th><td id="show_category"></td></tr>
                                <tr><th>Department</th><td id="show_department"></td></tr>
                                <tr><th>Sample Type</th><td id="show_sample_type"></td></tr>
                                <tr><th>Report Time (Hours)</th><td id="show_report_time_hours"></td></tr>
                                <tr><th>Normal Range</th><td id="show_normal_range"></td></tr>
                                <tr><th>Unit</th><td id="show_unit"></td></tr>
                                <tr><th>Method</th><td id="show_method"></td></tr>
                                <tr><th>Preparation</th><td id="show_preparation"></td></tr>
                                <tr><th>Description</th><td id="show_description"></td></tr>
                                <tr><th>Price</th><td id="show_price"></td></tr>
                                <tr><th>Sort Order</th><td id="show_sort_order"></td></tr>
                                <tr><th>Notes</th><td id="show_notes"></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('formModal');
            const titleEl = document.getElementById('formModalTitle');
            const formEl = document.getElementById('formEl');
            const submitBtn = document.getElementById('formSubmitBtn');
            const methodInp = document.getElementById('formMethod');

            const fields = [
                'inv_name', 'inv_short_name', 'inv_category_id', 'inv_department',
                'inv_sample_type', 'inv_report_time_hours', 'inv_normal_range',
                'inv_unit', 'inv_method', 'inv_preparation', 'inv_description',
                'inv_price', 'inv_sort_order', 'inv_notes'
            ];

            const dataKeys = [
                'name', 'short_name', 'category_id', 'department',
                'sample_type', 'report_time_hours', 'normal_range',
                'unit', 'method', 'preparation', 'description',
                'price', 'sort_order', 'notes'
            ];

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Lab Investigation';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    fields.forEach(function(fieldId, i) {
                        const el = document.getElementById(fieldId);
                        if (el) el.value = trigger.getAttribute('data-' + dataKeys[i]) || '';
                    });
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    fields.forEach(function(fieldId) {
                        const el = document.getElementById(fieldId);
                        if (el) el.value = '';
                    });
                    // Set defaults
                    const priceEl = document.getElementById('inv_price');
                    const sortEl = document.getElementById('inv_sort_order');
                    if (priceEl) priceEl.value = '0';
                    if (sortEl) sortEl.value = '0';
                }
            });
        });

        // Show Modal
        const showModalEl = document.getElementById('showModal');
        showModalEl.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const showFields = [
                'name', 'short_name', 'type', 'category', 'department',
                'sample_type', 'report_time_hours', 'normal_range',
                'unit', 'method', 'preparation', 'description',
                'price', 'sort_order', 'notes'
            ];

            showFields.forEach(function(key) {
                const el = document.getElementById('show_' + key);
                if (el) el.textContent = trigger.getAttribute('data-' + key) || '-';
            });

            document.getElementById('showModalTitle').textContent =
                (trigger.getAttribute('data-name') || 'Lab Investigation') + ' — Details';
        });
    </script>
@endpush

@push('styles')
    <style>
        #formModal .modal-dialog { margin-top: 0 !important; }
        #formModal .modal-content {
            border: 0 !important; border-radius: 14px !important;
            overflow: hidden !important; box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important;
            background: #fff !important;
        }
        #formModal .modal-header { padding: 16px 20px !important; border-bottom: 1px solid #eef1f6 !important; align-items: center !important; }
        #formModal .modal-title { font-size: 18px !important; font-weight: 600 !important; margin: 0 !important; }
        #formModal .modal-body { padding: 20px !important; }
        #formModal .modal-footer { padding: 14px 20px !important; border-top: 1px solid #eef1f6 !important; }
        .modal-backdrop.show { opacity: .55 !important; }
        #formModal .btn-close { margin: 0 !important; }

        #showModal .modal-dialog { margin-top: 0 !important; }
        #showModal .modal-content {
            border: 0 !important; border-radius: 14px !important;
            overflow: hidden !important; box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important;
            background: #fff !important;
        }
        #showModal .modal-header { padding: 16px 20px !important; border-bottom: 1px solid #eef1f6 !important; }
        #showModal .modal-title { font-size: 18px !important; font-weight: 600 !important; margin: 0 !important; }
        #showModal .modal-body { padding: 20px !important; }
        #showModal .modal-footer { padding: 14px 20px !important; border-top: 1px solid #eef1f6 !important; }
        #showModal th { color: #5a6a85; font-weight: 500; }
    </style>
@endpush
