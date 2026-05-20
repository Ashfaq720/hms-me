<style>
.fd-sec { margin-bottom: 1.25rem; }
.fd-sec-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
    color: #6c757d; display: flex; align-items: center; gap: .4rem; margin-bottom: .65rem;
}
.fd-sec-label::after { content: ''; flex: 1; height: 1px; background: #e9ecef; }
.fd-sec-num {
    width: 1.4rem; height: 1.4rem; border-radius: 50%; background: #0d6efd;
    color: #fff; font-size: .65rem; font-weight: 700; display: inline-flex;
    align-items: center; justify-content: center; flex-shrink: 0;
}
.fd-field-feedback { font-size: .76rem; margin-top: .2rem; min-height: 1rem; }
.fd-phone-icon { min-width: 2rem; display: flex; align-items: center; justify-content: center; }
#phoneWarning { display: none; }
#orgFields { display: none; }
.fd-card {
    border: 1px solid #e3e8ef; border-radius: .5rem;
    padding: .875rem 1rem; background: #f8fafc;
}
</style>

<form action="{{ route('front_desk.registration.store') }}" method="POST" enctype="multipart/form-data" id="fdRegForm" novalidate>
    @csrf

    {{-- ── Server-side error summary ──────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible py-2 mb-3 d-flex gap-2 align-items-start" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">
                <strong>{{ $errors->count() }} issue(s) need attention:</strong>
                <ul class="mb-0 ps-3 mt-1 small">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 1 · PATIENT IDENTITY
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="fd-sec">
        <div class="fd-sec-label"><span class="fd-sec-num">1</span> Patient Identity</div>

        <div class="row g-2">

            {{-- Registration Type --}}
            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Registration Type <span class="text-danger">*</span>
                </label>
                <select name="registration_type" id="registration_type"
                    class="form-select form-select-sm @error('registration_type') is-invalid @enderror" required>
                    <option value="NEW_PATIENT"      {{ old('registration_type', 'NEW_PATIENT') == 'NEW_PATIENT'      ? 'selected' : '' }}>New Patient</option>
                    <option value="EXISTING_PATIENT" {{ old('registration_type') == 'EXISTING_PATIENT'                ? 'selected' : '' }}>Existing Patient</option>
                    <option value="UNKNOWN"          {{ old('registration_type') == 'UNKNOWN'                         ? 'selected' : '' }}>Unknown / Emergency</option>
                </select>
                @error('registration_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Patient Type --}}
            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Patient Type <span class="text-danger">*</span>
                </label>
                <select name="patient_type" id="patient_type"
                    class="form-select form-select-sm @error('patient_type') is-invalid @enderror" required>
                    <option value="">Choose...</option>
                    <option value="OPD" {{ old('patient_type') == 'OPD'  ? 'selected' : '' }}>OPD – Out Patient</option>
                    <option value="Ipd" {{ old('patient_type') == 'Ipd'  ? 'selected' : '' }}>IPD – In Patient</option>
                    <option value="ER"  {{ old('patient_type') == 'ER'   ? 'selected' : '' }}>ER – Emergency</option>
                    <option value="LAB" {{ old('patient_type') == 'LAB'  ? 'selected' : '' }}>Lab Only</option>
                </select>
                @error('patient_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Existing patient search (EXISTING_PATIENT only) --}}
            <div class="col-12" id="existingPatientWrap" style="display:none;">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Search Existing Patient <span class="text-danger">*</span>
                </label>
                <select name="patient_id" id="patient_search"
                    class="form-select form-select-sm patient-select @error('patient_id') is-invalid @enderror">
                    <option value="">-- Search by Name / MRN / Contact --</option>
                    @foreach ($patients as $p)
                        <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->patient_name }} | {{ $p->mobileno }}
                        </option>
                    @endforeach
                </select>
                @error('patient_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Full Name --}}
            <div class="col-12 col-md-6" id="nameWrap">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Full Name <span class="text-danger" id="nameAsterisk">*</span>
                </label>
                <input type="text" name="name" id="patient_name"
                    class="form-control form-control-sm @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" placeholder="Patient full name" autocomplete="off">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Contact No + real-time check --}}
            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Contact No <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="contact_no" id="patient_contact"
                        class="form-control @error('contact_no') is-invalid @enderror"
                        value="{{ old('contact_no') }}" placeholder="01XXXXXXXXX"
                        required maxlength="20" inputmode="tel" autocomplete="off">
                    <span class="input-group-text fd-phone-icon" id="phoneFeedbackIcon"></span>
                </div>
                @error('contact_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="fd-field-feedback" id="phoneFeedbackMsg"></div>

                {{-- Duplicate phone warning --}}
                <div id="phoneWarning" class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Number already registered.</strong><br>
                        <span id="phoneWarningPatient" class="text-muted"></span>
                        <a href="#" id="switchToExisting" class="d-block mt-1 fw-semibold text-decoration-none small">
                            <i class="bi bi-arrow-repeat me-1"></i>Switch to Existing Patient
                        </a>
                    </div>
                </div>
            </div>

            {{-- NID / Passport --}}
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">NID / Passport / ID</label>
                <input type="text" name="nid_passport"
                    class="form-control form-control-sm @error('nid_passport') is-invalid @enderror"
                    value="{{ old('nid_passport') }}" placeholder="Optional">
                @error('nid_passport')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Discount Type --}}
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Discount Type</label>
                <select name="discount_type" id="discount_type"
                    class="form-select form-select-sm @error('discount_type') is-invalid @enderror">
                    <option value="">-- Select --</option>
                    <option value="SELF"      {{ old('discount_type') == 'SELF'      ? 'selected' : '' }}>Self Pay</option>
                    <option value="CORPORATE" {{ old('discount_type') == 'CORPORATE' ? 'selected' : '' }}>Corporate</option>
                    <option value="INSURANCE" {{ old('discount_type') == 'INSURANCE' ? 'selected' : '' }}>Insurance</option>
                    <option value="STUFF"     {{ old('discount_type') == 'STUFF'     ? 'selected' : '' }}>Staff</option>
                </select>
                @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Upload Document --}}
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Supporting Document</label>
                <input type="file" name="supporting_doc" id="supporting_doc"
                    class="form-control form-control-sm @error('supporting_doc') is-invalid @enderror"
                    accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                @error('supporting_doc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text" id="docFileInfo">PDF, DOCX, PNG, JPEG · max 5 MB</div>
            </div>

        </div>{{-- /row --}}
    </div>{{-- /fd-sec 1 --}}

    {{-- Organisation fields (CORPORATE / INSURANCE only) --}}
    <div id="orgFields" class="fd-sec">
        <div class="fd-sec-label"><span class="fd-sec-num" style="background:#6c757d;">↳</span> Organisation Details</div>
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Organisation Name</label>
                <input type="text" name="organization_name" id="organization_name"
                    class="form-control form-control-sm @error('organization_name') is-invalid @enderror"
                    value="{{ old('organization_name') }}" placeholder="Name">
                @error('organization_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Organisation ID</label>
                <input type="text" name="organization_id" id="organization_id"
                    class="form-control form-control-sm @error('organization_id') is-invalid @enderror"
                    value="{{ old('organization_id') }}" placeholder="Corp / Ins ID">
                @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Portal / API Link</label>
                <input type="text" name="organization_api_link" id="organization_api_link"
                    class="form-control form-control-sm @error('organization_api_link') is-invalid @enderror"
                    value="{{ old('organization_api_link') }}" placeholder="https://...">
                @error('organization_api_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 2 · VISIT DETAILS
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="fd-sec">
        <div class="fd-sec-label"><span class="fd-sec-num">2</span> Visit Details</div>

        {{-- LAB hint --}}
        <div id="labHint" class="alert alert-info py-2 px-3 small mb-2" style="display:none;">
            <i class="bi bi-flask me-1"></i>
            After saving you will be redirected to create Pathology / Radiology orders.
        </div>

        <div class="row g-2">

            {{-- Appointment Date --}}
            <div class="col-12 col-md-6" id="visitFieldWrap">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Appointment Date <span class="text-danger">*</span>
                </label>
                <input id="visitFieldInput" type="date" name="appointment_date"
                    class="form-control form-control-sm @error('appointment_date') is-invalid @enderror"
                    value="{{ old('appointment_date') }}" required>
                @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Non-LAB fields ──────────────────────────────────────────────── --}}
            <div id="nonLabFields" class="col-12">
                <div class="row g-2">

                    {{-- Booking Status --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="booking_status" id="booking_status"
                            class="form-select form-select-sm @error('booking_status') is-invalid @enderror">
                            <option value="WALK_IN"  {{ old('booking_status', 'WALK_IN') == 'WALK_IN'  ? 'selected' : '' }}>Walk In</option>
                            <option value="PRE_BOOK" {{ old('booking_status') == 'PRE_BOOK'             ? 'selected' : '' }}>Pre Book</option>
                        </select>
                        @error('booking_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- ER Priority (ER only) --}}
                    <div class="col-12 col-md-6" id="erFields" hidden>
                        <label class="form-label form-label-sm fw-medium mb-1">ER Priority</label>
                        <select name="er_priority" id="er_priority"
                            class="form-select form-select-sm @error('er_priority') is-invalid @enderror">
                            <option value="NORMAL"   {{ old('er_priority', 'NORMAL') == 'NORMAL'   ? 'selected' : '' }}>Normal</option>
                            <option value="HIGH"     {{ old('er_priority') == 'HIGH'               ? 'selected' : '' }}>High</option>
                            <option value="CRITICAL" {{ old('er_priority') == 'CRITICAL'           ? 'selected' : '' }}>
                                Critical
                            </option>
                        </select>
                        @error('er_priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select name="department_id" id="department_id"
                            class="form-select form-select-sm @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach ($departments as $dep)
                                <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Doctor --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Doctor <span class="text-danger">*</span>
                        </label>
                        <select name="doctor_id" id="doctor_id"
                            class="form-select form-select-sm @error('doctor_id') is-invalid @enderror">
                            <option value="">-- Select Department First --</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}"
                                    data-dept="{{ $d->department_id }}"
                                    {{ old('doctor_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Shift (OPD only) --}}
                    <div class="col-12 col-md-6" id="shift_field" hidden>
                        <label class="form-label form-label-sm fw-medium mb-1">Doctor Shift</label>
                        <select name="shift_id" id="shift_id"
                            class="form-select form-select-sm @error('shift_id') is-invalid @enderror">
                            <option value="">-- Select Shift --</option>
                        </select>
                        @error('shift_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Time Slot (OPD only) --}}
                    <div class="col-12 col-md-6" id="opdSlotField" hidden>
                        <label class="form-label form-label-sm fw-medium mb-1">Time Slot</label>
                        <select name="slot_time" id="slot_time"
                            class="form-select form-select-sm @error('slot_time') is-invalid @enderror">
                            <option value="">-- Select Slot --</option>
                        </select>
                        @error('slot_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>{{-- /inner row --}}
            </div>{{-- /nonLabFields --}}

        </div>{{-- /row --}}

        {{-- IPD Bed Assignment ──────────────────────────────────────────────── --}}
        <div id="ipdFields" class="mt-2" hidden>
            <div class="fd-card">
                <div class="fd-sec-label mb-2"><span class="fd-sec-num" style="background:#198754;">↳</span> Bed Assignment</div>
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-sm fw-medium mb-1">Bed <span class="text-danger">*</span></label>
                        <select name="bed_id" class="form-select form-select-sm @error('bed_id') is-invalid @enderror">
                            <option value="">-- Select --</option>
                            @foreach ($beds as $b)
                                <option value="{{ $b->id }}" @selected(old('bed_id') == $b->id)>
                                    {{ $b->name ?? "Bed #{$b->id}" }}{{ !empty($b->ward?->name) ? " – {$b->ward->name}" : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('bed_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label form-label-sm fw-medium mb-1">From <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="from" value="{{ old('from') }}"
                            class="form-control form-control-sm @error('from') is-invalid @enderror">
                        @error('from')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label form-label-sm fw-medium mb-1">To</label>
                        <input type="datetime-local" name="to" value="{{ old('to') }}"
                            class="form-control form-control-sm @error('to') is-invalid @enderror">
                        @error('to')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm fw-medium mb-1">Bed Remarks</label>
                        <textarea name="bed_remarks" class="form-control form-control-sm" rows="2"
                            placeholder="Reason / notes">{{ old('bed_remarks') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /fd-sec 2 --}}

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 3 · BILLING & PAYMENT (OPD only)
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="fd-sec" id="billingSection" style="display:none;">
        <div class="fd-sec-label"><span class="fd-sec-num">3</span> Billing &amp; Payment</div>

        {{-- Doctor fee badges --}}
        <div id="fdDoctorFeeBox" class="mb-3" style="display:none;">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="font-size:12px;">
                    <i class="bi bi-cash-coin me-1"></i>OPD Visit: <strong id="fd_fee_opd_visit">—</strong>
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2" style="font-size:12px;">
                    <i class="bi bi-person-plus me-1"></i>First Visit: <strong id="fd_fee_first_visit">—</strong>
                </span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2" style="font-size:12px;">
                    <i class="bi bi-arrow-repeat me-1"></i>Follow-up: <strong id="fd_fee_follow_up">—</strong>
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2" style="font-size:12px;">
                    <i class="bi bi-calendar-check me-1"></i>Follow-up Window: <strong id="fd_fee_follow_up_window">—</strong>
                </span>
            </div>
            <small class="text-muted mt-1 d-block">Doctor's configured visit charges</small>
        </div>
        <div id="fdDoctorFeeLoading" class="mb-2 text-muted small" style="display:none;">
            <span class="spinner-border spinner-border-sm me-1"></span> Loading fee…
        </div>
        <div id="fdDoctorFeeNone" class="mb-2 text-muted small" style="display:none;">
            <i class="bi bi-info-circle me-1"></i> No fee configured for this doctor.
        </div>

        <div class="row g-2">
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Consultant Doctor Fee</label>
                <input type="number" step="0.01" class="form-control form-control-sm bg-light"
                    name="standard_charge" id="fd_standard_charge" readonly
                    value="{{ old('standard_charge') }}" placeholder="Auto-filled on doctor selection">
                <small class="text-muted">Auto-filled based on doctor.</small>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Applied Charge</label>
                <input type="number" step="0.01" class="form-control form-control-sm"
                    name="applied_charge" id="fd_applied_charge"
                    value="{{ old('applied_charge') }}">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Discount</label>
                <input type="number" step="0.01" class="form-control form-control-sm"
                    name="discount" id="fd_discount"
                    value="{{ old('discount', 0) }}">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Tax (%)</label>
                <input type="number" step="0.01" class="form-control form-control-sm"
                    name="tax" id="fd_tax"
                    value="{{ old('tax', 0) }}">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Total Amount</label>
                <input type="number" step="0.01" class="form-control form-control-sm bg-light fw-bold"
                    name="amount" id="fd_amount" readonly
                    value="{{ old('amount') }}">
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 4 · NOTES
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="fd-sec">
        <div class="fd-sec-label"><span class="fd-sec-num">4</span> Notes</div>
        <textarea name="description" id="description" rows="3" maxlength="2000"
            class="form-control form-control-sm @error('description') is-invalid @enderror"
            placeholder="Optional remarks, clinical notes, referral info…">{{ old('description') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text text-end"><span id="descCount">{{ strlen(old('description', '')) }}</span> / 2000</div>
    </div>

    {{-- ── Actions ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-2 pt-1">
        <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="fdSubmitBtn" class="btn btn-sm btn-primary px-4">
            <i class="bi bi-check2-circle me-1"></i>Save Registration
        </button>
    </div>

</form>

<script>
(function () {

    /* ── Element refs ───────────────────────────────────────────────────── */
    var regTypeEl    = document.getElementById('registration_type');
    var patientTypeEl = document.getElementById('patient_type');
    var contactEl    = document.getElementById('patient_contact');
    var discountEl   = document.getElementById('discount_type');
    var nameEl       = document.getElementById('patient_name');
    var descEl       = document.getElementById('description');

    var existingWrap = document.getElementById('existingPatientWrap');
    var nameWrap     = document.getElementById('nameWrap');
    var nameAsterisk = document.getElementById('nameAsterisk');
    var nonLabFields = document.getElementById('nonLabFields');
    var ipdFields    = document.getElementById('ipdFields');
    var erFields     = document.getElementById('erFields');
    var visitFieldWrap = document.getElementById('visitFieldWrap');
    var labHint      = document.getElementById('labHint');
    var shiftField   = document.getElementById('shift_field');
    var opdSlotField = document.getElementById('opdSlotField');
    var orgFields    = document.getElementById('orgFields');
    var billingSection = document.getElementById('billingSection');

    /* ── Helpers ────────────────────────────────────────────────────────── */
    function show(el) { if (el) el.style.display = ''; }
    function hide(el) { if (el) el.style.display = 'none'; }

    /* ── Registration Type → adjust visible fields ──────────────────────── */
    function handleRegType() {
        var val = regTypeEl.value;
        var isExisting = val === 'EXISTING_PATIENT';
        var isNew      = val === 'NEW_PATIENT';

        // Existing patient search
        isExisting ? show(existingWrap) : hide(existingWrap);

        // Name required marker
        if (nameAsterisk) nameAsterisk.style.display = isNew ? '' : 'none';
        if (nameEl)       nameEl.required = isNew;

        // Hide phone check for existing patients
        if (isExisting) {
            document.getElementById('phoneWarning').style.display = 'none';
            document.getElementById('phoneFeedbackIcon').innerHTML = '';
            document.getElementById('phoneFeedbackMsg').innerHTML = '';
        }
    }

    if (regTypeEl) {
        regTypeEl.addEventListener('change', handleRegType);
        handleRegType();
    }

    /* ── Patient Type → toggle visit sections ───────────────────────────── */
    var _fdReady = false; // prevents double loadFdShifts() on init

    function toggleByType() {
        var val   = patientTypeEl ? patientTypeEl.value : '';
        var isLab = val === 'LAB';
        var isOpd = val === 'OPD';
        var isIpd = val === 'Ipd';
        var isEr  = val === 'ER';

        isLab ? hide(nonLabFields)  : show(nonLabFields);
        isLab ? hide(visitFieldWrap) : show(visitFieldWrap);
        if (labHint) labHint.style.display = isLab ? '' : 'none';

        if (ipdFields)    { ipdFields.hidden   = !isIpd; }
        if (erFields)     { erFields.hidden    = !isEr;  }
        if (shiftField)   { shiftField.hidden   = !isOpd; }
        if (opdSlotField) { opdSlotField.hidden  = !isOpd; }
        if (billingSection) { billingSection.style.display = isOpd ? '' : 'none'; }

        // Only reload shifts on user-driven type change (not on initial render)
        if (_fdReady) {
            if (isOpd && document.getElementById('doctor_id').value) {
                loadFdShifts();
            } else if (!isOpd) {
                $('#shift_id').html('<option value="">— Select Shift —</option>');
                $('#slot_time').html('<option value="">— Select Slot —</option>');
            }
        }
    }

    if (patientTypeEl) {
        patientTypeEl.addEventListener('change', toggleByType);
        toggleByType();
    }

    /* ── Discount Type → show/hide org fields ───────────────────────────── */
    function toggleOrgFields() {
        var val = discountEl ? discountEl.value : '';
        (val === 'CORPORATE' || val === 'INSURANCE') ? show(orgFields) : hide(orgFields);
    }

    if (discountEl) {
        discountEl.addEventListener('change', toggleOrgFields);
        toggleOrgFields();
    }

    /* ── Department → Doctor filter ─────────────────────────────────────── */
    var allDoctorOptions = Array.from(document.getElementById('doctor_id').options).slice(1);

    function filterDoctors() {
        var deptId  = document.getElementById('department_id').value;
        var docEl   = document.getElementById('doctor_id');
        var current = docEl.value;

        while (docEl.options.length > 1) docEl.remove(1);

        var matches = deptId
            ? allDoctorOptions.filter(function (o) { return o.dataset.dept == deptId; })
            : [];

        docEl.options[0].text = !deptId
            ? '-- Select Department First --'
            : (matches.length ? 'Select Doctor' : '-- No doctors in this department --');

        matches.forEach(function (o) { docEl.add(o.cloneNode(true)); });

        if (current && matches.some(function (o) { return o.value == current; })) {
            docEl.value = current;
        } else {
            docEl.value = '';
            $('#shift_id').html('<option value="">— Select Doctor First —</option>');
            $('#slot_time').html('<option value="">— Select Slot —</option>');
        }
    }

    document.getElementById('department_id').addEventListener('change', filterDoctors);
    filterDoctors();

    /* ── Phone: format + real-time uniqueness check ─────────────────────── */
    var _phoneTimer  = null;
    var _phoneXhr    = null;
    var _dupPatientId   = null;
    var _dupPatientName = null;

    var iconEl    = document.getElementById('phoneFeedbackIcon');
    var msgEl     = document.getElementById('phoneFeedbackMsg');
    var warnEl    = document.getElementById('phoneWarning');
    var warnName  = document.getElementById('phoneWarningPatient');

    function phoneSetState(state, msg) {
        var icons = {
            checking: '<span class="spinner-border spinner-border-sm text-secondary" style="width:.85rem;height:.85rem;"></span>',
            ok:       '<i class="bi bi-check-circle-fill text-success"></i>',
            warn:     '<i class="bi bi-exclamation-triangle-fill text-warning"></i>',
            error:    '<i class="bi bi-x-circle-fill text-danger"></i>',
            '':       ''
        };
        iconEl.innerHTML = icons[state] || '';
        msgEl.innerHTML  = msg || '';
    }

    function validatePhoneFormat(phone) {
        if (!phone) { phoneSetState('', ''); return false; }
        var digits = phone.replace(/[\s\-\(\)\+]/g, '');
        if (!/^[\d\+\-\(\)\s]+$/.test(phone)) {
            phoneSetState('error', '<span class="text-danger">Only digits, +, -, () are allowed.</span>');
            return false;
        }
        if (digits.length < 7) {
            phoneSetState('error', '<span class="text-danger">Too short — minimum 7 digits.</span>');
            return false;
        }
        if (digits.length > 15) {
            phoneSetState('error', '<span class="text-danger">Too long — maximum 15 digits.</span>');
            return false;
        }
        return true;
    }

    function checkPhoneUniqueness(phone) {
        phoneSetState('checking', '');
        warnEl.style.display = 'none';

        if (_phoneXhr) { _phoneXhr.abort(); _phoneXhr = null; }

        _phoneXhr = $.ajax({
            url:      "{{ route('front_desk.check.phone') }}",
            type:     'GET',
            data:     { phone: phone },
            dataType: 'json',
            success: function (res) {
                _phoneXhr = null;
                if (res.exists) {
                    _dupPatientId   = res.patient_id;
                    _dupPatientName = res.patient_name;
                    phoneSetState('warn', '');
                    warnName.textContent = res.patient_name + '  ·  MRN: ' + res.mrn;
                    warnEl.style.display = 'flex';
                } else {
                    _dupPatientId   = null;
                    _dupPatientName = null;
                    phoneSetState('ok', '<span class="text-success">Phone number is available.</span>');
                }
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                _phoneXhr = null;
                phoneSetState('', '');
            }
        });
    }

    if (contactEl) {
        contactEl.addEventListener('input', function () {
            var phone   = this.value.trim();
            var regType = regTypeEl.value;

            warnEl.style.display = 'none';
            clearTimeout(_phoneTimer);

            if (!validatePhoneFormat(phone)) return;

            if (regType === 'NEW_PATIENT' || regType === 'UNKNOWN') {
                _phoneTimer = setTimeout(function () { checkPhoneUniqueness(phone); }, 600);
            } else {
                phoneSetState('', '');
            }
        });
    }

    /* ── Switch to Existing Patient ─────────────────────────────────────── */
    document.getElementById('switchToExisting').addEventListener('click', function (e) {
        e.preventDefault();
        regTypeEl.value = 'EXISTING_PATIENT';
        handleRegType();

        if (_dupPatientId && _dupPatientName) {
            var phone = contactEl.value.trim();
            var label = _dupPatientName + ' | ' + phone;
            var opt   = new Option(label, _dupPatientId, true, true);
            $('#patient_search').append(opt).trigger('change');
        }

        warnEl.style.display = 'none';
        phoneSetState('', '');
    });

    /* ── Description character counter ─────────────────────────────────── */
    var descCount = document.getElementById('descCount');
    if (descEl && descCount) {
        descEl.addEventListener('input', function () {
            descCount.textContent = this.value.length;
        });
    }

    /* ── File input label ───────────────────────────────────────────────── */
    var docInput    = document.getElementById('supporting_doc');
    var docFileInfo = document.getElementById('docFileInfo');
    if (docInput && docFileInfo) {
        docInput.addEventListener('change', function () {
            if (this.files.length) {
                var f    = this.files[0];
                var size = (f.size / 1024 / 1024).toFixed(2);
                var ok   = f.size <= 5 * 1024 * 1024;
                docFileInfo.innerHTML = ok
                    ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>' + f.name + ' (' + size + ' MB)</span>'
                    : '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>' + f.name + ' — exceeds 5 MB limit</span>';
            } else {
                docFileInfo.textContent = 'PDF, DOCX, PNG, JPEG · max 5 MB';
            }
        });
    }

    /* ── Shift / Slot AJAX ──────────────────────────────────────────────── */
    var _shiftXhr = null;
    var _slotXhr  = null;

    function fmt12(t) {
        var p  = t.split(':'), h = +p[0], m = +p[1];
        var ap = h >= 12 ? 'PM' : 'AM';
        var hh = ((h + 11) % 12 + 1);
        return String(hh).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ap;
    }

    function loadFdShifts() {
        var doctorId = document.getElementById('doctor_id').value;
        var $shift   = $('#shift_id');
        var $slot    = $('#slot_time');

        if (_shiftXhr) { _shiftXhr.abort(); _shiftXhr = null; }
        if (_slotXhr)  { _slotXhr.abort();  _slotXhr  = null; }

        $shift.html('<option value="">Loading…</option>');
        $slot.html('<option value="">— Select Shift First —</option>');

        if (!doctorId) {
            $shift.html('<option value="">— Select Doctor First —</option>');
            return;
        }

        _shiftXhr = $.ajax({
            url:      "{{ route('appointments.get-doctor-shifts') }}",
            type:     'POST',
            data:     { _token: "{{ csrf_token() }}", doctor_id: doctorId },
            dataType: 'json',
            success: function (list) {
                _shiftXhr = null;
                if (!list || !list.length) {
                    $shift.html('<option value="">No shifts configured</option>');
                    return;
                }
                $shift.html('<option value="">— Select Shift —</option>');
                var _seenShift = {};
                $.each(list, function (i, s) {
                    if (!_seenShift[s.name]) {
                        _seenShift[s.name] = true;
                        $shift.append($('<option>', { value: s.id, text: s.name }));
                    }
                });
                var old = "{{ old('shift_id') }}";
                if (old) { $shift.val(old); loadFdSlots(); }
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $shift.html('<option value="">Failed (' + xhr.status + ')</option>');
            }
        });
    }

    function loadFdSlots() {
        var doctorId = document.getElementById('doctor_id').value;
        var shiftId  = document.getElementById('shift_id').value;
        var dateVal  = document.getElementById('visitFieldInput').value;
        var $slot    = $('#slot_time');

        if (_slotXhr) { _slotXhr.abort(); _slotXhr = null; }
        $slot.html('<option value="">— Select Slot —</option>');
        if (!doctorId || !shiftId || !dateVal) return;

        _slotXhr = $.ajax({
            url:      "{{ route('appointments.get-slots') }}",
            type:     'POST',
            data:     { _token: "{{ csrf_token() }}", doctor_id: doctorId, shift_id: shiftId, date: dateVal.substring(0, 10) },
            dataType: 'json',
            success: function (list) {
                _slotXhr = null;
                if (!list || !list.length) {
                    $slot.html('<option value="">No slots available</option>');
                    return;
                }
                $slot.html('<option value="">— Select Slot —</option>');
                var _seenSlot = {};
                $.each(list, function (i, s) {
                    var _slotKey = s.time_from + '|' + s.time_to;
                    if (!_seenSlot[_slotKey]) {
                        _seenSlot[_slotKey] = true;
                        $slot.append($('<option>', { value: _slotKey, text: fmt12(s.time_from) + ' – ' + fmt12(s.time_to) }));
                    }
                });
                var old = "{{ old('slot_time') }}";
                if (old) $slot.val(old);
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $slot.html('<option value="">Failed (' + xhr.status + ')</option>');
            }
        });
    }

    document.getElementById('doctor_id').addEventListener('change', function () {
        if (patientTypeEl.value === 'OPD') {
            loadFdShifts();
            loadFdDoctorFee(this.value);
        }
    });
    document.getElementById('shift_id').addEventListener('change', loadFdSlots);
    document.getElementById('visitFieldInput').addEventListener('change', loadFdSlots);

    /* ── Init: single shift load after all setup is complete ───────────── */
    _fdReady = true; // from here on, toggleByType() may call loadFdShifts()
    if (document.getElementById('doctor_id').value && patientTypeEl.value === 'OPD') {
        loadFdShifts();
    }

    @if(old('discount_type') === 'CORPORATE' || old('discount_type') === 'INSURANCE')
        show(orgFields);
    @endif

    @if(old('registration_type') === 'EXISTING_PATIENT')
        show(existingWrap);
    @endif

    /* ── Doctor Fee (OPD billing) ───────────────────────────────────────── */
    var _fdFeeData = null;
    var _fdFeeXhr  = null;

    function fdResetFee() {
        _fdFeeData = null;
        $('#fdDoctorFeeBox, #fdDoctorFeeNone').hide();
        $('#fd_standard_charge, #fd_applied_charge, #fd_amount').val('');
        $('#fd_discount').val(0);
        $('#fd_tax').val(0);
    }

    function fdCalculateAmount() {
        var applied    = parseFloat($('#fd_applied_charge').val()) || 0;
        var discount   = parseFloat($('#fd_discount').val())       || 0;
        var tax        = parseFloat($('#fd_tax').val())            || 0;
        var discounted = Math.max(applied - discount, 0);
        var total      = discounted + (discounted * tax / 100);
        $('#fd_amount').val(total.toFixed(2));
    }

    function fdApplyFee() {
        if (!_fdFeeData) return;
        var fee = _fdFeeData.first_visit_fee
               ?? _fdFeeData.opd_visit_fee
               ?? _fdFeeData.follow_up_fee
               ?? 0;
        var val = fee !== null ? parseFloat(fee) : 0;
        $('#fd_standard_charge').val(val.toFixed(2));
        $('#fd_applied_charge').val(val.toFixed(2));
        fdCalculateAmount();
    }

    function loadFdDoctorFee(doctorId) {
        $('#fdDoctorFeeBox, #fdDoctorFeeNone').hide();
        if (_fdFeeXhr) { _fdFeeXhr.abort(); _fdFeeXhr = null; }
        _fdFeeData = null;

        if (!doctorId) { fdResetFee(); return; }

        $('#fdDoctorFeeLoading').show();

        _fdFeeXhr = $.get('{{ route('opd-patients.get-doctor-opd-fee') }}', { doctor_id: doctorId })
            .done(function (data) {
                $('#fdDoctorFeeLoading').hide();
                var hasAny = data.opd_visit_fee !== null || data.first_visit_fee !== null || data.follow_up_fee !== null;
                if (!hasAny) { $('#fdDoctorFeeNone').show(); fdResetFee(); return; }

                _fdFeeData = data;
                var fmt = function (v) { return v !== null ? parseFloat(v).toFixed(2) : '—'; };
                $('#fd_fee_opd_visit').text(fmt(data.opd_visit_fee));
                $('#fd_fee_first_visit').text(fmt(data.first_visit_fee));
                $('#fd_fee_follow_up').text(fmt(data.follow_up_fee));
                $('#fd_fee_follow_up_window').text(data.follow_up_window !== null ? data.follow_up_window + ' days' : '—');
                $('#fdDoctorFeeBox').show();
                fdApplyFee();
            })
            .fail(function (xhr) {
                $('#fdDoctorFeeLoading').hide();
                if (xhr.statusText !== 'abort') fdResetFee();
            });
    }

    $('#fd_applied_charge, #fd_discount, #fd_tax').on('input change', fdCalculateAmount);

    // Init fee on validation-error repopulate
    (function () {
        var initDoc  = document.getElementById('doctor_id').value;
        var initType = patientTypeEl ? patientTypeEl.value : '';
        if (initDoc && initType === 'OPD') loadFdDoctorFee(initDoc);
    }());

})();
</script>
