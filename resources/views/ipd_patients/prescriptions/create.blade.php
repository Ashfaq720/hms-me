@extends('backend.layouts.master')
@section('title', 'Add Prescription - Ipd')

@push('styles')
    <style>
        .rx-page {
            background: #f0f0f0;
            min-height: 100vh;
            padding: 24px 0;
        }

        /* ── Header ── */
        .rx-header {
            background: #213f5c;
            color: #fff;
            border-radius: 12px;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rx-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .rx-logo {
            width: 60px;
            height: 60px;
            background: #f5c518;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #2d2d2d;
        }

        .rx-hospital-name {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            margin: 0;
        }

        .rx-hospital-sub {
            font-size: 13px;
            color: #ccc;
            margin: 2px 0 0;
        }

        .rx-doctor-info {
            text-align: right;
        }

        .rx-doctor-name {
            font-size: 16px;
            color: #fff;
            font-weight: 700;
        }

        .rx-doctor-desg {
            font-size: 12px;
            color: #ccc;
        }

        /* ── Patient Info Bar ── */
        .rx-patient-bar {
            background: #e8e8e8;
            border-radius: 10px;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .rx-patient-bar .rx-field label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: .5px;
        }

        .rx-patient-bar .rx-field p {
            font-size: 15px;
            font-weight: 600;
            margin: 2px 0 0;
        }

        /* ── Action Buttons ── */
        .rx-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 10px;
        }

        .btn-rx-action {
            border: 2px solid #d7aa04;
            background: #f5c518;
            color: #333;
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: .2s;
        }

        .btn-rx-action:hover {
            background: #fff;
            color: #333;
        }

        /* ── Cards ── */
        .rx-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            border-left: 4px solid #f5c518;
            margin-top: 16px;
        }

        .rx-card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rx-card-title i {
            font-size: 20px;
            color: #666;
        }

        /* ── Symptom / Test Items ── */
        .rx-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rx-item .remove-item {
            color: #dc3545;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
        }

        /* ── Test checkboxes ── */
        .rx-test-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rx-test-item label {
            font-size: 14px;
            cursor: pointer;
            margin: 0;
        }

        .rx-test-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #f5c518;
            cursor: pointer;
        }

        /* ── Rx Notes ── */
        .rx-notes-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            margin-top: 16px;
            position: relative;
        }

        .rx-notes-card .rx-watermark {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 64px;
            color: #f0f0f0;
        }

        .rx-notes-title {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .rx-notes-title i {
            color: #666;
        }

        /* ── Tx Medicine List ── */
        .rx-tx-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 16px 20px;
            min-height: 100px;
        }

        .rx-medicine-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .rx-medicine-row:last-child {
            border-bottom: none;
        }

        .rx-medicine-num {
            font-weight: 700;
            color: #666;
            min-width: 30px;
        }

        .rx-medicine-row .remove-item {
            color: #dc3545;
            cursor: pointer;
            border: none;
            background: none;
            margin-left: auto;
        }

        /* ── Footer Buttons ── */
        .rx-footer {
            display: flex;
            justify-content: end;
            gap: 16px;
            margin-top: 28px;
            padding-bottom: 20px;
        }

        .btn-rx-submit {
            background: #f5c518;
            color: #333;
            font-weight: 700;
            border: none;
            padding: 12px 36px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-rx-submit:hover {
            background: #e0b200;
        }

        .btn-rx-outline {
            background: #fff;
            color: #333;
            font-weight: 600;
            border: 2px solid #ccc;
            padding: 12px 36px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-rx-outline:hover {
            border-color: #999;
        }

        /* ── Hidden form fields ── */
        .rx-hidden-select {
            max-width: 250px;
        }

        /* ── Dosage Grid ── */
        .dosage-grid {
            display: flex;
            gap: 8px;
        }

        .dosage-slot {
            flex: 1;
            text-align: center;
        }

        .dosage-slot .slot-label {
            font-size: 11px;
            font-weight: 600;
            color: #666;
            margin-bottom: 4px;
        }

        .dosage-slot .slot-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .dosage-slot .slot-controls .dose-btn {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1px solid #ccc;
            background: #f8f9fa;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
            transition: .15s;
        }

        .dosage-slot .slot-controls .dose-btn:hover {
            background: #f5c518;
            border-color: #f5c518;
        }

        .dosage-slot .slot-controls .dose-val {
            width: 32px;
            text-align: center;
            font-weight: 700;
            font-size: 16px;
            border: none;
            background: none;
            padding: 0;
        }

        /* ── Quick Duration Chips ── */
        .duration-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .dur-chip {
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: .15s;
        }

        .dur-chip:hover,
        .dur-chip.active {
            background: #f5c518;
            border-color: #f5c518;
            color: #333;
        }

        /* ── Timing Toggle Buttons ── */
        .timing-toggles {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .timing-btn {
            padding: 6px 14px;
            border-radius: 6px;
            border: 1.5px solid #ddd;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: .15s;
        }

        .timing-btn:hover {
            border-color: #f5c518;
        }

        .timing-btn.active {
            background: #f5c518;
            border-color: #f5c518;
            color: #333;
        }
    </style>
@endpush

@section('content')
    <div class="rx-page">
        <div class="container">

            {{-- Back button --}}
            <div class="mb-2">
                <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('ipd-patients.prescriptions.store', $ipdPatient->id) }}" method="POST"
                id="prescriptionForm">
                @csrf

                {{-- ══════════ HEADER ══════════ --}}
                <div class="rx-header">
                    <div class="rx-header-left">
                        <div class="rx-logo"><i class="bi bi-hospital"></i></div>
                        <div>
                            <p class="rx-hospital-name">{{ setting('company_name') }}</p>
                            @if ($ipdPatient->doctor && $ipdPatient->doctor->department)
                                <p class="rx-hospital-sub">{{ $ipdPatient->doctor->department->name ?? '' }}</p>
                            @endif
                            <p class="rx-hospital-sub">{{ setting('company_address') }} &bull;
                                {{ setting('company_phone') }}</p>
                        </div>
                    </div>
                    <div class="rx-doctor-info">
                        {{-- Doctor selector (hidden by default, shown via button) --}}
                        <div id="doctorDisplay">
                            @if ($ipdPatient->doctor)
                                <p class="rx-doctor-name">{{ $ipdPatient->doctor->name }}</p>
                                <p class="rx-doctor-desg">{{ $ipdPatient->doctor?->designation?->name ?? '' }}</p>
                                <p class="rx-doctor-desg">Reg. No: {{ $ipdPatient->doctor?->registration_no ?? '-' }}</p>
                            @else
                                <p class="rx-doctor-name">Select Doctor</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ══════════ PATIENT INFO BAR ══════════ --}}
                <div class="rx-patient-bar">
                    <div class="rx-field">
                        <label>Ipd No</label>
                        <p>{{ $ipdPatient->ipd_no ?? '-' }}</p>
                    </div>
                    <div class="rx-field">
                        <label>Pt Name</label>
                        <p>{{ $ipdPatient->patient->patient_name ?? '-' }}</p>
                    </div>
                    <div class="rx-field">
                        <label>Age / Gender</label>
                        <p>
                            @if ($ipdPatient->patient->dob)
                                {{ calculateAgeFromDob($ipdPatient->patient?->dob) ?? '' }}
                            @else
                                N/A
                            @endif
                            / {{ ucfirst($ipdPatient->patient->gender ?? '-') }}
                        </p>
                    </div>
                    <div class="rx-field">
                        <label>Contact</label>
                        <p>{{ $ipdPatient->patient->mobileno ?? '-' }}</p>
                    </div>
                    <div class="rx-field">
                        <label>Date</label>
                        <p>{{ now()->format('M d, Y') }}</p>
                        <input type="hidden" name="date" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                {{-- ══════════ ACTION BUTTONS ══════════ --}}
                <div class="rx-actions">
                    <button type="button" class="btn-rx-action" data-bs-toggle="modal" data-bs-target="#symptomModal">
                        <i class="bi bi-plus-circle"></i> Add Symptoms
                    </button>
                    <button type="button" class="btn-rx-action" data-bs-toggle="modal" data-bs-target="#medicineModal">
                        <i class="bi bi-plus-circle"></i> Add Medicine
                    </button>
                </div>

                {{-- ══════════ MAIN CONTENT (2 columns) ══════════ --}}
                <div class="row mt-0">

                    {{-- LEFT COLUMN --}}
                    <div class="col-lg-5">
                        {{-- Symptoms Card --}}
                        <div class="rx-card">
                            <div class="rx-card-title"><i class="bi bi-virus"></i> Symptoms</div>
                            <div id="symptomList">
                                <p class="text-muted small" id="noSymptoms">No symptoms added yet.</p>
                            </div>
                        </div>

                        {{-- Tests Card --}}
                        <div class="rx-card">
                            <div class="rx-card-title"><i class="bi bi-clipboard2-pulse"></i> Lab Investigations</div>
                            <div id="labList">
                                @foreach ($labInvestigations as $lab)
                                    <div class="rx-test-item" style="flex-wrap: wrap;">
                                        <label for="lab_{{ $lab->id }}">{{ $lab->name }}</label>
                                        <input type="checkbox" id="lab_{{ $lab->id }}"
                                            name="lab_investigations[{{ $lab->id }}][lab_investigation_id]"
                                            value="{{ $lab->id }}" class="lab-check">
                                        <input type="text" class="form-control form-control-sm lab-note"
                                            name="lab_investigations[{{ $lab->id }}][note]"
                                            placeholder="Note...(Optional)" disabled
                                            style="font-size: 12px; width: 100%; display: none; margin-top: 6px;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="col-lg-7">
                        {{-- Rx Diagnosis & Clinical Notes --}}
                        <div class="rx-notes-card">
                            <span class="rx-watermark"><i class="bi bi-clipboard2-check"></i></span>
                            <div class="rx-notes-title"><i class="bi bi-journal-medical"></i> Tx - Diagnosis &amp; Clinical
                                Notes</div>
                            <textarea name="findings" id="findings" class="form-control border-0 bg-transparent p-0" rows="3"
                                placeholder="Enter diagnosis and clinical notes..." style="font-size:15px; font-weight:600; resize:none;">{{ old('findings') }}</textarea>

                            <hr class="my-3">

                            <div class="rx-notes-title"><i class="bi bi-journal-medical"></i> Rx</div>
                            <div class="rx-tx-area" id="medicineList">
                                <p class="text-muted small" id="noMedicines">No medicines added yet.</p>
                            </div>
                        </div>

                        {{-- Advice / Next Visit --}}
                        <div class="rx-notes-card">
                            <div class="rx-notes-title"><i class="bi bi-chat-square-text"></i> Advice</div>
                            <textarea name="advice" id="advice" class="form-control border-0 bg-transparent p-0" rows="2"
                                placeholder="Enter advice..." style="font-size:14px; resize:none;">{{ old('advice') }}</textarea>
                            <hr class="my-2">
                            <div class="d-flex align-items-center gap-2">
                                <label for="next_visit" class="form-label mb-0 fw-semibold"
                                    style="white-space:nowrap;">Next
                                    Visit:</label>
                                <input type="date" name="next_visit" id="next_visit"
                                    class="form-control form-control-sm border-0 bg-transparent"
                                    value="{{ old('next_visit') }}" style="max-width: 180px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════ FOOTER BUTTONS ══════════ --}}
                <div class="rx-footer">
                    {{-- <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn-rx-outline">
                        View Details
                    </a> --}}
                    <button type="submit" class="btn-rx-submit bg-success">Submit Prescription</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════ SYMPTOM MODAL (multi-select) ══════════ --}}
    <div class="modal fade" id="symptomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Symptoms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    @foreach ($symptoms as $symptom)
                        <div class="rx-test-item">
                            <label for="modal_symptom_{{ $symptom->id }}">{{ $symptom->name }}</label>
                            <input type="checkbox" class="modal-symptom-check" id="modal_symptom_{{ $symptom->id }}"
                                value="{{ $symptom->id }}" data-name="{{ $symptom->name }}">
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="addSymptomsBtn">Add Selected</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ MEDICINE MODAL ══════════ --}}
    <div class="modal fade" id="medicineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-capsule me-2"></i>Add Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Medicine Select --}}
                        <div class="col-12">
                            <label for="modal_medicine_name" class="form-label fw-semibold">Medicine <span
                                    class="text-danger">*</span></label>
                            <select id="modal_medicine_name" class="form-select">
                                <option value="" selected disabled>--- Select Medicine ---</option>
                                @foreach ($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->medicine_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dosage Grid --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dosage</label>
                            <div class="dosage-grid">
                                <div class="dosage-slot">
                                    <div class="slot-label"><i class="bi bi-sunrise"></i> Morning</div>
                                    <div class="slot-controls">
                                        <button type="button" class="dose-btn dose-minus"
                                            data-target="dose_morning">-</button>
                                        <input type="text" class="dose-val" id="dose_morning" value="0"
                                            readonly>
                                        <button type="button" class="dose-btn dose-plus"
                                            data-target="dose_morning">+</button>
                                    </div>
                                </div>
                                <div class="dosage-slot">
                                    <div class="slot-label"><i class="bi bi-sun"></i> Afternoon</div>
                                    <div class="slot-controls">
                                        <button type="button" class="dose-btn dose-minus"
                                            data-target="dose_afternoon">-</button>
                                        <input type="text" class="dose-val" id="dose_afternoon" value="0"
                                            readonly>
                                        <button type="button" class="dose-btn dose-plus"
                                            data-target="dose_afternoon">+</button>
                                    </div>
                                </div>
                                {{-- <div class="dosage-slot">
                                    <div class="slot-label"><i class="bi bi-sunset"></i> Evening</div>
                                    <div class="slot-controls">
                                        <button type="button" class="dose-btn dose-minus"
                                            data-target="dose_evening">-</button>
                                        <input type="text" class="dose-val" id="dose_evening" value="0"
                                            readonly>
                                        <button type="button" class="dose-btn dose-plus"
                                            data-target="dose_evening">+</button>
                                    </div>
                                </div> --}}
                                <div class="dosage-slot">
                                    <div class="slot-label"><i class="bi bi-moon"></i> Night</div>
                                    <div class="slot-controls">
                                        <button type="button" class="dose-btn dose-minus"
                                            data-target="dose_night">-</button>
                                        <input type="text" class="dose-val" id="dose_night" value="0" readonly>
                                        <button type="button" class="dose-btn dose-plus"
                                            data-target="dose_night">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Frequency --}}
                        <div class="col-12">
                            <label for="modal_medicine_frequency" class="form-label fw-semibold">Frequency</label>
                            <select id="modal_medicine_frequency" class="form-select">
                                <option value="" selected disabled>--- Select Frequency ---</option>
                                <option value="Once daily">Once daily</option>
                                <option value="Twice daily">Twice daily</option>
                                <option value="Thrice daily">Thrice daily</option>
                                <option value="Four times daily">Four times daily</option>
                                <option value="Every 4 hours">Every 4 hours</option>
                                <option value="Every 6 hours">Every 6 hours</option>
                                <option value="Every 8 hours">Every 8 hours</option>
                                <option value="Every 12 hours">Every 12 hours</option>
                                <option value="Once a week">Once a week</option>
                                <option value="Twice a week">Twice a week</option>
                                <option value="As needed (SOS)">As needed (SOS)</option>
                                <option value="Before sleep">Before sleep</option>
                                <option value="Stat (immediately)">Stat (immediately)</option>
                            </select>
                        </div>

                        {{-- Duration --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Duration</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number" id="modal_duration_num" class="form-control" min="1"
                                    value="" placeholder="0" style="max-width: 80px;">
                                <select id="modal_duration_unit" class="form-select" style="max-width: 120px;">
                                    <option value="Days" selected>Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                </select>
                            </div>
                            <div class="duration-chips mt-2">
                                <span class="dur-chip" data-days="3">3 Days</span>
                                <span class="dur-chip" data-days="5">5 Days</span>
                                <span class="dur-chip" data-days="7">7 Days</span>
                                <span class="dur-chip" data-days="10">10 Days</span>
                                <span class="dur-chip" data-days="14">14 Days</span>
                                <span class="dur-chip" data-days="21">21 Days</span>
                                <span class="dur-chip" data-days="30">30 Days</span>
                            </div>
                        </div>

                        {{-- Timing --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Timing</label>
                            <div class="timing-toggles">
                                <button type="button" class="timing-btn" data-value="Before meal">Before meal</button>
                                <button type="button" class="timing-btn" data-value="After meal">After meal</button>
                                <button type="button" class="timing-btn" data-value="With meal">With meal</button>
                                <button type="button" class="timing-btn" data-value="Empty stomach">Empty
                                    stomach</button>
                            </div>
                        </div>

                        {{-- Route --}}
                        <div class="col-6">
                            <label for="modal_medicine_route" class="form-label fw-semibold">Route</label>
                            <select id="modal_medicine_route" class="form-select">
                                <option value="" selected>--- Optional ---</option>
                                <option value="Oral">Oral</option>
                                <option value="IV">IV (Intravenous)</option>
                                <option value="IM">IM (Intramuscular)</option>
                                <option value="SC">SC (Subcutaneous)</option>
                                <option value="Topical">Topical</option>
                                <option value="Sublingual">Sublingual</option>
                                <option value="Inhaled">Inhaled</option>
                                <option value="Rectal">Rectal</option>
                                <option value="Ophthalmic">Ophthalmic (Eye)</option>
                                <option value="Otic">Otic (Ear)</option>
                                <option value="Nasal">Nasal</option>
                            </select>
                        </div>

                        {{-- Note --}}
                        <div class="col-6">
                            <label for="modal_medicine_note" class="form-label fw-semibold">Note</label>
                            <input type="text" id="modal_medicine_note" class="form-control"
                                placeholder="Any special instruction">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="addMedicineBtn"><i
                            class="bi bi-plus-circle me-1"></i>Add Medicine</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let symptomIndex = 0;
            let medicineIndex = 0;

            // Toggle note field when lab checkbox is checked/unchecked
            document.querySelectorAll('.lab-check').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const note = this.closest('.rx-test-item').querySelector('.lab-note');
                    note.style.display = this.checked ? 'block' : 'none';
                    note.disabled = !this.checked;
                    if (!this.checked) note.value = '';
                });
            });

            function toggleEmpty(containerId, msgId) {
                const container = document.getElementById(containerId);
                const msg = document.getElementById(msgId);
                const items = container.querySelectorAll('.rx-item, .rx-medicine-row');
                msg.style.display = items.length ? 'none' : 'block';
            }

            // ========== SYMPTOMS (multi-select) ==========
            document.getElementById('addSymptomsBtn').addEventListener('click', function() {
                const checked = document.querySelectorAll('.modal-symptom-check:checked');
                if (!checked.length) {
                    alert('Please select at least one symptom.');
                    return;
                }

                // Get already-added symptom IDs
                const existing = new Set();
                document.querySelectorAll('#symptomList input[name$="[symptom_id]"]').forEach(el => existing
                    .add(el.value));

                checked.forEach(cb => {
                    if (existing.has(cb.value)) return; // skip duplicates

                    const i = symptomIndex++;
                    const div = document.createElement('div');
                    div.className = 'rx-item';
                    div.innerHTML = `
                <span>${cb.dataset.name}</span>
                <input type="hidden" name="symptoms[${i}][symptom_id]" value="${cb.value}">
                <button type="button" class="remove-item" title="Remove"><i class="bi bi-x-lg"></i></button>
            `;
                    document.getElementById('symptomList').appendChild(div);
                    existing.add(cb.value);
                });

                toggleEmpty('symptomList', 'noSymptoms');

                // Uncheck all in modal
                document.querySelectorAll('.modal-symptom-check').forEach(cb => cb.checked = false);
                bootstrap.Modal.getInstance(document.getElementById('symptomModal')).hide();
            });

            // ========== DOSAGE +/- BUTTONS ==========
            document.querySelectorAll('.dose-plus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    let val = parseInt(input.value) || 0;
                    if (val < 9) input.value = val + 1;
                });
            });
            document.querySelectorAll('.dose-minus').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    let val = parseInt(input.value) || 0;
                    if (val > 0) input.value = val - 1;
                });
            });

            // ========== DURATION QUICK CHIPS ==========
            document.querySelectorAll('.dur-chip').forEach(chip => {
                chip.addEventListener('click', function() {
                    document.querySelectorAll('.dur-chip').forEach(c => c.classList.remove(
                        'active'));
                    this.classList.add('active');
                    document.getElementById('modal_duration_num').value = this.dataset.days;
                    document.getElementById('modal_duration_unit').value = 'Days';
                });
            });

            // ========== TIMING TOGGLE BUTTONS ==========
            document.querySelectorAll('.timing-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.timing-btn').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.toggle('active');
                });
            });

            // ========== MEDICINES ==========
            document.getElementById('addMedicineBtn').addEventListener('click', function() {
                const select = document.getElementById('modal_medicine_name');
                const medicineId = select.value;
                const medicineName = select.options[select.selectedIndex]?.text || '';

                // Dosage from grid
                const dm = document.getElementById('dose_morning').value;
                const da = document.getElementById('dose_afternoon').value;
                //const de = document.getElementById('dose_evening').value;
                const dn = document.getElementById('dose_night').value;
                const dosage = `${dm} + ${da} + ${dn}`;

                // Frequency
                const freqSelect = document.getElementById('modal_medicine_frequency');
                const frequency = freqSelect.value || '';

                // Duration
                const durNum = document.getElementById('modal_duration_num').value;
                const durUnit = document.getElementById('modal_duration_unit').value;
                const duration = durNum ? `${durNum} ${durUnit}` : '';

                // Timing
                const activeTimingBtn = document.querySelector('.timing-btn.active');
                const timing = activeTimingBtn ? activeTimingBtn.dataset.value : '';

                // Route
                const route = document.getElementById('modal_medicine_route').value || '';

                // Note
                const note = document.getElementById('modal_medicine_note').value.trim();

                if (!medicineId) {
                    alert('Please select a medicine.');
                    return;
                }

                const i = medicineIndex++;
                const num = String(i + 1).padStart(2, '0');

                // Build display string
                let display = medicineName;
                if (dosage !== '0 + 0 + 0') display += ` --- [ ${dm} - ${da} - ${dn} ]`;
                if (frequency) display += ` --- ${frequency}`;
                if (duration) display += ` --- ${duration}`;
                if (timing) display += ` --- ${timing}`;
                if (route) display += ` (${route})`;

                const div = document.createElement('div');
                div.className = 'rx-medicine-row';
                div.innerHTML = `
            <span class="rx-medicine-num">${num}.</span>
            <span>${display}</span>
            <input type="hidden" name="medicines[${i}][medicine_name]" value="${medicineName}">
            <input type="hidden" name="medicines[${i}][medicine_id]" value="${medicineId}">
            <input type="hidden" name="medicines[${i}][dosage]" value="${dosage}">
            <input type="hidden" name="medicines[${i}][frequency]" value="${frequency}">
            <input type="hidden" name="medicines[${i}][duration]" value="${duration}">
            <input type="hidden" name="medicines[${i}][note]" value="${[timing, route, note].filter(Boolean).join(', ')}">
            <button type="button" class="remove-item" title="Remove"><i class="bi bi-x-lg"></i></button>
        `;
                document.getElementById('medicineList').appendChild(div);
                toggleEmpty('medicineList', 'noMedicines');

                // Reset modal
                select.selectedIndex = 0;
                document.getElementById('dose_morning').value = '0';
                document.getElementById('dose_afternoon').value = '0';
                //document.getElementById('dose_evening').value = '0';
                document.getElementById('dose_night').value = '0';
                freqSelect.selectedIndex = 0;
                document.getElementById('modal_duration_num').value = '';
                document.getElementById('modal_duration_unit').value = 'Days';
                document.querySelectorAll('.dur-chip').forEach(c => c.classList.remove('active'));
                document.querySelectorAll('.timing-btn').forEach(b => b.classList.remove('active'));
                document.getElementById('modal_medicine_route').value = '';
                document.getElementById('modal_medicine_note').value = '';
                bootstrap.Modal.getInstance(document.getElementById('medicineModal')).hide();
            });

            // ========== REMOVE ITEM (delegated) ==========
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-item');
                if (!btn) return;
                const item = btn.closest('.rx-item, .rx-medicine-row');
                item.remove();
                toggleEmpty('symptomList', 'noSymptoms');
                toggleEmpty('medicineList', 'noMedicines');
            });
        });
    </script>
@endpush
