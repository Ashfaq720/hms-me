@extends('backend.layouts.master')
@section('title', 'Storage Locations')

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
                        <h1 class="app-page-title">Storage Location</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbStorageModal" data-mode="create" data-title="Create Storage Location"
                        data-action="{{ route('bb.storage-locations.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Location
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Storage Location List</h6>
                                <div id="dt_bb_storage_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_storage" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Capacity</th>
                                            <th>Temp Monitoring</th>
                                            <th>Device ID</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->location_code }}</td>
                                                <td>{{ $row->location_name }}</td>
                                                <td>{{ $row->location_type }}</td>
                                                <td>{{ $row->capacity_units }}</td>
                                                <td>
                                                    @if ($row->temperature_monitoring_required)
                                                        <span class="badge bg-info text-dark">Yes</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $row->device_id ?? '-' }}</td>
                                                <td>
                                                    @if ($row->status === 'ACTIVE')
                                                        <span class="badge bg-success">ACTIVE</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">{{ $row->status }}</span>
                                                    @endif
                                                </td>

                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bbStorageModal"
                                                            data-mode="edit" data-title="Edit Storage Location"
                                                            data-action="{{ route('bb.storage-locations.update', $row->id) }}"
                                                            data-name="{{ $row->location_name }}"
                                                            data-type="{{ $row->location_type }}"
                                                            data-capacity="{{ $row->capacity_units }}"
                                                            data-monitor="{{ $row->temperature_monitoring_required ? 1 : 0 }}"
                                                            data-device="{{ $row->device_id }}"
                                                            data-status="{{ $row->status }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.storage-locations.destroy', $row->id) }}"
                                                            onsubmit="return confirm('Move to MAINTENANCE?')"
                                                            class="m-0">
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
    <div class="modal fade" id="bbStorageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbStorageModalTitle">Storage Location</h5>
                </div>

                <form id="bbStorageForm" method="POST" action="{{ route('bb.storage-locations.store') }}">
                    @csrf
                    <input type="hidden" id="bbStorageMethod" value="">

                    <div class="modal-body">
                        @include('master-data.storage_locations._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbStorageSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('bbStorageModal');
            const titleEl = document.getElementById('bbStorageModalTitle');
            const formEl = document.getElementById('bbStorageForm');
            const submitBtn = document.getElementById('bbStorageSubmitBtn');
            const methodInp = document.getElementById('bbStorageMethod');

            const nameInp = document.getElementById('bb_sl_name');
            const typeInp = document.getElementById('bb_sl_type');
            const capInp = document.getElementById('bb_sl_capacity');
            const monInp = document.getElementById('bb_sl_monitor');
            const devInp = document.getElementById('bb_sl_device');
            const statInp = document.getElementById('bb_sl_status');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode = trigger.getAttribute('data-mode');
                const title = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Storage Location';
                formEl.action = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value = 'PUT';
                    submitBtn.innerText = 'Update';

                    nameInp.value = trigger.getAttribute('data-name') || '';
                    typeInp.value = trigger.getAttribute('data-type') || 'BLOOD_BANK';
                    capInp.value = trigger.getAttribute('data-capacity') || 0;
                    monInp.value = trigger.getAttribute('data-monitor') || 0;
                    devInp.value = trigger.getAttribute('data-device') || '';
                    statInp.value = trigger.getAttribute('data-status') || 'ACTIVE';
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value = '';
                    submitBtn.innerText = 'Save';

                    nameInp.value = '';
                    typeInp.value = 'BLOOD_BANK';
                    capInp.value = 0;
                    monInp.value = 0;
                    devInp.value = '';
                    statInp.value = 'ACTIVE';
                }
            });
        });
    </script>
@endpush
