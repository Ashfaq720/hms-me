@extends('backend.layouts.master')

@section('title', 'Doctor Fees')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- RIGHT: Doctor Fees Content --}}
            <div class="col-lg-12 col-md-12">
                {{-- Page Head --}}
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title">Doctor Fees</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bedGroupModal" data-mode="create" data-title="Create Doctor Fees"
                        data-action="{{ route('doctor-fees.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Doctor Fees
                    </button>

                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" id="validationAlert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Table --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Doctor Fees List</h6>
                                <div id="dt_patients_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_patients" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Doctor Name</th>
                                            <th>First Visit Fee</th>
                                            <th>Follow Up Window</th>
                                            <th>Follow Up Fee</th>
                                            <th>Ipd Visit Fee</th>
                                            <th>OPD Visit Fee</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($data as $p)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>

                                                <td>
                                                    <div class="fw-bold">
                                                        {{ $p->doctor->name ?? 'N/A' }}
                                                    </div>
                                                </td>

                                                <td>{{ $p->first_visit_fee ? number_format($p->first_visit_fee, 2) : '-' }}
                                                </td>

                                                <td>
                                                    {{ $p->follow_up_window ? $p->follow_up_window . ' Days' : '-' }}
                                                </td>

                                                <td>
                                                    {{ $p->follow_up_fee ? number_format($p->follow_up_fee, 2) : '-' }}
                                                </td>

                                                <td>
                                                    {{ $p->ipd_visit_fee ? number_format($p->ipd_visit_fee, 2) : '-' }}
                                                </td>

                                                <td>
                                                    {{ $p->opd_visit_fee ? number_format($p->opd_visit_fee, 2) : '-' }}
                                                </td>

                                                <td>
                                                    @if ($p->is_active == 1)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>

                                                <td class="text-nowrap">
                                                    <div class="d-flex gap-1">
                                                        {{-- Edit --}}
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal" data-bs-target="#bedGroupModal"
                                                            data-mode="edit"
                                                            data-action="{{ route('doctor-fees.update', $p->id) }}"
                                                            data-doctor_id="{{ $p->doctor_id }}"
                                                            data-first_visit_fee="{{ $p->first_visit_fee }}"
                                                            data-follow_up_window="{{ $p->follow_up_window }}"
                                                            data-follow_up_fee="{{ $p->follow_up_fee }}"
                                                            data-ipd_visit_fee="{{ $p->ipd_visit_fee }}"
                                                            data-opd_visit_fee="{{ $p->opd_visit_fee }}" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        {{-- Delete --}}
                                                        <form method="POST"
                                                            action="{{ route('doctor-fees.destroy', $p->id) }}"
                                                            onsubmit="return confirm('Delete this record?')" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    No doctor fees found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>

                                {{-- If you paginate --}}
                                <div class="mt-3 pb-3">
                                    {{-- {{ $floors->links() }} --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Doctor Fees Create/Edit Modal -->
        <div class="modal fade" id="bedGroupModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="bedGroupModalTitle">Doctor Fees</h5>
                    </div>

                    <form id="bedGroupForm" method="POST" action="{{ route('doctor-fees.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- IMPORTANT: keep it without name by default --}}
                        <input type="hidden" id="bedGroupMethod" value="">

                        <div class="modal-body">
                            @include('doctor-fees._form', [
                                'data' => null,
                                'doctors' => $doctors,
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

                // Make sure these IDs exist in bed._form
                const docSel = document.getElementById('doctor_id');
                const firstVInp = document.getElementById('first_visit_fee');
                const followUpWindowInp = document.getElementById('follow_up_window');
                const followUpFeeInp = document.getElementById('follow_up_fee');
                const ipdVisitFeeInp = document.getElementById('ipd_visit_fee');
                const opdVisitFeeInp = document.getElementById('opd_visit_fee');

                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const mode = trigger.getAttribute('data-mode'); // create/edit
                    const title = trigger.getAttribute('data-title');
                    const action = trigger.getAttribute('data-action');

                    titleEl.innerText = title || 'Doctor Fees';
                    formEl.action = action || formEl.action;

                    if (mode === 'edit') {
                        methodInp.setAttribute('name', '_method');
                        methodInp.value = 'PUT';
                        submitBtn.innerText = 'Update';

                        docSel.value = trigger.getAttribute('data-doctor_id') || '';
                        firstVInp.value = trigger.getAttribute('data-first_visit_fee') || '';
                        followUpWindowInp.value = trigger.getAttribute('data-follow_up_window') || '';
                        followUpFeeInp.value = trigger.getAttribute('data-follow_up_fee') || '';
                        ipdVisitFeeInp.value = trigger.getAttribute('data-ipd_visit_fee') || '';
                        opdVisitFeeInp.value = trigger.getAttribute('data-opd_visit_fee') || '';
                    } else {
                        methodInp.removeAttribute('name');
                        methodInp.value = '';
                        submitBtn.innerText = 'Save';

                        // Clear all inputs
                        docSel.value = '';
                        firstVInp.value = '';
                        followUpWindowInp.value = '';
                        followUpFeeInp.value = '';
                        ipdVisitFeeInp.value = '';
                        opdVisitFeeInp.value = '';
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const alert = document.getElementById('validationAlert');
                if (alert) {
                    setTimeout(() => {
                        alert.classList.add('fade');
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 500);
                    }, 3000);
                }
            })
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
