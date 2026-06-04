@extends('backend.layouts.master')
@section('title', 'Component')

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
                        <h1 class="app-page-title">Component</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbComponentModal" data-mode="create" data-title="Create Component"
                        data-action="{{ route('bb.components.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Component
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Component List</h6>
                                <div id="dt_bb_components_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_components" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Component Name</th>
                                            <th>Derived From</th>
                                            <th>Shelf Life</th>
                                            <th>Storage</th>
                                            <th>Volume Range (ml)</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->component_code }}</td>
                                                <td>{{ $row->component_name }}</td>
                                                <td>{{ $row->derived_from }}</td>
                                                <td>{{ $row->shelf_life_value }} {{ strtolower($row->shelf_life_unit) }}
                                                </td>
                                                <td>{{ $row->storage_requirement }}</td>
                                                <td>{{ $row->min_volume_ml ?? '-' }} - {{ $row->max_volume_ml ?? '-' }}</td>
                                                <td>
                                                    @if ($row->is_active)
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>

                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bbComponentModal"
                                                            data-mode="edit" data-title="Edit Component"
                                                            data-action="{{ route('bb.components.update', $row->id) }}"
                                                            data-name="{{ $row->component_name }}"
                                                            data-derived="{{ $row->derived_from }}"
                                                            data-shelfvalue="{{ $row->shelf_life_value }}"
                                                            data-shelfunit="{{ $row->shelf_life_unit }}"
                                                            data-storage="{{ $row->storage_requirement }}"
                                                            data-minvol="{{ $row->min_volume_ml }}"
                                                            data-maxvol="{{ $row->max_volume_ml }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.components.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Set inactive?')" class="m-0">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
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
    <div class="modal fade" id="bbComponentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbComponentModalTitle">Component</h5>
                </div>

                <form id="bbComponentForm" method="POST" action="{{ route('bb.components.store') }}">
                    @csrf
                    <input type="hidden" id="bbComponentMethod" value="">

                    <div class="modal-body">
                        @include('master-data.components._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbComponentSubmitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbComponentModal');
            const titleEl = document.getElementById('bbComponentModalTitle');
            const formEl = document.getElementById('bbComponentForm');
            const submitBtn = document.getElementById('bbComponentSubmitBtn');
            const methodInp = document.getElementById('bbComponentMethod');

            const nameInp = document.getElementById('bb_cmp_name');
            const derivedInp = document.getElementById('bb_cmp_derived');
            const shelfVal = document.getElementById('bb_cmp_shelf_value');
            const shelfUnit = document.getElementById('bb_cmp_shelf_unit');
            const storage = document.getElementById('bb_cmp_storage');
            const minVol = document.getElementById('bb_cmp_min_vol');
            const maxVol = document.getElementById('bb_cmp_max_vol');
            const activeInp = document.getElementById('bb_cmp_active');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Component';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    nameInp.value = trigger.getAttribute('data-name') || '';
                    derivedInp.value = trigger.getAttribute('data-derived') || 'WHOLE_BLOOD';
                    shelfVal.value = trigger.getAttribute('data-shelfvalue') || '';
                    shelfUnit.value = trigger.getAttribute('data-shelfunit') || 'DAYS';
                    storage.value = trigger.getAttribute('data-storage') || 'REFRIGERATOR';
                    minVol.value = trigger.getAttribute('data-minvol') || '';
                    maxVol.value = trigger.getAttribute('data-maxvol') || '';
                    activeInp.value = trigger.getAttribute('data-active') || 1;
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    nameInp.value = '';
                    derivedInp.value = 'WHOLE_BLOOD';
                    shelfVal.value = '';
                    shelfUnit.value = 'DAYS';
                    storage.value = 'REFRIGERATOR';
                    minVol.value = '';
                    maxVol.value = '';
                    activeInp.value = 1;
                }
            });
        });
    </script>
@endpush
