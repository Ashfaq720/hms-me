@extends('backend.layouts.master')

@section('title', 'Room')

@section('content')
<div class="container-fluid">

    @include('_partials.bed_setup_tabs')

    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
        <div>
            <h1 class="app-page-title mb-0">Rooms <span class="badge bg-light text-dark border ms-2">{{ $rooms->count() }}</span></h1>
            <div class="text-muted small">Floor &rarr; <strong>Room</strong> &rarr; Bed Group &rarr; Bed</div>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#roomModal" data-mode="create" data-title="Create Room"
            data-action="{{ route('rooms.store') }}">
            <i class="bi bi-plus-lg me-1"></i> Add Room
        </button>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Room Name</th>
                            <th>Floor</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:120px;" class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $i => $p)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $p->name }}</td>
                                <td>
                                    @if($p->floor)
                                        <span class="badge bg-light text-dark border">{{ $p->floor->name }}</span>
                                    @else <span class="text-muted small">—</span> @endif
                                </td>
                                <td>
                                    @if ($p->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal" data-bs-target="#roomModal"
                                        data-mode="edit" data-title="Edit Room"
                                        data-action="{{ route('rooms.update', $p) }}"
                                        data-name="{{ $p->name }}"
                                        data-floor-id="{{ $p->floor_id }}"
                                        data-is-active="{{ $p->is_active ? 1 : 0 }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('rooms.destroy', $p) }}"
                                        onsubmit="return confirm('Delete this room?')" class="d-inline m-0">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-door-open fs-2 d-block mb-2 opacity-50"></i>
                                No rooms yet. Click <strong>Add Room</strong> to create one.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title" id="roomModalTitle"><i class="bi bi-door-open me-1"></i> Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="roomForm" method="POST" action="{{ route('rooms.store') }}">
                    @csrf
                    <input type="hidden" id="roomMethod" value="">
                    <div class="modal-body">
                        @include('room._form', ['room' => null, 'floors' => $floors])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="roomSubmitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl   = document.getElementById('roomModal');
    const titleEl   = document.getElementById('roomModalTitle');
    const formEl    = document.getElementById('roomForm');
    const submitBtn = document.getElementById('roomSubmitBtn');
    const methodInp = document.getElementById('roomMethod');
    const nameInp   = document.getElementById('room_name');
    const floorInp  = document.getElementById('room_floor_id');
    const activeInp = document.getElementById('room_is_active');

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const mode   = trigger.getAttribute('data-mode');
        const title  = trigger.getAttribute('data-title');
        const action = trigger.getAttribute('data-action');

        titleEl.innerHTML = '<i class="bi bi-door-open me-1"></i> ' + (title || 'Room');
        formEl.action = action || formEl.action;

        if (mode === 'edit') {
            methodInp.setAttribute('name', '_method');
            methodInp.value = 'PUT';
            submitBtn.innerText = 'Update';
            if (nameInp)  nameInp.value  = trigger.getAttribute('data-name') || '';
            if (floorInp) floorInp.value = trigger.getAttribute('data-floor-id') || '';
            if (activeInp) activeInp.checked = trigger.getAttribute('data-is-active') === '1';
        } else {
            methodInp.removeAttribute('name');
            methodInp.value = '';
            submitBtn.innerText = 'Save';
            if (nameInp)  nameInp.value = '';
            if (floorInp) floorInp.value = '';
            if (activeInp) activeInp.checked = true;
        }
    });
});
</script>
@endpush
