        @php
            $genders = ['Male', 'Female', 'Other'];
            $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        @endphp

        <style>
            .er-card {
                border: 1px solid #f8d7da;
                border-radius: .55rem;
                padding: .9rem 1rem;
                background: #fff;
                margin-bottom: .85rem;
            }

            .er-card-title {
                font-size: .67rem;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: #dc3545;
                display: flex;
                align-items: center;
                gap: .4rem;
                margin-bottom: .75rem;
            }

            .er-card-title::after {
                content: '';
                flex: 1;
                height: 1px;
                background: #f8d7da;
            }

            .er-card-num {
                width: 1.3rem;
                height: 1.3rem;
                border-radius: 50%;
                background: #dc3545;
                color: #fff;
                font-size: .6rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .er-patient-found {
                background: #d1e7dd;
                border: 1px solid #a3cfbb;
                border-radius: .4rem;
                padding: .55rem .8rem;
                display: flex;
                align-items: center;
                gap: .6rem;
                font-size: .83rem;
            }

            .er-patient-found .er-mrn {
                font-size: .72rem;
                color: #0f5132;
                background: rgba(0, 0, 0, .06);
                border-radius: 3px;
                padding: 1px 5px;
            }

            .er-js-err {
                font-size: .77rem;
                color: #dc3545;
                margin-top: .2rem;
                display: none;
            }

            /* Priority radio pills */
            .er-prio-label {
                font-size: .78rem;
                padding: .35rem .9rem;
                border-radius: .35rem;
                cursor: pointer;
                user-select: none;
                flex: 1;
                text-align: center;
                transition: all .12s;
            }

            #erSubmitBtn:disabled {
                opacity: .85;
            }

            @keyframes er-shake {

                0%,
                100% {
                    transform: translateX(0);
                }

                20%,
                60% {
                    transform: translateX(-5px);
                }

                40%,
                80% {
                    transform: translateX(5px);
                }
            }

            .er-shake {
                animation: er-shake .35s ease;
            }
        </style>

        <form action="{{ route('front_desk.er_registration.store') }}" method="POST" id="erForm" novalidate>
            @csrf
            <input type="hidden" name="patient_id" id="er_patient_id" value="{{ old('patient_id') }}">
            <input type="hidden" name="patient_mode" id="er_patient_mode" value="{{ old('patient_mode', 'new') }}">

            {{-- Server errors --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible py-2 mb-3 d-flex gap-2 align-items-start small"
                    role="alert">
                    <i class="bi bi-exclamation-octagon-fill flex-shrink-0 mt-1"></i>
                    <div class="flex-grow-1">
                        <strong>{{ $errors->count() }} issue(s):</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Client-side validation summary --}}
            <div id="erValSummary" style="display:none;"
                class="alert alert-warning py-2 px-3 mb-3 small d-flex gap-2 align-items-start">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                <div id="erValBody" class="flex-grow-1"></div>
            </div>

            {{-- ══ SECTION 1 — PATIENT ══════════════════════════════════════════════ --}}
            <div class="er-card">
                <div class="er-card-title">
                    <span class="er-card-num">1</span> Patient
                </div>

                <div class="row g-2">

                    {{-- Phone lookup --}}
                    <div class="col-12">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Phone Number <span class="text-danger">*</span>
                            <span id="erPhoneSpinner" class="spinner-border spinner-border-sm ms-1 text-secondary d-none"
                                style="width:.75rem;height:.75rem;"></span>
                        </label>
                        <input type="text" name="contact_no" id="er_phone"
                            class="form-control form-control-sm @error('contact_no') is-invalid @enderror"
                            value="{{ old('contact_no') }}" placeholder="Enter phone number to look up patient"
                            autocomplete="off" inputmode="tel">
                        @error('contact_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="er-js-err" id="er_phone_err"></div>
                    </div>

                    {{-- Existing patient found card --}}
                    <div class="col-12" id="erFoundCard" style="display:none;">
                        <div class="er-patient-found">
                            <i class="bi bi-person-check-fill text-success fs-5"></i>
                            <div class="flex-grow-1">
                                <span class="fw-semibold" id="erFoundName"></span>
                                <span class="er-mrn ms-2" id="erFoundMrn"></span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="erClearPatient"
                                style="font-size:.72rem;">
                                <i class="bi bi-x me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    {{-- New patient fields --}}
                    <div id="erNewFields" style="display:none;" class="col-12">
                        <div class="row g-2">
                            <div class="col-12 col-md-8">
                                <label class="form-label form-label-sm fw-medium mb-1">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="er_name"
                                    class="form-control form-control-sm @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Patient full name" autocomplete="off">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="er-js-err" id="er_name_err"></div>
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label form-label-sm fw-medium mb-1">Age</label>
                                <input type="number" name="age" id="er_age"
                                    class="form-control form-control-sm @error('age') is-invalid @enderror"
                                    value="{{ old('age') }}" min="0" max="150" placeholder="Yrs">
                                @error('age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label form-label-sm fw-medium mb-1">Gender</label>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    @foreach ($genders as $g)
                                        <input type="radio" class="btn-check" name="gender"
                                            id="erGender{{ $g }}" value="{{ $g }}"
                                            autocomplete="off" {{ old('gender') === $g ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="erGender{{ $g }}"
                                            style="font-size:.72rem; padding:.25rem .4rem;">{{ $g }}</label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label form-label-sm fw-medium mb-1">Blood Group</label>
                                <select name="blood_group"
                                    class="form-select form-select-sm @error('blood_group') is-invalid @enderror">
                                    <option value="">--</option>
                                    @foreach ($bloods as $b)
                                        <option value="{{ $b }}" @selected(old('blood_group') === $b)>
                                            {{ $b }}</option>
                                    @endforeach
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label form-label-sm fw-medium mb-1">DOB</label>
                                <input type="date" name="dob" id="er_dob" value="{{ old('dob') }}"
                                    class="form-control form-control-sm">
                                <div class="form-text" id="erAgeFromDob" style="min-height:.9rem;"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ══ SECTION 2 — TRIAGE ══════════════════════════════════════════════ --}}
            <div class="er-card">
                <div class="er-card-title">
                    <span class="er-card-num">2</span> Triage
                </div>

                <div class="row g-2">

                    {{-- Priority (most important in ER — show first and prominently) --}}
                    <div class="col-12">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="priority" id="erPrioNormal" value="NORMAL"
                                autocomplete="off" {{ old('priority', 'NORMAL') === 'NORMAL' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success er-prio-label" for="erPrioNormal">
                                <i class="bi bi-check-circle me-1"></i>Normal
                            </label>

                            <input type="radio" class="btn-check" name="priority" id="erPrioHigh" value="HIGH"
                                autocomplete="off" {{ old('priority') === 'HIGH' ? 'checked' : '' }}>
                            <label class="btn btn-outline-warning er-prio-label" for="erPrioHigh">
                                <i class="bi bi-exclamation-circle me-1"></i>High
                            </label>

                            <input type="radio" class="btn-check" name="priority" id="erPrioCritical"
                                value="CRITICAL" autocomplete="off"
                                {{ old('priority') === 'CRITICAL' ? 'checked' : '' }}>
                            <label class="btn btn-outline-danger er-prio-label" for="erPrioCritical">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Critical
                            </label>
                        </div>
                    </div>

                    {{-- Arrival time --}}
                    <div class="col-6 col-md-4">
                        <label class="form-label form-label-sm fw-medium mb-1">
                            Arrival Time <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" name="arrival_time"
                            class="form-control form-control-sm @error('arrival_time') is-invalid @enderror"
                            value="{{ old('arrival_time', now()->format('Y-m-d\TH:i')) }}">
                        @error('arrival_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-sm fw-medium mb-1">Department</label>
                        <select name="department_id" id="er_dept"
                            class="form-select form-select-sm @error('department_id') is-invalid @enderror">
                            <option value="">-- Select Department --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Doctor --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-sm fw-medium mb-1">Doctor</label>
                        <select name="doctor_id" id="er_doctor"
                            class="form-select form-select-sm @error('doctor_id') is-invalid @enderror">
                            <option value="">-- Select Department First --</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}" data-dept="{{ $d->department_id }}"
                                    @selected(old('doctor_id') == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Chief complaint --}}
                    <div class="col-12">
                        <label class="form-label form-label-sm fw-medium mb-1">Chief Complaint</label>
                        <textarea name="description" rows="2"
                            class="form-control form-control-sm @error('description') is-invalid @enderror"
                            placeholder="Presenting complaint or reason for ER visit…">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-1">
                <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="erSubmitBtn" class="btn btn-sm btn-danger px-4 fw-semibold">
                    <i class="bi bi-activity me-1"></i>Register Emergency
                </button>
            </div>

        </form>

        <script>
            (function() {
                var PHONE_URL = "{{ route('front_desk.check.phone') }}";

                /* ── Refs ─────────────────────────────────────────────────────────────── */
                var phoneEl = document.getElementById('er_phone');
                var nameEl = document.getElementById('er_name');
                var deptEl = document.getElementById('er_dept');
                var doctorEl = document.getElementById('er_doctor');
                var dobEl = document.getElementById('er_dob');
                var ageFromDob = document.getElementById('erAgeFromDob');
                var spinner = document.getElementById('erPhoneSpinner');
                var foundCard = document.getElementById('erFoundCard');
                var foundName = document.getElementById('erFoundName');
                var foundMrn = document.getElementById('erFoundMrn');
                var newFields = document.getElementById('erNewFields');
                var patientId = document.getElementById('er_patient_id');
                var patientMode = document.getElementById('er_patient_mode');
                var clearBtn = document.getElementById('erClearPatient');

                /* ── DOB → age hint ──────────────────────────────────────────────────── */
                function calcAge(dob) {
                    if (!dob) return '';
                    var d = new Date(dob),
                        t = new Date();
                    var age = t.getFullYear() - d.getFullYear();
                    if (t.getMonth() < d.getMonth() || (t.getMonth() === d.getMonth() && t.getDate() < d.getDate())) age--;
                    return age >= 0 ? 'Age: ' + age + ' yrs' : '';
                }
                if (dobEl) {
                    dobEl.addEventListener('change', function() {
                        var label = calcAge(this.value);
                        if (ageFromDob) ageFromDob.textContent = label;
                        /* auto-fill age field if empty */
                        var ageEl = document.getElementById('er_age');
                        if (ageEl && !ageEl.value && label) ageEl.value = label.replace(/\D/g, '');
                    });
                }

                /* ── Patient mode helpers ───────────────────────────────────────────── */
                function showExisting(id, name, mrn) {
                    patientId.value = id;
                    patientMode.value = 'existing';
                    foundName.textContent = name;
                    foundMrn.textContent = 'MRN: ' + (mrn || '—');
                    foundCard.style.display = '';
                    newFields.style.display = 'none';
                    if (nameEl) nameEl.value = '';
                }

                function showNewFields() {
                    patientId.value = '';
                    patientMode.value = 'new';
                    foundCard.style.display = 'none';
                    newFields.style.display = '';
                }

                function resetPatient() {
                    patientId.value = '';
                    patientMode.value = 'new';
                    foundCard.style.display = 'none';
                    newFields.style.display = 'none';
                }

                /* ── Phone lookup ───────────────────────────────────────────────────── */
                var _timer = null,
                    _xhr = null;

                phoneEl.addEventListener('input', function() {
                    var phone = this.value.trim();
                    clearTimeout(_timer);
                    if (_xhr) {
                        _xhr.abort && _xhr.abort();
                        _xhr = null;
                    }
                    spinner.classList.add('d-none');
                    erClearErr('er_phone_err');
                    resetPatient();

                    var digits = phone.replace(/[\s\-\(\)\+]/g, '');
                    if (digits.length < 7) return;

                    spinner.classList.remove('d-none');
                    _timer = setTimeout(function() {
                        _xhr = fetch(PHONE_URL + '?phone=' + encodeURIComponent(phone), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    Accept: 'application/json'
                                }
                            }).then(function(r) {
                                return r.json();
                            })
                            .then(function(data) {
                                spinner.classList.add('d-none');
                                _xhr = null;
                                if (data.exists) {
                                    showExisting(data.patient_id, data.patient_name, data.mrn);
                                } else {
                                    showNewFields();
                                }
                            }).catch(function() {
                                spinner.classList.add('d-none');
                                _xhr = null;
                                showNewFields();
                            });
                    }, 500);
                });

                clearBtn.addEventListener('click', function() {
                    phoneEl.value = '';
                    resetPatient();
                    phoneEl.focus();
                });

                /* ── Doctor filter (client-side) ────────────────────────────────────── */
                var allDoctorOpts = Array.from(doctorEl.querySelectorAll('option[data-dept]'))
                    .map(function(o) {
                        return o.cloneNode(true);
                    });

                function filterDoctors(deptId) {
                    var cur = doctorEl.value;
                    while (doctorEl.options.length > 1) doctorEl.remove(1);
                    if (!deptId) {
                        doctorEl.options[0].text = '-- Select Department First --';
                        return;
                    }
                    var matches = allDoctorOpts.filter(function(o) {
                        return String(o.dataset.dept) === String(deptId);
                    });
                    doctorEl.options[0].text = matches.length ? '-- Select Doctor --' :
                        '-- No doctors in this department --';
                    matches.forEach(function(o) {
                        doctorEl.add(o.cloneNode(true));
                    });
                    if (matches.some(function(o) {
                            return o.value === cur;
                        })) doctorEl.value = cur;
                }

                deptEl.addEventListener('change', function() {
                    filterDoctors(this.value);
                    doctorEl.value = '';
                });

                /* ── Validation ─────────────────────────────────────────────────────── */
                function erSetErr(id, msg) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.textContent = msg;
                        el.style.display = 'block';
                    }
                    return msg;
                }

                function erClearErr(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.textContent = '';
                        el.style.display = 'none';
                    }
                }

                function erClearAll() {
                    ['er_phone_err', 'er_name_err'].forEach(erClearErr);
                    document.getElementById('erValSummary').style.display = 'none';
                    document.getElementById('erValBody').innerHTML = '';
                }

                function erValidate() {
                    erClearAll();
                    var errs = [];
                    var isExisting = patientMode.value === 'existing';

                    if (!phoneEl.value.trim())
                        errs.push(erSetErr('er_phone_err', 'Phone number is required.'));

                    if (!isExisting && (!nameEl || !nameEl.value.trim()))
                        errs.push(erSetErr('er_name_err', 'Patient name is required.'));

                    if (errs.length) {
                        var sumEl = document.getElementById('erValSummary');
                        var bodyEl = document.getElementById('erValBody');
                        var label = errs.length === 1 ? '1 field needs attention:' : errs.length +
                            ' fields need attention:';
                        bodyEl.innerHTML = '<strong>' + label + '</strong><ul class="mb-0 mt-1 ps-3">' +
                            errs.map(function(e) {
                                return '<li>' + e + '</li>';
                            }).join('') + '</ul>';
                        sumEl.style.display = '';
                        var first = document.querySelector('.er-js-err[style*="block"]');
                        if (first) first.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        var btn = document.getElementById('erSubmitBtn');
                        btn.classList.add('er-shake');
                        setTimeout(function() {
                            btn.classList.remove('er-shake');
                        }, 400);
                    }
                    return errs.length === 0;
                }

                document.getElementById('erForm').addEventListener('submit', function(e) {
                    if (!erValidate()) {
                        e.preventDefault();
                        return;
                    }
                    var btn = document.getElementById('erSubmitBtn');
                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" style="width:.8rem;height:.8rem;"></span>Saving…';
                });

                /* Live clear */
                phoneEl.addEventListener('input', function() {
                    erClearErr('er_phone_err');
                });
                if (nameEl) nameEl.addEventListener('input', function() {
                    erClearErr('er_name_err');
                });

                /* ── Init: restore old() state after server validation fail ─────────── */
                (function init() {
                    @if (old('department_id'))
                        filterDoctors('{{ old('department_id') }}');
                        doctorEl.value = '{{ old('doctor_id', '') }}';
                    @endif
                    @if (old('patient_mode') === 'existing' && old('patient_id'))
                        showExisting('{{ old('patient_id') }}', '', '');
                        phoneEl.value = '{{ old('contact_no', '') }}';
                    @elseif (old('contact_no'))
                        showNewFields();
                    @endif
                }());


            }());
        </script>
