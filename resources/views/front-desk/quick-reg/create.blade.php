@php
    $genders = ['Male', 'Female', 'Other'];
    $bloods  = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<style>
.qr-section {
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #fff;
}
.qr-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7390;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.qr-section-title i { font-size: 14px; }
.qr-mode-btn.active { font-weight: 600; }
</style>

<form action="{{ route('front_desk.quickreg.store') }}" method="POST" enctype="multipart/form-data" id="qrForm">
    @csrf

    {{-- ── Patient Mode Toggle ── --}}
    <div class="qr-section">
        <div class="qr-section-title"><i class="bi bi-person"></i> Patient</div>

        <div class="d-flex gap-2 mb-3 flex-wrap">
            <input type="hidden" name="patient_mode" id="patient_mode" value="new">
            <button type="button" class="btn btn-sm btn-primary qr-mode-btn active" id="qrBtnNew">
                <i class="bi bi-person-plus me-1"></i>New Patient
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary qr-mode-btn" id="qrBtnExisting">
                <i class="bi bi-person-check me-1"></i>Existing
            </button>
            <button type="button" class="btn btn-sm btn-outline-success qr-mode-btn" id="qrBtnCard">
                <i class="bi bi-credit-card me-1"></i>Health Card
            </button>
        </div>

        {{-- Existing Patient --}}
        <div class="d-none" id="existingPatientBox">
            <label class="form-label fw-semibold">Search Patient <span class="text-danger">*</span></label>
            <select name="patient_id" id="patient_id"
                    class="form-select select2 @error('patient_id') is-invalid @enderror"
                    style="width:100%">
                <option value="">-- Search by name, MRN or contact --</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                        {{ $p->patient_name }} — {{ $p->mrn ?? 'No MRN' }} {{ $p->mobileno ? '| '.$p->mobileno : '' }}
                    </option>
                @endforeach
            </select>
            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Health Card --}}
        <div class="d-none" id="healthCardBox">
            <label class="form-label fw-semibold">Health Card Number</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                <input type="text" id="hc_input" class="form-control"
                    placeholder="Scan or type — e.g. HC-2026-00001"
                    autocomplete="off" style="text-transform:uppercase">
                <button type="button" class="btn btn-outline-secondary" id="hcSearchBtn">Find</button>
            </div>
            <div id="hcResult" class="mt-2"></div>
        </div>

        {{-- New Patient Fields --}}
        <div id="newPatientFields">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Patient Name <span class="text-danger">*</span></label>
                    <input type="text" name="patient_name" id="patient_name"
                        class="form-control @error('patient_name') is-invalid @enderror"
                        value="{{ old('patient_name') }}" placeholder="Full name">
                    @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact No. <span class="text-danger">*</span>
                        <span id="phoneSpinner" class="spinner-border spinner-border-sm text-primary ms-1 d-none"></span>
                    </label>
                    <input type="text" name="mobileno" id="mobileno"
                        class="form-control @error('mobileno') is-invalid @enderror"
                        value="{{ old('mobileno') }}" placeholder="01XXXXXXXXX" autocomplete="off">
                    <div id="phoneMsg" class="small mt-1"></div>
                    @error('mobileno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4">
                    <label class="form-label">DOB</label>
                    <input type="date" name="dob" value="{{ old('dob') }}" class="form-control">
                </div>
                <div class="col-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">--</option>
                        @foreach ($genders as $g)
                            <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">--</option>
                        @foreach ($bloods as $b)
                            <option value="{{ $b }}" @selected(old('blood_group') === $b)>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Visit Info ── --}}
    <div class="qr-section">
        <div class="qr-section-title"><i class="bi bi-calendar2-check"></i> Visit Info</div>
        <div class="row g-2">

            <div class="col-md-4">
                <label class="form-label">Patient Type <span class="text-danger">*</span></label>
                <select name="patient_type" id="patient_type"
                        class="form-select @error('patient_type') is-invalid @enderror" required>
                    <option value="" disabled @selected(!old('patient_type'))>-- Select --</option>
                    <option value="OPD" @selected(old('patient_type') === 'OPD')>OPD</option>
                    <option value="Ipd" @selected(old('patient_type') === 'Ipd')>IPD</option>
                    <option value="ER"  @selected(old('patient_type') === 'ER')>ER</option>
                </select>
                @error('patient_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" id="qr_date"
                       class="form-control @error('date') is-invalid @enderror"
                       value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="qr_department_id"
                        class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="">-- Select --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                <select name="doctor_id" id="qr_doctor_id"
                        class="form-select @error('doctor_id') is-invalid @enderror" required>
                    <option value="">-- Select Department First --</option>
                    @foreach ($doctors as $d)
                        <option value="{{ $d->id }}"
                                data-dept="{{ $d->department_id }}"
                                @selected(old('doctor_id') == $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
                @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ── OPD Fields ── --}}
    <div class="qr-section d-none" id="opdSection">
        <div class="qr-section-title"><i class="bi bi-hospital"></i> OPD Details</div>
        <div class="row g-2">

            <div class="col-md-4">
                <label class="form-label">Visit Type <span class="text-danger">*</span></label>
                <select name="visit_type" id="visit_type"
                        class="form-select @error('visit_type') is-invalid @enderror">
                    <option value="new"       @selected(old('visit_type','new') === 'new')>New</option>
                    <option value="follow_up" @selected(old('visit_type') === 'follow_up')>Follow-up</option>
                    <option value="recheckup" @selected(old('visit_type') === 'recheckup')>Re-checkup</option>
                    <option value="referred"  @selected(old('visit_type') === 'referred')>Referred</option>
                    <option value="emergency" @selected(old('visit_type') === 'emergency')>Emergency</option>
                </select>
                @error('visit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Shift</label>
                <select name="shift_id" id="qr_shift_id" class="form-select">
                    <option value="">— Select Doctor First —</option>
                </select>
                @error('shift_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Slot</label>
                <select name="slot" id="qr_slot" class="form-select">
                    <option value="">— Select Shift First —</option>
                </select>
                @error('slot')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Chief Complaint</label>
                <textarea name="chief_complaint" rows="2" class="form-control"
                    placeholder="Patient's main complaint…">{{ old('chief_complaint') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ── IPD Fields ── --}}
    <div class="qr-section d-none" id="ipdSection">
        <div class="qr-section-title"><i class="bi bi-building-add"></i> Bed Allocation</div>
        <div class="row g-2">

            <div class="col-md-12">
                <label class="form-label">Bed <span class="text-danger">*</span></label>
                <select name="bed_id" id="bed_id"
                        class="form-select select2 @error('bed_id') is-invalid @enderror"
                        style="width:100%">
                    <option value="">-- Select Available Bed --</option>
                    @foreach ($beds as $bed)
                        <option value="{{ $bed->id }}" @selected(old('bed_id') == $bed->id)>
                            {{ $bed->name }}
                            @if($bed->bedGroup) — {{ $bed->bedGroup->name }} @endif
                            @if($bed->rent) (৳{{ number_format($bed->rent, 0) }}/day) @endif
                        </option>
                    @endforeach
                </select>
                @error('bed_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Admission From <span class="text-danger">*</span></label>
                <input type="datetime-local" name="bed_from" id="bed_from"
                       class="form-control @error('bed_from') is-invalid @enderror"
                       value="{{ old('bed_from', now()->format('Y-m-d\TH:i')) }}">
                @error('bed_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Expected Discharge</label>
                <input type="datetime-local" name="bed_to"
                       class="form-control @error('bed_to') is-invalid @enderror"
                       value="{{ old('bed_to') }}">
                @error('bed_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Bed Remarks</label>
                <input type="text" name="bed_remarks" class="form-control"
                       value="{{ old('bed_remarks') }}" placeholder="Optional note on bed allocation">
            </div>

        </div>
    </div>

    {{-- ── ER Fields ── --}}
    <div class="qr-section d-none" id="erSection">
        <div class="qr-section-title"><i class="bi bi-activity text-danger"></i> ER Details</div>
        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label">Arrival Time</label>
                <input type="datetime-local" name="er_arrival"
                       class="form-control"
                       value="{{ old('er_arrival', now()->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Priority</label>
                <select name="er_priority" class="form-select @error('er_priority') is-invalid @enderror">
                    <option value="NORMAL"   @selected(old('er_priority','NORMAL') === 'NORMAL')>Normal</option>
                    <option value="HIGH"     @selected(old('er_priority') === 'HIGH')>High</option>
                    <option value="CRITICAL" @selected(old('er_priority') === 'CRITICAL')>
                        🔴 Critical
                    </option>
                </select>
                @error('er_priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ── Additional Info ── --}}
    <div class="qr-section">
        <div class="qr-section-title"><i class="bi bi-info-circle"></i> Additional Info</div>
        <div class="row g-2">

            <div class="col-md-4">
                <label class="form-label">Discount Type</label>
                <select name="discount_type" id="qr_discount_type"
                        class="form-select @error('discount_type') is-invalid @enderror">
                    <option value="">-- None --</option>
                    <option value="CORPORATE" @selected(old('discount_type') === 'CORPORATE')>Corporate</option>
                    <option value="INSURANCE" @selected(old('discount_type') === 'INSURANCE')>Insurance</option>
                    <option value="STAFF"     @selected(old('discount_type') === 'STAFF')>Staff</option>
                    <option value="SELF"      @selected(old('discount_type') === 'SELF')>Self</option>
                </select>
            </div>

            {{-- Org fields — shown only for CORPORATE / INSURANCE --}}
            <div class="col-md-4 d-none" id="orgNameBox">
                <label class="form-label">Organization Name</label>
                <input type="text" name="organization_name"
                    class="form-control" value="{{ old('organization_name') }}"
                    placeholder="Organization name">
            </div>

            <div class="col-md-4 d-none" id="orgIdBox">
                <label class="form-label">Organization ID</label>
                <input type="text" name="organization_id"
                    class="form-control" value="{{ old('organization_id') }}"
                    placeholder="Corporate ID">
            </div>

            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"
                    placeholder="Optional note…">{{ old('remarks') }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Upload Document</label>
                <input type="file" name="supporting_doc"
                    class="form-control @error('supporting_doc') is-invalid @enderror">
                <div class="small text-muted">Max 5MB — pdf, docx, png, jpeg</div>
                @error('supporting_doc')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="reset" class="btn btn-light btn-sm" id="qrResetBtn">Reset</button>
        <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-check2-circle me-1"></i>Save Registration
        </button>
    </div>
</form>

<script>
(function () {
    var CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    var SHIFTS_URL  = "{{ route('appointments.get-doctor-shifts') }}";
    var SLOTS_URL   = "{{ route('appointments.get-slots') }}";
    var PHONE_URL   = "{{ route('front_desk.check.phone') }}";

    /* ─── Element refs ─── */
    var $form     = document.getElementById('qrForm');
    var btnNew    = document.getElementById('qrBtnNew');
    var btnExist  = document.getElementById('qrBtnExisting');
    var btnCard   = document.getElementById('qrBtnCard');
    var modeInput = document.getElementById('patient_mode');
    var newFields = document.getElementById('newPatientFields');
    var existBox  = document.getElementById('existingPatientBox');
    var cardBox   = document.getElementById('healthCardBox');
    var patSel    = document.getElementById('patient_id');

    var ptypeEl   = document.getElementById('patient_type');
    var deptEl    = document.getElementById('qr_department_id');
    var doctorEl  = document.getElementById('qr_doctor_id');
    var shiftEl   = document.getElementById('qr_shift_id');
    var slotEl    = document.getElementById('qr_slot');
    var dateEl    = document.getElementById('qr_date');

    /* ─── Patient Mode ─── */
    function setMode(mode) {
        [btnNew, btnExist, btnCard].forEach(function(b) {
            b.classList.remove('btn-primary','btn-secondary','btn-success','active');
            b.classList.add(b === btnNew ? 'btn-outline-primary' : b === btnExist ? 'btn-outline-secondary' : 'btn-outline-success');
        });
        newFields.classList.add('d-none');
        existBox.classList.add('d-none');
        cardBox.classList.add('d-none');
        modeInput.value = mode === 'card' ? 'existing' : mode;

        if (mode === 'new') {
            btnNew.classList.replace('btn-outline-primary','btn-primary');
            btnNew.classList.add('active');
            newFields.classList.remove('d-none');
        } else if (mode === 'existing') {
            btnExist.classList.replace('btn-outline-secondary','btn-secondary');
            btnExist.classList.add('active');
            existBox.classList.remove('d-none');
            if (typeof $ !== 'undefined' && $(patSel).data('select2')) $(patSel).trigger('change.select2');
        } else {
            btnCard.classList.replace('btn-outline-success','btn-success');
            btnCard.classList.add('active');
            cardBox.classList.remove('d-none');
        }
    }
    btnNew.addEventListener('click',   function(){ setMode('new'); });
    btnExist.addEventListener('click', function(){ setMode('existing'); });
    btnCard.addEventListener('click',  function(){ setMode('card'); });

    /* ─── Health Card Lookup ─── */
    var hcInput = document.getElementById('hc_input');
    var hcBtn   = document.getElementById('hcSearchBtn');
    var hcResult= document.getElementById('hcResult');

    function lookupCard() {
        var cardNo = (hcInput.value || '').trim().toUpperCase();
        if (!cardNo) return;
        hcResult.innerHTML = '<span class="text-muted small">Searching…</span>';
        fetch("{{ route('health-card.find') }}?card_no=" + encodeURIComponent(cardNo), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        }).then(function(r){ return r.json(); }).then(function(data) {
            if (data.error) { hcResult.innerHTML = '<div class="alert alert-danger py-1 px-2 small">' + data.error + '</div>'; return; }
            var opt = patSel.querySelector('option[value="' + data.id + '"]');
            if (!opt) { opt = new Option(data.patient_name + ' — ' + data.mrn, data.id, true, true); patSel.appendChild(opt); }
            patSel.value = data.id;
            if (typeof $ !== 'undefined' && $(patSel).data('select2')) $(patSel).trigger('change.select2');
            hcResult.innerHTML = '<div class="alert alert-success py-2 px-3 small mb-0"><strong>' + data.patient_name + '</strong> | ' + data.mrn + ' | ' + (data.gender ?? '') + ' | Blood: ' + (data.blood_group ?? '—') + (data.known_allergies ? '<br><span class="text-danger">⚠ Allergy: ' + data.known_allergies + '</span>' : '') + '</div>';
        }).catch(function(){ hcResult.innerHTML = '<div class="alert alert-danger py-1 px-2 small">Lookup failed.</div>'; });
    }
    hcBtn.addEventListener('click', lookupCard);
    hcInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); lookupCard(); } });

    /* ─── Phone uniqueness check ─── */
    var phoneTimer = null;
    var phoneXhr   = null;
    document.getElementById('mobileno').addEventListener('input', function() {
        var phone = this.value.trim();
        var spinner = document.getElementById('phoneSpinner');
        var msg     = document.getElementById('phoneMsg');
        msg.innerHTML = ''; spinner.classList.add('d-none');
        clearTimeout(phoneTimer);
        if (phone.replace(/[\s\-\(\)\+]/g,'').length < 7) return;
        spinner.classList.remove('d-none');
        phoneTimer = setTimeout(function() {
            fetch(PHONE_URL + '?phone=' + encodeURIComponent(phone))
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    spinner.classList.add('d-none');
                    if (data.exists) {
                        msg.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Phone already registered — <strong>' + data.patient_name + '</strong> (MRN: ' + (data.mrn ?? '—') + '). Use "Existing Patient" mode.</span>';
                    } else {
                        msg.innerHTML = '<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Available</span>';
                    }
                }).catch(function(){ spinner.classList.add('d-none'); });
        }, 600);
    });

    /* ─── Patient Type → show/hide sections ─── */
    function toggleTypeSections() {
        var t = ptypeEl.value;
        document.getElementById('opdSection').classList.toggle('d-none', t !== 'OPD');
        document.getElementById('ipdSection').classList.toggle('d-none', t !== 'Ipd');
        document.getElementById('erSection').classList.toggle('d-none',  t !== 'ER');

        if (t === 'OPD' && doctorEl.value) loadShifts(doctorEl.value, null);
    }
    ptypeEl.addEventListener('change', toggleTypeSections);

    /* ─── Department → Doctor filter (data-dept, client-side) ─── */
    var allDoctorOpts = Array.from(doctorEl.querySelectorAll('option[data-dept]')).map(function(o){ return o.cloneNode(true); });

    function filterDoctors(deptId) {
        var cur = doctorEl.value;
        while (doctorEl.options.length > 1) doctorEl.remove(1);
        var matches = allDoctorOpts.filter(function(o){ return !deptId || String(o.dataset.dept) === String(deptId); });
        if (!deptId) {
            doctorEl.options[0].text = '-- Select Department First --';
        } else {
            doctorEl.options[0].text = matches.length ? '-- Select Doctor --' : '-- No doctors in this department --';
            matches.forEach(function(o){ doctorEl.add(o.cloneNode(true)); });
        }
        doctorEl.value = cur;
        if (typeof $ !== 'undefined' && $(doctorEl).data('select2')) $(doctorEl).trigger('change.select2');
    }

    deptEl.addEventListener('change', function() {
        filterDoctors(this.value);
        doctorEl.value = '';
        resetShiftSlot();
    });

    doctorEl.addEventListener('change', function() {
        if (ptypeEl.value === 'OPD') loadShifts(this.value, null);
        else resetShiftSlot();
    });

    /* ─── Shift loading ─── */
    var _shiftXhr = null;
    function resetShiftSlot() {
        shiftEl.innerHTML = '<option value="">— Select Doctor First —</option>';
        slotEl.innerHTML  = '<option value="">— Select Shift First —</option>';
    }
    function loadShifts(doctorId, preselectShiftId) {
        if (_shiftXhr) { _shiftXhr.abort(); _shiftXhr = null; }
        shiftEl.innerHTML = '<option value="">Loading shifts…</option>';
        slotEl.innerHTML  = '<option value="">— Select Shift First —</option>';
        if (!doctorId) { shiftEl.innerHTML = '<option value="">— Select Doctor First —</option>'; return; }

        _shiftXhr = $.ajax({
            url: SHIFTS_URL, type: 'POST',
            data: { _token: CSRF, doctor_id: doctorId },
            success: function(list) {
                if (!list || !list.length) { shiftEl.innerHTML = '<option value="">No shifts for this doctor</option>'; return; }
                shiftEl.innerHTML = '<option value="">— Select Shift —</option>';
                list.forEach(function(s) { shiftEl.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>'; });
                if (preselectShiftId) { shiftEl.value = preselectShiftId; if (shiftEl.value) loadSlots(doctorId, preselectShiftId, dateEl.value, null); }
            },
            error: function(xhr) { if (xhr.statusText !== 'abort') shiftEl.innerHTML = '<option value="">Failed to load shifts</option>'; }
        });
    }

    /* ─── Slot loading ─── */
    var _slotXhr = null;
    function fmt12(t) {
        var p = t.split(':'), h = parseInt(p[0],10), m = parseInt(p[1],10);
        return String((h+11)%12+1).padStart(2,'0') + ':' + String(m).padStart(2,'0') + (h>=12?' PM':' AM');
    }
    function loadSlots(doctorId, shiftId, dateVal, preselectSlot) {
        if (_slotXhr) { _slotXhr.abort(); _slotXhr = null; }
        slotEl.innerHTML = '<option value="">Loading slots…</option>';
        if (!doctorId || !shiftId || !dateVal) { slotEl.innerHTML = '<option value="">— Select Shift First —</option>'; return; }

        _slotXhr = $.ajax({
            url: SLOTS_URL, type: 'POST',
            data: { _token: CSRF, doctor_id: doctorId, shift_id: shiftId, date: dateVal.substring(0,10) },
            success: function(list) {
                if (!list || !list.length) { slotEl.innerHTML = '<option value="">No slots for this day</option>'; return; }
                slotEl.innerHTML = '<option value="">— Select Slot —</option>';
                list.forEach(function(s) {
                    var val = s.time_from + '|' + s.time_to;
                    slotEl.innerHTML += '<option value="' + val + '">' + fmt12(s.time_from) + ' – ' + fmt12(s.time_to) + '</option>';
                });
                if (preselectSlot) slotEl.value = preselectSlot;
            },
            error: function(xhr) { if (xhr.statusText !== 'abort') slotEl.innerHTML = '<option value="">Failed to load slots</option>'; }
        });
    }

    shiftEl.addEventListener('change', function() {
        loadSlots(doctorEl.value, this.value, dateEl.value, null);
    });
    dateEl.addEventListener('change', function() {
        if (ptypeEl.value === 'OPD' && shiftEl.value) loadSlots(doctorEl.value, shiftEl.value, this.value, null);
    });

    /* ─── Discount Type → org fields toggle ─── */
    var orgTypes = ['CORPORATE', 'INSURANCE'];
    document.getElementById('qr_discount_type').addEventListener('change', function() {
        var show = orgTypes.includes(this.value);
        document.getElementById('orgNameBox').classList.toggle('d-none', !show);
        document.getElementById('orgIdBox').classList.toggle('d-none', !show);
    });

    /* ─── Init select2 on patient dropdown ─── */
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        var $modal = $('#qrForm').closest('.modal');
        $('#patient_id, #bed_id').select2({ width: '100%', dropdownParent: $modal.length ? $modal : $('body') });
    }

    /* ─── Reset button ─── */
    document.getElementById('qrResetBtn').addEventListener('click', function() {
        setTimeout(function() { setMode('new'); toggleTypeSections(); resetShiftSlot(); }, 10);
    });

    /* ─── Init with old() values on validation error repopulate ─── */
    (function init() {
        @if(old('department_id'))
            filterDoctors('{{ old("department_id") }}');
            doctorEl.value = '{{ old("doctor_id") }}';
        @endif
        @if(old('patient_type'))
            toggleTypeSections();
        @endif
        @if(old('patient_mode') === 'existing')
            setMode('existing');
        @else
            setMode('new');
        @endif
        @if(old('discount_type') && in_array(old('discount_type'), ['CORPORATE','INSURANCE']))
            document.getElementById('orgNameBox').classList.remove('d-none');
            document.getElementById('orgIdBox').classList.remove('d-none');
        @endif
    }());

}());
</script>
