<style>
    .vis-card {
        border: 1px solid #e3e8ef;
        border-radius: .55rem;
        padding: .9rem 1rem;
        background: #f8fafc;
        margin-bottom: .85rem;
    }
    .vis-card-title {
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
    .vis-card-title::after { content:''; flex:1; height:1px; background:#e3e8ef; }
    .vis-card-num {
        width: 1.3rem; height: 1.3rem; border-radius: 50%;
        background: #0dcaf0; color: #fff;
        font-size: .6rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .vis-type-btn { font-size: .78rem; padding: .3rem .9rem; }
    .vis-js-err   { font-size:.77rem; color:#dc3545; margin-top:.2rem; display:none; }
    #visSubmitBtn:disabled { opacity:.85; }
    @keyframes vis-shake {
        0%,100% { transform:translateX(0); }
        20%,60%  { transform:translateX(-5px); }
        40%,80%  { transform:translateX(5px); }
    }
    .vis-shake { animation: vis-shake .35s ease; }
    #patientLoadingSpinner { display:none; }
</style>

<form action="{{ route('front_desk.visitor.store') }}" method="POST" id="visitorForm" novalidate>
    @csrf

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

    {{-- Client-side summary --}}
    <div id="visValSummary" style="display:none;"
         class="alert alert-warning py-2 px-3 mb-3 small d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div id="visValBody" class="flex-grow-1"></div>
    </div>

    {{-- ══ SECTION 1 — VISITOR ═════════════════════════════════════════════ --}}
    <div class="vis-card">
        <div class="vis-card-title">
            <span class="vis-card-num">1</span> Visitor
        </div>
        <div class="row g-2">

            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Visitor Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="visitor_name" id="vis_name"
                       class="form-control form-control-sm @error('visitor_name') is-invalid @enderror"
                       value="{{ old('visitor_name') }}" placeholder="Full name" autocomplete="off">
                @error('visitor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="vis-js-err" id="vis_name_err"></div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Contact No. <span class="text-danger">*</span>
                </label>
                <input type="text" name="contact_no" id="vis_contact"
                       class="form-control form-control-sm @error('contact_no') is-invalid @enderror"
                       value="{{ old('contact_no') }}" placeholder="01XXXXXXXXX" inputmode="tel">
                @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="vis-js-err" id="vis_contact_err"></div>
            </div>

            <div class="col-5 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Visit Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="visit_date"
                       class="form-control form-control-sm @error('visit_date') is-invalid @enderror"
                       value="{{ old('visit_date', date('Y-m-d')) }}">
                @error('visit_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-4 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">Visit Time</label>
                <input type="time" name="visit_time"
                       class="form-control form-control-sm @error('visit_time') is-invalid @enderror"
                       value="{{ old('visit_time', date('H:i')) }}">
                @error('visit_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-3 col-md-4">
                <label class="form-label form-label-sm fw-medium mb-1">
                    No. of Visitors <span class="text-danger">*</span>
                </label>
                <input type="number" name="visitor_qty"
                       class="form-control form-control-sm @error('visitor_qty') is-invalid @enderror"
                       value="{{ old('visitor_qty', 1) }}" min="1" max="20">
                @error('visitor_qty')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    {{-- ══ SECTION 2 — PATIENT BEING VISITED ══════════════════════════════ --}}
    <div class="vis-card">
        <div class="vis-card-title">
            <span class="vis-card-num">2</span> Patient Being Visited
        </div>

        <div class="row g-2">

            {{-- Patient type --}}
            <div class="col-12">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Patient Type <span class="text-danger">*</span>
                </label>
                <input type="hidden" name="patient_type" id="patient_type" value="{{ old('patient_type') }}">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="_pt_radio" id="visTypeOpd"
                           value="OPD" autocomplete="off" {{ old('patient_type') === 'OPD' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary vis-type-btn" for="visTypeOpd">
                        <i class="bi bi-clipboard2-pulse me-1"></i>OPD
                    </label>

                    <input type="radio" class="btn-check" name="_pt_radio" id="visTypeIpd"
                           value="Ipd" autocomplete="off" {{ old('patient_type') === 'Ipd' ? 'checked' : '' }}>
                    <label class="btn btn-outline-success vis-type-btn" for="visTypeIpd">
                        <i class="bi bi-building-add me-1"></i>IPD
                    </label>

                    <input type="radio" class="btn-check" name="_pt_radio" id="visTypeEr"
                           value="ER" autocomplete="off" {{ old('patient_type') === 'ER' ? 'checked' : '' }}>
                    <label class="btn btn-outline-danger vis-type-btn" for="visTypeEr">
                        <i class="bi bi-activity me-1"></i>ER
                    </label>
                </div>
                <div class="vis-js-err" id="vis_type_err"></div>
            </div>

            {{-- Patient dropdown (AJAX-loaded) --}}
            <div class="col-12" id="patientSelectBox" style="{{ old('patient_type') ? '' : 'display:none;' }}">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Select Patient
                    <span id="patientLoadingSpinner"
                          class="spinner-border spinner-border-sm text-primary ms-1"
                          style="width:.75rem;height:.75rem;"></span>
                </label>
                <select name="patient_id" id="patient_id"
                        class="form-select form-select-sm select2 @error('patient_id') is-invalid @enderror"
                        style="width:100%">
                    <option value="">-- Select Patient --</option>
                    @if(old('patient_id'))
                        <option value="{{ old('patient_id') }}" selected>{{ old('patient_name_display','') }}</option>
                    @endif
                </select>
                @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-link btn-sm p-0 text-muted mt-1" id="toggleManualName"
                        style="font-size:.75rem;">
                    <i class="bi bi-pencil me-1"></i>Not in list? Enter name manually
                </button>
            </div>

            {{-- Manual patient name fallback --}}
            <div class="col-12" id="manualPatientBox"
                 style="{{ (old('patient_name') && !old('patient_id')) ? '' : 'display:none;' }}">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Patient Name <span class="text-muted fw-normal">(manual)</span>
                </label>
                <div class="input-group input-group-sm">
                    <input type="text" name="patient_name" id="patient_name"
                           class="form-control form-control-sm @error('patient_name') is-invalid @enderror"
                           value="{{ old('patient_name') }}" placeholder="Patient name if not registered">
                    <button type="button" class="btn btn-outline-secondary" id="cancelManualName">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Placeholder before type chosen --}}
            <div class="col-12" id="patientPlaceholder"
                 style="{{ old('patient_type') ? 'display:none;' : '' }}">
                <div class="text-muted small py-1">
                    <i class="bi bi-arrow-up-circle me-1"></i>Select patient type above to load patients.
                </div>
            </div>

            {{-- Department --}}
            <div class="col-12 col-md-7">
                <label class="form-label form-label-sm fw-medium mb-1">
                    Department <span class="text-danger">*</span>
                </label>
                <select name="department_id" id="vis_dept"
                        class="form-select form-select-sm @error('department_id') is-invalid @enderror">
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="vis-js-err" id="vis_dept_err"></div>
            </div>

            {{-- Remarks --}}
            <div class="col-12 col-md-5">
                <label class="form-label form-label-sm fw-medium mb-1">Remarks</label>
                <input type="text" name="remarks"
                       class="form-control form-control-sm"
                       value="{{ old('remarks') }}"
                       placeholder="Ward / bed no., reason…">
            </div>

        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2 pt-1">
        <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="visSubmitBtn" class="btn btn-sm btn-info px-4 fw-semibold text-white">
            <i class="bi bi-person-walking me-1"></i>Save Visitor Entry
        </button>
    </div>

</form>

<script>
(function () {
    var PATIENTS_URL = "{{ route('front_desk.visitor.patients-by-type') }}";

    var patientSel    = document.getElementById('patient_id');
    var patientSelBox = document.getElementById('patientSelectBox');
    var patientPH     = document.getElementById('patientPlaceholder');
    var manualBox     = document.getElementById('manualPatientBox');
    var spinner       = document.getElementById('patientLoadingSpinner');
    var typeHidden    = document.getElementById('patient_type');

    /* ── Select2 reinit (destroy + reinit so new options are reflected) ── */
    function reinitSelect2(el) {
        if (typeof $ === 'undefined' || !$.fn || !$.fn.select2) return;
        var $modal = $('#visitorForm').closest('.modal');
        var $el    = $(el);
        if ($el.data('select2')) $el.select2('destroy');
        $el.select2({ width: '100%', dropdownParent: $modal.length ? $modal : $('body') });
    }

    /* ── Patient type radio → load patients ─────────────────────────── */
    var _xhr = null;

    function loadPatients(type) {
        if (_xhr) { try { _xhr.abort(); } catch(e){} _xhr = null; }
        spinner.style.display = 'inline-block';

        while (patientSel.options.length) patientSel.remove(0);
        addOption('', 'Loading…');

        _xhr = fetch(PATIENTS_URL + '?type=' + encodeURIComponent(type), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        }).then(function (r) { return r.json(); })
          .then(function (list) {
              spinner.style.display = 'none'; _xhr = null;
              while (patientSel.options.length) patientSel.remove(0);
              if (!list || !list.length) {
                  addOption('', '— No active ' + type + ' patients today —');
              } else {
                  addOption('', '-- Select Patient --');
                  list.forEach(function (p) { addOption(p.id, p.text); });
              }
              reinitSelect2(patientSel);
              var oldVal = "{{ old('patient_id') }}";
              if (oldVal) patientSel.value = oldVal;
          })
          .catch(function (err) {
              if (err && err.name === 'AbortError') return;
              spinner.style.display = 'none'; _xhr = null;
              while (patientSel.options.length) patientSel.remove(0);
              addOption('', '— Failed to load patients —');
              reinitSelect2(patientSel);
          });
    }

    function addOption(val, text) {
        var o = document.createElement('option');
        o.value = val; o.textContent = text;
        patientSel.appendChild(o);
    }

    document.querySelectorAll('input[name="_pt_radio"]').forEach(function (r) {
        r.addEventListener('change', function () {
            var t = this.value;
            typeHidden.value = t;
            visClearErr('vis_type_err');
            hideManualName();
            patientPH.style.display = 'none';
            patientSelBox.style.display = '';
            loadPatients(t);
        });
    });

    /* ── Manual name toggle ─────────────────────────────────────────── */
    function showManualName() {
        manualBox.style.display = '';
        patientSel.value = '';
        document.getElementById('patient_name').focus();
    }
    function hideManualName() {
        manualBox.style.display = 'none';
        var pn = document.getElementById('patient_name');
        if (pn) pn.value = '';
    }
    document.getElementById('toggleManualName').addEventListener('click', showManualName);
    document.getElementById('cancelManualName').addEventListener('click', hideManualName);

    /* ── Validation ─────────────────────────────────────────────────── */
    function visSetErr(id, msg) {
        var el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
        return msg;
    }
    function visClearErr(id) {
        var el = document.getElementById(id);
        if (el) { el.textContent = ''; el.style.display = 'none'; }
    }
    function visClearAll() {
        ['vis_name_err','vis_contact_err','vis_type_err','vis_dept_err'].forEach(visClearErr);
        document.getElementById('visValSummary').style.display = 'none';
        document.getElementById('visValBody').innerHTML = '';
    }

    function visValidate() {
        visClearAll();
        var errs = [];

        var nameEl    = document.getElementById('vis_name');
        var contactEl = document.getElementById('vis_contact');
        var deptEl    = document.getElementById('vis_dept');

        if (!nameEl.value.trim())
            errs.push(visSetErr('vis_name_err', 'Visitor name is required.'));
        if (!contactEl.value.trim())
            errs.push(visSetErr('vis_contact_err', 'Contact number is required.'));
        if (!typeHidden.value)
            errs.push(visSetErr('vis_type_err', 'Please select a patient type.'));
        if (!deptEl.value)
            errs.push(visSetErr('vis_dept_err', 'Please select a department.'));

        if (errs.length) {
            var bodyEl = document.getElementById('visValBody');
            var label  = errs.length === 1 ? '1 field needs attention:' : errs.length + ' fields need attention:';
            bodyEl.innerHTML = '<strong>' + label + '</strong><ul class="mb-0 mt-1 ps-3">'
                + errs.map(function (e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
            document.getElementById('visValSummary').style.display = '';
            var first = document.querySelector('.vis-js-err[style*="block"]');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var btn = document.getElementById('visSubmitBtn');
            btn.classList.add('vis-shake');
            setTimeout(function () { btn.classList.remove('vis-shake'); }, 400);
        }
        return errs.length === 0;
    }

    document.getElementById('visitorForm').addEventListener('submit', function (e) {
        if (!visValidate()) { e.preventDefault(); return; }
        var btn = document.getElementById('visSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.8rem;height:.8rem;"></span>Saving…';
    });

    /* Live clear */
    document.getElementById('vis_name').addEventListener('input', function () { visClearErr('vis_name_err'); });
    document.getElementById('vis_contact').addEventListener('input', function () { visClearErr('vis_contact_err'); });
    document.getElementById('vis_dept').addEventListener('change', function () { visClearErr('vis_dept_err'); });

    /* ── Repopulate on server validation error ───────────────────────── */
    @if(old('patient_type'))
    (function () {
        loadPatients('{{ old("patient_type") }}');
    }());
    @endif

}());
</script>
