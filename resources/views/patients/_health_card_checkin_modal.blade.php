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

                                {{-- Source --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Source <span class="text-danger">*</span></label>
                                    <select name="booking_status" id="hcBookingStatus" class="form-select">
                                        <option value="WALK_IN" selected>Walk In</option>
                                        <option value="PRE_BOOK">Appointment</option>
                                        <option value="REFERRAL">Referral</option>
                                    </select>
                                </div>

                                {{-- Shift (Appointment only) --}}
                                <div class="col-md-4" id="hcShiftField" style="display:none;">
                                    <label class="form-label fw-semibold small">Doctor Shift <span class="text-danger">*</span></label>
                                    <select name="shift_id" id="hcShiftId" class="form-select" disabled>
                                        <option value="">-- Select Shift --</option>
                                    </select>
                                </div>

                                {{-- Slot (Appointment only) --}}
                                <div class="col-md-4" id="hcSlotField" style="display:none;">
                                    <label class="form-label fw-semibold small">Time Slot <span class="text-danger">*</span></label>
                                    <select name="slot_time" id="hcSlotTime" class="form-select" disabled>
                                        <option value="">-- Select Slot --</option>
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
                        @php
                            $hcAllBeds  = \App\Models\Bed::with('bedType')->where('is_reserved', false)->get();
                            $hcBeds     = $hcAllBeds->filter(fn($b) => ! optional($b->bedType)->is_icu)->values();
                            $hcIcuBeds  = $hcAllBeds->filter(fn($b) =>   optional($b->bedType)->is_icu)->values();
                            $hcAdmTypes = ['Emergency', 'Planned', 'Unplanned', 'Transfer(Internal)', 'Transfer(External)'];
                        @endphp
                        <div id="hcFields_ipd" class="hc-dest-fields" style="display:none;">
                            <div class="row g-3">

                                {{-- Row 1: Dept | Doctor | Admission Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" id="hcIpdDept" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Doctor <span class="text-danger">*</span></label>
                                    <select name="doctor_id" id="hcIpdDoctor" class="form-select" required>
                                        <option value="">— select dept first —</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Admission Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="admission_date" id="hcIpdAdmDate" class="form-control" required>
                                </div>

                                {{-- Row 2: Admission Type | Possible Discharge | Status --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Admission Type</label>
                                    <select name="admission_type" class="form-select">
                                        <option value="">-- Select --</option>
                                        @foreach($hcAdmTypes as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Possible Discharge</label>
                                    <input type="datetime-local" name="possible_discharge_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Status</label>
                                    <input type="text" name="ipd_status" class="form-control" value="Admitted" readonly>
                                </div>

                                {{-- Row 3: Patient History | Remarks --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Patient History</label>
                                    <textarea name="patient_history" rows="2" class="form-control"
                                        placeholder="Short history / reason for admission"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Remarks</label>
                                    <textarea name="remarks" rows="2" class="form-control"
                                        placeholder="Notes / initial diagnosis"></textarea>
                                </div>

                                {{-- Bed Allocation --}}
                                <div class="col-12">
                                    <div class="fw-semibold small text-uppercase text-muted mb-2" style="letter-spacing:.5px;">
                                        Bed Allocation <span class="text-danger">*</span>
                                    </div>
                                    <div class="btn-group mb-2" role="group">
                                        <input type="radio" class="btn-check" name="allocation_choice"
                                            id="hcIpdAllocBed" value="bed" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="hcIpdAllocBed">
                                            <i class="bi bi-hospital"></i> Regular Bed
                                        </label>
                                        <input type="radio" class="btn-check" name="allocation_choice"
                                            id="hcIpdAllocIcu" value="icu" autocomplete="off">
                                        <label class="btn btn-outline-danger btn-sm" for="hcIpdAllocIcu">
                                            <i class="bi bi-heart-pulse"></i> ICU / Critical Care
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-5" id="hcIpdBedWrap">
                                    <label class="form-label fw-semibold small">Bed <span class="text-danger">*</span></label>
                                    <select name="bed_id" id="hcIpdBedId" class="form-select" required>
                                        <option value="">-- Select Bed --</option>
                                        @foreach($hcBeds as $b)
                                            <option value="{{ $b->id }}">
                                                {{ $b->name }}
                                                @if(optional($b->bedType)->name) [{{ $b->bedType->name }}] @endif
                                                (৳ {{ $b->rent }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-5" id="hcIpdIcuWrap" style="display:none;">
                                    <label class="form-label fw-semibold small">ICU Bed <span class="text-danger">*</span></label>
                                    <select name="icu_bed_id" id="hcIpdIcuId" class="form-select">
                                        <option value="">-- Select ICU Bed --</option>
                                        @forelse($hcIcuBeds as $b)
                                            <option value="{{ $b->id }}">
                                                {{ $b->name }}
                                                @if(optional($b->bedType)->name) [{{ $b->bedType->name }}] @endif
                                                (৳ {{ $b->rent }})
                                            </option>
                                        @empty
                                            <option value="" disabled>No ICU beds available</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">From</label>
                                    <input type="datetime-local" name="from" id="hcIpdBedFrom" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Bed Remarks</label>
                                    <textarea name="bed_remarks" rows="1" class="form-control"
                                        placeholder="Shifting reason / bed note"></textarea>
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
                        <div id="hcFields_lab" class="hc-dest-fields" style="display:none;">
                            <div class="row g-3">

                                {{-- Lab Type --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Lab Type <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <input type="radio" class="btn-check" name="lab_type" id="hcLabTypePath" value="pathology" autocomplete="off">
                                        <label class="btn btn-outline-primary btn-sm" for="hcLabTypePath">
                                            <i class="bi bi-droplet-fill me-1"></i>Pathology
                                        </label>
                                        <input type="radio" class="btn-check" name="lab_type" id="hcLabTypeRad" value="radiology" autocomplete="off">
                                        <label class="btn btn-outline-info btn-sm" for="hcLabTypeRad">
                                            <i class="bi bi-broadcast-pin me-1"></i>Radiology
                                        </label>
                                        <input type="radio" class="btn-check" name="lab_type" id="hcLabTypeBoth" value="both" autocomplete="off" checked>
                                        <label class="btn btn-outline-secondary btn-sm" for="hcLabTypeBoth">
                                            <i class="bi bi-grid-fill me-1"></i>Both
                                        </label>
                                    </div>
                                </div>

                                {{-- Loading spinner (shown while AJAX pending) --}}
                                <div id="hcLabLoading" class="col-12 text-center text-muted py-3" style="display:none;">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Loading tests…
                                </div>

                                {{-- Pathology section --}}
                                <div id="hcLabPathSection" class="col-12" style="display:none;">
                                    <div class="hc-lab-section">
                                        <div class="hc-lab-section-title">
                                            <i class="bi bi-droplet-fill text-primary me-1"></i>Pathology
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold small">Referring Doctor</label>
                                                <select name="pathology_doctor_id" class="form-select form-select-sm">
                                                    <option value="">-- Select Doctor --</option>
                                                    @foreach(\App\Models\Doctor::where('is_active', 1)->orderBy('name')->get(['id', 'name']) as $doc)
                                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Priority</label>
                                                <select name="pathology_priority" class="form-select form-select-sm">
                                                    <option value="Regular">Regular</option>
                                                    <option value="Urgent">Urgent</option>
                                                    <option value="STAT">STAT</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold small">Remarks</label>
                                                <input type="text" name="pathology_remarks" class="form-control form-control-sm" placeholder="Optional">
                                            </div>
                                        </div>
                                        <label class="form-label fw-semibold small">Tests <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm mb-2" id="hcLabPathSearch" placeholder="Search tests…" autocomplete="off">
                                        <div id="hcLabPathTests" class="hc-lab-test-list"></div>
                                    </div>
                                </div>

                                {{-- Radiology section --}}
                                <div id="hcLabRadSection" class="col-12" style="display:none;">
                                    <div class="hc-lab-section">
                                        <div class="hc-lab-section-title">
                                            <i class="bi bi-broadcast-pin text-info me-1"></i>Radiology
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Referring Doctor</label>
                                                <select name="radiology_doctor_id" class="form-select form-select-sm">
                                                    <option value="">-- Select Doctor --</option>
                                                    @foreach(\App\Models\Doctor::where('is_active', 1)->orderBy('name')->get(['id', 'name']) as $doc)
                                                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">Remarks</label>
                                                <input type="text" name="radiology_remarks" class="form-control form-control-sm" placeholder="Optional">
                                            </div>
                                        </div>
                                        <label class="form-label fw-semibold small">Tests <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm mb-2" id="hcLabRadSearch" placeholder="Search tests…" autocomplete="off">
                                        <div id="hcLabRadTests" class="hc-lab-test-list"></div>
                                    </div>
                                </div>

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

                    {{-- OPD Token success panel (shown after successful OPD check-in) --}}
                    <div id="hcTokenSuccess" style="display:none;" class="mt-3">
                        <div class="hc-token-success-box">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="hc-token-check-icon"><i class="bi bi-check-circle-fill"></i></div>
                                <div>
                                    <div class="fw-bold fs-6">OPD Check-in Successful</div>
                                    <div class="text-muted small">Patient has been registered for OPD visit.</div>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <div class="hc-token-label">OPD Token No</div>
                                <div id="hcTokenNoDisplay" class="hc-token-number"></div>
                            </div>
                            <div class="d-flex gap-2">
                                <a id="hcPrintTokenBtn" href="#" target="_blank"
                                   class="btn btn-primary flex-grow-1 fw-semibold">
                                    <i class="bi bi-printer me-2"></i>Print Token
                                </a>
                                <button type="button" id="hcNewCheckinBtn"
                                        class="btn btn-outline-secondary fw-semibold">
                                    <i class="bi bi-arrow-repeat me-1"></i>New
                                </button>
                            </div>
                        </div>
                    </div>
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

/* Simple destination (pharmacy) */
.hc-simple-dest {
    display: flex; align-items: center; gap: 18px;
    background: #f8fafc; border: 1.5px dashed #cbd5e1;
    border-radius: 12px; padding: 18px 20px;
}
.hc-simple-icon { font-size: 36px; flex-shrink: 0; }
.hc-simple-text { font-size: 14px; line-height: 1.6; }

/* Lab order panel */
.hc-lab-section {
    background: #f8fafc; border: 1.5px solid #e2e8f0;
    border-radius: 10px; padding: 14px 16px;
}
.hc-lab-section-title {
    font-weight: 600; font-size: 12px; text-transform: uppercase;
    letter-spacing: .5px; color: #64748b; margin-bottom: 10px;
}
.hc-lab-test-list {
    max-height: 180px; overflow-y: auto;
    border: 1px solid #e2e8f0; border-radius: 6px;
    padding: 8px 10px; background: #fff;
}
.hc-lab-cat { font-size: 13px; }
.hc-lab-cat-name { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.hc-lab-test-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 400; cursor: pointer;
    border-radius: 20px; padding: 3px 10px;
    transition: background .15s, color .15s;
    user-select: none;
}
.hc-lab-test-badge.selected {
    background: #3b82f6 !important; color: #fff !important;
    border-color: #3b82f6 !important;
}

/* Token success panel */
.hc-token-success-box {
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1.5px solid #86efac;
    border-radius: 16px;
    padding: 20px 22px;
}
.hc-token-check-icon {
    font-size: 2.2rem; color: #16a34a; flex-shrink: 0;
}
.hc-token-label {
    font-size: 11px; text-transform: uppercase; letter-spacing: .8px;
    font-weight: 700; color: #6b7280; margin-bottom: 4px;
}
.hc-token-number {
    font-size: 2rem; font-weight: 800; letter-spacing: 2px;
    color: #1e2a6e; font-family: monospace;
}

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
        document.getElementById('hcBookingStatus').value = 'WALK_IN';
        document.getElementById('hcIpdAdmDate').value  = '';
        document.getElementById('hcIpdBedFrom').value  = '';
        document.getElementById('hcIpdAllocBed').checked = true;
        document.getElementById('hcIpdBedWrap').style.display = '';
        document.getElementById('hcIpdIcuWrap').style.display = 'none';
        toggleHcShiftSlot();
        // Reset lab panel
        const labBoth = document.getElementById('hcLabTypeBoth');
        if (labBoth) labBoth.checked = true;
        document.querySelectorAll('.hc-lab-cb').forEach(cb => {
            cb.checked = false;
            cb.closest('.hc-lab-test-badge')?.classList.remove('selected');
        });
        ['hcLabPathSearch','hcLabRadSearch'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
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

        // Toggle required on dept/doctor/bed based on destination
        const opdRequired = dest === 'opd';
        const ipdRequired = dest === 'ipd';
        document.getElementById('hcOpdDept').required    = opdRequired;
        document.getElementById('hcOpdDoctor').required  = opdRequired;
        document.getElementById('hcIpdDept').required    = ipdRequired;
        document.getElementById('hcIpdDoctor').required  = ipdRequired;
        document.getElementById('hcIpdAdmDate').required = ipdRequired;
        document.getElementById('hcIpdBedId').required   = ipdRequired && document.getElementById('hcIpdAllocBed').checked;

        if (dest === 'ipd') {
            const now = new Date(), p = n => String(n).padStart(2,'0');
            const nowStr = `${now.getFullYear()}-${p(now.getMonth()+1)}-${p(now.getDate())}T${p(now.getHours())}:${p(now.getMinutes())}`;
            const admDate = document.getElementById('hcIpdAdmDate');
            const bedFrom = document.getElementById('hcIpdBedFrom');
            if (!admDate.value) admDate.value = nowStr;
            if (!bedFrom.value) bedFrom.value = nowStr;
        }

        if (dest === 'lab') {
            loadHcLab();
        }
    }

    // ── IPD inline Bed / ICU toggle ──────────────
    document.getElementById('hcIpdAllocBed').addEventListener('change', function () {
        document.getElementById('hcIpdBedWrap').style.display = '';
        document.getElementById('hcIpdIcuWrap').style.display = 'none';
        document.getElementById('hcIpdBedId').required = true;
        document.getElementById('hcIpdIcuId').required = false;
    });
    document.getElementById('hcIpdAllocIcu').addEventListener('change', function () {
        document.getElementById('hcIpdBedWrap').style.display = 'none';
        document.getElementById('hcIpdIcuWrap').style.display = '';
        document.getElementById('hcIpdBedId').required = false;
        document.getElementById('hcIpdIcuId').required = true;
    });

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

    // ── Shift / Slot visibility ───────────────────
    let _hcShiftXhr = null, _hcSlotXhr = null;

    function toggleHcShiftSlot() {
        const isAppointment = document.getElementById('hcBookingStatus').value === 'PRE_BOOK';
        const shiftField = document.getElementById('hcShiftField');
        const slotField  = document.getElementById('hcSlotField');
        const shiftSel   = document.getElementById('hcShiftId');
        const slotSel    = document.getElementById('hcSlotTime');

        shiftField.style.display = isAppointment ? 'block' : 'none';
        slotField.style.display  = isAppointment ? 'block' : 'none';
        shiftSel.disabled = !isAppointment;
        slotSel.disabled  = !isAppointment;

        if (!isAppointment) {
            shiftSel.innerHTML = '<option value="">-- Select Shift --</option>';
            slotSel.innerHTML  = '<option value="">-- Select Slot --</option>';
        } else if (document.getElementById('hcOpdDoctor').value) {
            loadHcShifts();
        }
    }

    function loadHcShifts() {
        const doctorId = document.getElementById('hcOpdDoctor').value;
        const $shift   = document.getElementById('hcShiftId');
        const $slot    = document.getElementById('hcSlotTime');

        if (_hcShiftXhr) { _hcShiftXhr.abort(); _hcShiftXhr = null; }
        if (_hcSlotXhr)  { _hcSlotXhr.abort();  _hcSlotXhr  = null; }

        $slot.innerHTML = '<option value="">-- Select Shift First --</option>';

        if (!doctorId) {
            $shift.innerHTML = '<option value="">-- Select Doctor First --</option>';
            return;
        }

        $shift.innerHTML = '<option value="">Loading…</option>';

        _hcShiftXhr = $.ajax({
            url: "{{ route('appointments.get-doctor-shifts') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", doctor_id: doctorId },
            dataType: 'json',
            success: function (list) {
                _hcShiftXhr = null;
                if (!list || !list.length) {
                    $shift.innerHTML = '<option value="">No shifts configured</option>';
                    return;
                }
                $shift.innerHTML = '<option value="">-- Select Shift --</option>';
                const seen = {};
                list.forEach(function (s) {
                    if (!seen[s.name]) {
                        seen[s.name] = true;
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.name;
                        $shift.appendChild(opt);
                    }
                });
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $shift.innerHTML = '<option value="">Failed (' + xhr.status + ')</option>';
            }
        });
    }

    function loadHcSlots() {
        const doctorId = document.getElementById('hcOpdDoctor').value;
        const shiftId  = document.getElementById('hcShiftId').value;
        const dateVal  = dateInput.value ? dateInput.value.substring(0, 10) : '';
        const $slot    = document.getElementById('hcSlotTime');

        if (_hcSlotXhr) { _hcSlotXhr.abort(); _hcSlotXhr = null; }
        $slot.innerHTML = '<option value="">-- Select Slot --</option>';
        if (!doctorId || !shiftId || !dateVal) return;

        _hcSlotXhr = $.ajax({
            url: "{{ route('front_desk.opd.slots') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", doctor_id: doctorId, shift_id: shiftId, date: dateVal },
            dataType: 'json',
            success: function (list) {
                _hcSlotXhr = null;
                if (!list || !list.length) {
                    $slot.innerHTML = '<option value="">No slots available</option>';
                    return;
                }
                $slot.innerHTML = '<option value="">-- Select Slot --</option>';
                const seen = {};
                list.forEach(function (s) {
                    const key = s.time_from + '|' + s.time_to;
                    if (seen[key]) return;
                    seen[key] = true;
                    const booked = s.booked_count || 0;
                    const suffix = booked === 0 ? ' (available)' : ' (' + booked + ' booked)';
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = s.time_from.substring(0,5) + ' – ' + s.time_to.substring(0,5) + suffix;
                    $slot.appendChild(opt);
                });
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') return;
                $slot.innerHTML = '<option value="">Failed (' + xhr.status + ')</option>';
            }
        });
    }

    document.getElementById('hcBookingStatus').addEventListener('change', toggleHcShiftSlot);
    document.getElementById('hcShiftId').addEventListener('change', loadHcSlots);

    // ── Lab order panel ──────────────────────────────
    let hcLabData = null;

    function loadHcLab() {
        if (hcLabData) { showHcLabSections(); return; }

        // Hide sections and show spinner until data arrives
        document.getElementById('hcLabPathSection').style.display = 'none';
        document.getElementById('hcLabRadSection').style.display  = 'none';
        document.getElementById('hcLabLoading').style.display     = 'block';

        fetch('{{ route("health-card.lab-investigations") }}?type=both', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            }
        })
        .then(r => r.json())
        .then(data => {
            hcLabData = data;
            document.getElementById('hcLabLoading').style.display = 'none';

            if (data.pathology) {
                document.getElementById('hcLabPathSection').dataset.typeId = data.pathology.type_id;
                renderHcTestList('hcLabPathTests', data.pathology.categories);
            }
            if (data.radiology) {
                document.getElementById('hcLabRadSection').dataset.typeId = data.radiology.type_id;
                renderHcTestList('hcLabRadTests', data.radiology.categories);
            }

            showHcLabSections();
        })
        .catch(() => {
            document.getElementById('hcLabLoading').innerHTML =
                '<div class="text-danger small py-2"><i class="bi bi-x-circle me-1"></i>Failed to load tests. Please refresh.</div>';
        });
    }

    function renderHcTestList(containerId, categories) {
        const container = document.getElementById(containerId);
        if (!categories || !categories.length) {
            container.innerHTML = '<div class="text-muted small py-2">No tests configured.</div>';
            return;
        }

        let html = '';
        categories.forEach(cat => {
            if (!cat.investigations || !cat.investigations.length) return;
            html += `<div class="hc-lab-cat mb-2">
                <div class="hc-lab-cat-name">${cat.name}</div>
                <div class="d-flex flex-wrap gap-1">`;
            cat.investigations.forEach(inv => {
                const price = inv.price ? ` <small class="opacity-75">৳${Number(inv.price).toLocaleString()}</small>` : '';
                html += `<label class="badge bg-secondary-subtle text-dark border border-secondary-subtle hc-lab-test-badge">
                    <input type="checkbox" class="d-none hc-lab-cb"
                        data-inv-id="${inv.id}" data-cat-id="${cat.id}">
                    ${inv.name}${price}
                </label>`;
            });
            html += `</div></div>`;
        });

        container.innerHTML = html || '<div class="text-muted small py-2">No tests available.</div>';

        container.querySelectorAll('.hc-lab-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const badge = this.closest('.hc-lab-test-badge');
                if (this.checked) {
                    badge.classList.add('selected');
                } else {
                    badge.classList.remove('selected');
                }
            });
        });
    }

    function showHcLabSections() {
        const labType  = document.querySelector('input[name="lab_type"]:checked')?.value || 'both';
        const showPath = ['pathology', 'both'].includes(labType);
        const showRad  = ['radiology', 'both'].includes(labType);
        const pathSec  = document.getElementById('hcLabPathSection');
        const radSec   = document.getElementById('hcLabRadSection');

        pathSec.style.display = showPath ? 'block' : 'none';
        radSec.style.display  = showRad  ? 'block' : 'none';

        pathSec.querySelectorAll('input, select, textarea').forEach(f => { f.disabled = !showPath; });
        radSec.querySelectorAll('input, select, textarea').forEach(f => { f.disabled = !showRad; });
    }

    document.querySelectorAll('input[name="lab_type"]').forEach(radio => {
        radio.addEventListener('change', showHcLabSections);
    });

    // Search filter for each section
    [['hcLabPathSearch', 'hcLabPathTests'], ['hcLabRadSearch', 'hcLabRadTests']].forEach(([searchId, listId]) => {
        document.getElementById(searchId)?.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.getElementById(listId)?.querySelectorAll('.hc-lab-cat').forEach(catEl => {
                let visible = 0;
                catEl.querySelectorAll('.hc-lab-test-badge').forEach(badge => {
                    const match = !q || badge.textContent.toLowerCase().includes(q);
                    badge.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                catEl.style.display = visible ? '' : 'none';
            });
        });
    });

    // ── Form submit: AJAX for OPD (shows token), redirect otherwise ──
    document.getElementById('hcCheckinFormEl').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const dest = document.getElementById('hcDestination').value;

        // Build lab hidden inputs when destination is lab
        if (dest === 'lab') {
            form.querySelectorAll('[data-hc-lab-hidden]').forEach(el => el.remove());

            function addHidden(f, name, value) {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = name; inp.value = value;
                inp.dataset.hcLabHidden = '1';
                f.appendChild(inp);
            }

            const pathSec = document.getElementById('hcLabPathSection');
            if (pathSec.style.display !== 'none') {
                addHidden(form, 'pathology_lab_inv_type_id', pathSec.dataset.typeId || '');
                let pi = 0;
                pathSec.querySelectorAll('.hc-lab-cb:checked').forEach(cb => {
                    addHidden(form, `pathology_requests[${pi}][lab_inv]`, cb.dataset.invId || '');
                    addHidden(form, `pathology_requests[${pi}][lab_inv_category_id]`, cb.dataset.catId || '');
                    pi++;
                });
            }

            const radSec = document.getElementById('hcLabRadSection');
            if (radSec.style.display !== 'none') {
                addHidden(form, 'radiology_lab_inv_type_id', radSec.dataset.typeId || '');
                let ri = 0;
                radSec.querySelectorAll('.hc-lab-cb:checked').forEach(cb => {
                    addHidden(form, `radiology_requests[${ri}][lab_inv]`, cb.dataset.invId || '');
                    addHidden(form, `radiology_requests[${ri}][lab_inv_category_id]`, cb.dataset.catId || '');
                    ri++;
                });
            }
        }

        // For OPD: submit via AJAX so we can show the token inline
        if (dest === 'opd') {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i><span id="hcSubmitLabel">Confirm OPD Check-in</span>';

                if (data.success) {
                    // Hide the form, show the token success panel
                    form.style.display = 'none';
                    const panel = document.getElementById('hcTokenSuccess');
                    document.getElementById('hcTokenNoDisplay').textContent = data.token_no || '—';
                    document.getElementById('hcPrintTokenBtn').href = data.pdf_url || '#';
                    panel.style.display = 'block';
                } else {
                    statusEl.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0 mt-2">`
                        + `<i class="bi bi-x-circle me-1"></i>${data.message || 'Check-in failed.'}</div>`;
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-2"></i><span id="hcSubmitLabel">Confirm OPD Check-in</span>';
                statusEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0 mt-2">'
                    + '<i class="bi bi-wifi-off me-1"></i>Submission failed — check your connection.</div>';
            });

            return;
        }

        // For all other destinations: standard form submit (redirect)
        form.submit();
    });

    // "New Check-in" button resets the modal back to the scan step
    document.getElementById('hcNewCheckinBtn').addEventListener('click', function () {
        document.getElementById('hcCheckinFormEl').style.display = '';
        document.getElementById('hcTokenSuccess').style.display = 'none';
        input.value = '';
        step2.style.display = 'none';
        statusEl.innerHTML = '';
        document.getElementById('hcPatientId').value = '';
        switchDest('opd');
    });

    // ── Follow-up auto-detection ──────────────────
    document.getElementById('hcOpdDoctor').addEventListener('change', function () {
        const doctorId  = this.value;
        const patientId = document.getElementById('hcPatientId').value;
        const badgeEl   = document.getElementById('hcVisitTypeBadge');
        const visitSel  = document.getElementById('hcVisitType');

        // reload shifts if Appointment source is active
        if (document.getElementById('hcBookingStatus').value === 'PRE_BOOK') {
            loadHcShifts();
        }

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
