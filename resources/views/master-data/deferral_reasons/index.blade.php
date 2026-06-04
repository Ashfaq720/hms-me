@extends('backend.layouts.master')
@section('title', 'Deferral Reasons')

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- LEFT --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bloodbank_setup')
            </div>

            {{-- RIGHT --}}
            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Deferral Reason</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbDefModal" data-mode="create" data-title="Create Deferral Reason"
                        data-action="{{ route('bb.deferral-reasons.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Reason
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Deferral Reason List</h6>
                                <div id="dt_bb_deferrals_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_deferrals" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Reason</th>
                                            <th>Type</th>
                                            <th>Duration (days)</th>
                                            <th>Reg. Ref</th>
                                            <th>Status</th>
                                            <th>Locked</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->deferral_code }}</td>
                                                <td>{{ $row->deferral_reason }}</td>
                                                <td>{{ $row->deferral_type }}</td>
                                                <td>{{ $row->deferral_type === 'TEMP' ? $row->default_duration_days ?? '-' : '-' }}
                                                </td>
                                                <td>{{ $row->regulatory_reference ?? '-' }}</td>

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
                                                            data-bs-toggle="modal" data-bs-target="#bbDefModal"
                                                            data-mode="edit" data-title="Edit Deferral Reason"
                                                            data-action="{{ route('bb.deferral-reasons.update', $row->id) }}"
                                                            data-reason="{{ $row->deferral_reason }}"
                                                            data-type="{{ $row->deferral_type }}"
                                                            data-duration="{{ $row->default_duration_days }}"
                                                            data-ref="{{ $row->regulatory_reference }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}"
                                                            data-locked="{{ $row->is_locked ? 1 : 0 }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.deferral-reasons.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Set inactive?')" class="m-0">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST"
                                                            action="{{ route('bb.deferral-reasons.lock', $row->id) }}"
                                                            onsubmit="return confirm('Lock this deferral reason?')"
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
                                                <td colspan="9" class="text-center">No data found</td>
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
    <div class="modal fade" id="bbDefModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbDefModalTitle">Deferral Reason</h5>
                </div>

                <form id="bbDefForm" method="POST" action="{{ route('bb.deferral-reasons.store') }}">
                    @csrf
                    <input type="hidden" id="bbDefMethod" value="">

                    <div class="modal-body">
                        @include('master-data.deferral_reasons._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbDefSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbDefModal');
            const titleEl = document.getElementById('bbDefModalTitle');
            const formEl = document.getElementById('bbDefForm');
            const submitBtn = document.getElementById('bbDefSubmitBtn');
            const methodInp = document.getElementById('bbDefMethod');

            const reasonInp = document.getElementById('bb_def_reason');
            const typeInp = document.getElementById('bb_def_type');
            const durInp = document.getElementById('bb_def_duration');
            const refInp = document.getElementById('bb_def_ref');
            const activeInp = document.getElementById('bb_def_active');
            const lockMsg = document.getElementById('bb_def_lock_msg');

            function setDisabled(disabled) {
                [reasonInp, typeInp, durInp, refInp, activeInp].forEach(el => {
                    if (!el) return;
                    if (disabled) el.setAttribute('disabled', 'disabled');
                    else el.removeAttribute('disabled');
                });
                if (lockMsg) lockMsg.style.display = disabled ? 'block' : 'none';
                if (submitBtn) submitBtn.disabled = disabled;
            }

            // show/hide duration based on type
            function toggleDuration() {
                const isTemp = (typeInp.value === 'TEMP');
                durInp.closest('.col-md-3').style.display = isTemp ? '' : 'none';
                if (!isTemp) durInp.value = '';
            }

            typeInp.addEventListener('change', toggleDuration);

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Deferral Reason';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    reasonInp.value = trigger.getAttribute('data-reason') || '';
                    typeInp.value = trigger.getAttribute('data-type') || 'TEMP';
                    durInp.value = trigger.getAttribute('data-duration') || '';
                    refInp.value = trigger.getAttribute('data-ref') || '';
                    activeInp.value = trigger.getAttribute('data-active') || 1;

                    const locked = (trigger.getAttribute('data-locked') || '0') === '1';
                    setDisabled(locked);
                    toggleDuration();
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    reasonInp.value = '';
                    typeInp.value = 'TEMP';
                    durInp.value = '';
                    refInp.value = '';
                    activeInp.value = 1;

                    setDisabled(false);
                    toggleDuration();
                }
            });
        });
    </script>
@endpush
