@extends('backend.layouts.master')
@section('title', 'Blood Donors')

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
                        <h1 class="app-page-title">Blood Donor</h1>
                    </div>

                    <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                        data-bs-target="#bbDonorModal" data-mode="create" data-title="Create Blood Donor"
                        data-action="{{ route('bb.blood-donors.store') }}">
                        <i class="fi fi-rr-plus me-1"></i> Add Donor
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div
                                class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">Blood Donor List</h6>
                                <div id="dt_bb_donors_Search"></div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table id="dt_bb_donors" class="table display table-row-rounded">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>DOB</th>
                                            <th>Gender</th>
                                            <th>Blood Group</th>
                                            <th>Father's Name</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($items as $row)
                                            <tr>
                                                <td>{{ $row->id }}</td>
                                                <td class="fw-bold">{{ $row->donor_code }}</td>
                                                <td>{{ $row->name }}</td>
                                                <td>{{ $row->dob->format('d-m-Y') }}</td>
                                                <td>{{ ucfirst(strtolower($row->gender)) }}</td>
                                                <td>{{ $row->bloodGroup->display_name ?? '-' }}</td>
                                                <td>{{ $row->father_name ?? '-' }}</td>
                                                <td>{{ $row->contact_no }}</td>

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
                                                            data-bs-toggle="modal" data-bs-target="#bbDonorModal"
                                                            data-mode="edit" data-title="Edit Blood Donor"
                                                            data-action="{{ route('bb.blood-donors.update', $row->id) }}"
                                                            data-name="{{ $row->name }}"
                                                            data-dob="{{ $row->dob->format('Y-m-d') }}"
                                                            data-gender="{{ $row->gender }}"
                                                            data-blood-group="{{ $row->blood_group_id }}"
                                                            data-father="{{ $row->father_name }}"
                                                            data-contact="{{ $row->contact_no }}"
                                                            data-address="{{ $row->address }}"
                                                            data-active="{{ $row->is_active ? 1 : 0 }}"
                                                            title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('bb.blood-donors.destroy', $row->id) }}"
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
                                                <td colspan="10" class="text-center">No data found</td>
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
    <div class="modal fade" id="bbDonorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title" id="bbDonorModalTitle">Blood Donor</h5>
                </div>

                <form id="bbDonorForm" method="POST" action="{{ route('bb.blood-donors.store') }}">
                    @csrf
                    <input type="hidden" id="bbDonorMethod" value="">

                    <div class="modal-body">
                        @include('master-data.blood_donors._form', ['item' => null])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="bbDonorSubmitBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl   = document.getElementById('bbDonorModal');
            const titleEl   = document.getElementById('bbDonorModalTitle');
            const formEl    = document.getElementById('bbDonorForm');
            const submitBtn = document.getElementById('bbDonorSubmitBtn');
            const methodInp = document.getElementById('bbDonorMethod');

            const nameInp    = document.getElementById('bb_donor_name');
            const dobInp     = document.getElementById('bb_donor_dob');
            const genderInp  = document.getElementById('bb_donor_gender');
            const bgInp      = document.getElementById('bb_donor_blood_group');
            const fatherInp  = document.getElementById('bb_donor_father');
            const contactInp = document.getElementById('bb_donor_contact');
            const addressInp = document.getElementById('bb_donor_address');
            const activeInp  = document.getElementById('bb_donor_active');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mode   = trigger.getAttribute('data-mode');
                const title  = trigger.getAttribute('data-title');
                const action = trigger.getAttribute('data-action');

                titleEl.innerText = title || 'Blood Donor';
                formEl.action     = action || formEl.action;

                if (mode === 'edit') {
                    methodInp.setAttribute('name', '_method');
                    methodInp.value    = 'PUT';
                    submitBtn.innerText = 'Update';

                    nameInp.value    = trigger.getAttribute('data-name') || '';
                    dobInp.value     = trigger.getAttribute('data-dob') || '';
                    genderInp.value  = trigger.getAttribute('data-gender') || '';
                    bgInp.value      = trigger.getAttribute('data-blood-group') || '';
                    fatherInp.value  = trigger.getAttribute('data-father') || '';
                    contactInp.value = trigger.getAttribute('data-contact') || '';
                    addressInp.value = trigger.getAttribute('data-address') || '';
                    activeInp.value  = trigger.getAttribute('data-active') || 1;
                } else {
                    methodInp.removeAttribute('name');
                    methodInp.value    = '';
                    submitBtn.innerText = 'Save';

                    nameInp.value    = '';
                    dobInp.value     = '';
                    genderInp.value  = '';
                    bgInp.value      = '';
                    fatherInp.value  = '';
                    contactInp.value = '';
                    addressInp.value = '';
                    activeInp.value  = 1;
                }
            });
        });
    </script>
@endpush
