<style>
.vis-section {
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #fff;
}
.vis-section-title {
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
.vis-section-title i { font-size: 13px; }
.patient-type-btn {
    flex: 1;
    min-width: 0;
    border-radius: 8px !important;
    font-size: 13px;
    padding: 7px 4px;
    transition: all .15s;
}
.patient-type-btn.active {
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
}
#patientLoadingSpinner { display: none; }
</style>

<form action="{{ route('front_desk.visitor.store') }}" method="POST" id="visitorForm">
    @csrf

    {{-- ── Visitor Information ── --}}
    <div class="vis-section">
        <div class="vis-section-title"><i class="bi bi-person-badge"></i> Visitor Information</div>
        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label">Visitor Name <span class="text-danger">*</span></label>
                <input type="text" name="visitor_name"
                    class="form-control @error('visitor_name') is-invalid @enderror"
                    value="{{ old('visitor_name') }}" placeholder="Full name of visitor" required>
                @error('visitor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Contact No. <span class="text-danger">*</span></label>
                <input type="text" name="contact_no"
                    class="form-control @error('contact_no') is-invalid @enderror"
                    value="{{ old('contact_no') }}" placeholder="01XXXXXXXXX" required>
                @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Visit Date <span class="text-danger">*</span></label>
                <input type="date" name="visit_date"
                    class="form-control @error('visit_date') is-invalid @enderror"
                    value="{{ old('visit_date', date('Y-m-d')) }}" required>
                @error('visit_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Visit Time</label>
                <input type="time" name="visit_time"
                    class="form-control @error('visit_time') is-invalid @enderror"
                    value="{{ old('visit_time', date('H:i')) }}">
                @error('visit_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">No. of Visitors <span class="text-danger">*</span></label>
                <input type="number" name="visitor_qty"
                    class="form-control @error('visitor_qty') is-invalid @enderror"
                    value="{{ old('visitor_qty', 1) }}" min="1" max="20" required>
                @error('visitor_qty')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ── Visiting Patient ── --}}
    <div class="vis-section">
        <div class="vis-section-title"><i class="bi bi-hospital"></i> Patient Being Visited</div>

        {{-- Patient Type Toggle --}}
        <div class="mb-3">
            <label class="form-label">Patient Type <span class="text-danger">*</span></label>
            <input type="hidden" name="patient_type" id="patient_type" value="{{ old('patient_type') }}">
            <div class="d-flex gap-2">
                <button type="button" class="patient-type-btn btn btn-outline-primary @if(old('patient_type')==='OPD') active btn-primary text-white @endif"
                        data-type="OPD">
                    <i class="bi bi-clipboard2-pulse me-1"></i>OPD
                </button>
                <button type="button" class="patient-type-btn btn btn-outline-success @if(old('patient_type')==='Ipd') active btn-success text-white @endif"
                        data-type="Ipd">
                    <i class="bi bi-building-add me-1"></i>IPD
                </button>
                <button type="button" class="patient-type-btn btn btn-outline-danger @if(old('patient_type')==='ER') active btn-danger text-white @endif"
                        data-type="ER">
                    <i class="bi bi-activity me-1"></i>ER
                </button>
            </div>
            @error('patient_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Patient placeholder before type selected --}}
        <div id="patientPlaceholder" class="text-muted small py-2 {{ old('patient_type') ? 'd-none' : '' }}">
            <i class="bi bi-arrow-up-circle me-1"></i>Select patient type above to load patients.
        </div>

        {{-- Patient Dropdown (loaded via AJAX) --}}
        <div id="patientSelectBox" class="{{ old('patient_type') ? '' : 'd-none' }}">
            <label class="form-label">
                Select Patient
                <span id="patientLoadingSpinner" class="spinner-border spinner-border-sm text-primary ms-1"></span>
            </label>
            <select name="patient_id" id="patient_id"
                    class="form-select select2 @error('patient_id') is-invalid @enderror"
                    style="width:100%">
                <option value="">-- Select Patient --</option>
                @if(old('patient_id'))
                    {{-- keep old selection on validation error --}}
                    <option value="{{ old('patient_id') }}" selected>{{ old('patient_name') }}</option>
                @endif
            </select>
            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

            <div class="mt-2">
                <button type="button" class="btn btn-link btn-sm p-0 text-muted" id="toggleManualName">
                    <i class="bi bi-pencil me-1"></i>Patient not in list? Enter name manually
                </button>
            </div>
        </div>

        {{-- Manual Patient Name (fallback) --}}
        <div id="manualPatientBox" class="mt-2 {{ (old('patient_name') && !old('patient_id')) ? '' : 'd-none' }}">
            <label class="form-label">Patient Name <small class="text-muted">(manual)</small></label>
            <div class="input-group">
                <input type="text" name="patient_name" id="patient_name"
                    class="form-control @error('patient_name') is-invalid @enderror"
                    value="{{ old('patient_name') }}"
                    placeholder="Enter patient name if not registered">
                <button type="button" class="btn btn-outline-secondary" id="cancelManualName" title="Cancel">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Patient info card shown after selection --}}
        <div id="patientInfoCard" class="mt-2 p-2 border rounded bg-light d-none">
            <div class="d-flex gap-3 flex-wrap small">
                <span><strong>Name:</strong> <span id="pi_name">—</span></span>
                <span><strong>MRN:</strong> <span id="pi_mrn">—</span></span>
                <span><strong>Contact:</strong> <span id="pi_contact">—</span></span>
                <span><strong>Gender:</strong> <span id="pi_gender">—</span></span>
            </div>
        </div>

    </div>

    {{-- ── Department & Remarks ── --}}
    <div class="vis-section">
        <div class="vis-section-title"><i class="bi bi-building"></i> Department & Notes</div>
        <div class="row g-2">

            <div class="col-md-6">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select name="department_id"
                        class="form-select select2 @error('department_id') is-invalid @enderror"
                        style="width:100%" required>
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"
                    placeholder="Ward / bed no., reason of visit…">{{ old('remarks') }}</textarea>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="reset" class="btn btn-light btn-sm" id="visResetBtn">Reset</button>
        <button type="submit" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-check2-circle me-1"></i>Save Visitor Entry
        </button>
    </div>
</form>

<script>
(function () {
    var PATIENTS_URL = "{{ route('front_desk.visitor.patients-by-type') }}";
    var SEARCH_URL   = "{{ route('front_desk.patients.search') }}";

    var typeInput      = document.getElementById('patient_type');
    var patientSel     = document.getElementById('patient_id');
    var patientSelBox  = document.getElementById('patientSelectBox');
    var patientPH      = document.getElementById('patientPlaceholder');
    var manualBox      = document.getElementById('manualPatientBox');
    var infoCard       = document.getElementById('patientInfoCard');
    var spinner        = document.getElementById('patientLoadingSpinner');
    var toggleManual   = document.getElementById('toggleManualName');
    var cancelManual   = document.getElementById('cancelManualName');

    /* ─── Type toggle buttons ─── */
    var typeColors = { OPD: 'primary', Ipd: 'success', ER: 'danger' };

    document.querySelectorAll('.patient-type-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = this.dataset.type;

            // reset all buttons
            document.querySelectorAll('.patient-type-btn').forEach(function (b) {
                var c = typeColors[b.dataset.type];
                b.className = 'patient-type-btn btn btn-outline-' + c;
            });

            // activate clicked
            var col = typeColors[t];
            this.classList.replace('btn-outline-' + col, 'btn-' + col);
            this.classList.add('active', 'text-white');

            typeInput.value = t;

            // hide manual, reset patient
            hideManualName();
            resetPatientSelect();
            patientPH.classList.add('d-none');
            patientSelBox.classList.remove('d-none');

            loadPatients(t);
        });
    });

    /* ─── Load patients by type via AJAX ─── */
    var _xhr = null;
    function loadPatients(type) {
        if (_xhr) { _xhr.abort(); }
        spinner.style.display = 'inline-block';
        clearPatientSelect();

        _xhr = $.ajax({
            url: PATIENTS_URL,
            data: { type: type },
            success: function (list) {
                spinner.style.display = 'none';
                if (!list || !list.length) {
                    addOption('', '— No active ' + type + ' patients found —');
                    return;
                }
                addOption('', '-- Select Patient --');
                list.forEach(function (p) {
                    addOption(p.id, p.text);
                });
                // re-trigger select2
                if ($(patientSel).data('select2')) $(patientSel).trigger('change.select2');

                // re-select old value on repopulate
                var old = "{{ old('patient_id') }}";
                if (old) { patientSel.value = old; }
            },
            error: function (xhr) {
                spinner.style.display = 'none';
                if (xhr.statusText !== 'abort') addOption('', '— Failed to load patients —');
            }
        });
    }

    function clearPatientSelect() {
        while (patientSel.options.length) patientSel.remove(0);
        addOption('', 'Loading…');
        infoCard.classList.add('d-none');
    }

    function resetPatientSelect() {
        while (patientSel.options.length) patientSel.remove(0);
        addOption('', '-- Select Patient --');
        infoCard.classList.add('d-none');
    }

    function addOption(val, text) {
        var o = document.createElement('option');
        o.value = val; o.textContent = text;
        patientSel.appendChild(o);
    }

    /* ─── Patient selection → show info card ─── */
    $(patientSel).on('change', function () {
        var id = $(this).val();
        infoCard.classList.add('d-none');
        if (!id) return;

        $.get(SEARCH_URL, { id: id }, function (data) {
            document.getElementById('pi_name').textContent    = data.patient_name ?? '—';
            document.getElementById('pi_mrn').textContent     = ''; // not returned by search
            document.getElementById('pi_contact').textContent = data.mobileno ?? '—';
            document.getElementById('pi_gender').textContent  = data.gender ?? '—';
            infoCard.classList.remove('d-none');
        });
    });

    /* ─── Manual name toggle ─── */
    function showManualName() {
        manualBox.classList.remove('d-none');
        patientSel.value = '';
        if ($(patientSel).data('select2')) $(patientSel).trigger('change.select2');
        infoCard.classList.add('d-none');
        document.getElementById('patient_name').focus();
    }

    function hideManualName() {
        manualBox.classList.add('d-none');
        document.getElementById('patient_name').value = '';
    }

    toggleManual.addEventListener('click', showManualName);
    cancelManual.addEventListener('click', hideManualName);

    /* ─── Manual name typed → clear patient_id ─── */
    document.getElementById('patient_name').addEventListener('input', function () {
        if (this.value.trim()) {
            patientSel.value = '';
            if ($(patientSel).data('select2')) $(patientSel).trigger('change.select2');
        }
    });

    /* ─── Init select2 ─── */
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        var $modal = $('#visitorForm').closest('.modal');
        $('select.select2').select2({ width: '100%', dropdownParent: $modal.length ? $modal : $('body') });
    }

    /* ─── Reset ─── */
    document.getElementById('visResetBtn').addEventListener('click', function () {
        setTimeout(function () {
            typeInput.value = '';
            document.querySelectorAll('.patient-type-btn').forEach(function (b) {
                var c = typeColors[b.dataset.type];
                b.className = 'patient-type-btn btn btn-outline-' + c;
            });
            patientPH.classList.remove('d-none');
            patientSelBox.classList.add('d-none');
            manualBox.classList.add('d-none');
            infoCard.classList.add('d-none');
            resetPatientSelect();
        }, 10);
    });

    /* ─── Repopulate on validation error ─── */
    @if(old('patient_type'))
    (function () {
        var t = '{{ old("patient_type") }}';
        var col = typeColors[t];
        document.querySelectorAll('.patient-type-btn').forEach(function (b) {
            if (b.dataset.type === t) {
                b.classList.replace('btn-outline-' + col, 'btn-' + col);
                b.classList.add('active', 'text-white');
            }
        });
        loadPatients(t);
    }());
    @endif

}());
</script>
