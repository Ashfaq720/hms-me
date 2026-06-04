@extends('backend.layouts.master')

@section('title', 'Doctor Shift')

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.appointment_setup')
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title mb-0">Doctor Shift Assignment</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 mt-1">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointment</a></li>
                                <li class="breadcrumb-item active">Doctor Shift</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card overflow-hidden">
                            <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                                <h6 class="card-title mb-2">
                                    <i class="fa-solid fa-user-clock text-primary me-1"></i> Doctor Shift Matrix
                                    <span class="badge bg-primary ms-1">{{ $doctors->count() }} Doctors</span>
                                </h6>
                                <div class="text-muted small">
                                    <i class="fa-solid fa-circle-info me-1"></i> Toggle checkboxes to assign shifts to doctors
                                </div>
                            </div>

                            <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                                <table class="table display table-row-rounded table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Doctor Name</th>
                                            @foreach ($shifts as $shift)
                                                <th class="text-center" width="120">
                                                    <span class="badge bg-light text-dark border fw-semibold">
                                                        {{ $shift->name }}
                                                    </span>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($doctors as $i => $doctor)
                                            @php
                                                $assignedIds = $doctor->shifts->pluck('id')->toArray();
                                            @endphp
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        <i class="fa-solid fa-user-doctor text-muted me-1 small"></i>
                                                        {{ $doctor->name }}
                                                        @if ($doctor->doctor_code)
                                                            <span class="text-muted small">({{ $doctor->doctor_code }})</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @foreach ($shifts as $shift)
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input type="checkbox"
                                                                class="form-check-input js-doctor-shift-toggle"
                                                                data-doctor-id="{{ $doctor->id }}"
                                                                data-shift-id="{{ $shift->id }}"
                                                                role="switch"
                                                                {{ in_array($shift->id, $assignedIds) ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ 2 + $shifts->count() }}" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                                        No doctors found. Please add doctors first.
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleUrl = "{{ route('doctor-shifts.toggle') }}";
            const csrf = "{{ csrf_token() }}";

            document.querySelectorAll('.js-doctor-shift-toggle').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    const checkbox = this;
                    const previous = !checkbox.checked;

                    checkbox.disabled = true;

                    fetch(toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                doctor_id: checkbox.dataset.doctorId,
                                shift_id: checkbox.dataset.shiftId,
                                assigned: checkbox.checked ? 1 : 0,
                            }),
                        })
                        .then(function(res) {
                            if (!res.ok) throw new Error('Request failed');
                            return res.json();
                        })
                        .then(function(json) {
                            if (!json.success) throw new Error('Update failed');
                            if (checkbox.checked) {
                                toastr.success('Shift assigned successfully.');
                            } else {
                                toastr.warning('Shift removed successfully.');
                            }
                        })
                        .catch(function() {
                            checkbox.checked = previous;
                            toastr.error('Could not update shift. Please try again.');
                        })
                        .finally(function() {
                            checkbox.disabled = false;
                        });
                });
            });
        });
    </script>
@endpush
