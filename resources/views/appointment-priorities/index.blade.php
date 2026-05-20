@extends('backend.layouts.master')

@section('title', 'Appointment Priority')

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.appointment_setup')
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title mb-0">Appointment Priority</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 mt-1">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointment</a></li>
                                <li class="breadcrumb-item active">Priority</li>
                            </ol>
                        </nav>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#priorityModal" data-mode="create" data-title="Create Priority"
                        data-action="{{ route('appointment-priorities.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Priority
                    </button>
                </div>

                <div class="row mt-3">
                    <div class="col-12">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-xmark me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card overflow-hidden">
                            <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">
                                    <i class="fa-solid fa-flag text-primary me-1"></i> Priority List
                                    <span class="badge bg-primary ms-1">{{ $priorities->count() }}</span>
                                </h6>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table class="table display table-row-rounded table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Priority Name</th>
                                            <th width="100" class="text-center">Status</th>
                                            <th width="120" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($priorities as $i => $p)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        <i class="fa-solid fa-flag text-muted me-1 small"></i>
                                                        {{ $p->name }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($p->is_active)
                                                        <span class="badge bg-success-subtle text-success px-3">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary px-3">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning"
                                                            data-bs-toggle="modal" data-bs-target="#priorityModal"
                                                            data-mode="edit" data-title="Edit Priority"
                                                            data-action="{{ route('appointment-priorities.update', $p) }}"
                                                            data-name="{{ $p->name }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('appointment-priorities.destroy', $p) }}"
                                                            onsubmit="return confirm('Are you sure you want to delete this priority?')" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                                        No priorities found. Click "Add Priority" to create one.
                                                    </div>
                                                </td>
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

        {{-- Create / Edit Modal --}}
        <div class="modal fade" id="priorityModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="priorityModalTitle">
                            <i class="fa-solid fa-flag text-primary me-2"></i> Priority
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="priorityForm" method="POST"
                        action="{{ route('appointment-priorities.store') }}">
                        @csrf
                        <input type="hidden" id="priorityMethod" value="">

                        <div class="modal-body">
                            @include('appointment-priorities._form', ['priority' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i> Close
                            </button>
                            <button type="submit" class="btn btn-primary" id="prioritySubmitBtn">
                                <i class="fa-solid fa-check me-1"></i> Save
                            </button>
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
            const modalEl = document.getElementById('priorityModal');
            const titleEl = document.getElementById('priorityModalTitle');
            const formEl = document.getElementById('priorityForm');
            const submitBtn = document.getElementById('prioritySubmitBtn');
            const methodInp = document.getElementById('priorityMethod');
            const nameInp = document.getElementById('priority_name');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerHTML = '<i class="fa-solid fa-flag text-primary me-2"></i> ' + (title || 'Priority');
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Update';
                    nameInp.value = trigger.getAttribute('data-name') || '';
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save';
                    nameInp.value = '';
                }
            });
        });
    </script>
@endpush
