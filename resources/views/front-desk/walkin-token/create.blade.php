@php
    $genders = ['Male', 'Female', 'Other'];
    $bloods  = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<style>
    .wt-card {
        border: 1px solid #e3e8ef;
        border-radius: .55rem;
        padding: .9rem 1rem;
        background: #f8fafc;
        margin-bottom: .85rem;
    }
    .wt-card-title {
        font-size: .67rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-bottom: .75rem;
    }
    .wt-card-title::after { content:''; flex:1; height:1px; background:#e3e8ef; }
    .wt-card-num {
        width: 1.3rem; height: 1.3rem; border-radius: 50%;
        background: #fd7e14; color: #fff;
        font-size: .6rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .wt-patient-found {
        background: #d1e7dd;
        border: 1px solid #a3cfbb;
        border-radius: .4rem;
        padding: .55rem .8rem;
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .83rem;
    }
    .wt-patient-found .wt-mrn {
        font-size: .72rem;
        color: #0f5132;
        background: rgba(0,0,0,.06);
        border-radius: 3px;
        padding: 1px 5px;
    }
    .wt-js-err { font-size:.77rem; color:#dc3545; margin-top:.2rem; display:none; }
    #wtSubmitBtn:disabled { opacity:.85; }
    @keyframes wt-shake {
        0%,100% { transform:translateX(0); }
        20%,60%  { transform:translateX(-5px); }
        40%,80%  { transform:translateX(5px); }
    }
    .wt-shake { animation: wt-shake .35s ease; }
</style>

<form action="{{ route('front_desk.walkintoken.store') }}" method="POST" id="wtForm" novalidate>
    @csrf
    <input type="hidden" name="patient_id" id="wt_patient_id" value="{{ old('patient_id') }}">
    <input type="hidden" name="patient_mode" id="wt_patient_mode" value="{{ old('patient_mode', 'new') }}">

    {{-- Server errors --}}
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

    {{-- Client-side validation summary --}}
    <div id="wtValSummary" style="display:none;"
         class="alert alert-warning py-2 px-3 mb-3 small d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div id="wtValBody" class="flex-grow-1"></div>
    </div>

    {{-- ══ SECTION 1 — PATIENT ══════════════════════════════════════════════ --}}
    <div class="wt-card">
        <div class="wt-card-title">
            <span class="wt-card-num">1</span> Patient
        </div>

        <div class="row g-2">

            {{-- Phone — primary lookup field --}}
            <div class="col-12">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Phone Number <span class="text-danger">*</span>
                    <span id="wtPhoneSpinner"
                          class="spinner-border spinner-border-sm ms-1 text-secondary d-none"
                          style="width:.75rem;height:.75rem;"></span>
                </label>
                <input type="text" name="mobileno" id="wt_phone"
                       class="form-control form-control-sm @error('mobileno') is-invalid @enderror"
                       value="{{ old('mobileno') }}"
                       placeholder="Enter patient phone number" autocomplete="off" inputmode="tel">
                @error('mobileno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="wt-js-err" id="wt_phone_err"></div>
            </div>

            {{-- Existing patient found card --}}
            <div class="col-12" id="wtFoundCard" style="display:none;">
                <div class="wt-patient-found">
                    <i class="bi bi-person-check-fill text-success fs-5"></i>
                    <div class="flex-grow-1">
                        <span class="fw-semibold" id="wtFoundName"></span>
                        <span class="wt-mrn ms-2" id="wtFoundMrn"></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                            id="wtClearPatient" style="font-size:.72rem;">
                        <i class="bi bi-x me-1"></i>Clear
                    </button>
                </div>
            </div>

            {{-- New patient fields — shown when phone not found --}}
            <div id="wtNewFields" style="display:none;" class="col-12">
                <div class="row g-2">
                    <div class="col-12 col-md-7">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="patient_name" id="wt_name"
                               class="form-control form-control-sm @error('patient_name') is-invalid @enderror"
                               value="{{ old('patient_name') }}" placeholder="Patient full name" autocomplete="off">
                        @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="wt-js-err" id="wt_name_err"></div>
                    </div>

                    <div class="col-6 col-md-5">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Gender <span class="text-danger">*</span>
                        </label>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            @foreach ($genders as $g)
                                <input type="radio" class="btn-check" name="gender" id="wtGender{{ $g }}"
                                       value="{{ $g }}" autocomplete="off"
                                       {{ old('gender') === $g ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary" for="wtGender{{ $g }}"
                                       style="font-size:.72rem; padding:.25rem .4rem;">{{ $g }}</label>
                            @endforeach
                        </div>
                        <div class="wt-js-err" id="wt_gender_err"></div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label form-label-sm fw-medium mb-1">Date of Birth</label>
                        <input type="date" name="dob" id="wt_dob" value="{{ old('dob') }}"
                               class="form-control form-control-sm">
                        <div class="form-text" id="wtAgeDisplay" style="min-height:.9rem;"></div>
                    </div>

                    <div class="col-6 col-md-4">
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

        </div>
    </div>

    {{-- ══ SECTION 2 — VISIT ════════════════════════════════════════════════ --}}
    <div class="wt-card">
        <div class="wt-card-title">
            <span class="wt-card-num">2</span> Visit Details
        </div>

        <div class="row g-2">

            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Department <span class="text-danger">*</span>
                </label>
                <select name="department_id" id="wt_dept"
                        class="form-select form-select-sm @error('department_id') is-invalid @enderror">
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="wt-js-err" id="wt_dept_err"></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Doctor <span class="text-danger">*</span>
                </label>
                <select name="doctor_id" id="wt_doctor"
                        class="form-select form-select-sm @error('doctor_id') is-invalid @enderror">
                    <option value="">-- Select Department First --</option>
                    @foreach ($doctors as $d)
                        <option value="{{ $d->id }}" data-dept="{{ $d->department_id }}"
                                @selected(old('doctor_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="wt-js-err" id="wt_doctor_err"></div>
            </div>

            <div class="col-6 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="date" id="wt_date"
                       class="form-control form-control-sm @error('date') is-invalid @enderror"
                       value="{{ old('date', date('Y-m-d')) }}">
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2 pt-1">
        <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="wtSubmitBtn" class="btn btn-sm btn-warning px-4 fw-semibold">
            <i class="bi bi-ticket-perforated me-1"></i>Register &amp; Print Token
        </button>
    </div>

</form>

<script>
(function () {
    var PHONE_URL = "{{ route('front_desk.check.phone') }}";

    /* ── Refs ─────────────────────────────────────────────────────────────── */
    var phoneEl    = document.getElementById('wt_phone');
    var nameEl     = document.getElementById('wt_name');
    var deptEl     = document.getElementById('wt_dept');
    var doctorEl   = document.getElementById('wt_doctor');
    var dobEl      = document.getElementById('wt_dob');
    var ageEl      = document.getElementById('wtAgeDisplay');
    var spinner    = document.getElementById('wtPhoneSpinner');
    var foundCard  = document.getElementById('wtFoundCard');
    var foundName  = document.getElementById('wtFoundName');
    var foundMrn   = document.getElementById('wtFoundMrn');
    var newFields  = document.getElementById('wtNewFields');
    var patientId  = document.getElementById('wt_patient_id');
    var patientMode= document.getElementById('wt_patient_mode');
    var clearBtn   = document.getElementById('wtClearPatient');

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

    /* ── Patient mode helpers ───────────────────────────────────────────── */
    function showExisting(id, name, mrn) {
        patientId.value   = id;
        patientMode.value = 'existing';
        foundName.textContent = name;
        foundMrn.textContent  = 'MRN: ' + (mrn || '—');
        foundCard.style.display = '';
        newFields.style.display = 'none';
        /* clear new-patient required fields so they don't block validation */
        if (nameEl) nameEl.value = '';
    }

    function showNewFields() {
        patientId.value   = '';
        patientMode.value = 'new';
        foundCard.style.display = 'none';
        newFields.style.display = '';
    }

    function resetPatient() {
        patientId.value   = '';
        patientMode.value = 'new';
        foundCard.style.display = 'none';
        newFields.style.display = 'none';
    }

    /* ── Phone lookup ───────────────────────────────────────────────────── */
    var _phoneTimer = null;
    var _phoneXhr   = null;

    phoneEl.addEventListener('input', function () {
        var phone = this.value.trim();
        clearTimeout(_phoneTimer);
        if (_phoneXhr) { _phoneXhr.abort && _phoneXhr.abort(); _phoneXhr = null; }
        spinner.classList.add('d-none');
        wtClearErr('wt_phone_err');
        resetPatient();

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
                      showExisting(data.patient_id, data.patient_name, data.mrn);
                  } else {
                      showNewFields();
                  }
              }).catch(function () {
                  spinner.classList.add('d-none');
                  _phoneXhr = null;
                  showNewFields();
              });
        }, 500);
    });

    /* Clear existing patient → back to new fields */
    clearBtn.addEventListener('click', function () {
        phoneEl.value = '';
        resetPatient();
        phoneEl.focus();
    });

    /* ── Doctor filter (client-side) ────────────────────────────────────── */
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
        wtClearErr('wt_dept_err');
    });
    doctorEl.addEventListener('change', function () { wtClearErr('wt_doctor_err'); });

    /* ── Client-side validation ──────────────────────────────────────────── */
    function wtSetErr(id, msg) {
        var el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
        return msg;
    }
    function wtClearErr(id) {
        var el = document.getElementById(id);
        if (el) { el.textContent = ''; el.style.display = 'none'; }
    }
    function wtClearAll() {
        ['wt_phone_err','wt_name_err','wt_gender_err','wt_dept_err','wt_doctor_err'].forEach(wtClearErr);
        document.getElementById('wtValSummary').style.display = 'none';
        document.getElementById('wtValBody').innerHTML = '';
    }

    function wtValidate() {
        wtClearAll();
        var errs = [];
        var isExisting = patientMode.value === 'existing';

        if (!phoneEl.value.trim())
            errs.push(wtSetErr('wt_phone_err', 'Phone number is required.'));

        if (!isExisting) {
            if (!nameEl || !nameEl.value.trim())
                errs.push(wtSetErr('wt_name_err', 'Patient name is required.'));
            var genderChecked = document.querySelector('input[name="gender"]:checked');
            if (!genderChecked)
                errs.push(wtSetErr('wt_gender_err', 'Please select a gender.'));
        }

        if (!deptEl.value)
            errs.push(wtSetErr('wt_dept_err', 'Please select a department.'));
        if (!doctorEl.value)
            errs.push(wtSetErr('wt_doctor_err', 'Please select a doctor.'));

        if (errs.length) {
            var sumEl  = document.getElementById('wtValSummary');
            var bodyEl = document.getElementById('wtValBody');
            var label  = errs.length === 1 ? '1 field needs attention:' : errs.length + ' fields need attention:';
            bodyEl.innerHTML = '<strong>' + label + '</strong><ul class="mb-0 mt-1 ps-3">'
                + errs.map(function (e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
            sumEl.style.display = '';
            var first = document.querySelector('.wt-js-err[style*="block"]');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var btn = document.getElementById('wtSubmitBtn');
            btn.classList.add('wt-shake');
            setTimeout(function () { btn.classList.remove('wt-shake'); }, 400);
        }
        return errs.length === 0;
    }

    document.getElementById('wtForm').addEventListener('submit', function (e) {
        if (!wtValidate()) { e.preventDefault(); return; }
        var btn = document.getElementById('wtSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.8rem;height:.8rem;"></span>Saving…';
    });

    /* Live clear */
    phoneEl.addEventListener('input',  function () { wtClearErr('wt_phone_err'); });
    if (nameEl) nameEl.addEventListener('input', function () { wtClearErr('wt_name_err'); });
    document.querySelectorAll('input[name="gender"]').forEach(function (r) {
        r.addEventListener('change', function () { wtClearErr('wt_gender_err'); });
    });

    /* ── Init: restore old() state after server validation fail ─────────── */
    (function init() {
        @if (old('department_id'))
            filterDoctors('{{ old("department_id") }}');
            doctorEl.value = '{{ old("doctor_id", "") }}';
        @endif
        @if (old('patient_mode') === 'existing' && old('patient_id'))
            showExisting('{{ old("patient_id") }}', '', '');
            phoneEl.value = '{{ old("mobileno", "") }}';
        @elseif (old('mobileno'))
            showNewFields();
        @endif
    }());

}());
</script>
