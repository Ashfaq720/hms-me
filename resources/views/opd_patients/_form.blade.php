@php
    $genders      = ['Male', 'Female', 'Other'];
    $bloods       = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $payment_mode = [
        'cash'   => 'Cash',
        'cheque' => 'Cheque',
        'bank'   => 'Transfer To Bank Account',
        'upi'    => 'UPI',
        'online' => 'Online',
        'other'  => 'Other',
    ];
    $defaultDate = old('appointment_date',
        isset($opd->appointment_date)
            ? \Carbon\Carbon::parse($opd->appointment_date)->format('Y-m-d')
            : now()->format('Y-m-d')
    );
@endphp

<div class="opd-form">

    {{-- ============ SECTION 1 : PATIENT ============ --}}
    <section class="form-card mb-4">
        <header class="form-card__header">
            <div class="form-card__title">
                <span class="step-badge">1</span>
                <div>
                    <h5 class="mb-0">Patient Information</h5>
                    <small class="text-muted">Select an existing patient or register a new one</small>
                </div>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Patient mode">
                <button type="button" class="btn btn-outline-primary" id="btnExisting">
                    <i class="bi bi-person-check"></i> Existing Patient
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnNew">
                    <i class="bi bi-person-plus"></i> New Patient
                </button>
                <button type="button" class="btn btn-outline-success" id="btnCard">
                    <i class="bi bi-credit-card"></i> Health Card
                </button>
            </div>
        </header>

        <div class="form-card__body">
            @php $preselectedPatientId = old('patient_id', request('patient_id')); @endphp
            @php $restoredMode = old('patient_mode', $preselectedPatientId ? 'existing' : 'new'); @endphp
            <input type="hidden" name="patient_mode" id="patient_mode"
                value="{{ $restoredMode }}">

            {{-- Health Card Lookup --}}
            <div id="cardBox" class="mb-3" style="display:none;">
                <label class="form-label fw-semibold">Health Card Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                    <input type="text" id="hc_input_opd" class="form-control"
                        placeholder="Scan or type — e.g. HC-2026-00001"
                        autocomplete="off" style="text-transform:uppercase;">
                    <button type="button" class="btn btn-outline-secondary" id="hcSearchBtnOpd">
                        <i class="bi bi-search me-1"></i> Find
                    </button>
                </div>
                <div id="hcResultOpd" class="mt-2"></div>
            </div>

            {{-- Existing Patient Picker --}}
            <div id="existingBox" class="mb-3">
                <label for="patient_id" class="form-label fw-semibold">
                    Search Patient
                    <span class="text-muted fw-normal small ms-1">— by name or MRN</span>
                </label>
                <select name="patient_id" id="patient_id"
                    class="form-select select2 @error('patient_id') is-invalid @enderror" style="width:100%">
                    <option value="">-- Select Patient --</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected($preselectedPatientId == $patient->id)>
                            {{ $patient->patient_name }} ({{ $patient->mrn ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('patient_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Existing patient info preview --}}
            <div id="patientInfoBox" class="info-panel mb-3" style="display:none;">
                <div class="info-panel__title d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span><i class="bi bi-person-circle me-1"></i> Patient Profile</span>
                    <span id="patientAllergyBadge" class="badge bg-danger d-none fs-12">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Allergy Alert
                    </span>
                </div>

                {{-- Primary (always visible) --}}
                <div class="row g-2 mb-2">
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell">
                            <small class="info-cell__label">Patient Name</small>
                            <span id="info_name" class="info-cell__value">—</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell">
                            <small class="info-cell__label">Contact No</small>
                            <span id="info_mobile" class="info-cell__value">—</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell">
                            <small class="info-cell__label">Age</small>
                            <span id="info_age" class="info-cell__value">—</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell">
                            <small class="info-cell__label">Gender</small>
                            <span id="info_gender" class="info-cell__value">—</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell">
                            <small class="info-cell__label">Blood Group</small>
                            <span id="info_blood" class="info-cell__value">—</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="info-cell info-cell--allergy" id="allergyCell">
                            <small class="info-cell__label">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Known Allergies
                            </small>
                            <span id="info_known_allergies" class="info-cell__value">—</span>
                        </div>
                    </div>
                </div>

                {{-- Secondary (collapsible) --}}
                <div class="collapse" id="patientMoreInfo">
                    <div class="row g-2 pt-1">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Marital Status</small>
                                <span id="info_marital_status" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Insurance</small>
                                <span id="info_insurance" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Insurance Validity</small>
                                <span id="info_insurance_validity" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">ID Number</small>
                                <span id="info_identification_number" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Organization</small>
                                <span id="info_organization_name" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Organization ID</small>
                                <span id="info_organization_id" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Organization Link</small>
                                <span id="info_organization_api_link" class="info-cell__value">—</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-cell info-cell--secondary">
                                <small class="info-cell__label">Address</small>
                                <span id="info_address" class="info-cell__value">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <a class="small text-muted text-decoration-none" data-bs-toggle="collapse"
                        href="#patientMoreInfo" role="button" aria-expanded="false">
                        <i class="bi bi-chevron-down me-1" id="moreInfoChevron"></i>
                        <span id="moreInfoText">Show more details</span>
                    </a>
                </div>
            </div>

            {{-- New / Editable patient fields --}}
            <div id="newBox" class="row g-3">
                <div class="col-md-6">
                    <label for="patient_name" class="form-label">Patient Name <span class="text-danger">*</span></label>
                    <input type="text" name="patient_name" id="patient_name"
                        class="form-control @error('patient_name') is-invalid @enderror"
                        value="{{ old('patient_name') }}" placeholder="Enter patient name">
                    @error('patient_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="mobileno" class="form-label">Contact No <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="mobileno" id="mobileno"
                            class="form-control @error('mobileno') is-invalid @enderror"
                            value="{{ old('mobileno') }}" placeholder="01XXXXXXXXX"
                            maxlength="20" inputmode="tel" autocomplete="off">
                        <span class="input-group-text" id="mobilenoFeedbackIcon"
                            style="min-width:2rem;display:flex;align-items:center;justify-content:center;"></span>
                    </div>
                    @error('mobileno')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div id="mobilenoFeedbackMsg" class="form-text"></div>
                    <div id="mobilenoPhoneWarning"
                        class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-flex align-items-start gap-2"
                        style="display:none !important;">
                        <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Number already registered.</strong><br>
                            <span id="mobilenoWarningPatient" class="text-muted"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="{{ old('dob') }}"
                        class="form-control @error('dob') is-invalid @enderror">
                    @error('dob')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="dobAgeDisplay" class="form-text text-primary fw-semibold"></div>
                </div>

                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">-- Select --</option>
                        @foreach ($genders as $g)
                            <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select name="blood_group" id="blood_group"
                        class="form-select @error('blood_group') is-invalid @enderror">
                        <option value="">-- Select --</option>
                        @foreach ($bloods as $b)
                            <option value="{{ $b }}" @selected(old('blood_group') === $b)>{{ $b }}</option>
                        @endforeach
                    </select>
                    @error('blood_group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="discount_type" class="form-label">Discount Type</label>
                    <select name="discount_type" id="discount_type"
                        class="form-select @error('discount_type') is-invalid @enderror">
                        <option value="">-- Select --</option>
                        <option value="CORPORATE" @selected(old('discount_type') == 'CORPORATE')>Corporate</option>
                        <option value="INSURANCE" @selected(old('discount_type') == 'INSURANCE')>Insurance</option>
                        <option value="STAFF"     @selected(old('discount_type') == 'STAFF')>Staff</option>
                        <option value="SELF"      @selected(old('discount_type') == 'SELF')>Self</option>
                    </select>
                    @error('discount_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="image" class="form-control">
                    @error('image')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    @if (!empty($patient?->image))
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $patient->image) }}" width="90"
                                style="border-radius:10px;object-fit:cover;">
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <label for="organization_name" class="form-label">Organization Name</label>
                    <input type="text" name="organization_name" id="organization_name"
                        class="form-control @error('organization_name') is-invalid @enderror"
                        value="{{ old('organization_name') }}" placeholder="Enter organization name">
                    @error('organization_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="organization_id" class="form-label">Organization ID</label>
                    <input type="text" name="organization_id" id="organization_id"
                        class="form-control @error('organization_id') is-invalid @enderror"
                        value="{{ old('organization_id') }}" placeholder="Corporate ID">
                    @error('organization_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="organization_api_link" class="form-label">Organization API Link</label>
                    <input type="text" name="organization_api_link" id="organization_api_link"
                        class="form-control @error('organization_api_link') is-invalid @enderror"
                        value="{{ old('organization_api_link') }}" placeholder="www.organization.com">
                    @error('organization_api_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="supporting_doc" class="form-label">Upload Document</label>
                    <input type="file" name="supporting_doc" id="supporting_doc"
                        class="form-control @error('supporting_doc') is-invalid @enderror">
                    @error('supporting_doc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Max 5MB · pdf, docx, png, jpeg</div>
                </div>

                <div class="col-md-4">
                    <label for="known_allergies" class="form-label">Known Allergies</label>
                    <textarea name="known_allergies" rows="2" id="known_allergies"
                        class="form-control">{{ old('known_allergies', $opd->known_allergies ?? '') }}</textarea>
                    @error('known_allergies')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="note_field" class="form-label">Note</label>
                    <textarea name="note" id="note_field" rows="2"
                        class="form-control">{{ old('note', $opd->note ?? '') }}</textarea>
                    @error('note')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    {{-- ============ SECTION 2 : APPOINTMENT & CONSULTATION ============ --}}
    <section class="form-card mb-4">
        <header class="form-card__header">
            <div class="form-card__title">
                <span class="step-badge">2</span>
                <div>
                    <h5 class="mb-0">Appointment & Consultation</h5>
                    <small class="text-muted">Department, doctor, visit date and details</small>
                </div>
            </div>
        </header>

        <div class="form-card__body">
            <div class="row g-3">

                {{-- Department & Doctor — selected first so visit history can load --}}
                <div class="col-md-6">
                    <label for="department_id" class="form-label">
                        Department <span class="text-danger">*</span>
                    </label>
                    <select name="department_id" id="department_id" style="width:100%"
                        class="form-select department select2">
                        <option value="">Select department</option>
                        @foreach ($departments ?? [] as $value)
                            <option value="{{ $value['id'] }}"
                                {{ (string) old('department_id', $opd->department_id ?? '') === (string) $value['id'] ? 'selected' : '' }}>
                                {{ $value['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="consultant_doctor" class="form-label">
                        Consultant Doctor <span class="text-danger">*</span>
                    </label>
                    <select name="consultant_doctor" id="consultant_doctor" class="form-select select2"
                        style="width:100%" {{ $disable_option ?? false ? 'disabled' : '' }}>
                        <option value="">— Select Department First —</option>
                        @foreach ($doctors ?? [] as $dvalue)
                            <option value="{{ $dvalue['id'] }}"
                                data-dept="{{ $dvalue['department_id'] }}"
                                {{ (string) old('consultant_doctor', $opd->consultant_doctor ?? ($doctor_select ?? '')) === (string) $dvalue['id'] ? 'selected' : '' }}>
                                {{ $dvalue['name'] . ($dvalue['doctor_code'] ? ' (' . $dvalue['doctor_code'] . ')' : '') }}
                            </option>
                        @endforeach
                    </select>
                    @error('consultant_doctor')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    @if (($disable_option ?? false) === true)
                        <input type="hidden" name="consultant_doctor"
                            value="{{ old('consultant_doctor', $doctor_select ?? ($opd->consultant_doctor ?? '')) }}">
                    @endif
                </div>

                {{-- Doctor Fee Bar --}}
                <div class="col-12">
                    <div id="doctorFeeLoading" class="text-muted small" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-1"></span> Loading fee…
                    </div>
                    <div id="doctorFeeNone" class="text-muted small" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> No fee configured for this doctor.
                    </div>
                    <div id="doctorFeeBox" class="doctor-fee-bar" style="display:none;">
                        <div class="fee-pill fee-pill--opd">
                            <span class="fee-pill__label">
                                <i class="bi bi-cash-coin me-1"></i>OPD Visit
                            </span>
                            <strong id="fee_opd_visit" class="fee-pill__value">—</strong>
                        </div>
                        <div class="fee-pill fee-pill--first">
                            <span class="fee-pill__label">
                                <i class="bi bi-person-plus me-1"></i>First Visit
                            </span>
                            <strong id="fee_first_visit" class="fee-pill__value">—</strong>
                        </div>
                        <div class="fee-pill fee-pill--followup">
                            <span class="fee-pill__label">
                                <i class="bi bi-arrow-repeat me-1"></i>Follow-up
                            </span>
                            <strong id="fee_follow_up" class="fee-pill__value">—</strong>
                        </div>
                        <div class="fee-pill fee-pill--window">
                            <span class="fee-pill__label">
                                <i class="bi bi-calendar-check me-1"></i>Follow-up Window
                            </span>
                            <strong id="fee_follow_up_window" class="fee-pill__value">—</strong>
                        </div>
                    </div>
                </div>

                {{-- Visit History Status --}}
                <div class="col-12">
                    <div id="visitHistoryLoading" class="text-muted small" style="display:none;">
                        <span class="spinner-border spinner-border-sm me-1"></span> Checking patient visit history…
                    </div>
                    <div id="visitHistoryBox" style="display:none;">
                        <div id="visitHistoryContent"></div>
                    </div>
                </div>

                {{-- Date & Visit Type --}}
                <div class="col-md-3">
                    <label for="appointment_date" class="form-label">
                        Appointment Date <span class="text-danger">*</span>
                    </label>
                    <input id="appointment_date" name="appointment_date" type="date"
                        class="form-control" value="{{ $defaultDate }}" />
                    @error('appointment_date')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="visit_type" class="form-label">
                        Visit Type <span class="text-danger">*</span>
                    </label>
                    <select name="visit_type" id="visit_type"
                        class="form-select @error('visit_type') is-invalid @enderror">
                        <option value="new"       @selected(old('visit_type', 'new') === 'new')>New Visit</option>
                        <option value="follow_up" @selected(old('visit_type') === 'follow_up')>Follow-up</option>
                        <option value="recheckup" @selected(old('visit_type') === 'recheckup')>Re-checkup</option>
                        <option value="referred"  @selected(old('visit_type') === 'referred')>Referred</option>
                        <option value="emergency" @selected(old('visit_type') === 'emergency')>Emergency</option>
                    </select>
                    @error('visit_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted" id="visitTypeFeeHint"></small>
                </div>

                <div class="col-md-6" id="referralSourceBox" style="display:none;">
                    <label for="referral_source" class="form-label">Referral Source</label>
                    <input class="form-control" type="text" name="referral_source" id="referral_source"
                        value="{{ old('referral_source', $opd->referral_source ?? '') }}"
                        placeholder="Doctor name, clinic, hospital…" />
                    @error('referral_source')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="opd_priority" class="form-label">Queue Priority</label>
                    <select name="priority" id="opd_priority"
                        class="form-select @error('priority') is-invalid @enderror">
                        <option value="Normal"         @selected(old('priority', $opd->priority ?? 'Normal') === 'Normal')>Normal</option>
                        <option value="Senior Citizen" @selected(old('priority', $opd->priority ?? '') === 'Senior Citizen')>Senior Citizen</option>
                        <option value="VIP"            @selected(old('priority', $opd->priority ?? '') === 'VIP')>VIP</option>
                        <option value="Emergency"      @selected(old('priority', $opd->priority ?? '') === 'Emergency')>Emergency</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Active token warning --}}
                <div class="col-12" id="activeTokenWarningOpd" style="display:none;">
                    <div class="alert alert-warning py-2 px-3 mb-0 small d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div id="activeTokenWarningOpdText"></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="reference" class="form-label">Reference / Remarks</label>
                    <input class="form-control" type="text" name="reference" id="reference"
                        value="{{ old('reference', $opd->reference ?? ($opd->refference ?? '')) }}"
                        placeholder="Additional reference…" />
                    @error('reference')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="chief_complaint" class="form-label">Chief Complaint</label>
                    <textarea name="chief_complaint" id="chief_complaint" rows="2"
                        class="form-control @error('chief_complaint') is-invalid @enderror"
                        placeholder="Describe the patient's primary complaint…">{{ old('chief_complaint', $opd->chief_complaint ?? '') }}</textarea>
                    @error('chief_complaint')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Shift & Slot --}}
                <div class="col-12">
                    <div class="section-divider">
                        <i class="bi bi-clock me-1"></i>
                        <span>Shift &amp; Time Slot</span>
                        <small class="text-muted fw-normal ms-1">— select after choosing a doctor and date</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="opd_shift_id" class="form-label">Shift</label>
                    <select name="shift_id" id="opd_shift_id" class="form-select">
                        <option value="">— Select Doctor First —</option>
                    </select>
                    @error('shift_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="opd_slot" class="form-label">Slot</label>
                    <select name="slot" id="opd_slot" class="form-select">
                        <option value="">— Select Shift First —</option>
                    </select>
                    @error('slot')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Visit Time</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                        <input type="time" id="opd_slot_time" class="form-control bg-light" readonly
                            placeholder="Auto-filled from slot">
                    </div>
                    <small class="text-muted">Auto-filled when a slot is selected.</small>
                </div>

            </div>
        </div>
    </section>

    {{-- ============ SECTION 3 : BILLING & PAYMENT ============ --}}
    <section class="form-card mb-4">
        <header class="form-card__header">
            <div class="form-card__title">
                <span class="step-badge">3</span>
                <div>
                    <h5 class="mb-0">Billing & Payment</h5>
                    <small class="text-muted">Charges, discount, tax and payment details</small>
                </div>
            </div>
        </header>

        <div class="form-card__body">
            <div class="row g-3">

                {{-- Charge calculation row --}}
                <div class="col-md-3">
                    <label for="standard_charge" class="form-label">
                        Doctor Fee
                        <span class="text-muted fw-normal small" id="feeTypeLabel"></span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">৳</span>
                        <input type="number" step="0.01" class="form-control bg-light" name="standard_charge"
                            id="standard_charge" readonly
                            value="{{ old('standard_charge', $opd->standard_charge ?? '') }}"
                            placeholder="—">
                    </div>
                    <small class="text-muted">Auto-filled by doctor &amp; visit type.</small>
                </div>

                <div class="col-md-3">
                    <label for="applied_charge" class="form-label">Applied Charge</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">৳</span>
                        <input type="number" step="0.01" min="0" class="form-control" name="applied_charge"
                            id="applied_charge"
                            value="{{ old('applied_charge', $opd->applied_charge ?? '') }}"
                            placeholder="0.00">
                    </div>
                    <small class="text-muted">Editable if different from standard.</small>
                </div>

                <div class="col-md-3">
                    <label for="discount" class="form-label">Discount</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">৳</span>
                        <input type="number" step="0.01" min="0" class="form-control" name="discount"
                            id="discount"
                            value="{{ old('discount', $opd->discount ?? 0) }}"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="tax" class="form-label">Tax</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" class="form-control" name="tax"
                            id="tax"
                            value="{{ old('tax', $opd->tax ?? 0) }}"
                            placeholder="0">
                        <span class="input-group-text bg-light text-muted">%</span>
                    </div>
                </div>

                <div class="col-12"><hr class="my-0"></div>

                {{-- Package (optional) --}}
                @php
                    $opdPackages = \App\Models\Package::where('is_active', true)
                        ->whereIn('package_type', ['OPD', 'DIAGNOSTIC', 'PATHOLOGY', 'RADIOLOGY', 'PHARMACY', 'PHYSIOTHERAPY', 'DENTAL', 'WELLNESS', 'CORPORATE'])
                        ->orderBy('package_type')->orderBy('name')->get();
                @endphp
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong class="text-success"><i class="bi bi-box-seam"></i> Package Enrolment <small class="text-muted">(optional — auto-bundles charges)</small></strong>
                                <a href="{{ route('packages.index') }}" target="_blank" class="small text-muted"><i class="bi bi-arrow-up-right"></i> Manage</a>
                            </div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <select name="enroll_package_id" id="opdEnrollPkg" class="form-select select2" data-placeholder="-- No Package --" style="width:100%">
                                        <option value="">-- No Package — Charge individually --</option>
                                        @foreach ($opdPackages->groupBy('package_type') as $type => $items)
                                            <optgroup label="{{ $type }}">
                                                @foreach ($items as $pkg)
                                                    <option value="{{ $pkg->id }}" data-amount="{{ $pkg->total_amount }}" data-discount="{{ $pkg->discount }}"
                                                        @selected(old('enroll_package_id') == $pkg->id)>
                                                        {{ $pkg->name }} — ৳{{ number_format($pkg->total_amount, 0) }} @if($pkg->discount > 0) ({{ $pkg->discount }}% off) @endif
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">If a package is chosen, its services auto-post to the bill.</small>
                                    <div id="opdPkgPreview" style="display:none;">
                                        <strong class="text-success">Selected: ৳ <span id="opdPkgPrice">0</span></strong>
                                        <small class="text-muted">– <span id="opdPkgDisc">0</span>% off</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12"><hr class="my-0"></div>

                {{-- Totals row --}}
                <div class="col-md-4">
                    <label for="amount" class="form-label fw-semibold">Total Amount</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white fw-bold">৳</span>
                        <input type="number" step="0.01" class="form-control total-amount-field fw-bold"
                            name="amount" id="amount" readonly
                            value="{{ old('amount', $opd->amount ?? '') }}"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="paid_amount" class="form-label">Paid Amount</label>
                    <div class="input-group">
                        <span class="input-group-text bg-success text-white">৳</span>
                        <input type="number" step="0.01" min="0" class="form-control" name="paid_amount"
                            id="paid_amount" autocomplete="off"
                            value="{{ old('paid_amount', $opd->paid_amount ?? '') }}"
                            placeholder="0.00">
                    </div>
                    <small class="text-muted">Adjust for partial payments.</small>
                    @error('paid_amount')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Balance Due</label>
                    <div class="input-group">
                        <span class="input-group-text" id="balanceDueIcon">৳</span>
                        <input type="number" step="0.01" class="form-control fw-semibold"
                            id="balance_due" readonly placeholder="0.00">
                    </div>
                    <small class="text-muted">Total − Paid amount.</small>
                </div>

                <div class="col-12"><hr class="my-0"></div>

                {{-- Payment mode row --}}
                <div class="col-md-4">
                    <label for="payment_mode" class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select" id="payment_mode">
                        @foreach ($payment_mode ?? [] as $payment_key => $payment_value)
                            <option value="{{ $payment_key }}"
                                {{ old('payment_mode', $opd->payment_mode ?? 'cash') == $payment_key ? 'selected' : '' }}>
                                {{ $payment_value }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_mode')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 payment-extra d-none" id="cheque_no_div">
                    <label for="cheque_no" class="form-label">Cheque No</label>
                    <input type="text" name="cheque_no" id="cheque_no" class="form-control"
                        value="{{ old('cheque_no', $opd->cheque_no ?? '') }}" disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="cheque_date_div">
                    <label for="cheque_date" class="form-label">Cheque Date</label>
                    <input type="date" name="cheque_date" id="cheque_date" class="form-control"
                        value="{{ old('cheque_date', isset($opd->cheque_date) ? \Carbon\Carbon::parse($opd->cheque_date)->format('Y-m-d') : '') }}"
                        disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="bank_name_div">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" class="form-control"
                        value="{{ old('bank_name', $opd->bank_name ?? '') }}" disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="account_no_div">
                    <label for="account_no" class="form-label">Account No</label>
                    <input type="text" name="account_no" id="account_no" class="form-control"
                        value="{{ old('account_no', $opd->account_no ?? '') }}" disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="transaction_id_div">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                        value="{{ old('transaction_id', $opd->transaction_id ?? '') }}" disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="upi_id_div">
                    <label for="upi_id" class="form-label">UPI / Online ID</label>
                    <input type="text" name="upi_id" id="upi_id" class="form-control"
                        value="{{ old('upi_id', $opd->upi_id ?? '') }}" disabled>
                </div>

                <div class="col-md-4 payment-extra d-none" id="other_payment_div">
                    <label for="other_payment_details" class="form-label">Payment Details</label>
                    <input type="text" name="other_payment_details" id="other_payment_details"
                        class="form-control"
                        value="{{ old('other_payment_details', $opd->other_payment_details ?? '') }}" disabled>
                </div>

            </div>
        </div>
    </section>

    {{-- ============ SECTION 4 : PATIENT DOCUMENTS ============ --}}
    <section class="form-card mb-4">
        <header class="form-card__header">
            <div class="form-card__title">
                <span class="step-badge">4</span>
                <div>
                    <h5 class="mb-0">Patient Documents</h5>
                    <small class="text-muted">Upload supporting files (lab reports, referral letters, etc.)</small>
                </div>
            </div>
        </header>

        <div class="form-card__body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-2" id="opdDocumentsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%;">Title</th>
                            <th style="width:30%;">File</th>
                            <th>Remarks</th>
                            <th style="width:60px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="opdDocumentsTbody">
                        <tr class="opd-document-row">
                            <td><input type="text" name="documents[0][title]" class="form-control"
                                placeholder="e.g. Lab Report"></td>
                            <td><input type="file" name="documents[0][file]" class="form-control"></td>
                            <td><input type="text" name="documents[0][remarks]" class="form-control"
                                placeholder="Optional notes"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger opd-remove-document"
                                    title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="opdAddDocumentBtn">
                <i class="bi bi-plus-lg"></i> Add Document
            </button>
            @error('documents')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('documents.*.file')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </section>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Priority select color
        const priorityColors = {
            'Normal':         { bg: '', border: '' },
            'Senior Citizen': { bg: '#fff8e1', border: '#ffc107' },
            'VIP':            { bg: '#e8f5e9', border: '#28a745' },
            'Emergency':      { bg: '#fdecea', border: '#dc3545' },
        };
        function applyPriorityStyleOpd() {
            const sel  = document.getElementById('opd_priority');
            if (!sel) return;
            const style = priorityColors[sel.value] || priorityColors['Normal'];
            sel.style.backgroundColor = style.bg;
            sel.style.borderColor     = style.border;
        }
        const prioritySel = document.getElementById('opd_priority');
        if (prioritySel) {
            prioritySel.addEventListener('change', applyPriorityStyleOpd);
            applyPriorityStyleOpd();
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody  = document.getElementById('opdDocumentsTbody');
        const addBtn = document.getElementById('opdAddDocumentBtn');
        let docIndex = 1;

        function rebuildIndexes() {
            Array.from(tbody.querySelectorAll('tr.opd-document-row')).forEach(function (row, i) {
                row.querySelectorAll('input').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) return;
                    input.setAttribute('name', name.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
                });
            });
        }

        addBtn.addEventListener('click', function () {
            const row = document.createElement('tr');
            row.className = 'opd-document-row';
            row.innerHTML = `
                <td><input type="text" name="documents[${docIndex}][title]" class="form-control" placeholder="e.g. Lab Report"></td>
                <td><input type="file" name="documents[${docIndex}][file]" class="form-control"></td>
                <td><input type="text" name="documents[${docIndex}][remarks]" class="form-control" placeholder="Optional notes"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger opd-remove-document" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
            tbody.appendChild(row);
            docIndex++;
        });

        tbody.addEventListener('click', function (e) {
            const btn = e.target.closest('.opd-remove-document');
            if (!btn) return;
            const rows = tbody.querySelectorAll('tr.opd-document-row');
            if (rows.length <= 1) {
                btn.closest('tr').querySelectorAll('input').forEach(function (i) { i.value = ''; });
            } else {
                btn.closest('tr').remove();
                rebuildIndexes();
            }
        });
    });
</script>
@endpush

<style>
    /* ---- Card layout ---- */
    .opd-form .form-card {
        background: #fff;
        border: 1px solid #e3e6ef;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(43, 51, 93, .04);
        overflow: hidden;
    }
    .opd-form .form-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 20px;
        background: linear-gradient(180deg, #f7f8fc 0%, #eef0f8 100%);
        border-bottom: 1px solid #e3e6ef;
    }
    .opd-form .form-card__title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .opd-form .form-card__title h5 { color: #2b335d; font-weight: 600; }
    .opd-form .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #2b335d;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(43, 51, 93, .25);
    }
    .opd-form .form-card__body { padding: 20px; }

    /* ---- Form controls ---- */
    .opd-form .form-label {
        font-weight: 500;
        font-size: 13px;
        color: #45506b;
        margin-bottom: 4px;
    }
    .opd-form .form-control,
    .opd-form .form-select {
        border-radius: 8px;
        border-color: #d8dce8;
    }
    .opd-form .form-control:focus,
    .opd-form .form-select:focus {
        border-color: #2b335d;
        box-shadow: 0 0 0 .15rem rgba(43, 51, 93, .15);
    }

    /* ---- Patient info panel ---- */
    .opd-form .info-panel {
        background: #f7f9ff;
        border: 1px dashed #c5cce0;
        border-radius: 10px;
        padding: 16px 18px;
    }
    .opd-form .info-panel__title {
        font-weight: 600;
        color: #2b335d;
        margin-bottom: 12px;
    }
    .opd-form .info-cell {
        display: flex;
        flex-direction: column;
        padding: 8px 10px;
        background: #fff;
        border-radius: 6px;
        border: 1px solid #e8ebf3;
        height: 100%;
    }
    .opd-form .info-cell--secondary {
        background: #f9fafb;
        border-color: #edf0f7;
    }
    .opd-form .info-cell--allergy {
        border-color: #ffd6d6;
    }
    .opd-form .info-cell--allergy.has-allergy {
        background: #fff1f0;
        border-color: #ffccc7;
    }
    .opd-form .info-cell--allergy.has-allergy .info-cell__label { color: #cf1322; }
    .opd-form .info-cell--allergy.has-allergy .info-cell__value { color: #820014; font-weight: 600; }
    .opd-form .info-cell__label {
        color: #8a92a6;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 600;
    }
    .opd-form .info-cell__value {
        color: #2b335d;
        font-weight: 500;
        font-size: 14px;
        word-break: break-word;
    }

    /* ---- Doctor fee bar ---- */
    .doctor-fee-bar {
        display: flex;
        border: 1px solid #e3e6ef;
        border-radius: 8px;
        overflow: hidden;
        background: #f8f9fc;
    }
    .fee-pill {
        flex: 1;
        padding: 10px 14px;
        border-right: 1px solid #e3e6ef;
        text-align: center;
        border-top: 3px solid transparent;
    }
    .fee-pill:last-child { border-right: none; }
    .fee-pill__label {
        display: block;
        font-size: 11px;
        color: #8a92a6;
        margin-bottom: 3px;
    }
    .fee-pill__value { font-size: 16px; font-weight: 700; color: #2b335d; }
    .fee-pill--opd     { border-top-color: #0d6efd; }
    .fee-pill--first   { border-top-color: #198754; }
    .fee-pill--followup{ border-top-color: #ffc107; }
    .fee-pill--window  { border-top-color: #0dcaf0; }

    /* ---- Visit history cards ---- */
    .vh-card {
        border-radius: 8px;
        border: 1px solid #e3e6ef;
        border-left: 4px solid #0dcaf0;
        padding: 12px 16px;
        background: #f0fbff;
    }
    .vh-card--followup {
        border-left-color: #198754;
        background: #f0fff4;
        border-color: #b7ebca;
    }
    .vh-card--expired {
        border-left-color: #ffc107;
        background: #fffbf0;
        border-color: #ffeeba;
    }
    .vh-card__head {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .vh-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 20px;
        font-size: 13px;
        color: #6b7280;
    }
    .vh-card__meta strong { color: #1a1f36; }
    .vh-card__hint { font-size: 12px; margin-top: 8px; }

    /* ---- Billing ---- */
    .total-amount-field { font-size: 18px !important; color: #2b335d !important; }
    #balance_due.balance-due    { color: #dc3545 !important; }
    #balance_due.balance-paid   { color: #198754 !important; }
    #balance_due.balance-credit { color: #fd7e14 !important; }
    #balanceDueIcon.due-icon-danger  { background: #dc3545 !important; color: #fff !important; border-color: #dc3545 !important; }
    #balanceDueIcon.due-icon-success { background: #198754 !important; color: #fff !important; border-color: #198754 !important; }
    #balanceDueIcon.due-icon-warning { background: #fd7e14 !important; color: #fff !important; border-color: #fd7e14 !important; }

    /* ---- Section divider ---- */
    .section-divider {
        display: flex;
        align-items: center;
        font-size: 13px;
        font-weight: 600;
        color: #45506b;
        padding-bottom: 6px;
        border-bottom: 1px solid #e3e6ef;
    }

    /* ---- Patient mode buttons ---- */
    #btnExisting.active,
    #btnNew.active {
        background-color: #2b335d !important;
        border-color: #2b335d !important;
        color: #fff !important;
    }

    .fs-12 { font-size: 12px !important; }
</style>
