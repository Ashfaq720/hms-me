@extends('backend.layouts.master')

@section('title', 'Bed')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Bed Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bed_setup')
            </div>

            {{-- RIGHT: Bed Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Bed</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bedGroupModal" data-mode="create" data-title="Create Bed"
                        data-action="{{ route('beds.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Bed
                    </button>



                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Bed List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Bed Name</th>
                                            <th>Rent</th>
                                            <th>Bed Type</th>
                                            <th>Bed Group</th>
                                            <th>Floor</th>
                                            <th>Status</th>
                                            <th>Available</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($data as $p)
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
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div>
                                                            <div class="fw-bold">{{ $p->rent ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $p->bedType->name ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $p->bedGroup->name ?? '' }}
                                                </td>
                                                <td>
                                                    {{ $p->bedGroup->floor->name ?? '' }}
                                                </td>
                                                <td>
                                                    @if ($p->is_active)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($p->is_reserved)
                                                        <span class="badge bg-danger">No</span>
                                                    @else
                                                        <span class="badge bg-success">Yes</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bedGroupModal"
                                                            data-mode="edit" data-title="Edit Bed"
                                                            data-action="{{ route('beds.update', $p->id) }}"
                                                            data-name="{{ $p->name }}" data-rent="{{ $p->rent }}"
                                                            data-bed_type_id="{{ $p->bed_type_id }}"
                                                            data-bed_group_id="{{ $p->bed_group_id }}"
                                                            data-is_reserved="{{ (int) $p->is_reserved }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('beds.destroy', $p) }}"
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
                                                <td colspan="10" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                {{-- If you paginate --}}
                                <div class="mt-3 pb-3">
                                    {{-- {{ $floors->links() }} --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bed Create/Edit Modal -->
        <div class="modal fade" id="bedGroupModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="bedGroupModalTitle">Bed</h5>
                    </div>

                    <form id="bedGroupForm" method="POST" action="{{ route('beds.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- IMPORTANT: keep it without name by default --}}
                        <input type="hidden" id="bedGroupMethod" value="">

                        <div class="modal-body">
                            @include('bed._form', [
                                'data' => null,
                                'bedTypes' => $bedTypes,
                                'bedGroups' => $bedGroups,
                            ])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="bedGroupSubmitBtn">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('bedGroupModal');
                const titleEl = document.getElementById('bedGroupModalTitle');
                const formEl = document.getElementById('bedGroupForm');
                const submitBtn = document.getElementById('bedGroupSubmitBtn');
                const methodInp = document.getElementById('bedGroupMethod');

                // Make sure these IDs exist in bed._form
                const nameInp = document.getElementById('bed_group_name'); // or change to your actual input id
                const rentInp = document.getElementById('bed_rent');
                const bedTypeSel = document.getElementById('bed_type_id');
                const bedGroupSel = document.getElementById('bed_group_id');
                const isReservedSel = document.getElementById('bed_is_reserved');

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode'); // create/edit
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Bed';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';
                        submitBtn.innerText = 'Update';

                        nameInp.value = trigger.getAttribute('data-name') || '';
                        rentInp.value = trigger.getAttribute('data-rent') || '';
                        bedTypeSel.value = trigger.getAttribute('data-bed_type_id') || '';
                        bedGroupSel.value = trigger.getAttribute('data-bed_group_id') || '';
                        if (isReservedSel) isReservedSel.value = trigger.getAttribute('data-is_reserved') || '0';
                    } else {
                        methodInp.removeAttribute('name');
                        methodInp.value = '';
                        submitBtn.innerText = 'Save';

                        nameInp.value = '';
                        rentInp.value = '';
                        bedTypeSel.value = '';
                        bedGroupSel.value = '';
                        if (isReservedSel) isReservedSel.value = '0';
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            /* Make modal centered perfectly */
            #bedGroupModal .modal-dialog {
                margin-top: 0 !important;
            }

            /* Modern modal box */
            #bedGroupModal .modal-content {
                border: 0 !important;
                border-radius: 14px !important;
                overflow: hidden !important;
                box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important;
                background: #fff !important;
            }

            /* Fix header spacing + line */
            #bedGroupModal .modal-header {
                padding: 16px 20px !important;
                border-bottom: 1px solid #eef1f6 !important;
                align-items: center !important;
            }

            #bedGroupModal .modal-title {
                font-size: 18px !important;
                font-weight: 600 !important;
                margin: 0 !important;
            }

            /* Fix body spacing */
            #bedGroupModal .modal-body {
                padding: 20px !important;
            }

            /* Fix footer spacing + line */
            #bedGroupModal .modal-footer {
                padding: 14px 20px !important;
                border-top: 1px solid #eef1f6 !important;
            }

            /* Backdrop nicer */
            .modal-backdrop.show {
                opacity: .55 !important;
            }

            /* Optional: close button alignment */
            #bedGroupModal .btn-close {
                margin: 0 !important;
            }
        </style>
    @endpush
