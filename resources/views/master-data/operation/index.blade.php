@extends('backend.layouts.master')

@section('title', 'Operation')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Operation Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.operation_setup')
            </div>

            {{-- RIGHT: Operation Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Operation</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#operationModal" data-mode="create" data-title="Create Operation"
                        data-action="{{ route('operations.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Operation
                    </button>
                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Operation List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Charge</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($operations as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>
                                                    <div class="fw-bold">{{ $p->name ?? '' }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold">{{ $p->charge ?? '' }}</div>
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
                                                            data-bs-toggle="modal" data-bs-target="#operationModal"
                                                            data-mode="edit" data-title="Edit Operation"
                                                            data-action="{{ route('operations.update', $p->id) }}"
                                                            data-name="{{ $p->name }}" data-charge="{{ $p->charge }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('operations.destroy', $p) }}"
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
                                                <td colspan="5" class="text-center">No data found</td>
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
        <!-- Operation Create/Edit Modal -->
        <div class="modal fade" id="operationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header d-flex align-items-center">
                        <h5 class="modal-title" id="operationModalTitle">Operation</h5>
                    </div>

                    <form id="operationForm" method="POST" action="{{ route('operations.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="operationMethod" value="">

                        <div class="modal-body">
                            @include('master-data.operation._form', ['operation' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="operationSubmitBtn">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('operationModal');
                const titleEl = document.getElementById('operationModalTitle');
                const formEl = document.getElementById('operationForm');
                const submitBtn = document.getElementById('operationSubmitBtn');
                const methodInp = document.getElementById('operationMethod');
                const nameInp = document.getElementById('operation_name');
                const chargeInp = document.getElementById('operation_charge');

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode');
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Operation';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';
                        submitBtn.innerText = 'Update';
                        nameInp.value = trigger.getAttribute('data-name') || '';
                        chargeInp.value = trigger.getAttribute('data-charge') || '';
                    } else {
                        methodInp.removeAttribute('name');
                        methodInp.value = '';
                        submitBtn.innerText = 'Save';
                        nameInp.value = '';
                        chargeInp.value = '';
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            #operationModal .modal-dialog { margin-top: 0 !important; }
            #operationModal .modal-content { border: 0 !important; border-radius: 14px !important; overflow: hidden !important; box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important; background: #fff !important; }
            #operationModal .modal-header { padding: 16px 20px !important; border-bottom: 1px solid #eef1f6 !important; align-items: center !important; }
            #operationModal .modal-title { font-size: 18px !important; font-weight: 600 !important; margin: 0 !important; }
            #operationModal .modal-body { padding: 20px !important; }
            #operationModal .modal-footer { padding: 14px 20px !important; border-top: 1px solid #eef1f6 !important; }
            .modal-backdrop.show { opacity: .55 !important; }
            #operationModal .btn-close { margin: 0 !important; }
        </style>
    @endpush
