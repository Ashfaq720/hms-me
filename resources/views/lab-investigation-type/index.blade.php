@extends('backend.layouts.master')

@section('title', 'Lab Investigation Types')

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
                        <h1 class="app-page-title">Lab Investigation Types</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#formModal" data-mode="create" data-title="Create Type"
                        data-action="{{ route('lab-investigation-types.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Type
                    </button>
                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Type List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Notes</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($data as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td><div class="fw-bold">{{ $p->name ?? '' }}</div></td>
                                                <td>{{ $p->notes ?? '' }}</td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#formModal"
                                                            data-mode="edit" data-title="Edit Type"
                                                            data-action="{{ route('lab-investigation-types.update', $p->id) }}"
                                                            data-name="{{ $p->name }}"
                                                            data-notes="{{ $p->notes }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('lab-investigation-types.destroy', $p) }}"
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
                                                <td colspan="4" class="text-center">No data found</td>
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
                        <h5 class="modal-title" id="formModalTitle">Type</h5>
                    </div>

                    <form id="formEl" method="POST" action="{{ route('lab-investigation-types.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="formMethod" value="">

                        <div class="modal-body">
                            @include('lab-investigation-type._form', ['data' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="formSubmitBtn">Save</button>
                        </div>
                    </form>
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
            const nameInp = document.getElementById('type_name');
            const notesInp = document.getElementById('type_notes');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Type';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';
                    nameInp.value = trigger.getAttribute('data-name') || '';
                    notesInp.value = trigger.getAttribute('data-notes') || '';
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';
                    nameInp.value = '';
                    notesInp.value = '';
                }
            });
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
    </style>
@endpush
