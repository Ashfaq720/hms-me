@extends('backend.layouts.master')
@section('title', 'Edit OPD Visit')
@section('content')

<div class="container-fluid py-3">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-semibold mb-0">Edit OPD Visit</h5>
            <small class="text-muted">
                {{ $opdPatient->patient?->patient_name }} &mdash; {{ $opdPatient->date?->format('d M Y') }}
            </small>
        </div>
        <a href="{{ route('opd-patients.show', $opdPatient->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $currentSlot = ($opdPatient->slot_time_from && $opdPatient->slot_time_to)
            ? substr($opdPatient->slot_time_from, 0, 5) . '|' . substr($opdPatient->slot_time_to, 0, 5)
            : '';
        $initDeptId   = old('department_id', $opdPatient->department_id);
        $initDoctorId = old('doctor_id', $opdPatient->doctor_id);
        $initShiftId  = old('shift_id', $opdPatient->shift_id);
        $initSlot     = old('slot', $currentSlot);
        $initDate     = old('date', $opdPatient->date?->format('Y-m-d'));
    @endphp

    <form action="{{ route('opd-patients.update', $opdPatient->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- Patient info (read-only) --}}
            <div class="col-12">
                <div class="p-3 border rounded bg-light">
                    <h6 class="mb-2 text-secondary">Patient</h6>
                    <div class="d-flex gap-3 flex-wrap">
                        <span><strong>Name:</strong> {{ $opdPatient->patient?->patient_name }}</span>
                        <span><strong>Contact:</strong> {{ $opdPatient->patient?->mobileno }}</span>
                        @if($opdPatient->patient?->mrn)
                            <span><strong>MRN:</strong> {{ $opdPatient->patient->mrn }}</span>
                        @endif
                        @if($opdPatient->token_no)
                            <span><strong>Token:</strong> {{ $opdPatient->token_no }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Visit Details --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-primary">Visit Details</h6>
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ $initDate }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Visit Type <span class="text-danger">*</span></label>
                            <select name="visit_type" id="visit_type"
                                    class="form-select @error('visit_type') is-invalid @enderror" required>
                                @foreach(['new' => 'New', 'follow_up' => 'Follow-up', 'recheckup' => 'Re-checkup', 'referred' => 'Referred', 'emergency' => 'Emergency'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('visit_type', $opdPatient->visit_type) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('visit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input type="text" name="status"
                                   class="form-control @error('status') is-invalid @enderror"
                                   value="{{ old('status', $opdPatient->status) }}">
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3" id="referralBox"
                             style="{{ in_array(old('visit_type', $opdPatient->visit_type), ['referred','emergency']) ? '' : 'display:none' }}">
                            <label class="form-label">Referral Source</label>
                            <input type="text" name="referral_source"
                                   class="form-control @error('referral_source') is-invalid @enderror"
                                   value="{{ old('referral_source', $opdPatient->referral_source) }}"
                                   placeholder="Doctor / clinic / hospital">
                            @error('referral_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Department & Doctor --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-primary">Department & Doctor</h6>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id"
                                    class="form-select select2 @error('department_id') is-invalid @enderror"
                                    style="width:100%" required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected($initDeptId == $dept->id)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Consultant Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id"
                                    class="form-select select2 @error('doctor_id') is-invalid @enderror"
                                    style="width:100%" required>
                                <option value="">-- Select Department First --</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}"
                                            data-dept="{{ $d->department_id }}"
                                            @selected($initDoctorId == $d->id)>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Shift & Slot --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-primary">Shift & Slot</h6>
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Shift</label>
                            <select name="shift_id" id="shift_id"
                                    class="form-select @error('shift_id') is-invalid @enderror">
                                <option value="">— Select Doctor First —</option>
                            </select>
                            @error('shift_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Slot</label>
                            <select name="slot" id="slot"
                                    class="form-select @error('slot') is-invalid @enderror">
                                <option value="">— Select Shift First —</option>
                            </select>
                            @error('slot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Visit Time</label>
                            <input type="time" id="slot_time_display" class="form-control bg-light" readonly
                                   placeholder="Auto-filled from slot"
                                   value="{{ $currentSlot ? substr($currentSlot, 0, 5) : '' }}">
                            <small class="text-muted">Auto-filled when a slot is selected.</small>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Complaint & Remarks --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-primary">Notes</h6>
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Chief Complaint</label>
                            <textarea name="chief_complaint" rows="2"
                                      class="form-control @error('chief_complaint') is-invalid @enderror"
                                      placeholder="Describe the patient's primary complaint…">{{ old('chief_complaint', $opdPatient->chief_complaint) }}</textarea>
                            @error('chief_complaint')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks"
                                   class="form-control @error('remarks') is-invalid @enderror"
                                   value="{{ old('remarks', $opdPatient->remarks) }}">
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Patient Documents --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-primary">Patient Documents</h6>

                    {{-- Existing documents --}}
                    @if ($opdPatient->documents->isNotEmpty())
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:30%;">Title</th>
                                        <th>Remarks</th>
                                        <th>Uploaded</th>
                                        <th style="width:90px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($opdPatient->documents as $doc)
                                        <tr>
                                            <td class="fw-semibold">{{ $doc->title ?: '—' }}</td>
                                            <td class="text-muted">{{ $doc->remarks ?: '—' }}</td>
                                            <td>{{ $doc->created_at->format('d M Y') }}</td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ asset('storage/' . $doc->file) }}"
                                                        target="_blank"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="View / Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <form action="{{ route('opd-patients.documents.destroy', [$opdPatient->id, $doc->id]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this document?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Upload new documents --}}
                    <p class="text-muted small mb-2">Add new documents:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%;">Title</th>
                                    <th style="width:30%;">File</th>
                                    <th>Remarks</th>
                                    <th style="width:60px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="editDocumentsTbody">
                                <tr class="edit-document-row">
                                    <td><input type="text" name="documents[0][title]" class="form-control form-control-sm" placeholder="e.g. Lab Report"></td>
                                    <td><input type="file" name="documents[0][file]" class="form-control form-control-sm"></td>
                                    <td><input type="text" name="documents[0][remarks]" class="form-control form-control-sm" placeholder="Optional notes"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger edit-remove-doc" title="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="editAddDocBtn">
                        <i class="bi bi-plus-lg"></i> Add Document
                    </button>

                    @error('documents.*.file')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('opd-patients.show', $opdPatient->id) }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Update Visit</button>
            </div>

        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var CSRF      = "{{ csrf_token() }}";
    var SHIFTS_URL = "{{ route('appointments.get-doctor-shifts') }}";
    var SLOTS_URL  = "{{ route('appointments.get-slots') }}";

    var initDeptId   = "{{ $initDeptId }}";
    var initDoctorId = "{{ $initDoctorId }}";
    var initShiftId  = "{{ $initShiftId }}";
    var initSlot     = "{{ $initSlot }}";
    var initDate     = "{{ $initDate }}";

    var $dept   = $('#department_id');
    var $doctor = $('#doctor_id');
    var $shift  = $('#shift_id');
    var $slot   = $('#slot');
    var $date   = $('#date');

    // Capture all doctor options once
    var allDoctorOpts = $doctor.find('option[data-dept]').clone();

    function filterDoctors(deptId) {
        $doctor.find('option[data-dept]').remove();
        var matches = [];
        allDoctorOpts.each(function () {
            if (!deptId || String($(this).data('dept')) === String(deptId)) {
                matches.push($(this).clone());
            }
        });
        var placeholder = !deptId
            ? '-- Select Department First --'
            : (matches.length ? '-- Select Doctor --' : '-- No doctors in this department --');
        $doctor.find('option:first').text(placeholder);
        matches.forEach(function (o) { $doctor.append(o); });
        if ($doctor.hasClass('select2-hidden-accessible')) {
            $doctor.trigger('change.select2');
        }
    }

    function fmt12(t) {
        var p = t.split(':'), h = parseInt(p[0], 10), m = parseInt(p[1], 10);
        return String((h + 11) % 12 + 1).padStart(2, '0') + ':' + String(m).padStart(2, '0') + (h >= 12 ? ' PM' : ' AM');
    }

    // ---- Shift loading ----
    var _shiftXhr = null;
    function loadShifts(doctorId, preselectShiftId) {
        if (_shiftXhr) { _shiftXhr.abort(); _shiftXhr = null; }
        $shift.html('<option value="">Loading shifts…</option>');
        $slot.html('<option value="">— Select Shift First —</option>');
        $('#slot_time_display').val('');

        if (!doctorId) {
            $shift.html('<option value="">— Select Doctor First —</option>');
            return;
        }

        _shiftXhr = $.ajax({
            url: SHIFTS_URL, type: 'POST',
            data: { _token: CSRF, doctor_id: doctorId },
            success: function (list) {
                if (!list || !list.length) {
                    $shift.html('<option value="">No shifts for this doctor</option>');
                    return;
                }
                $shift.html('<option value="">— Select Shift —</option>');
                $.each(list, function (i, s) {
                    $shift.append($('<option>', { value: s.id, text: s.name }));
                });
                if (preselectShiftId) {
                    $shift.val(preselectShiftId);
                    if ($shift.val() == preselectShiftId) {
                        loadSlots(doctorId, preselectShiftId, initDate, initSlot);
                    }
                }
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $shift.html('<option value="">Failed to load shifts</option>');
            }
        });
    }

    // ---- Slot loading ----
    var _slotXhr = null;
    function loadSlots(doctorId, shiftId, dateVal, preselectSlot) {
        if (_slotXhr) { _slotXhr.abort(); _slotXhr = null; }
        $slot.html('<option value="">Loading slots…</option>');
        $('#slot_time_display').val('');

        if (!doctorId || !shiftId || !dateVal) {
            $slot.html('<option value="">— Select Shift First —</option>');
            return;
        }

        _slotXhr = $.ajax({
            url: SLOTS_URL, type: 'POST',
            data: { _token: CSRF, doctor_id: doctorId, shift_id: shiftId, date: dateVal.substring(0, 10) },
            success: function (list) {
                if (!list || !list.length) {
                    $slot.html('<option value="">No slots available for this day</option>');
                    return;
                }
                $slot.html('<option value="">— Select Slot —</option>');
                $.each(list, function (i, s) {
                    $slot.append($('<option>', { value: s.time_from + '|' + s.time_to, text: fmt12(s.time_from) + ' – ' + fmt12(s.time_to) }));
                });
                if (preselectSlot) {
                    $slot.val(preselectSlot);
                    if ($slot.val() && $slot.val().indexOf('|') !== -1) {
                        $('#slot_time_display').val($slot.val().split('|')[0]);
                    }
                }
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $slot.html('<option value="">Failed to load slots</option>');
            }
        });
    }

    // ---- Event bindings ----
    $dept.on('change', function () {
        var deptId = $(this).val();
        filterDoctors(deptId);
        $doctor.val('');
        if ($doctor.hasClass('select2-hidden-accessible')) $doctor.trigger('change.select2');
        $shift.html('<option value="">— Select Doctor First —</option>');
        $slot.html('<option value="">— Select Shift First —</option>');
        $('#slot_time_display').val('');
    });

    $doctor.on('change', function () {
        loadShifts($(this).val(), null);
    });

    $shift.on('change', function () {
        loadSlots($doctor.val(), $(this).val(), $date.val(), null);
    });

    $date.on('change', function () {
        loadSlots($doctor.val(), $shift.val(), $(this).val(), null);
    });

    $slot.on('change', function () {
        var v = $(this).val();
        $('#slot_time_display').val(v && v.indexOf('|') !== -1 ? v.split('|')[0] : '');
    });

    // Referral source toggle
    function toggleReferral() {
        var v = $('#visit_type').val();
        $('#referralBox').toggle(v === 'referred' || v === 'emergency');
    }
    $('#visit_type').on('change', toggleReferral);

    // ---- Page init: filter doctors then load shifts → slots ----
    if (initDeptId) {
        filterDoctors(initDeptId);
        $doctor.val(initDoctorId);
        if ($doctor.hasClass('select2-hidden-accessible')) $doctor.trigger('change.select2');
    }

    if (initDoctorId) {
        // loadShifts will preselect shift and trigger loadSlots(→slot) in its callback
        loadShifts(initDoctorId, initShiftId);
    }

}());

// Document add/remove rows
(function () {
    const tbody  = document.getElementById('editDocumentsTbody');
    const addBtn = document.getElementById('editAddDocBtn');
    if (!tbody || !addBtn) return;
    let idx = 1;

    function rebuildIndexes() {
        Array.from(tbody.querySelectorAll('tr.edit-document-row')).forEach(function (row, i) {
            row.querySelectorAll('input').forEach(function (input) {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
            });
        });
    }

    addBtn.addEventListener('click', function () {
        const row = document.createElement('tr');
        row.className = 'edit-document-row';
        row.innerHTML = `
            <td><input type="text" name="documents[${idx}][title]" class="form-control form-control-sm" placeholder="e.g. Lab Report"></td>
            <td><input type="file" name="documents[${idx}][file]" class="form-control form-control-sm"></td>
            <td><input type="text" name="documents[${idx}][remarks]" class="form-control form-control-sm" placeholder="Optional notes"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger edit-remove-doc" title="Remove"><i class="bi bi-trash"></i></button></td>`;
        tbody.appendChild(row);
        idx++;
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-remove-doc');
        if (!btn) return;
        const rows = tbody.querySelectorAll('tr.edit-document-row');
        if (rows.length <= 1) {
            btn.closest('tr').querySelectorAll('input').forEach(function (i) { i.value = ''; });
        } else {
            btn.closest('tr').remove();
            rebuildIndexes();
        }
    });
}());
</script>
@endpush
