{{-- ═══════════════════════════════════════════════
     Health Card IPD Check-in Modal
═══════════════════════════════════════════════ --}}
@php
    $hcAdmissionTypes = ['Emergency', 'Planned', 'Unplanned', 'Transfer(Internal)', 'Transfer(External)'];
    $hcStatuses       = ['Admitted'];
    $hcAllBeds        = \App\Models\Bed::with('bedType')->where('is_reserved', false)->get();
    $hcBeds           = $hcAllBeds->filter(fn($b) => ! optional($b->bedType)->is_icu)->values();
    $hcIcuBeds        = $hcAllBeds->filter(fn($b) => optional($b->bedType)->is_icu)->values();
@endphp

<div class="modal fade" id="hcIpdCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- ── Header ─────────────────────────────── --}}
            <div class="hc-modal-header">
                <div>
                    <div class="hc-modal-title">
                        <i class="bi bi-qr-code-scan"></i>
                        Health Card IPD Check-in
                    </div>
                    <div class="hc-modal-sub">Scan or type the card number to admit the patient</div>
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
                                <i class="bi bi-search me-1"></i>
                            </button>
                        </div>
                        <div class="form-text mt-1">Works with a USB QR / barcode scanner — it auto-finds when the scan completes.</div>
                    </div>
                </div>

                {{-- ── Status / error ──────────────────── --}}
                <div id="hcModalStatus"></div>

                {{-- ── Step 2 : Patient + IPD form (revealed after lookup) ── --}}
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

                    {{-- ── IPD Form ─────────────────────── --}}
                    <form action="{{ route('health-card.checkin') }}" method="POST" id="hcCheckinFormEl"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="patient_id" id="hcPatientId">
                        <input type="hidden" name="destination" value="ipd">
                        <input type="hidden" name="date" id="hcDate">

                        {{-- ===== STEP 1 : Admission Details ===== --}}
                        <div class="accordion mb-3" id="hcIpdAdmissionAcc">
                            <div class="accordion-item border shadow-sm rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button text-white" style="background-color:#2b335d;"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#hcAdmissionCollapse"
                                        aria-expanded="true">
                                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                                            <span class="fw-bold">IPD Admission</span>
                                            <span class="badge text-bg-light border">Step 1</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="hcAdmissionCollapse" class="accordion-collapse collapse show"
                                    data-bs-parent="#hcIpdAdmissionAcc">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Department <span
                                                        class="text-danger">*</span></label>
                                                <select name="department_id" id="hcIpdDept" class="form-select" required>
                                                    <option value="">-- Select --</option>
                                                    @foreach (\App\Models\Department::orderBy('name')->get() as $dept)
                                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Doctor <span
                                                        class="text-danger">*</span></label>
                                                <select name="doctor_id" id="hcIpdDoctor" class="form-select" required>
                                                    <option value="">— select dept first —</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Admission Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="datetime-local" name="admission_date" id="hcAdmissionDate"
                                                    class="form-control" required>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Possible Discharge Date</label>
                                                <input type="datetime-local" name="possible_discharge_date"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Admission Type</label>
                                                <select name="admission_type" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    @foreach ($hcAdmissionTypes as $type)
                                                        <option value="{{ $type }}">{{ $type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Status <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" readonly class="form-control" name="ipd_status" value="Admitted">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Patient History</label>
                                                <textarea name="patient_history" rows="2" class="form-control"
                                                    placeholder="Short history / Reason for admission"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Remarks</label>
                                                <textarea name="remarks" rows="2" class="form-control"
                                                    placeholder="Notes / Initial Diagnosis"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== STEP 2 : Bed / ICU Allocation ===== --}}
                        <div class="accordion mb-3" id="hcIpdBedAcc">
                            <div class="accordion-item border shadow-sm rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed text-white"
                                        style="background-color:#2b335d;" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#hcBedCollapse">
                                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                                            <span class="fw-bold">Bed / ICU Allocation</span>
                                            <span class="badge text-bg-light border">Step 2</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="hcBedCollapse" class="accordion-collapse collapse show"
                                    data-bs-parent="#hcIpdBedAcc">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="btn-group" role="group">
                                                    <input type="radio" class="btn-check" name="allocation_choice"
                                                        id="hcAllocBed" value="bed" autocomplete="off" checked>
                                                    <label class="btn btn-outline-primary" for="hcAllocBed">
                                                        <i class="bi bi-hospital"></i> Regular Bed
                                                    </label>
                                                    <input type="radio" class="btn-check" name="allocation_choice"
                                                        id="hcAllocIcu" value="icu" autocomplete="off">
                                                    <label class="btn btn-outline-danger" for="hcAllocIcu">
                                                        <i class="bi bi-heart-pulse"></i> ICU / Critical Care
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 hc-alloc-bed">
                                                <label class="form-label fw-semibold small">Bed <span class="text-danger">*</span></label>
                                                <select name="bed_id" class="form-select" required>
                                                    <option value="">-- Select Bed --</option>
                                                    @foreach ($hcBeds as $b)
                                                        <option value="{{ $b->id }}">
                                                            {{ $b->name }}
                                                            @if (optional($b->bedType)->name) [{{ $b->bedType->name }}] @endif
                                                            (৳ {{ $b->rent }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6 hc-alloc-icu" style="display:none;">
                                                <label class="form-label fw-semibold small">ICU Bed</label>
                                                <select name="icu_bed_id" class="form-select">
                                                    <option value="">-- Select ICU Bed --</option>
                                                    @forelse ($hcIcuBeds as $b)
                                                        <option value="{{ $b->id }}">
                                                            {{ $b->name }}
                                                            @if (optional($b->bedType)->name) [{{ $b->bedType->name }}] @endif
                                                            (৳ {{ $b->rent }})
                                                        </option>
                                                    @empty
                                                        <option value="" disabled>No ICU beds available</option>
                                                    @endforelse
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold small">From</label>
                                                <input type="datetime-local" name="from" id="hcBedFrom"
                                                    class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Bed Remarks</label>
                                                <textarea name="bed_remarks" rows="2" class="form-control"
                                                    placeholder="Shifting reason / bed note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== STEP 3 : Documents ===== --}}
                        <div class="accordion mb-3" id="hcIpdDocsAcc">
                            <div class="accordion-item border shadow-sm rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed text-white"
                                        style="background-color:#2b335d;" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#hcDocsCollapse">
                                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                                            <span class="fw-bold">Patient Documents</span>
                                            <span class="badge text-bg-light border">Step 3</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="hcDocsCollapse" class="accordion-collapse collapse"
                                    data-bs-parent="#hcIpdDocsAcc">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle mb-2" id="hcDocsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:30%;">Title</th>
                                                        <th style="width:30%;">File</th>
                                                        <th>Remarks</th>
                                                        <th style="width:60px;" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="hcDocsTbody">
                                                    <tr class="hc-doc-row">
                                                        <td><input type="text" name="documents[0][title]"
                                                                class="form-control" placeholder="e.g. Lab Report"></td>
                                                        <td><input type="file" name="documents[0][file]"
                                                                class="form-control"></td>
                                                        <td><input type="text" name="documents[0][remarks]"
                                                                class="form-control" placeholder="Optional notes"></td>
                                                        <td class="text-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger hc-doc-remove">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="hcDocsAddBtn">
                                            <i class="bi bi-plus-lg"></i> Add Document
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== STEP 4 : Advance Payment ===== --}}
                        <div class="accordion mb-3" id="hcIpdPayAcc">
                            <div class="accordion-item border shadow-sm rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed text-white"
                                        style="background-color:#2b335d;" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#hcPayCollapse">
                                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                                            <span class="fw-bold">Advance Payment</span>
                                            <span class="badge text-bg-light border">Step 4</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="hcPayCollapse" class="accordion-collapse collapse"
                                    data-bs-parent="#hcIpdPayAcc">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Amount</label>
                                                <input type="number" name="amount" value="0" min="0"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">VAT (%)</label>
                                                <input type="number" name="vat" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">TAX (%)</label>
                                                <input type="number" name="tax" class="form-control">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Payment Via</label>
                                                <select name="payment_via" id="hcPaymentVia" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="card">Card</option>
                                                    <option value="cheque">Cheque</option>
                                                    <option value="mfs">MFS</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Payment Date</label>
                                                <input type="datetime-local" name="payment_date" id="hcPaymentDate"
                                                    class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Received By</label>
                                                <input type="text" name="received_by" class="form-control"
                                                    placeholder="Cashier / Staff name">
                                            </div>

                                            {{-- Card --}}
                                            <div class="col-12 d-none" id="hcCardFields">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <div class="fw-semibold mb-2 small">Card Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Card No</label>
                                                            <input type="text" name="card_no" class="form-control"
                                                                placeholder="**** **** **** 1234">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Card Type</label>
                                                            <select name="card_type" class="form-select">
                                                                <option value="">-- Select --</option>
                                                                <option value="visa">Visa</option>
                                                                <option value="master">Master</option>
                                                                <option value="american_express">American Express</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cheque --}}
                                            <div class="col-12 d-none" id="hcChequeFields">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <div class="fw-semibold mb-2 small">Cheque Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Cheque Name</label>
                                                            <input type="text" name="cheque_name"
                                                                class="form-control" placeholder="Account holder name">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Cheque No</label>
                                                            <input type="text" name="cheque_no" class="form-control"
                                                                placeholder="Cheque number">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Cheque Date</label>
                                                            <input type="date" name="cheque_date"
                                                                class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MFS --}}
                                            <div class="col-12 d-none" id="hcMfsFields">
                                                <div class="border rounded-3 p-3 bg-light">
                                                    <div class="fw-semibold mb-2 small">MFS Details</div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label small">MFS Type</label>
                                                            <select name="mfs_type" class="form-select">
                                                                <option value="">-- Select --</option>
                                                                <option value="bkash">bKash</option>
                                                                <option value="nagad">Nagad</option>
                                                                <option value="rocket">Rocket</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">MFS No</label>
                                                            <input type="text" name="mfs_no" class="form-control"
                                                                placeholder="01XXXXXXXXX">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small">Transaction ID</label>
                                                            <input type="text" name="mfs_transaction_id"
                                                                class="form-control" placeholder="TXN123456789">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <label class="form-label fw-semibold small">Notes</label>
                                                <textarea name="notes" rows="2" class="form-control"
                                                    placeholder="Any note for this advance payment"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold small">Status</label>
                                                <select name="payment_status" class="form-select">
                                                    <option value="successed" selected>Successed</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="failed">Failed</option>
                                                    <option value="canceled">Canceled</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold small">Files</label>
                                                <input type="file" name="files[]" class="form-control" multiple>
                                                <div class="form-text">Upload receipt / cheque scan / supporting documents.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-lg fw-bold hc-submit-btn dest-ipd">
                                <i class="bi bi-check2-circle me-2"></i>
                                Confirm IPD Admission
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

