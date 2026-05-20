@extends('backend.layouts.master')

@section('title', 'Operation Theatre')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Operation Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.operation_setup')
            </div>

            {{-- RIGHT: Operation Theatre Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Operation Theatre</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#operationTheatreModal" data-mode="create" data-title="Create Operation Theatre"
                        data-action="{{ route('operation-theatres.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Operation Theatre
                    </button>
                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Operation Theatre List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Charge</th>
                                            <th>Remarks</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($operationTheatres as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>
                                                    <div class="fw-bold">{{ $p->name ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">{{ $p->charge ?? '' }}</div>
                                                </td>
                                                <td>{{ $p->remarks ?? '' }}</td>
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
                                                            data-bs-toggle="modal" data-bs-target="#operationTheatreModal"
                                                            data-mode="edit" data-title="Edit Operation Theatre"
                                                            data-action="{{ route('operation-theatres.update', $p->id) }}"
                                                            data-name="{{ $p->name }}"
                                                            data-charge="{{ $p->charge }}"
                                                            data-remarks="{{ $p->remarks }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('operation-theatres.destroy', $p) }}"
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
                                                <td colspan="6" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-3 pb-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Operation Theatre Create/Edit Modal -->
        <div class="modal fade" id="operationTheatreModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header d-flex align-items-center">
                        <h5 class="modal-title" id="operationTheatreModalTitle">Operation Theatre</h5>
                    </div>

                    <form id="operationTheatreForm" method="POST" action="{{ route('operation-theatres.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="operationTheatreMethod" value="">

                        <div class="modal-body">
                            @include('master-data.operation-theatre._form', ['operationTheatre' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="operationTheatreSubmitBtn">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('operationTheatreModal');
                const titleEl = document.getElementById('operationTheatreModalTitle');
                const formEl = document.getElementById('operationTheatreForm');
                const submitBtn = document.getElementById('operationTheatreSubmitBtn');
                const methodInp = document.getElementById('operationTheatreMethod');
                const nameInp = document.getElementById('operation_theatre_name');
                const chargeInp = document.getElementById('operation_theatre_charge');
                const remarksInp = document.getElementById('operation_theatre_remarks');

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode');
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Operation Theatre';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';
                        submitBtn.innerText = 'Update';
                        nameInp.value = trigger.getAttribute('data-name') || '';
                        chargeInp.value = trigger.getAttribute('data-charge') || '';
                        remarksInp.value = trigger.getAttribute('data-remarks') || '';
                    } else {
                        methodInp.removeAttribute('name');
                        methodInp.value = '';
                        submitBtn.innerText = 'Save';
                        nameInp.value = '';
                        chargeInp.value = '';
                        remarksInp.value = '';
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            #operationTheatreModal .modal-dialog { margin-top: 0 !important; }
            #operationTheatreModal .modal-content { border: 0 !important; border-radius: 14px !important; overflow: hidden !important; box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important; background: #fff !important; }
            #operationTheatreModal .modal-header { padding: 16px 20px !important; border-bottom: 1px solid #eef1f6 !important; align-items: center !important; }
            #operationTheatreModal .modal-title { font-size: 18px !important; font-weight: 600 !important; margin: 0 !important; }
            #operationTheatreModal .modal-body { padding: 20px !important; }
            #operationTheatreModal .modal-footer { padding: 14px 20px !important; border-top: 1px solid #eef1f6 !important; }
            .modal-backdrop.show { opacity: .55 !important; }
            #operationTheatreModal .btn-close { margin: 0 !important; }
        </style>
    @endpush
