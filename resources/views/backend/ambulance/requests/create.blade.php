<style>
.amb-section {
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #fff;
}
.amb-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #6b7390;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.amb-section-title i { font-size: 13px; }
.req-type-btn {
    flex: 1;
    border-radius: 8px !important;
    font-size: 12px;
    padding: 6px 4px;
    transition: all .15s;
}
.req-type-btn.active { font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
.amb-found-card {
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
@keyframes amb-shake {
    0%,100%{ transform:translateX(0); }
    20%    { transform:translateX(-6px); }
    40%    { transform:translateX(6px); }
    60%    { transform:translateX(-4px); }
    80%    { transform:translateX(4px); }
}
.amb-shake { animation: amb-shake .4s ease; }
</style>

<form method="POST" action="{{ route('amb.requests.store') }}" id="ambReqForm">
    @csrf
    <input type="hidden" name="patient_id"   id="amb_patient_id"   value="{{ old('patient_id') }}">
    <input type="hidden" name="patient_mode" id="amb_patient_mode" value="{{ old('patient_mode', 'new') }}">

    {{-- ── Patient ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-person-vcard"></i> Patient</div>

        <div class="mb-2">
            <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-telephone text-muted"></i></span>
                <input type="text" name="contact_no" id="amb_phone" required
                    class="form-control @error('contact_no') is-invalid @enderror"
                    value="{{ old('contact_no') }}"
                    placeholder="Enter patient's phone number"
                    autocomplete="off" inputmode="tel">
            </div>
            @error('contact_no')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Search status --}}
        <div id="amb_phone_status"></div>

        {{-- Existing patient found --}}
        <div id="amb_found_card" class="amb-found-card mt-2" style="display:none;">
            <div>
                <div class="fw-semibold small text-success">
                    <i class="bi bi-person-check-fill me-1"></i><span id="amb_found_name"></span>
                </div>
                <div class="text-muted" style="font-size:12px;" id="amb_found_meta"></div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="amb_clear_btn" title="Clear patient">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        @error('patient_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

        {{-- New patient fields --}}
        <div id="amb_new_patient" class="mt-2" style="display:none;">
            <div class="alert alert-info py-2 px-3 small mb-2">
                <i class="bi bi-info-circle me-1"></i>
                No patient found. Fill in details to register a new patient.
            </div>
            <div class="row g-2">
                <div class="col-8">
                    <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="patient_name" id="amb_patient_name"
                        class="form-control form-control-sm @error('patient_name') is-invalid @enderror"
                        value="{{ old('patient_name') }}" placeholder="Patient full name">
                    @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4">
                    <label class="form-label small fw-semibold">Gender</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="Male"   @selected(old('gender') == 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') == 'Female')>Female</option>
                        <option value="Other"  @selected(old('gender') == 'Other')>Other</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Request Details ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-truck"></i> Request Details</div>

        <div class="mb-2">
            <label class="form-label small fw-semibold">Request Type <span class="text-danger">*</span></label>
            <input type="hidden" name="request_type" id="request_type_input" value="{{ old('request_type', 'NORMAL') }}">
            <div class="d-flex gap-2">
                @foreach(['EMERGENCY' => 'danger', 'NORMAL' => 'secondary', 'TRANSFER' => 'primary', 'SCHEDULED' => 'success'] as $type => $color)
                    <button type="button"
                        class="req-type-btn btn btn-outline-{{ $color }} @if(old('request_type','NORMAL') === $type) active btn-{{ $color }} text-white @endif"
                        data-type="{{ $type }}" data-color="{{ $color }}">
                        {{ ucfirst(strtolower($type)) }}
                    </button>
                @endforeach
            </div>
            @error('request_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Priority <span class="text-danger">*</span></label>
                <select name="priority" class="form-select form-select-sm @error('priority') is-invalid @enderror" required>
                    @foreach(['NORMAL' => 'Normal', 'LOW' => 'Low', 'HIGH' => 'High', 'CRITICAL' => 'Critical'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('priority', 'NORMAL') == $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Patient Condition</label>
                <select name="patient_condition" class="form-select form-select-sm @error('patient_condition') is-invalid @enderror">
                    <option value="STABLE"   @selected(old('patient_condition','STABLE') == 'STABLE')>Stable</option>
                    <option value="CRITICAL" @selected(old('patient_condition') == 'CRITICAL')>Critical</option>
                </select>
                @error('patient_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Case Tag</label>
                <select name="case_tag" class="form-select form-select-sm @error('case_tag') is-invalid @enderror">
                    <option value="">— None —</option>
                    @foreach(['TRAUMA','STROKE','CARDIAC','RESPIRATORY','OTHER'] as $tag)
                        <option value="{{ $tag }}" @selected(old('case_tag') == $tag)>{{ ucfirst(strtolower($tag)) }}</option>
                    @endforeach
                </select>
                @error('case_tag')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Source</label>
                <select name="source" class="form-select form-select-sm @error('source') is-invalid @enderror">
                    <option value="ER_DESK"    @selected(old('source','ER_DESK') == 'ER_DESK')>ER Desk</option>
                    <option value="OPD"        @selected(old('source') == 'OPD')>OPD</option>
                    <option value="IPD"        @selected(old('source') == 'IPD')>IPD</option>
                    <option value="CALL_CENTER" @selected(old('source') == 'CALL_CENTER')>Call Center</option>
                    <option value="REFERRAL"   @selected(old('source') == 'REFERRAL')>Referral</option>
                </select>
                @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" required
                    class="form-control form-control-sm @error('date') is-invalid @enderror"
                    value="{{ old('date', date('Y-m-d')) }}">
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Time <span class="text-danger">*</span></label>
                <input type="time" name="time" required
                    class="form-control form-control-sm @error('time') is-invalid @enderror"
                    value="{{ old('time', date('H:i')) }}">
                @error('time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Requested By</label>
                <input type="text" name="requested_by_name"
                    class="form-control form-control-sm @error('requested_by_name') is-invalid @enderror"
                    value="{{ old('requested_by_name') }}" placeholder="Caller / staff name">
                @error('requested_by_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Locations ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-geo-alt"></i> Locations</div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Pickup Location <span class="text-danger">*</span></label>
                <input type="text" name="pick_up_location" required
                    class="form-control form-control-sm @error('pick_up_location') is-invalid @enderror"
                    value="{{ old('pick_up_location') }}" placeholder="Ward / address / area">
                @error('pick_up_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Drop Location</label>
                <input type="text" name="drop_location"
                    class="form-control form-control-sm @error('drop_location') is-invalid @enderror"
                    value="{{ old('drop_location') }}" placeholder="Destination (optional)">
                @error('drop_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12" id="destination_hospital_field" style="{{ old('request_type','NORMAL') === 'TRANSFER' ? '' : 'display:none' }}">
                <label class="form-label small fw-semibold">Destination Hospital / ER</label>
                <input type="text" name="destination_hospital"
                    class="form-control form-control-sm @error('destination_hospital') is-invalid @enderror"
                    value="{{ old('destination_hospital') }}" placeholder="e.g. City General Hospital ER">
                @error('destination_hospital')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Dispatch ── --}}
    <div class="amb-section">
        <div class="amb-section-title">
            <i class="bi bi-person-gear"></i> Dispatch
            <span class="text-muted fw-normal text-lowercase">(optional — can assign later)</span>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Ambulance</label>
                <select name="ambulance_id" class="form-select form-select-sm">
                    <option value="">— Assign later —</option>
                    @foreach ($ambulances as $a)
                        <option value="{{ $a->id }}" @selected(old('ambulance_id') == $a->id)>
                            {{ $a->reg_no }} — {{ $a->type }}
                            @if($a->status !== 'AVAILABLE') ({{ $a->status }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Driver</label>
                <select name="driver_id" class="form-select form-select-sm">
                    <option value="">— Assign later —</option>
                    @foreach ($drivers as $d)
                        <option value="{{ $d->id }}" @selected(old('driver_id') == $d->id)>
                            {{ $d->name }}@if($d->phone) | {{ $d->phone }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning btn-sm px-4" id="ambSubmitBtn">
            <i class="bi bi-truck me-1"></i>Save Request
        </button>
    </div>
</form>

<script>
(function () {
    const PHONE_URL  = '{{ route("front_desk.check.phone") }}';
    const phoneEl    = document.getElementById('amb_phone');
    const statusEl   = document.getElementById('amb_phone_status');
    const foundCard  = document.getElementById('amb_found_card');
    const foundName  = document.getElementById('amb_found_name');
    const foundMeta  = document.getElementById('amb_found_meta');
    const clearBtn   = document.getElementById('amb_clear_btn');
    const newPatient = document.getElementById('amb_new_patient');
    const patientId  = document.getElementById('amb_patient_id');
    const modeEl     = document.getElementById('amb_patient_mode');
    const nameEl     = document.getElementById('amb_patient_name');
    const submitBtn  = document.getElementById('ambSubmitBtn');

    let lookupTimer;

    function setStatus(html) { statusEl.innerHTML = html; }

    function showFound(data) {
        foundName.textContent    = data.patient_name;
        foundMeta.textContent    = 'MRN: ' + (data.mrn || '—');
        foundCard.style.display  = '';
        newPatient.style.display = 'none';
        patientId.value          = data.patient_id;
        modeEl.value             = 'existing';
        setStatus('');
    }

    function showNew() {
        foundCard.style.display  = 'none';
        newPatient.style.display = '';
        patientId.value          = '';
        modeEl.value             = 'new';
        setStatus('');
    }

    function clearState() {
        foundCard.style.display  = 'none';
        newPatient.style.display = 'none';
        patientId.value          = '';
        modeEl.value             = 'new';
        setStatus('');
    }

    function lookup(phone) {
        if (!phone || phone.replace(/[\s\-\(\)\+]/g, '').length < 7) {
            clearState();
            return;
        }

        setStatus('<div class="d-flex align-items-center gap-1 text-muted small mt-1">'
            + '<div class="spinner-border spinner-border-sm"></div> Searching…</div>');
        foundCard.style.display  = 'none';
        newPatient.style.display = 'none';

        fetch(PHONE_URL + '?phone=' + encodeURIComponent(phone), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.exists) {
                showFound(data);
            } else {
                showNew();
            }
        })
        .catch(() => {
            setStatus('<div class="text-danger small mt-1"><i class="bi bi-x-circle me-1"></i>Lookup failed. Try again.</div>');
        });
    }

    phoneEl.addEventListener('input', function () {
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(() => lookup(this.value.trim()), 500);
    });

    clearBtn.addEventListener('click', function () {
        phoneEl.value = '';
        clearState();
        phoneEl.focus();
    });

    // Request type toggle
    const reqTypeInput = document.getElementById('request_type_input');
    const destHospitalField = document.getElementById('destination_hospital_field');

    function toggleDestHospital(type) {
        if (destHospitalField) {
            destHospitalField.style.display = type === 'TRANSFER' ? '' : 'none';
        }
    }

    document.querySelectorAll('.req-type-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const t = this.dataset.type, col = this.dataset.color;
            document.querySelectorAll('.req-type-btn').forEach(function (b) {
                b.className = 'req-type-btn btn btn-outline-' + b.dataset.color;
            });
            this.classList.replace('btn-outline-' + col, 'btn-' + col);
            this.classList.add('active', 'text-white');
            reqTypeInput.value = t;
            toggleDestHospital(t);
        });
    });

    // Submit guard
    document.getElementById('ambReqForm').addEventListener('submit', function (e) {
        const phone = phoneEl.value.trim();
        const mode  = modeEl.value;

        if (!phone) {
            e.preventDefault();
            phoneEl.classList.add('amb-shake');
            setTimeout(() => phoneEl.classList.remove('amb-shake'), 500);
            phoneEl.focus();
            return;
        }

        if (mode === 'new' && nameEl && !nameEl.value.trim()) {
            e.preventDefault();
            nameEl.classList.add('amb-shake');
            setTimeout(() => nameEl.classList.remove('amb-shake'), 500);
            nameEl.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    });

    // Restore state after validation error (old() values pre-filled)
    const oldPhone = phoneEl.value;
    if (oldPhone) {
        lookup(oldPhone);
    }
}());
</script>
