@extends('backend.layouts.master')

@section('title', 'Bed Type')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Bed Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bed_setup')
            </div>

            {{-- RIGHT: Bed Type Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Bed Type</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#floorModal" data-mode="create" data-title="Create Bed Type"
                        data-action="{{ route('bed-types.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Bed Type
                    </button>



                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Bed Type List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Bed Type Name</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($bedTypes as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div>
                                                            <div class="fw-bold">{{ $p->name ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($p->is_active)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#floorModal"
                                                            data-mode="edit" data-title="Edit Bed Type"
                                                            data-action="{{ route('bed-types.update', $p) }}"
                                                            data-name="{{ $p->name }}"
                                                            data-is-icu="{{ $p->is_icu ? 1 : 0 }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>


                                                        <form title="Delete" method="POST"
                                                            action="{{ route('bed-types.destroy', $p) }}"
                                                            onsubmit="return confirm('Delete this data?')"
                                                            class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit"><i
                                                                    class="fa-solid fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                {{-- If you paginate --}}
                                <div class="mt-3 pb-3">
                                    {{-- {{ $bed-types->links() }} --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bed Type Create/Edit Modal -->
        <div class="modal fade" id="floorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header d-flex align-items-center">
                        <h5 class="modal-title" id="floorModalTitle">Bed Type</h5>
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> --}}
                    </div>

                    <form id="floorForm" method="POST" action="{{ route('bed-types.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- IMPORTANT: keep it without name by default --}}
                        <input type="hidden" id="floorMethod" value="">

                        <div class="modal-body">
                            @include('bed-type._form', ['bedType' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="floorSubmitBtn">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('floorModal');
                const titleEl = document.getElementById('floorModalTitle');
                const formEl = document.getElementById('floorForm');
                const submitBtn = document.getElementById('floorSubmitBtn');
                const methodInp = document.getElementById('floorMethod');
                const nameInp = document.getElementById('bed_type_name');
                const isIcuInp = document.getElementById('bed_type_is_icu');

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget; // button/link that opened modal
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode'); // create / edit
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Bed Type';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        // enable PUT spoofing
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';

                        submitBtn.innerText = 'Update';
                        nameInp.value = trigger.getAttribute('data-name') || '';
                        if (isIcuInp) {
                            isIcuInp.checked = trigger.getAttribute('data-is-icu') === '1';
                        }
                    } else {
                        // CREATE mode: remove spoofing
                        methodInp.removeAttribute('name');
                        methodInp.value = '';

                        submitBtn.innerText = 'Save';
                        nameInp.value = '';
                        if (isIcuInp) {
                            isIcuInp.checked = false;
                        }
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            /* Make modal centered perfectly */
            #floorModal .modal-dialog {
                margin-top: 0 !important;
            }

            /* Modern modal box */
            #floorModal .modal-content {
                border: 0 !important;
                border-radius: 14px !important;
                overflow: hidden !important;
                box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important;
                background: #fff !important;
            }

            /* Fix header spacing + line */
            #floorModal .modal-header {
                padding: 16px 20px !important;
                border-bottom: 1px solid #eef1f6 !important;
                align-items: center !important;
            }

            #floorModal .modal-title {
                font-size: 18px !important;
                font-weight: 600 !important;
                margin: 0 !important;
            }

            /* Fix body spacing */
            #floorModal .modal-body {
                padding: 20px !important;
            }

            /* Fix footer spacing + line */
            #floorModal .modal-footer {
                padding: 14px 20px !important;
                border-top: 1px solid #eef1f6 !important;
            }

            /* Backdrop nicer */
            .modal-backdrop.show {
                opacity: .55 !important;
            }

            /* Optional: close button alignment */
            #floorModal .btn-close {
                margin: 0 !important;
            }
        </style>
    @endpush
