@php
    $genders = ['Male', 'Female', 'Other'];
    $bloods  = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<style>
    .qr-card {
        border: 1px solid #e3e8ef;
        border-radius: .55rem;
        padding: .9rem 1rem;
        background: #f8fafc;
        margin-bottom: .85rem;
    }
    .qr-card-title {
        font-size: .67rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-bottom: .7rem;
    }
    .qr-card-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e3e8ef;
    }
    .qr-card-num {
        width: 1.3rem;
        height: 1.3rem;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-size: .6rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .qr-phone-status { font-size: .75rem; min-height: 1rem; margin-top: .2rem; }
    /* Patient type toggle */
    .qr-type-btn { font-size: .78rem; padding: .3rem .9rem; }
    /* ER priority pills */
    .qr-prio-btn { font-size: .75rem; padding: .25rem .7rem; }
    /* Submit spinner */
    #qrSubmitBtn:disabled { opacity: .85; }
</style>

<form action="{{ route('front_desk.quickreg.store') }}" method="POST"
      enctype="multipart/form-data" id="qrForm" novalidate>
    @csrf

    {{-- Always new patient --}}
    <input type="hidden" name="patient_mode" value="new">
    {{-- visit_type always "new" for quick reg --}}
    <input type="hidden" name="visit_type" value="new">

    {{-- ── Server errors ──────────────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible py-2 mb-3 d-flex gap-2 align-items-start small" role="alert">
            <i class="bi bi-exclamation-octagon-fill flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">
                <strong>{{ $errors->count() }} issue(s):</strong>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Client-side error summary --}}
    <div id="qrValSummary" style="display:none;"
         class="alert alert-warning py-2 px-3 mb-3 small d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div id="qrValBody" class="flex-grow-1"></div>
    </div>

    {{-- ══ SECTION 1 — PATIENT ══════════════════════════════════════════════ --}}
    <div class="qr-card">
        <div class="qr-card-title">
            <span class="qr-card-num">1</span> New Patient
        </div>

        <div class="row g-2">

            {{-- Name --}}
            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Full Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="patient_name" id="qr_name"
                       class="form-control form-control-sm @error('patient_name') is-invalid @enderror"
                       value="{{ old('patient_name') }}" placeholder="Patient full name"
                       autocomplete="off">
                @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="qr-js-err" id="qr_name_err" style="display:none; font-size:.77rem; color:#dc3545; margin-top:.2rem;"></div>
            </div>

            {{-- Contact No --}}
            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Contact No. <span class="text-danger">*</span>
                    <span id="qrPhoneSpinner" class="spinner-border spinner-border-sm ms-1 text-secondary d-none"
                          style="width:.75rem;height:.75rem;"></span>
                </label>
                <input type="text" name="mobileno" id="qr_phone"
                       class="form-control form-control-sm @error('mobileno') is-invalid @enderror"
                       value="{{ old('mobileno') }}" placeholder="01XXXXXXXXX"
                       autocomplete="off" inputmode="tel">
                @error('mobileno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="qr-js-err" id="qr_phone_err" style="display:none; font-size:.77rem; color:#dc3545; margin-top:.2rem;"></div>
                <div class="qr-phone-status" id="qrPhoneMsg"></div>
            </div>

            {{-- Gender --}}
            <div class="col-4 col-md-3">
                <label class="form-label form-label-sm fw-medium mb-1">Gender</label>
                <select name="gender" class="form-select form-select-sm">
                    <option value="">--</option>
                    @foreach ($genders as $g)
                        <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>

            {{-- DOB --}}
            <div class="col-5 col-md-5">
                <label class="form-label form-label-sm fw-medium mb-1">Date of Birth</label>
                <input type="date" name="dob" id="qr_dob"
                       value="{{ old('dob') }}"
                       class="form-control form-control-sm">
                <div class="form-text" id="qrAgeDisplay" style="min-height:.9rem;"></div>
            </div>

            {{-- Blood Group --}}
            <div class="col-3 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Blood Group</label>
                <select name="blood_group" class="form-select form-select-sm">
                    <option value="">--</option>
                    @foreach ($bloods as $b)
                        <option value="{{ $b }}" @selected(old('blood_group') === $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    {{-- ══ SECTION 2 — VISIT ════════════════════════════════════════════════ --}}
    <div class="qr-card">
        <div class="qr-card-title">
            <span class="qr-card-num">2</span> Visit Details
        </div>

        <div class="row g-2">

            {{-- Patient Type toggle --}}
            <div class="col-12">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Patient Type <span class="text-danger">*</span>
                </label>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="patient_type" id="qrTypeOpd"
                           value="OPD" autocomplete="off"
                           {{ old('patient_type', 'OPD') === 'OPD' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary qr-type-btn" for="qrTypeOpd">
                        <i class="bi bi-clipboard2-pulse me-1"></i>OPD
                    </label>

                    <input type="radio" class="btn-check" name="patient_type" id="qrTypeEr"
                           value="ER" autocomplete="off"
                           {{ old('patient_type') === 'ER' ? 'checked' : '' }}>
                    <label class="btn btn-outline-danger qr-type-btn" for="qrTypeEr">
                        <i class="bi bi-activity me-1"></i>Emergency
                    </label>

                    <input type="radio" class="btn-check" name="patient_type" id="qrTypeLab"
                           value="LAB" autocomplete="off"
                           {{ old('patient_type') === 'LAB' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary qr-type-btn" for="qrTypeLab">
                        <i class="bi bi-flask me-1"></i>Lab Only
                    </label>
                </div>
                <div class="qr-js-err" id="qr_type_err" style="display:none; font-size:.77rem; color:#dc3545; margin-top:.2rem;"></div>
            </div>

            {{-- Date --}}
            <div class="col-6 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="date" id="qr_date"
                       class="form-control form-control-sm @error('date') is-invalid @enderror"
                       value="{{ old('date', date('Y-m-d')) }}">
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Department --}}
            <div class="col-12 col-md-8">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Department <span class="text-danger">*</span>
                </label>
                <select name="department_id" id="qr_dept"
                        class="form-select form-select-sm @error('department_id') is-invalid @enderror">
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="qr-js-err" id="qr_dept_err" style="display:none; font-size:.77rem; color:#dc3545; margin-top:.2rem;"></div>
            </div>

            {{-- Doctor --}}
            <div class="col-12">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Doctor <span class="text-danger">*</span>
                </label>
                <select name="doctor_id" id="qr_doctor"
                        class="form-select form-select-sm @error('doctor_id') is-invalid @enderror">
                    <option value="">-- Select Department First --</option>
                    @foreach ($doctors as $d)
                        <option value="{{ $d->id }}" data-dept="{{ $d->department_id }}"
                                @selected(old('doctor_id') == $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
                @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="qr-js-err" id="qr_doctor_err" style="display:none; font-size:.77rem; color:#dc3545; margin-top:.2rem;"></div>
            </div>

            {{-- ER Priority (shown only for ER) --}}
            <div class="col-12" id="qrErPanel" style="display:none;">
                <label class="form-label form-label-sm fw-medium mb-1">ER Priority</label>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="er_priority" id="qrErNormal"
                           value="NORMAL" autocomplete="off"
                           {{ old('er_priority', 'NORMAL') === 'NORMAL' ? 'checked' : '' }}>
                    <label class="btn btn-outline-secondary qr-prio-btn" for="qrErNormal">Normal</label>

                    <input type="radio" class="btn-check" name="er_priority" id="qrErHigh"
                           value="HIGH" autocomplete="off"
                           {{ old('er_priority') === 'HIGH' ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning qr-prio-btn" for="qrErHigh">High</label>

                    <input type="radio" class="btn-check" name="er_priority" id="qrErCritical"
                           value="CRITICAL" autocomplete="off"
                           {{ old('er_priority') === 'CRITICAL' ? 'checked' : '' }}>
                    <label class="btn btn-outline-danger qr-prio-btn" for="qrErCritical">Critical</label>
                </div>
            </div>

            {{-- LAB hint --}}
            <div class="col-12" id="qrLabHint" style="display:none;">
                <div class="alert alert-info py-2 px-3 mb-0 small">
                    <i class="bi bi-flask me-1"></i>
                    After saving you will be redirected to create lab orders.
                </div>
            </div>

        </div>
    </div>

    {{-- ── Actions ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-2 pt-1">
        <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="qrSubmitBtn" class="btn btn-sm btn-primary px-4">
            <i class="bi bi-check2-circle me-1"></i>Register
        </button>
    </div>

</form>

<script>
(function () {
    var PHONE_URL = "{{ route('front_desk.check.phone') }}";

    /* ── Element refs ───────────────────────────────────────────────────── */
    var nameEl    = document.getElementById('qr_name');
    var phoneEl   = document.getElementById('qr_phone');
    var deptEl    = document.getElementById('qr_dept');
    var doctorEl  = document.getElementById('qr_doctor');
    var dobEl     = document.getElementById('qr_dob');
    var ageEl     = document.getElementById('qrAgeDisplay');
    var spinner   = document.getElementById('qrPhoneSpinner');
    var phoneMsg  = document.getElementById('qrPhoneMsg');
    var erPanel   = document.getElementById('qrErPanel');
    var labHint   = document.getElementById('qrLabHint');

    /* ── Age calculator ─────────────────────────────────────────────────── */
    function calcAge(dob) {
        if (!dob) return '';
        var d = new Date(dob), t = new Date();
        var age = t.getFullYear() - d.getFullYear();
        if (t.getMonth() < d.getMonth() || (t.getMonth() === d.getMonth() && t.getDate() < d.getDate())) age--;
        return age >= 0 ? 'Age: ' + age + ' yrs' : '';
    }
    if (dobEl) {
        dobEl.addEventListener('change', function () { if (ageEl) ageEl.textContent = calcAge(this.value); });
        if (dobEl.value && ageEl) ageEl.textContent = calcAge(dobEl.value);
    }

    /* ── Patient Type → show/hide ER panel ─────────────────────────────── */
    function getPatientType() {
        var r = document.querySelector('input[name="patient_type"]:checked');
        return r ? r.value : '';
    }
    function syncTypeUI() {
        var t = getPatientType();
        if (erPanel)  erPanel.style.display  = (t === 'ER')  ? '' : 'none';
        if (labHint)  labHint.style.display  = (t === 'LAB') ? '' : 'none';
    }
    document.querySelectorAll('input[name="patient_type"]').forEach(function (r) {
        r.addEventListener('change', syncTypeUI);
    });
    syncTypeUI();

    /* ── Department → Doctor filter ─────────────────────────────────────── */
    var allDoctorOpts = Array.from(doctorEl.querySelectorAll('option[data-dept]'))
                             .map(function (o) { return o.cloneNode(true); });

    function filterDoctors(deptId) {
        var cur = doctorEl.value;
        while (doctorEl.options.length > 1) doctorEl.remove(1);
        if (!deptId) {
            doctorEl.options[0].text = '-- Select Department First --';
            return;
        }
        var matches = allDoctorOpts.filter(function (o) {
            return String(o.dataset.dept) === String(deptId);
        });
        doctorEl.options[0].text = matches.length ? '-- Select Doctor --' : '-- No doctors in this department --';
        matches.forEach(function (o) { doctorEl.add(o.cloneNode(true)); });
        if (matches.some(function (o) { return o.value === cur; })) doctorEl.value = cur;
    }

    deptEl.addEventListener('change', function () {
        filterDoctors(this.value);
        doctorEl.value = '';
        qrClearErr('qr_dept_err');
    });
    doctorEl.addEventListener('change', function () { qrClearErr('qr_doctor_err'); });

    /* ── Phone uniqueness check (new patient only) ───────────────────────── */
    var _phoneTimer = null;
    var _phoneXhr   = null;
    if (phoneEl) {
        phoneEl.addEventListener('input', function () {
            var phone = this.value.trim();
            clearTimeout(_phoneTimer);
            if (_phoneXhr) { _phoneXhr.abort(); _phoneXhr = null; }
            phoneMsg.innerHTML = '';
            spinner.classList.add('d-none');
            qrClearErr('qr_phone_err');

            var digits = phone.replace(/[\s\-\(\)\+]/g, '');
            if (digits.length < 7) return;

            spinner.classList.remove('d-none');
            _phoneTimer = setTimeout(function () {
                _phoneXhr = fetch(PHONE_URL + '?phone=' + encodeURIComponent(phone), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
                }).then(function (r) { return r.json(); })
                  .then(function (data) {
                      spinner.classList.add('d-none');
                      _phoneXhr = null;
                      if (data.exists) {
                          phoneMsg.innerHTML =
                              '<span class="text-warning fw-medium">'
                              + '<i class="bi bi-exclamation-triangle me-1"></i>'
                              + 'Already registered — <strong>' + data.patient_name + '</strong>'
                              + ' (MRN: ' + (data.mrn ?? '—') + '). Use the full Patient Onboarding form to register an existing patient.'
                              + '</span>';
                      } else {
                          phoneMsg.innerHTML =
                              '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Available</span>';
                      }
                  }).catch(function () { spinner.classList.add('d-none'); _phoneXhr = null; });
            }, 600);
        });
    }

    /* ── Client-side validation ──────────────────────────────────────────── */
    function qrSetErr(id, msg) {
        var el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
        return msg;
    }
    function qrClearErr(id) {
        var el = document.getElementById(id);
        if (el) { el.textContent = ''; el.style.display = 'none'; }
    }
    function qrClearAll() {
        ['qr_name_err','qr_phone_err','qr_type_err','qr_dept_err','qr_doctor_err'].forEach(qrClearErr);
        document.getElementById('qrValSummary').style.display = 'none';
        document.getElementById('qrValBody').innerHTML = '';
    }

    function qrValidate() {
        qrClearAll();
        var errs = [];
        var patType = getPatientType();

        if (!nameEl.value.trim())     errs.push(qrSetErr('qr_name_err',   'Patient full name is required.'));
        if (!phoneEl.value.trim())    errs.push(qrSetErr('qr_phone_err',  'Contact number is required.'));
        if (!patType)                 errs.push(qrSetErr('qr_type_err',   'Please select a patient type.'));
        if (!deptEl.value)            errs.push(qrSetErr('qr_dept_err',   'Please select a department.'));
        if (!doctorEl.value)          errs.push(qrSetErr('qr_doctor_err', 'Please select a doctor.'));

        if (errs.length) {
            var sumEl  = document.getElementById('qrValSummary');
            var bodyEl = document.getElementById('qrValBody');
            var label  = errs.length === 1 ? '1 required field needs attention:' : errs.length + ' required fields need attention:';
            bodyEl.innerHTML = '<strong>' + label + '</strong><ul class="mb-0 mt-1 ps-3">'
                + errs.map(function (e) { return '<li>' + e + '</li>'; }).join('')
                + '</ul>';
            sumEl.style.display = '';
            /* Scroll to first error */
            var first = document.querySelector('.qr-js-err[style*="block"]');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            /* Shake submit */
            var btn = document.getElementById('qrSubmitBtn');
            btn.classList.add('shake-once');
            setTimeout(function () { btn.classList.remove('shake-once'); }, 400);
        }
        return errs.length === 0;
    }

    document.getElementById('qrForm').addEventListener('submit', function (e) {
        if (!qrValidate()) { e.preventDefault(); return; }
        var btn = document.getElementById('qrSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.8rem;height:.8rem;"></span>Saving…';
    });

    /* Live clear on input */
    nameEl.addEventListener('input',   function () { qrClearErr('qr_name_err'); });
    phoneEl.addEventListener('input',  function () { qrClearErr('qr_phone_err'); });
    deptEl.addEventListener('change',  function () { qrClearErr('qr_dept_err'); });
    doctorEl.addEventListener('change',function () { qrClearErr('qr_doctor_err'); });
    document.querySelectorAll('input[name="patient_type"]').forEach(function (r) {
        r.addEventListener('change', function () { qrClearErr('qr_type_err'); });
    });

    /* ── Init: restore old() values after validation error ──────────────── */
    (function init() {
        @if (old('department_id'))
            filterDoctors('{{ old("department_id") }}');
            doctorEl.value = '{{ old("doctor_id", "") }}';
        @endif
    }());

}());
</script>

{{-- Shake animation (reuse same keyframe as main form if loaded, else define inline) --}}
<style>
    @keyframes shake-once {
        0%,100% { transform: translateX(0); }
        20%,60%  { transform: translateX(-5px); }
        40%,80%  { transform: translateX(5px); }
    }
    .shake-once { animation: shake-once .35s ease; }
</style>
