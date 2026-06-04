@extends('backend.layouts.master')
@section('title', 'Blood Bags')

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
                        <h1 class="app-page-title">Blood Bag</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbBagModal" data-mode="create" data-title="Create Blood Bag"
                        data-action="{{ route('bb.blood-bags.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Blood Bag
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Blood Bag List</h6>
                                <div id="dt_bb_bags_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_bags" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Type</th>
                                            <th>Volume (ml)</th>
                                            <th>Allowed Components</th>
                                            <th>Status</th>
                                            <th>Locked</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->bag_code }}</td>
                                                <td>{{ $row->bag_type }}</td>
                                                <td>{{ $row->volume_ml }}</td>

                                                <td>
                                                    @if ($row->components && $row->components->count())
                                                        @foreach ($row->components as $c)
                                                            <span
                                                                class="badge bg-info text-dark me-1">{{ $c->component_name }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

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
                                                            data-bs-toggle="modal" data-bs-target="#bbBagModal"
                                                            data-mode="edit" data-title="Edit Blood Bag"
                                                            data-action="{{ route('bb.blood-bags.update', $row->id) }}"
                                                            data-type="{{ $row->bag_type }}"
                                                            data-volume="{{ $row->volume_ml }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}"
                                                            data-locked="{{ $row->is_locked ? 1 : 0 }}"
                                                            data-components="{{ $row->components?->pluck('id')->implode(',') }}"
                                                            title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.blood-bags.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Set inactive?')" class="m-0">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST"
                                                            action="{{ route('bb.blood-bags.lock', $row->id) }}"
                                                            onsubmit="return confirm('Lock this blood bag?')"
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
    <div class="modal fade" id="bbBagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbBagModalTitle">Blood Bag</h5>
                </div>

                <form id="bbBagForm" method="POST" action="{{ route('bb.blood-bags.store') }}">
                    @csrf
                    <input type="hidden" id="bbBagMethod" value="">

                    <div class="modal-body">
                        @include('master-data.blood_bags._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbBagSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbBagModal');
            const titleEl = document.getElementById('bbBagModalTitle');
            const formEl = document.getElementById('bbBagForm');
            const submitBtn = document.getElementById('bbBagSubmitBtn');
            const methodInp = document.getElementById('bbBagMethod');

            const typeInp = document.getElementById('bb_bag_type');
            const volInp = document.getElementById('bb_bag_volume');
            const compsSel = document.getElementById('bb_bag_components');
            const activeInp = document.getElementById('bb_bag_active');
            const lockMsg = document.getElementById('bb_bag_lock_msg');

            function setSelectedComponents(ids) {
                const set = new Set((ids || '').split(',').filter(Boolean));
                Array.from(compsSel.options).forEach(opt => {
                    opt.selected = set.has(String(opt.value));
                });
            }

            function setDisabled(disabled) {
                [typeInp, volInp, compsSel, activeInp].forEach(el => {
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

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Blood Bag';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    typeInp.value = trigger.getAttribute('data-type') || 'SINGLE';
                    volInp.value = trigger.getAttribute('data-volume') || '';
                    activeInp.value = trigger.getAttribute('data-active') || 1;
                    setSelectedComponents(trigger.getAttribute('data-components') || '');

                    const locked = (trigger.getAttribute('data-locked') || '0') === '1';
                    setDisabled(locked);
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    typeInp.value = 'SINGLE';
                    volInp.value = '';
                    activeInp.value = 1;
                    setSelectedComponents('');

                    setDisabled(false);
                }
            });
        });
    </script>
@endpush
