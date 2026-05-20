{{-- ═══════════════════════════════════════════════
     Health Card Check-in Modal
     Destinations: OPD · ER · Ipd · Pharmacy · Lab
═══════════════════════════════════════════════ --}}

<div class="modal fade" id="hcCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- ── Header ─────────────────────────────── --}}
            <div class="hc-modal-header">
                <div>
                    <div class="hc-modal-title">
                        <i class="bi bi-qr-code-scan"></i>
                        Health Card Check-in
                    </div>
                    <div class="hc-modal-sub">Scan or type the card number — then choose where the patient is going</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- ── Step 1 : Card Scan ──────────────── --}}
                <div class="hc-scan-row mb-3">
                    <div class="hc-scan-icon"><i class="bi bi-credit-card-2-front"></i></div>
                    <div class="flex-grow-1">
                        <div class="input-group input-group-lg">
                            <input type="text" id="hcModalInput" class="form-control hc-card-input"
                                placeholder="HC-2026-00001"
                                autocomplete="off" spellcheck="false">
                            <button class="btn btn-primary px-4 fw-semibold" id="hcModalSearchBtn" type="button">
                                <i class="bi bi-search me-1"></i>Find
                            </button>
                        </div>
                        <div class="form-text mt-1">Works with a USB QR / barcode scanner — it auto-finds when the scan completes.</div>
                    </div>
                </div>

                {{-- ── Status / error ──────────────────── --}}
                <div id="hcModalStatus"></div>

                {{-- ── Step 2 : Patient + Destination (revealed after lookup) ── --}}
                <div id="hcStep2" style="display:none;">

                    {{-- Patient summary strip --}}
                    <div class="hc-patient-strip mb-4" id="hcPatientStrip">
                        <div class="hc-patient-photo" id="hcPatientPhoto">
                            <i class="bi bi-person fs-2"></i>
                        </div>
                        <div class="hc-patient-details">
                            <div class="hc-patient-name" id="hcPatientName">—</div>
                            <div class="hc-patient-meta" id="hcPatientMeta">—</div>
                            <div id="hcAllergyBadge"></div>
                        </div>
                        <div class="hc-patient-ids">
                            <div class="hc-id-label">Card No</div>
                            <div class="hc-id-value text-primary" id="hcCardNo">—</div>
                            <div class="hc-id-value text-muted small" id="hcMrn">—</div>
                        </div>
                    </div>

                    {{-- Destination selector --}}
                    <div class="mb-3">
                        <div class="hc-section-label">Where is this patient going?</div>
                        <div class="hc-dest-grid">
                            <button type="button" class="hc-dest-btn hc-dest-opd active" data-dest="opd">
                                <i class="bi bi-clipboard2-pulse"></i>
                                <span>OPD</span>
                                <small>Consultation</small>
                            </button>
                            <button type="button" class="hc-dest-btn hc-dest-er" data-dest="er">
                                <i class="bi bi-activity"></i>
                                <span>Emergency</span>
                                <small>ER / Urgent</small>
                            </button>
                            <button type="button" class="hc-dest-btn hc-dest-ipd" data-dest="ipd">
                                <i class="bi bi-hospital"></i>
                                <span>Ipd</span>
                                <small>Admission</small>
                            </button>
                            <button type="button" class="hc-dest-btn hc-dest-pharmacy" data-dest="pharmacy">
                                <i class="bi bi-capsule"></i>
                                <span>Pharmacy</span>
                                <small>Medicines</small>
                            </button>
                            <button type="button" class="hc-dest-btn hc-dest-lab" data-dest="lab">
                                <i class="bi bi-flask"></i>
                                <span>Laboratory</span>
                                <small>Tests</small>
                            </button>
                        </div>
                    </div>

                    {{-- ── Destination Forms ────────────── --}}
                    <form action="{{ route('health-card.checkin') }}" method="POST" id="hcCheckinFormEl">
                        @csrf
                        <input type="hidden" name="patient_id" id="hcPatientId">
                        <input type="hidden" name="destination" id="hcDestination" value="opd">
                        <input type="hidden" name="date" id="hcDate">

                        {{-- OPD fields --}}
                        <div id="hcFields_opd" class="hc-dest-fields">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Visit Type</label>
                                    <select name="visit_type" id="hcVisitType" class="form-select">
                                        <option value="new" selected>New Visit</option>
                                        <option value="follow_up">Follow-up</option>
                                        <option value="recheckup">Re-checkup</option>
                                        <option value="referred">Referred</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                    <div id="hcVisitTypeBadge" class="mt-1"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="hcOpdDept" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Doctor <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="hcOpdDoctor" class="form-select" required>
                                        <option value="">— select dept first —</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Chief Complaint</label>
                                    <input type="text" name="chief_complaint" class="form-control"
                                        placeholder="e.g. Fever, headache, follow-up after discharge…">
                                </div>
                            </div>
                        </div>

                        {{-- ER fields --}}
                        <div id="hcFields_er" class="hc-dest-fields" style="display:none;">
                            <div class="hc-er-warning mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Emergency check-in will be logged immediately. Assign priority below.
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Priority <span class="text-danger">*</span></label>
                                    <div class="hc-priority-group">
                                        <label class="hc-priority-option hc-priority-critical">
                                            <input type="radio" name="priority" value="critical" class="d-none">
                                            <i class="bi bi-exclamation-circle-fill"></i> Critical
                                        </label>
                                        <label class="hc-priority-option hc-priority-high">
                                            <input type="radio" name="priority" value="high" class="d-none">
                                            <i class="bi bi-dash-circle-fill"></i> High
                                        </label>
                                        <label class="hc-priority-option hc-priority-normal">
                                            <input type="radio" name="priority" value="normal" class="d-none" checked>
                                            <i class="bi bi-check-circle-fill"></i> Normal
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Presenting Complaint / Reason</label>
                                    <textarea name="description" rows="2" class="form-control"
                                        placeholder="Describe the emergency complaint…"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Ipd fields --}}
                        <div id="hcFields_ipd" class="hc-dest-fields" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="hcIpdDept" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Doctor <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="hcIpdDoctor" class="form-select" required>
                                        <option value="">— select dept first —</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Admission Reason / Remarks</label>
                                    <input type="text" name="remarks" class="form-control"
                                        placeholder="e.g. Post-op care, surgical admission…">
                                </div>
                            </div>
                        </div>

                        {{-- Pharmacy fields --}}
                        <div id="hcFields_pharmacy" class="hc-dest-fields hc-simple-dest" style="display:none;">
                            <div class="hc-simple-icon text-warning"><i class="bi bi-capsule-pill"></i></div>
                            <div class="hc-simple-text">
                                Patient will be directed to the <strong>Pharmacy counter</strong>.<br>
                                <span class="text-muted small">Staff can dispense medicines against any active prescription.</span>
                            </div>
                        </div>

                        {{-- Lab fields --}}
                        <div id="hcFields_lab" class="hc-dest-fields hc-simple-dest" style="display:none;">
                            <div class="hc-simple-icon text-purple"><i class="bi bi-flask"></i></div>
                            <div class="hc-simple-text">
                                Patient will be directed to the <strong>Laboratory</strong>.<br>
                                <span class="text-muted small">Lab staff will collect samples against any pending test orders.</span>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-lg fw-bold hc-submit-btn" id="hcSubmitBtn">
                                <i class="bi bi-check2-circle me-2"></i>
                                <span id="hcSubmitLabel">Confirm OPD Check-in</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Styles ─────────────────────────────────── --}}
