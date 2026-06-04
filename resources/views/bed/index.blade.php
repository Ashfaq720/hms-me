@extends('backend.layouts.master')

@section('title', 'Bed')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- LEFT: Bed Setup Menu --}}
            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.bed_setup')
            </div>

            {{-- RIGHT: Bed Content --}}
            <div class="col-lg-9 col-md-8">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Bed</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bedGroupModal" data-mode="create" data-title="Create Bed"
                        data-action="{{ route('beds.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Bed
                    </button>



                </div>

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Bed List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Bed</th>
                                            <th>Room</th>
                                            <th>Type</th>
                                            <th>Ward / Floor</th>
                                            <th class="text-end">Daily Total</th>
                                            <th>Default Package</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($data as $p)
                                            @php
                                                $statusBadge = [
                                                    'available'   => 'bg-success',
                                                    'occupied'    => 'bg-danger',
                                                    'reserved'    => 'bg-warning text-dark',
                                                    'maintenance' => 'bg-secondary',
                                                    'cleaning'    => 'bg-info',
                                                ][$p->status ?? 'available'] ?? 'bg-secondary';
                                            @endphp
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>
                                                    <div class="fw-bold">{{ $p->name }}</div>
                                                    @if ($p->bed_no) <small class="text-muted">#{{ $p->bed_no }}</small> @endif
                                                </td>
                                                <td>
                                                    @if ($p->room)
                                                        <strong>{{ $p->room->room_no }}</strong>
                                                        <br><small class="text-muted">{{ \App\Models\Room::CLASSES[$p->room->room_class] ?? '' }}</small>
                                                    @else
                                                        <small class="text-muted">—</small>
                                                    @endif
                                                </td>
                                                <td>{{ $p->bedType->name ?? '—' }}</td>
                                                <td>
                                                    {{ $p->bedGroup->name ?? '—' }}
                                                    <br><small class="text-muted">{{ optional(optional($p->bedGroup)->floor)->name ?? '—' }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <strong>৳ {{ number_format($p->totalDailyRate(), 0) }}</strong>
                                                    <br><small class="text-muted">
                                                        bed {{ (int) $p->rent }}
                                                        @if ($p->room) +room {{ (int) $p->room->room_rent }} @endif
                                                        @if ($p->amenity_charge > 0) +amn {{ (int) $p->amenity_charge }} @endif
                                                        @if ($p->nursing_charge > 0) +nur {{ (int) $p->nursing_charge }} @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    @if ($p->defaultPackage)
                                                        <span class="badge bg-primary bg-opacity-15 text-primary">
                                                            <i class="bi bi-box-seam"></i> {{ $p->defaultPackage->name }}
                                                        </span>
                                                    @else
                                                        <small class="text-muted">—</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($p->status ?? 'available') }}</span>
                                                    @if ($p->is_reserved) <span class="badge bg-warning text-dark">Reserved</span> @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-start">
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bedGroupModal"
                                                            data-mode="edit" data-title="Edit Bed"
                                                            data-action="{{ route('beds.update', $p->id) }}"
                                                            data-name="{{ $p->name }}"
                                                            data-bed_no="{{ $p->bed_no }}"
                                                            data-rent="{{ $p->rent }}"
                                                            data-amenity_charge="{{ $p->amenity_charge }}"
                                                            data-nursing_charge="{{ $p->nursing_charge }}"
                                                            data-bed_type_id="{{ $p->bed_type_id }}"
                                                            data-bed_group_id="{{ $p->bed_group_id }}"
                                                            data-room_id="{{ $p->room_id }}"
                                                            data-status="{{ $p->status }}"
                                                            data-default_package_id="{{ $p->default_package_id }}"
                                                            data-is_reserved="{{ (int) $p->is_reserved }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form title="Delete" method="POST"
                                                            action="{{ route('beds.destroy', $p) }}"
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
                                                <td colspan="10" class="text-center">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                @if (method_exists($data, 'links'))
                                    <div class="mt-3 pb-3 px-2">
                                        <small class="text-muted">Showing {{ $data->firstItem() ?? 0 }} – {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} beds</small>
                                        {{ $data->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bed Create/Edit Modal -->
        <div class="modal fade" id="bedGroupModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="bedGroupModalTitle">Bed</h5>
                    </div>

                    <form id="bedGroupForm" method="POST" action="{{ route('beds.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- IMPORTANT: keep it without name by default --}}
                        <input type="hidden" id="bedGroupMethod" value="">

                        <div class="modal-body">
                            @include('bed._form', [
                                'data' => null,
                                'bedTypes' => $bedTypes,
                                'bedGroups' => $bedGroups,
                            ])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="bedGroupSubmitBtn">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('bedGroupModal');
                const titleEl = document.getElementById('bedGroupModalTitle');
                const formEl = document.getElementById('bedGroupForm');
                const submitBtn = document.getElementById('bedGroupSubmitBtn');
                const methodInp = document.getElementById('bedGroupMethod');

                const FIELD_MAP = {
                    name: 'bed_group_name',
                    bed_no: '[name="bed_no"]',
                    rent: 'bed_rent',
                    amenity_charge: '[name="amenity_charge"]',
                    nursing_charge: '[name="nursing_charge"]',
                    bed_type_id: 'bed_type_id',
                    bed_group_id: 'bed_group_id',
                    room_id: '[name="room_id"]',
                    status: '[name="status"]',
                    default_package_id: '[name="default_package_id"]',
                    is_reserved: 'bed_is_reserved',
                };

                const findField = (key) => {
                    const sel = FIELD_MAP[key];
                    if (!sel) return null;
                    return sel.startsWith('[') ? formEl.querySelector(sel) : document.getElementById(sel);
                };

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode');
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Bed';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';
                        submitBtn.innerText = 'Update';

                        Object.keys(FIELD_MAP).forEach(key => {
                            const el = findField(key);
                            if (el) el.value = trigger.getAttribute('data-' + key) ?? '';
                        });
                    } else {
                        methodInp.removeAttribute('name');
                        methodInp.value = '';
                        submitBtn.innerText = 'Save';

                        Object.keys(FIELD_MAP).forEach(key => {
                            const el = findField(key);
                            if (!el) return;
                            if (key === 'rent' || key === 'amenity_charge' || key === 'nursing_charge') el.value = '0';
                            else if (key === 'status') el.value = 'available';
                            else if (key === 'is_reserved') el.value = '0';
                            else el.value = '';
                        });
                    }
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            /* Make modal centered perfectly */
            #bedGroupModal .modal-dialog {
                margin-top: 0 !important;
            }

            /* Modern modal box */
            #bedGroupModal .modal-content {
                border: 0 !important;
                border-radius: 14px !important;
                overflow: hidden !important;
                box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18) !important;
                background: #fff !important;
            }

            /* Fix header spacing + line */
            #bedGroupModal .modal-header {
                padding: 16px 20px !important;
                border-bottom: 1px solid #eef1f6 !important;
                align-items: center !important;
            }

            #bedGroupModal .modal-title {
                font-size: 18px !important;
                font-weight: 600 !important;
                margin: 0 !important;
            }

            /* Fix body spacing */
            #bedGroupModal .modal-body {
                padding: 20px !important;
            }

            /* Fix footer spacing + line */
            #bedGroupModal .modal-footer {
                padding: 14px 20px !important;
                border-top: 1px solid #eef1f6 !important;
            }

            /* Backdrop nicer */
            .modal-backdrop.show {
                opacity: .55 !important;
            }

            /* Optional: close button alignment */
            #bedGroupModal .btn-close {
                margin: 0 !important;
            }
        </style>
    @endpush
