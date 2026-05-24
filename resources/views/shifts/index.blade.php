@extends('backend.layouts.master')

@section('title', 'Shift')

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.appointment_setup')
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title mb-0">Shift</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 mt-1">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointment</a></li>
                                <li class="breadcrumb-item active">Shift</li>
                            </ol>
                        </nav>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#shiftModal" data-mode="create" data-title="Create Shift"
                        data-action="{{ route('shifts.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Shift
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
                                    <i class="fa-solid fa-clock text-primary me-1"></i> Shift List
                                    <span class="badge bg-primary ms-1">{{ $shifts->count() }}</span>
                                </h6>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table class="table display table-row-rounded table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Shift Name</th>
                                            <th width="140" class="text-center">Time From</th>
                                            <th width="140" class="text-center">Time To</th>
                                            <th width="100" class="text-center">Status</th>
                                            <th width="120" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($shifts as $i => $s)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        <i class="fa-solid fa-clock text-muted me-1 small"></i>
                                                        {{ $s->name }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($s->time_from)
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fa-regular fa-clock me-1 small"></i>
                                                            {{ \Carbon\Carbon::parse($s->time_from)->format('h:i A') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($s->time_to)
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fa-regular fa-clock me-1 small"></i>
                                                            {{ \Carbon\Carbon::parse($s->time_to)->format('h:i A') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($s->is_active)
                                                        <span class="badge bg-success-subtle text-success px-3">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary px-3">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning"
                                                            data-bs-toggle="modal" data-bs-target="#shiftModal"
                                                            data-mode="edit" data-title="Edit Shift"
                                                            data-action="{{ route('shifts.update', $s) }}"
                                                            data-name="{{ $s->name }}"
                                                            data-time-from="{{ $s->time_from ? \Illuminate\Support\Str::substr($s->time_from, 0, 5) : '' }}"
                                                            data-time-to="{{ $s->time_to ? \Illuminate\Support\Str::substr($s->time_to, 0, 5) : '' }}"
                                                            title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('shifts.destroy', $s) }}"
                                                            onsubmit="return confirm('Are you sure you want to delete this shift?')" class="m-0">
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
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                                        No shifts found. Click "Add Shift" to create one.
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
        <div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="shiftModalTitle">
                            <i class="fa-solid fa-clock text-primary me-2"></i> Shift
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="shiftForm" method="POST" action="{{ route('shifts.store') }}">
                        @csrf
                        <input type="hidden" id="shiftMethod" value="">

                        <div class="modal-body">
                            @include('shifts._form', ['shift' => null])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fa-solid fa-xmark me-1"></i> Close
                            </button>
                            <button type="submit" class="btn btn-primary" id="shiftSubmitBtn">
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
            const modalEl = document.getElementById('shiftModal');
            const titleEl = document.getElementById('shiftModalTitle');
            const formEl = document.getElementById('shiftForm');
            const submitBtn = document.getElementById('shiftSubmitBtn');
            const methodInp = document.getElementById('shiftMethod');
            const nameInp = document.getElementById('shift_name');
            const fromInp = document.getElementById('shift_time_from');
            const toInp = document.getElementById('shift_time_to');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerHTML = '<i class="fa-solid fa-clock text-primary me-2"></i> ' + (title || 'Shift');
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Update';
                    nameInp.value = trigger.getAttribute('data-name') || '';
                    fromInp.value = trigger.getAttribute('data-time-from') || '';
                    toInp.value = trigger.getAttribute('data-time-to') || '';
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Save';
                    nameInp.value = '';
                    fromInp.value = '';
                    toInp.value = '';
                }
            });
        });
    </script>
@endpush