<style>
.hc-modal-header {
    background: linear-gradient(135deg, #1e2a6e 0%, #2b3f9e 60%, #1a6fa8 100%);
    padding: 20px 24px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.hc-modal-title {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 3px;
}
.hc-modal-sub { color: rgba(255,255,255,.6); font-size: 13px; }

.hc-scan-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.hc-scan-icon {
    width: 48px; height: 48px;
    background: #eff3ff;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #3b5bdb;
    flex-shrink: 0; margin-top: 4px;
}
.hc-card-input {
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-size: 15px;
}

/* Patient strip */
.hc-patient-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    background: linear-gradient(135deg, #eff3ff, #e8f4fd);
    border: 1.5px solid #bfdbfe;
    border-radius: 14px;
    padding: 14px 18px;
}
.hc-patient-photo {
    width: 60px; height: 72px;
    border-radius: 10px;
    border: 2px solid #c7d2fe;
    display: flex; align-items: center; justify-content: center;
    background: #e0e7ff; color: #3730a3;
    flex-shrink: 0; overflow: hidden;
}
.hc-patient-photo img { width:100%; height:100%; object-fit:cover; }
.hc-patient-details { flex: 1; min-width: 0; }
.hc-patient-name { font-size: 17px; font-weight: 700; color: #1e2a6e; }
.hc-patient-meta { font-size: 13px; color: #64748b; margin-top: 2px; }
.hc-patient-ids { text-align: right; flex-shrink: 0; }
.hc-id-label { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
.hc-id-value { font-weight: 700; font-size: 13px; }

/* Destination grid */
.hc-section-label {
    font-size: 12px; text-transform: uppercase; letter-spacing: .8px;
    font-weight: 700; color: #64748b; margin-bottom: 10px;
}
.hc-dest-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
}
.hc-dest-btn {
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 12px;
    padding: 12px 6px 10px;
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    cursor: pointer;
    transition: all .18s;
}
.hc-dest-btn i { font-size: 22px; }
.hc-dest-btn span { font-size: 12px; font-weight: 700; }
.hc-dest-btn small { font-size: 10px; color: #94a3b8; }
.hc-dest-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }

/* Destination colours */
.hc-dest-opd.active,     .hc-dest-opd:hover     { border-color:#3b5bdb; background:#eff3ff; color:#3b5bdb; }
.hc-dest-er.active,      .hc-dest-er:hover       { border-color:#e03131; background:#fff5f5; color:#e03131; }
.hc-dest-ipd.active,     .hc-dest-ipd:hover      { border-color:#2f9e44; background:#ebfbee; color:#2f9e44; }
.hc-dest-pharmacy.active,.hc-dest-pharmacy:hover  { border-color:#e67700; background:#fff8e7; color:#e67700; }
.hc-dest-lab.active,     .hc-dest-lab:hover       { border-color:#7048e8; background:#f3f0ff; color:#7048e8; }

/* Dest fields */
.hc-dest-fields { animation: fadeSlide .2s ease; }
@keyframes fadeSlide { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

/* ER warning */
.hc-er-warning {
    background: #fff5f5; border: 1.5px solid #ffc9c9;
    color: #c92a2a; border-radius: 10px;
    padding: 10px 14px; font-size: 13px; font-weight: 600;
}

/* Priority selector */
.hc-priority-group { display: flex; gap: 10px; }
.hc-priority-option {
    flex: 1; text-align: center;
    border: 2px solid #e2e8f0;
    border-radius: 10px; padding: 10px 8px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.hc-priority-critical:has(input:checked),
.hc-priority-critical:hover { border-color:#e03131; background:#fff5f5; color:#e03131; }
.hc-priority-high:has(input:checked),
.hc-priority-high:hover      { border-color:#f76707; background:#fff4e6; color:#f76707; }
.hc-priority-normal:has(input:checked),
.hc-priority-normal:hover    { border-color:#2f9e44; background:#ebfbee; color:#2f9e44; }

/* Simple destination (pharmacy/lab) */
.hc-simple-dest {
    display: flex; align-items: center; gap: 18px;
    background: #f8fafc; border: 1.5px dashed #cbd5e1;
    border-radius: 12px; padding: 18px 20px;
}
.hc-simple-icon { font-size: 36px; flex-shrink: 0; }
.hc-simple-text { font-size: 14px; line-height: 1.6; }

/* Submit button */
.hc-submit-btn {
    background: linear-gradient(135deg, #1e2a6e, #2b3f9e);
    color: #fff; border: none; border-radius: 12px;
    padding: 14px; font-size: 15px;
    transition: opacity .2s;
}
.hc-submit-btn:hover { opacity: .9; color: #fff; }
.hc-submit-btn.dest-er      { background: linear-gradient(135deg,#c92a2a,#e03131); }
.hc-submit-btn.dest-ipd     { background: linear-gradient(135deg,#1b7a38,#2f9e44); }
.hc-submit-btn.dest-pharmacy{ background: linear-gradient(135deg,#b85c00,#e67700); }
.hc-submit-btn.dest-lab     { background: linear-gradient(135deg,#5c3bc4,#7048e8); }

@media (max-width: 576px) {
    .hc-dest-grid { grid-template-columns: repeat(3,1fr); }
    .hc-patient-strip { flex-wrap: wrap; }
    .hc-patient-ids { text-align: left; }
}
</style>

{{-- ── Script ─────────────────────────────────── --}}
<script>
(function () {
    const modal      = document.getElementById('hcCheckinModal');
    const input      = document.getElementById('hcModalInput');
    const searchBtn  = document.getElementById('hcModalSearchBtn');
    const step2      = document.getElementById('hcStep2');
    const statusEl   = document.getElementById('hcModalStatus');
    const submitBtn  = document.getElementById('hcSubmitBtn');
    const submitLabel= document.getElementById('hcSubmitLabel');
    const destInput  = document.getElementById('hcDestination');
    const dateInput  = document.getElementById('hcDate');

    // Set date to now on open + ensure correct disabled state
    modal.addEventListener('show.bs.modal', () => {
        const now = new Date();
        const pad = n => String(n).padStart(2,'0');
        dateInput.value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        switchDest('opd');
    });

    // Reset on close
    modal.addEventListener('hidden.bs.modal', () => {
        input.value        = '';
        step2.style.display = 'none';
        statusEl.innerHTML  = '';
        document.getElementById('hcPatientId').value = '';
        switchDest('opd');
    });

    // ── Card lookup ──────────────────────────────
    function lookup() {
        const cardNo = input.value.trim().toUpperCase();
        if (!cardNo) return;
        input.value = cardNo;

        statusEl.innerHTML = '<div class="d-flex align-items-center gap-2 text-muted small py-1"><div class="spinner-border spinner-border-sm"></div> Searching…</div>';
        step2.style.display = 'none';

        fetch('{{ route("health-card.find") }}?card_no=' + encodeURIComponent(cardNo), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                statusEl.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0 mt-2">`
                    + `<i class="bi bi-x-circle me-1"></i>${data.error}</div>`;
                return;
            }

            statusEl.innerHTML = '';

            // Fill patient summary
            document.getElementById('hcPatientId').value         = data.id;
            document.getElementById('hcPatientName').textContent = data.patient_name;
            document.getElementById('hcPatientMeta').textContent =
                [data.gender, data.age ? 'Age ' + data.age : null, data.blood_group || null]
                .filter(Boolean).join(' · ');
            document.getElementById('hcCardNo').textContent = data.health_card_no;
            document.getElementById('hcMrn').textContent    = data.mrn;

            const allergyEl = document.getElementById('hcAllergyBadge');
            allergyEl.innerHTML = data.known_allergies
                ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1">`
                  + `<i class="bi bi-exclamation-triangle-fill me-1"></i>${data.known_allergies}</span>`
                : '';

            // Photo (only if available — server would need to return image_url)
            if (data.image_url) {
                document.getElementById('hcPatientPhoto').innerHTML =
                    `<img src="${data.image_url}" alt="">`;
            }

            step2.style.display = 'block';
        })
        .catch(() => {
            statusEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0 mt-2">'
                + '<i class="bi bi-wifi-off me-1"></i>Lookup failed — check your connection.</div>';
        });
    }

    searchBtn.addEventListener('click', lookup);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); lookup(); } });

    // Auto-lookup on scan (input fires fast, HC numbers are ~14 chars)
    let autoTimer;
    input.addEventListener('input', function () {
        clearTimeout(autoTimer);
        if (this.value.trim().length >= 10) autoTimer = setTimeout(lookup, 280);
    });

    // ── Destination switcher ──────────────────────
    const destMeta = {
        opd:      { label: 'Confirm OPD Check-in',      cls: '' },
        er:       { label: 'Confirm Emergency Check-in', cls: 'dest-er' },
        ipd:      { label: 'Confirm Ipd Admission',      cls: 'dest-ipd' },
        pharmacy: { label: 'Direct to Pharmacy',         cls: 'dest-pharmacy' },
        lab:      { label: 'Direct to Laboratory',       cls: 'dest-lab' },
    };

    function switchDest(dest) {
        // Update buttons
        document.querySelectorAll('.hc-dest-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.dest === dest);
        });

        // Show/hide field panels — disable inactive inputs so they don't submit
        document.querySelectorAll('.hc-dest-fields').forEach(el => {
            const isActive = el.id === 'hcFields_' + dest;
            el.style.display = isActive ? 'block' : 'none';
            el.querySelectorAll('input, select, textarea').forEach(f => {
                f.disabled = !isActive;
            });
        });

        // Update hidden input
        destInput.value = dest;

        // Update submit button
        const meta = destMeta[dest];
        submitBtn.className = 'btn btn-lg fw-bold hc-submit-btn ' + meta.cls;
        submitLabel.textContent = meta.label;

        // Toggle required on dept/doctor based on destination
        const opdRequired = dest === 'opd';
        const ipdRequired = dest === 'ipd';
        document.getElementById('hcOpdDept').required   = opdRequired;
        document.getElementById('hcOpdDoctor').required = opdRequired;
        document.getElementById('hcIpdDept').required   = ipdRequired;
        document.getElementById('hcIpdDoctor').required = ipdRequired;
    }

    document.querySelectorAll('.hc-dest-btn').forEach(btn => {
        btn.addEventListener('click', () => switchDest(btn.dataset.dest));
    });

    // ── Doctor cascade (OPD) ─────────────────────
    function loadDoctors(deptId, selectEl) {
        selectEl.innerHTML = '<option value="">Loading…</option>';
        if (!deptId) { selectEl.innerHTML = '<option value="">— select dept first —</option>'; return; }

        fetch('{{ url("front_desk/get-doctors-by-department") }}/' + deptId)
            .then(r => r.json())
            .then(docs => {
                selectEl.innerHTML = '<option value="">-- Select Doctor --</option>';
                docs.forEach(d => selectEl.innerHTML += `<option value="${d.id}">${d.name}</option>`);
            })
            .catch(() => { selectEl.innerHTML = '<option value="">— failed to load —</option>'; });
    }

    document.getElementById('hcOpdDept').addEventListener('change', function () {
        loadDoctors(this.value, document.getElementById('hcOpdDoctor'));
        document.getElementById('hcVisitTypeBadge').innerHTML = '';
    });
    document.getElementById('hcIpdDept').addEventListener('change', function () {
        loadDoctors(this.value, document.getElementById('hcIpdDoctor'));
    });

    // ── Follow-up auto-detection ──────────────────
    document.getElementById('hcOpdDoctor').addEventListener('change', function () {
        const doctorId  = this.value;
        const patientId = document.getElementById('hcPatientId').value;
        const badgeEl   = document.getElementById('hcVisitTypeBadge');
        const visitSel  = document.getElementById('hcVisitType');

        badgeEl.innerHTML = '';
        if (!doctorId || !patientId) return;

        fetch('{{ route("health-card.check-followup") }}?patient_id=' + patientId + '&doctor_id=' + doctorId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.is_follow_up) {
                visitSel.value = 'follow_up';
                badgeEl.innerHTML =
                    `<span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:11px;">`
                    + `<i class="bi bi-arrow-repeat me-1"></i>Follow-up detected — last visit ${data.last_visit_date} (${data.days_since_last}d ago, window ${data.follow_up_window}d)</span>`;
            } else if (data.last_visit_date) {
                visitSel.value = 'new';
                badgeEl.innerHTML =
                    `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:11px;">`
                    + `<i class="bi bi-calendar-x me-1"></i>Last visit ${data.last_visit_date} — outside follow-up window (${data.follow_up_window}d)</span>`;
            } else {
                visitSel.value = 'new';
                badgeEl.innerHTML =
                    `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:11px;">`
                    + `<i class="bi bi-person-plus me-1"></i>No prior visit with this doctor</span>`;
            }
        })
        .catch(() => { /* silently ignore — staff can still pick manually */ });
    });
})();
</script>