.hc-scan-row { display: flex; align-items: flex-start; gap: 14px; }
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

/* Submit button */
.hc-submit-btn {
    background: linear-gradient(135deg, #1e2a6e, #2b3f9e);
    color: #fff; border: none; border-radius: 12px;
    padding: 14px; font-size: 15px;
    transition: opacity .2s;
}
.hc-submit-btn:hover { opacity: .9; color: #fff; }
.hc-submit-btn.dest-ipd { background: linear-gradient(135deg,#1b7a38,#2f9e44); }

@media (max-width: 576px) {
    .hc-patient-strip { flex-wrap: wrap; }
    .hc-patient-ids { text-align: left; }
}
</style>

{{-- ── Script ─────────────────────────────────── --}}
<script>
(function () {
    const modal     = document.getElementById('hcIpdCheckinModal');
    const input     = document.getElementById('hcModalInput');
    const searchBtn = document.getElementById('hcModalSearchBtn');
    const step2     = document.getElementById('hcStep2');
    const statusEl  = document.getElementById('hcModalStatus');
    const dateInput = document.getElementById('hcDate');
    const admDate   = document.getElementById('hcAdmissionDate');
    const bedFrom   = document.getElementById('hcBedFrom');
    const payDate   = document.getElementById('hcPaymentDate');

    const fmtNow = () => {
        const n = new Date(), p = x => String(x).padStart(2,'0');
        return `${n.getFullYear()}-${p(n.getMonth()+1)}-${p(n.getDate())}T${p(n.getHours())}:${p(n.getMinutes())}`;
    };

    // Set defaults on open
    modal.addEventListener('show.bs.modal', () => {
        const now = fmtNow();
        dateInput.value = now;
        admDate.value   = now;
        bedFrom.value   = now;
        payDate.value   = now;
    });

    // Keep hidden `date` synced with admission_date
    admDate.addEventListener('change', () => { dateInput.value = admDate.value; });

    // Reset on close
    modal.addEventListener('hidden.bs.modal', () => {
        input.value         = '';
        step2.style.display = 'none';
        statusEl.innerHTML  = '';
        document.getElementById('hcCheckinFormEl').reset();
        document.getElementById('hcPatientId').value = '';
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

    let autoTimer;
    input.addEventListener('input', function () {
        clearTimeout(autoTimer);
        if (this.value.trim().length >= 10) autoTimer = setTimeout(lookup, 280);
    });

    // ── Doctor cascade ───────────────────────────
    document.getElementById('hcIpdDept').addEventListener('change', function () {
        const selectEl = document.getElementById('hcIpdDoctor');
        const deptId   = this.value;
        selectEl.innerHTML = '<option value="">Loading…</option>';
        if (!deptId) { selectEl.innerHTML = '<option value="">— select dept first —</option>'; return; }

        fetch('{{ url("front_desk/get-doctors-by-department") }}/' + deptId)
            .then(r => r.json())
            .then(docs => {
                selectEl.innerHTML = '<option value="">-- Select Doctor --</option>';
                docs.forEach(d => selectEl.innerHTML += `<option value="${d.id}">${d.name}</option>`);
            })
            .catch(() => { selectEl.innerHTML = '<option value="">— failed to load —</option>'; });
    });

    // ── Bed / ICU allocation toggle ──────────────
    const allocBed  = document.getElementById('hcAllocBed');
    const allocIcu  = document.getElementById('hcAllocIcu');
    const bedField  = document.querySelector('.hc-alloc-bed');
    const icuField  = document.querySelector('.hc-alloc-icu');
    function syncAllocation() {
        const isIcu = allocIcu.checked;
        bedField.style.display = isIcu ? 'none' : '';
        icuField.style.display = isIcu ? '' : 'none';
    }
    allocBed.addEventListener('change', syncAllocation);
    allocIcu.addEventListener('change', syncAllocation);

    // ── Documents add/remove ─────────────────────
    const docsTbody = document.getElementById('hcDocsTbody');
    const docsAdd   = document.getElementById('hcDocsAddBtn');
    let docIndex    = 1;

    function reindexDocs() {
        Array.from(docsTbody.querySelectorAll('tr.hc-doc-row')).forEach((row, i) => {
            row.querySelectorAll('input').forEach(inp => {
                const n = inp.getAttribute('name');
                if (n) inp.setAttribute('name', n.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
            });
        });
    }

    docsAdd.addEventListener('click', () => {
        const row = document.createElement('tr');
        row.className = 'hc-doc-row';
        row.innerHTML = `
            <td><input type="text" name="documents[${docIndex}][title]" class="form-control" placeholder="e.g. Lab Report"></td>
            <td><input type="file" name="documents[${docIndex}][file]" class="form-control"></td>
            <td><input type="text" name="documents[${docIndex}][remarks]" class="form-control" placeholder="Optional notes"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger hc-doc-remove">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
        docsTbody.appendChild(row);
        docIndex++;
    });

    docsTbody.addEventListener('click', e => {
        const btn = e.target.closest('.hc-doc-remove');
        if (!btn) return;
        const rows = docsTbody.querySelectorAll('tr.hc-doc-row');
        if (rows.length <= 1) {
            btn.closest('tr').querySelectorAll('input').forEach(i => i.value = '');
        } else {
            btn.closest('tr').remove();
            reindexDocs();
        }
    });

    // ── Payment Via conditional fields ───────────
    const payVia       = document.getElementById('hcPaymentVia');
    const cardFields   = document.getElementById('hcCardFields');
    const chequeFields = document.getElementById('hcChequeFields');
    const mfsFields    = document.getElementById('hcMfsFields');
    payVia.addEventListener('change', () => {
        cardFields.classList.toggle('d-none',   payVia.value !== 'card');
        chequeFields.classList.toggle('d-none', payVia.value !== 'cheque');
        mfsFields.classList.toggle('d-none',    payVia.value !== 'mfs');
    });
})();
</script>
