@extends('backend.layouts.master')
@section('title', 'Blood Group')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Blood Bank Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bloodbank_setup')
            </div>

            {{-- RIGHT: Content --}}
            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Blood Group</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbBloodGroupModal" data-mode="create" data-title="Create Blood Group"
                        data-action="{{ route('bb.blood-groups.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Blood Group
                    </button>
                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Blood Group List</h6>
                                <div id="dt_bb_blood_groups_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_blood_groups" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>ABO</th>
                                            <th>Rh</th>
                                            <th>Display Name</th>
                                            <th>Status</th>
                                            <th>Locked</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->code }}</td>
                                                <td>{{ $row->abo_group }}</td>
                                                <td>{{ $row->rh_factor === 'POS' ? '+' : '-' }}</td>
                                                <td>{{ $row->display_name }}</td>

                                                <td>
                                                    @if ($row->is_active)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($row->is_locked)
                                                        <span class="badge bg-danger">Locked</span>
                                                    @else
                                                        <span class="badge bg-info">Open</span>
                                                    @endif
                                                </td>

                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">

                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bbBloodGroupModal"
                                                            data-mode="edit" data-title="Edit Blood Group"
                                                            data-action="{{ route('bb.blood-groups.update', $row->id) }}"
                                                            data-abo="{{ $row->abo_group }}" data-rh="{{ $row->rh_factor }}"
                                                            data-display="{{ $row->display_name }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}"
                                                            data-locked="{{ $row->is_locked ? 1 : 0 }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.blood-groups.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Set inactive?')" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST"
                                                            action="{{ route('bb.blood-groups.lock', $row->id) }}"
                                                            onsubmit="return confirm('Lock this blood group?')"
                                                            class="m-0">
                                                            @csrf
                                                            <button class="btn btn-sm btn-dark" type="submit"
                                                                @disabled($row->is_locked)>
                                                                <i class="fa-solid fa-lock"></i>
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-3 pb-3">
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="bbBloodGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbBloodGroupModalTitle">Blood Group</h5>
                </div>

                <form id="bbBloodGroupForm" method="POST" action="{{ route('bb.blood-groups.store') }}">
                    @csrf

                    {{-- IMPORTANT: keep it without name by default --}}
                    <input type="hidden" id="bbBloodGroupMethod" value="">

                    <div class="modal-body">
                        @include('master-data.blood_groups._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbBloodGroupSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbBloodGroupModal');
            const titleEl = document.getElementById('bbBloodGroupModalTitle');
            const formEl = document.getElementById('bbBloodGroupForm');
            const submitBtn = document.getElementById('bbBloodGroupSubmitBtn');
            const methodInp = document.getElementById('bbBloodGroupMethod');

            const aboInp = document.getElementById('bb_bg_abo');
            const rhInp = document.getElementById('bb_bg_rh');
            const displayInp = document.getElementById('bb_bg_display');
            const activeInp = document.getElementById('bb_bg_active');
            const lockMsg = document.getElementById('bb_bg_lock_msg');

            function setDisabled(disabled) {
                // when locked: disable form fields + disable submit
                [aboInp, rhInp, displayInp, activeInp].forEach(el => {
                    if (!el) return;
                    if (disabled) el.setAttribute('disabled', 'disabled');
                    else el.removeAttribute('disabled');
                });
                if (lockMsg) lockMsg.style.display = disabled ? 'block' : 'none';
                if (submitBtn) submitBtn.disabled = disabled;
            }

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode'); // create/edit
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Blood Group';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    aboInp.value = trigger.getAttribute('data-abo') || '';
                    rhInp.value = trigger.getAttribute('data-rh') || '';
                    displayInp.value = trigger.getAttribute('data-display') || '';
                    activeInp.value = trigger.getAttribute('data-active') || 1;

                    // business rule: ABO & Rh cannot change after used -> keep disabled in edit
                    aboInp.setAttribute('disabled', 'disabled');
                    rhInp.setAttribute('disabled', 'disabled');

                    const locked = (trigger.getAttribute('data-locked') || '0') === '1';
                    if (locked) {
                        setDisabled(true);
                    } else {
                        // allow display_name + active update
                        displayInp.removeAttribute('disabled');
                        activeInp.removeAttribute('disabled');
                        if (lockMsg) lockMsg.style.display = 'none';
                        submitBtn.disabled = false;
                    }
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    aboInp.value = '';
                    rhInp.value = '';
                    displayInp.value = '';
                    activeInp.value = 1;

                    // create mode allow all
                    aboInp.removeAttribute('disabled');
                    rhInp.removeAttribute('disabled');
                    displayInp.removeAttribute('disabled');
                    activeInp.removeAttribute('disabled');
                    if (lockMsg) lockMsg.style.display = 'none';
                    submitBtn.disabled = false;
                }
            });
        });
    </script>
@endpush
