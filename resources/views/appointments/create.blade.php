@extends('backend.layouts.master')

@section('title', 'Create Appointment')

@section('content')
    <div class="container-fluid py-4 apt-form-page">
        <div class="page-head d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Create Appointment</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf

            {{-- SECTION 1: Patient & Doctor --}}
            <section class="form-card mb-4">
                <header class="form-card__header">
                    <div class="form-card__title">
                        <span class="step-badge">1</span>
                        <div>
                            <h5 class="mb-0">Patient & Doctor</h5>
                            <small class="text-muted">Select the patient and consulting doctor</small>
                        </div>
                    </div>
                </header>
                <div class="form-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('patient_id') is-invalid @enderror" name="patient_id"
                                id="patient_select" data-placeholder="--Select Patient--">
                                <option value="">---Select Patient---</option>
                                @foreach ($patients as $p)
                                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->patient_name }}</option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('doctor') is-invalid @enderror" name="doctor"
                                id="doctor_select" data-placeholder="--Select Doctor--">
                                <option value="">---Select Doctor---</option>
                                @foreach ($doctors as $d)
                                    <option value="{{ $d->id }}" {{ old('doctor') == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}</option>
                                @endforeach
                            </select>
                            @error('doctor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Doctor Fee <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                name="amount" id="amount_field" value="{{ old('amount') }}" placeholder="0.00" readonly>
                            <div id="fee_info" class="mt-1" style="display:none;">
                                <span id="fee_type_badge" class="badge"></span>
                                <small id="fee_details" class="text-muted ms-1"></small>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Specialist <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('specialist') is-invalid @enderror"
                                name="specialist" value="{{ old('specialist') }}" placeholder="e.g. Cardiology">
                            @error('specialist')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 2: Schedule --}}
            <section class="form-card mb-4">
                <header class="form-card__header">
                    <div class="form-card__title">
                        <span class="step-badge">2</span>
                        <div>
                            <h5 class="mb-0">Schedule & Priority</h5>
                            <small class="text-muted">Set the appointment date, time and urgency</small>
                        </div>
                    </div>
                </header>
                <div class="form-card__body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                name="date" id="date_field" value="{{ old('date') }}">
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>



                         <div class="col-md-3">
                            <label class="form-label fw-semibold">Shift</label>
                            <select class="form-select @error('shift_id') is-invalid @enderror" name="shift_id"
                                id="shift_select" data-current="{{ old('shift_id') }}">
                                <option value="">---Select Doctor First---</option>
                            </select>
                            @error('shift_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Slot</label>
                            <select class="form-select @error('slot') is-invalid @enderror" name="slot" id="slot_select">
                                <option value="">---Select Slot---</option>
                            </select>
                            @error('slot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <div class="col-md-6">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" class="form-control @error('time') is-invalid @enderror" name="time"
                                id="time_field" value="{{ old('time') }}" readonly>
                            <small class="text-muted">Auto-filled when a slot is selected</small>
                            @error('time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                                <option value="">---Select Priority---</option>
                                @foreach ($priorities as $pr)
                                    <option value="{{ $pr->name }}" {{ old('priority') == $pr->name ? 'selected' : '' }}>
                                        {{ $pr->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Source <span class="text-danger">*</span></label>
                            <select class="form-select @error('source') is-invalid @enderror" name="source">
                                <option value="">---Select Source---</option>
                                @foreach (['Walk-in', 'Phone', 'Web'] as $src)
                                    <option value="{{ $src }}" {{ old('source') == $src ? 'selected' : '' }}>
                                        {{ $src }}</option>
                                @endforeach
                            </select>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Live Consult <span class="text-danger">*</span></label>
                            <select class="form-select @error('live_consult') is-invalid @enderror" name="live_consult">
                                <option value="">---Select---</option>
                                @foreach (['Zoom', 'Meet', 'None'] as $lc)
                                    <option value="{{ $lc }}"
                                        {{ old('live_consult', 'None') == $lc ? 'selected' : '' }}>{{ $lc }}</option>
                                @endforeach
                            </select>
                            @error('live_consult')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Appointment Status</label>
                            <select class="form-select @error('appointment_status') is-invalid @enderror"
                                name="appointment_status">
                                <option value="">---Select Status---</option>
                                @foreach (['Pending', 'Approved', 'Rejected', 'Cancelled'] as $status)
                                    <option value="{{ $status }}"
                                        {{ old('appointment_status', 'Pending') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('appointment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION 3: Visit Details --}}
            <section class="form-card mb-4">
                <header class="form-card__header">
                    <div class="form-card__title">
                        <span class="step-badge">3</span>
                        <div>
                            <h5 class="mb-0">Visit Details</h5>
                            <small class="text-muted">Visit type, queue and additional notes</small>
                        </div>
                    </div>
                </header>
                <div class="form-card__body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Visit Status</label>
                            <select class="form-select @error('visit_status') is-invalid @enderror" name="visit_status">
                                @php
                                    $visitStatuses = [
                                        'booked' => 'Booked',
                                        'checked_in' => 'Checked In',
                                        'in_consultation' => 'In Consultation',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        'no_show' => 'No Show',
                                    ];
                                @endphp
                                @foreach ($visitStatuses as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('visit_status', 'booked') == $val ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('visit_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Is OPD <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_opd') is-invalid @enderror" name="is_opd">
                                <option value="Yes" {{ old('is_opd', 'Yes') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('is_opd') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_opd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Is Ipd <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_ipd') is-invalid @enderror" name="is_ipd">
                                <option value="Yes" {{ old('is_ipd') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('is_ipd', 'No') == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_ipd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Add to Queue</label>
                            <select class="form-select @error('is_queue') is-invalid @enderror" name="is_queue">
                                <option value="0" {{ old('is_queue', '0') == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_queue') == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('is_queue')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="2"
                                placeholder="Optional note or message">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="form-actions d-flex justify-content-end gap-2">
                <a href="{{ route('appointments.index') }}" class="btn btn-light px-4">Cancel</a>
                <button type="reset" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check2-circle"></i> Save Appointment
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .apt-form-page .page-head h3 {
            color: #2b335d;
        }

        .apt-form-page .breadcrumb-item a {
            color: #6b7390;
            text-decoration: none;
        }

        .apt-form-page .breadcrumb-item.active {
            color: #2b335d;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            overflow: hidden;
        }

        .form-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #f8f9fb;
            border-bottom: 1px solid #e3e6ef;
        }

        .form-card__title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-card__title h5 {
            font-size: 15px;
            font-weight: 600;
            color: #2b335d;
        }

        .form-card__title small {
            font-size: 12px;
        }

        .step-badge {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #2b335d;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .form-card__body {
            padding: 20px;
        }

        .form-card__body .form-label {
            font-size: 12.5px;
            color: #3b4055;
            margin-bottom: 4px;
        }

        .form-card__body .form-control,
        .form-card__body .form-select {
            font-size: 13px;
        }

        .form-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 14px 20px;
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            box-shadow: 0 -4px 12px rgba(43, 51, 93, .05);
            z-index: 10;
        }

        .form-actions .btn-primary {
            background: #2b335d;
            border-color: #2b335d;
        }

        .form-actions .btn-primary:hover {
            background: #1f254a;
            border-color: #1f254a;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.$ && $.fn.select2) {
                $('.select2').select2({
                    width: '100%',
                    placeholder: function() {
                        return $(this).data('placeholder') || 'Select';
                    },
                    allowClear: true
                });
            }

            const $patient = $('#patient_select');
            const $doctor = $('#doctor_select');
            const $amount = $('#amount_field');
            const $feeInfo = $('#fee_info');
            const $feeTypeBadge = $('#fee_type_badge');
            const $feeDetails = $('#fee_details');

            function fetchDoctorFee() {
                const patientId = $patient.val();
                const doctorId = $doctor.val();

                if (!patientId || !doctorId) {
                    $amount.val('');
                    $feeInfo.hide();
                    return;
                }

                $.ajax({
                    url: "{{ route('appointments.get-doctor-fee') }}",
                    type: "GET",
                    data: {
                        patient_id: patientId,
                        doctor_id: doctorId
                    },
                    success: function(res) {
                        $amount.val(res.fee);

                        if (res.fee_type === 'none') {
                            $feeInfo.show();
                            $feeTypeBadge.attr('class', 'badge bg-secondary-subtle text-secondary')
                                .text('No Fee');
                            $feeDetails.text(res.message);
                            return;
                        }

                        $feeInfo.show();

                        if (res.fee_type === 'Follow-up Visit') {
                            $feeTypeBadge.attr('class', 'badge bg-warning-subtle text-warning').text(
                                'Follow-up Visit');
                            $feeDetails.text(
                                'Last visit: ' + res.last_visit_date +
                                ' (' + res.days_since_last + ' days ago) — Window: ' + res
                                .follow_up_window + ' days'
                            );
                        } else {
                            $feeTypeBadge.attr('class', 'badge bg-primary-subtle text-primary').text(
                                'First Visit');
                            if (res.last_visit_date) {
                                $feeDetails.text(
                                    'Last visit: ' + res.last_visit_date +
                                    ' (' + res.days_since_last +
                                    ' days ago) — Outside follow-up window'
                                );
                            } else {
                                $feeDetails.text('No previous appointment with this doctor');
                            }
                        }
                    },
                    error: function() {
                        $amount.val('');
                        $feeInfo.hide();
                    }
                });
            }

            $patient.on('change', fetchDoctorFee);
            $doctor.on('change', fetchDoctorFee);

            // --- Shift + Slot loading (shift depends on doctor, slot depends on doctor+shift+date) ---
            const shiftsUrl = "{{ route('appointments.get-doctor-shifts') }}";
            const slotsUrl = "{{ route('appointments.get-slots') }}";
            const csrf = "{{ csrf_token() }}";
            const $shift = $('#shift_select');
            const $slot = $('#slot_select');
            const $date = $('input[name="date"]');

            function fmt12(t) {
                const [h, m] = t.split(':').map(Number);
                const ap = h >= 12 ? 'PM' : 'AM';
                const hh = ((h + 11) % 12 + 1);
                return String(hh).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ap;
            }

            function refreshShifts() {
                const doctorId = $doctor.val();
                const preselect = $shift.data('current') || '';

                $shift.html('<option value="">---Select Shift---</option>');
                $slot.html('<option value="">---Select Slot---</option>');

                if (!doctorId) {
                    $shift.html('<option value="">---Select Doctor First---</option>');
                    return Promise.resolve();
                }

                const fd = new FormData();
                fd.append('doctor_id', doctorId);

                return fetch(shiftsUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: fd,
                    })
                    .then(r => r.json())
                    .then(list => {
                        list.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            if (String(s.id) === String(preselect)) opt.selected = true;
                            $shift[0].appendChild(opt);
                        });
                        $shift.data('current', '');
                    });
            }

            function refreshSlots() {
                const doctorId = $doctor.val();
                const shiftId = $shift.val();
                const dateVal = $date.val();

                $slot.html('<option value="">---Select Slot---</option>');

                if (!doctorId || !shiftId || !dateVal) return;

                const dateOnly = dateVal.length >= 10 ? dateVal.substring(0, 10) : dateVal;

                const fd = new FormData();
                fd.append('doctor_id', doctorId);
                fd.append('shift_id', shiftId);
                fd.append('date', dateOnly);

                fetch(slotsUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: fd,
                    })
                    .then(r => r.json())
                    .then(list => {
                        list.forEach(s => {
                            const val = `${s.time_from}|${s.time_to}`;
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = fmt12(s.time_from) + ' - ' + fmt12(s.time_to);
                            $slot[0].appendChild(opt);
                        });
                    });
            }

            $doctor.on('change', function() {
                refreshShifts().then(refreshSlots);
            });
            $shift.on('change', refreshSlots);
            $date.on('change', refreshSlots);

            // Auto-fill Time when a Slot is chosen (slot's start time)
            $slot.on('change', function() {
                const v = $slot.val();
                $('#time_field').val(v && v.includes('|') ? v.split('|')[0] : '');
            });

            // Initial load (e.g. after validation redirect with old values)
            if ($doctor.val()) {
                refreshShifts().then(refreshSlots);
            }
        });
    </script>
@endpush
