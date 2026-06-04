@php
    $statuses = ['Admitted', 'Discharged', 'Transferred', 'Cancelled'];
    $types = ['Emergency', 'Planned', 'Unplanned', 'Transfer(Internal)', 'Transfer(External)'];
    $genders = ['Male', 'Female', 'Other'];
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

@if (request('from_opd_id'))
    <input type="hidden" name="from_opd_id" value="{{ request('from_opd_id') }}">
    <div class="alert alert-info py-2 small mb-3">
        <i class="bi bi-arrow-left-right me-1"></i>
        Admitting an existing OPD patient to IPD. Patient is preselected below.
    </div>
@endif

<div class="row g-3">

    {{-- ===== Ipd ADMISSION INFO ===== --}}
    <div class="col-12 mt-4">
        <div class="accordion" id="ipdAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="ipdHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#ipdCollapse" aria-expanded="true"
                        aria-controls="ipdCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">IPD Admission</span>
                            </div>
                            <span class="badge text-bg-light border">Step 1</span>
                        </div>
                    </button>
                </h2>

                <div id="ipdCollapse" class="accordion-collapse collapse show" aria-labelledby="ipdHeading"
                    data-bs-parent="#ipdAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">

                            <div class="rounded-box mb-3">
                                {{-- Mode Toggle --}}
                                <div class="col-12 d-flex gap-2 align-items-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        id="btnExisting">Existing Patient</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnNew">New
                                        Patient</button>
                                </div>

                                {{-- Existing Patient Dropdown --}}
                                <div class="col-md-12 mt-2" id="existingBox">
                                    <label for="patient_id" class="form-label">Select Patient</label>
                                    <select name="patient_id" id="patient_id"
                                        class="form-select select2 @error('patient_id') is-invalid @enderror"
                                        data-placeholder="-- Select Existing Patient --"
                                        style="width:100%">
                                        <option value=""></option>
                                        @foreach ($patients as $patient)
                                            <option value="{{ $patient->id }}" @selected(old('patient_id', request('patient_id')) == $patient->id)>
                                                {{ $patient->patient_name }} ({{ $patient->mrn ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Existing Patient Info Display --}}
                                <div id="patientInfoBox" class="col-md-12 mt-2" style="display: none;">
                                    <div class="border rounded-3 p-3 bg-white shadow-sm">
                                        <div class="fw-semibold mb-2 text-primary">
                                            <i class="bi bi-person-circle"></i> Patient Information
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Patient Name</small>
                                                <span id="info_name" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Contact No</small>
                                                <span id="info_mobile" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Age</small>
                                                <span id="info_age" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Gender</small>
                                                <span id="info_gender" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Blood Group</small>
                                                <span id="info_blood" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Marital Status</small>
                                                <span id="info_marital_status" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Organization Name</small>
                                                <span id="info_organization_name" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Organization ID</small>
                                                <span id="info_organization_id" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Organization API Link</small>
                                                <span id="info_organization_api_link" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Address</small>
                                                <span id="info_address" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Identification Number</small>
                                                <span id="info_identification_number" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Known Allergies</small>
                                                <span id="info_known_allergies" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Insurance</small>
                                                <span id="info_insurance" class="fw-medium">—</span>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted d-block fw-bold">Insurance Validity</small>
                                                <span id="info_insurance_validity" class="fw-medium">—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- New Patient Fields --}}
                                <div id="newBox" class="row g-3 p-0 m-0">
                                    <input type="hidden" name="patient_mode" id="patient_mode" value="existing">
                                    <div class="col-md-6">
                                        <label for="patient_name" class="form-label">Patient Name <span
                                                class="text-danger">*</span></label>
                                        <input required type="text" name="patient_name" id="patient_name"
                                            class="form-control @error('patient_name') is-invalid @enderror"
                                            value="{{ old('patient_name') }}" placeholder="Enter patient name">
                                        @error('patient_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mobileno" class="form-label">Contact No <span
                                                class="text-danger">*</span></label>
                                        <input required type="text" name="mobileno" id="mobileno"
                                            class="form-control @error('mobileno') is-invalid @enderror"
                                            value="{{ old('mobileno') }}" placeholder="Enter Contact No">
                                        @error('mobileno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">DOB</label>
                                        <input type="date" name="dob" id="dob" value="{{ old('dob') }}"
                                            class="form-control">
                                        @error('dob')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select name="gender" id="gender" class="form-select" required>
                                            <option value="">-- Select --</option>
                                            @foreach ($genders as $g)
                                                <option value="{{ $g }}" @selected(old('gender') === $g)>
                                                    {{ $g }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Blood Group</label>
                                        <select name="blood_group" id="blood_group" class="form-select">
                                            <option value="">-- Select --</option>
                                            @foreach ($bloods as $b)
                                                <option value="{{ $b }}" @selected(old('blood_group') === $b)>
                                                    {{ $b }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('blood_group')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Discount Type</label>
                                        <select name="discount_type"
                                            class="form-select @error('discount_type') is-invalid @enderror">
                                            <option value="">--Select Discount Type--</option>
                                            <option value="CORPORATE"
                                                {{ old('discount_type') == 'CORPORATE' ? 'selected' : '' }}>Corporate
                                            </option>
                                            <option value="INSURANCE"
                                                {{ old('discount_type') == 'INSURANCE' ? 'selected' : '' }}>Insurance
                                            </option>
                                            <option value="STUFF"
                                                {{ old('discount_type') == 'STUFF' ? 'selected' : '' }}>Stuff</option>
                                            <option value="SELF"
                                                {{ old('discount_type') == 'SELF' ? 'selected' : '' }}>Self</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Organization Name</label>
                                        <input type="text" name="organization_name"
                                            class="form-control @error('organization_name') is-invalid @enderror"
                                            value="{{ old('organization_name') }}"
                                            placeholder="Enter organization name">
                                        @error('organization_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Organization Id</label>
                                        <input type="text" name="organization_id"
                                            class="form-control @error('organization_id') is-invalid @enderror"
                                            value="{{ old('organization_id') }}" placeholder="Corporate ID">
                                        @error('organization_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Organization Api Link</label>
                                        <input type="text" name="organization_api_link"
                                            class="form-control @error('organization_api_link') is-invalid @enderror"
                                            value="{{ old('organization_api_link') }}"
                                            placeholder="www.organization.com">
                                        @error('organization_api_link')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="supporting_doc"
                                            class="form-control @error('supporting_doc') is-invalid @enderror">
                                        @error('supporting_doc')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="small text-muted">Max 5MB. pdf, docx, png, jpeg</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="department_id" class="form-select select2"
                                    data-placeholder="-- Select Department --" style="width:100%" required>
                                    <option value=""></option>
                                    @foreach ($departments as $dep)
                                        <option value="{{ $dep->id }}" @selected(old('department_id', $ipdPatient->department_id ?? '') == $dep->id)>
                                            {{ $dep->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Doctor (filtered by selected department) --}}
                            <div class="col-md-4">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" id="doctor_id" class="form-select select2"
                                    data-placeholder="-- Select Doctor --" style="width:100%" required
                                    data-selected="{{ old('doctor_id', $ipdPatient->doctor_id ?? '') }}">
                                    <option value=""></option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}"
                                            data-department="{{ $d->department_id }}"
                                            @selected(old('doctor_id', $ipdPatient->doctor_id ?? '') == $d->id)>
                                            {{ $d->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            @push('scripts')
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const $dept = $('#department_id');
                                        const $doc = $('#doctor_id');
                                        if (!$dept.length || !$doc.length) return;

                                        // Cache all original doctor <option>s once.
                                        const allDoctorOptions = $doc.find('option').map(function () {
                                            return {
                                                value: this.value,
                                                text: this.textContent,
                                                department: $(this).data('department') ?? '',
                                                selected: this.selected,
                                            };
                                        }).get();

                                        function rebuildDoctors(deptId) {
                                            const preserved = $doc.attr('data-selected') || $doc.val();
                                            $doc.empty();

                                            // No department selected → disable & show placeholder only
                                            if (!deptId) {
                                                $doc.append(new Option('', ''));
                                                $doc.prop('disabled', true);
                                                $doc.removeAttr('data-selected');
                                                $doc.trigger('change.select2');
                                                return;
                                            }

                                            $doc.prop('disabled', false);
                                            allDoctorOptions.forEach(function (opt) {
                                                if (opt.value === '') {
                                                    $doc.append(new Option('', ''));
                                                    return;
                                                }
                                                if (String(opt.department) === String(deptId)) {
                                                    const o = new Option(opt.text, opt.value, false, String(opt.value) === String(preserved));
                                                    $(o).attr('data-department', opt.department);
                                                    $doc.append(o);
                                                }
                                            });
                                            $doc.removeAttr('data-selected');
                                            $doc.trigger('change.select2');
                                        }

                                        // Initial filter on load (handles old('department_id') / edit screen)
                                        rebuildDoctors($dept.val());

                                        $dept.on('change', function () {
                                            rebuildDoctors($(this).val());
                                        });
                                    });
                                </script>
                            @endpush

                            {{-- Admission Date --}}
                            <div class="col-md-4">
                                <label class="form-label">Admission Date <span class="text-danger">*</span></label>

                                <input type="datetime-local" name="admission_date"
                                    value="{{ old('admission_date', isset($ipdPatient) ? $ipdPatient->admission_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                    class="form-control" required>

                                @error('admission_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>


                            {{-- Possible Discharge --}}
                            <div class="col-md-4">
                                <label class="form-label">Possible Discharge Date</label>
                                <input type="datetime-local" name="possible_discharge_date"
                                    value="{{ old('possible_discharge_date', optional($ipdPatient->possible_discharge_date ?? null)->format('Y-m-d H:i')) }}"
                                    class="form-control">
                                @error('possible_discharge_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Admission Type --}}
                            <div class="col-md-4">
                                <label class="form-label">Admission Type </label>
                                <select name="admission_type" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old('admission_type') === $type)>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('admission_type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="ipd_status" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($statuses as $st)
                                        <option value="{{ $st }}" @selected(old('status', $ipdPatient->status ?? 'Admitted') === $st)>
                                            {{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ipd_status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Patient History --}}
                            <div class="col-md-12">
                                <label class="form-label">Patient History</label>
                                <textarea type="text" name="patient_history"
                                    value="{{ old('patient_history', $ipdPatient->patient_history ?? '') }}" class="form-control"
                                    placeholder="Short history / Reason for admission" cols="4"></textarea>
                                @error('patient_history')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea type="text" name="remarks" value="{{ old('remarks', $ipdPatient->remarks ?? '') }}"
                                    class="form-control" placeholder="Notes / Initial Diagnosis" cols="4"></textarea>
                                @error('remarks')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    {{-- ===== BED / ICU ALLOCATION ===== --}}
    <div class="col-12 mt-2">
        <div class="accordion" id="bedAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="bedHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#bedCollapse" aria-expanded="true"
                        aria-controls="bedCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Bed / ICU Allocation</span>
                            </div>
                            <span class="badge text-bg-light border">Step 2</span>
                        </div>
                    </button>
                </h2>

                <div id="bedCollapse" class="accordion-collapse collapse show" aria-labelledby="bedHeading"
                    data-bs-parent="#bedAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">

                            {{-- Allocation Type Toggle --}}
                            <div class="col-12">
                                <div class="btn-group" role="group" aria-label="Allocation type">
                                    <input type="radio" class="btn-check" name="allocation_choice" id="alloc_bed"
                                        value="bed" autocomplete="off"
                                        {{ old('allocation_choice', 'bed') === 'bed' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alloc_bed">
                                        <i class="bi bi-hospital"></i> Regular Bed
                                    </label>

                                    <input type="radio" class="btn-check" name="allocation_choice" id="alloc_icu"
                                        value="icu" autocomplete="off"
                                        {{ old('allocation_choice') === 'icu' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="alloc_icu">
                                        <i class="bi bi-heart-pulse"></i> ICU / Critical Care
                                    </label>
                                </div>
                                <div class="form-text">Choose where to admit the patient.</div>
                            </div>

                            {{-- Regular Bed --}}
                            <div class="col-md-6 alloc-bed-field">
                                <label class="form-label">Bed <span class="text-danger">*</span></label>
                                <select name="bed_id" class="form-select select2"
                                    data-placeholder="-- Select Bed --" style="width:100%">
                                    <option value=""></option>
                                    @foreach ($beds as $b)
                                        <option value="{{ $b->id }}" @selected(old('bed_id') == $b->id)>
                                            {{ $b->name }}
                                            @if (optional($b->bedType)->name)
                                                [{{ $b->bedType->name }}]
                                            @endif
                                            (৳ {{ $b->rent }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('bed_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ICU Bed --}}
                            <div class="col-md-6 alloc-icu-field" style="display:none;">
                                <label class="form-label">ICU Bed <span class="text-danger">*</span></label>
                                <select name="icu_bed_id" class="form-select select2"
                                    data-placeholder="-- Select ICU Bed --" style="width:100%">
                                    <option value=""></option>
                                    @forelse ($icuBeds ?? [] as $b)
                                        <option value="{{ $b->id }}" @selected(old('icu_bed_id') == $b->id)>
                                            {{ $b->name }}
                                            @if (optional($b->bedType)->name)
                                                [{{ $b->bedType->name }}]
                                            @endif
                                            (৳ {{ $b->rent }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No ICU beds available</option>
                                    @endforelse
                                </select>
                                @error('icu_bed_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Configure ICU bed types in Master Data → Bed Types (toggle <em>Is ICU</em>).
                                </div>
                            </div>

                            {{-- From --}}
                            <div class="col-md-6">
                                <label class="form-label">From <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="from"
                                    value="{{ old('from', isset($ipdBed->from) ? \Carbon\Carbon::parse($ipdBed->from)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                    class="form-control" required>
                                @error('from')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Bed Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="bed_remarks" class="form-control" placeholder="Shifting reason / bed note"
                                    cols="3">{{ old('bed_remarks', $ipdBed->remarks ?? '') }}</textarea>
                                @error('bed_remarks')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PACKAGE SELECTION (optional) ===== --}}
    @php
        $admissionPackages = \App\Models\Package::with(['priceRules', 'bedType'])
            ->where('is_active', true)
            ->whereIn('package_type', ['IPD', 'OT', 'ICU', 'CCU', 'NICU', 'MATERNITY'])
            ->orderBy('package_type')->orderBy('name')->get();
    @endphp
    <div class="col-12 mt-2">
        <div class="accordion" id="packageAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="packageHeading">
                    <button class="accordion-button collapsed text-white" style="background-color:#1e7e34;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#packageCollapse" aria-expanded="false">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold"><i class="bi bi-box-seam"></i> Package Enrolment <small class="opacity-75">(optional)</small></span>
                            </div>
                            <span class="badge text-bg-light border">Step 2b</span>
                        </div>
                    </button>
                </h2>
                <div id="packageCollapse" class="accordion-collapse collapse" data-bs-parent="#packageAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Optional:</strong> Pick a hospital package now → its services will be pre-charged (cabin / OT / ICU / NICU / Maternity bundles).
                            Otherwise leave blank — charges will be posted line-by-line as treatment progresses.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Package</label>
                                <select name="enroll_package_id" id="enrollPackageId" class="form-select select2" data-placeholder="-- No Package --" style="width:100%">
                                    <option value="">-- No Package --</option>
                                    @foreach ($admissionPackages->groupBy('package_type') as $type => $items)
                                        <optgroup label="{{ $type }}">
                                            @foreach ($items as $pkg)
                                                <option value="{{ $pkg->id }}"
                                                    data-amount="{{ $pkg->total_amount }}"
                                                    data-bed-type="{{ $pkg->bed_type_id }}"
                                                    data-discount="{{ $pkg->discount }}"
                                                    @selected(old('enroll_package_id') == $pkg->id)>
                                                    {{ $pkg->name }} — ৳{{ number_format($pkg->total_amount, 0) }}
                                                    @if ($pkg->discount > 0) ({{ $pkg->discount }}% off) @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small class="text-muted">Filtered to IPD/OT/ICU/CCU/NICU/MATERNITY packages only. <a href="{{ route('packages.index') }}" target="_blank">Manage packages</a></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Stay Duration (days)</label>
                                <input type="number" name="enroll_duration_days" id="enrollDuration" class="form-control" min="1" value="{{ old('enroll_duration_days') }}" placeholder="(auto from package validity)">
                            </div>

                            <div id="pkgPreview" class="col-12" style="display:none;">
                                <div class="card border-success">
                                    <div class="card-body py-2 px-3 d-flex flex-wrap gap-3 align-items-center">
                                        <div><small class="text-muted">Package Price</small><br><strong class="text-success">৳ <span id="pkgPrice">0</span></strong></div>
                                        <div><small class="text-muted">Discount</small><br><strong>– <span id="pkgDiscount">0</span>%</strong></div>
                                        <div class="ms-auto">
                                            <small class="text-muted d-block">After admission, the package will auto-post its services to the patient bill.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sel = document.getElementById('enrollPackageId');
                const preview = document.getElementById('pkgPreview');
                const priceEl = document.getElementById('pkgPrice');
                const discEl  = document.getElementById('pkgDiscount');
                if (sel) {
                    const sync = () => {
                        const o = sel.options[sel.selectedIndex];
                        if (!o || !o.value) { preview.style.display = 'none'; return; }
                        priceEl.textContent = Number(o.dataset.amount || 0).toLocaleString();
                        discEl.textContent  = Number(o.dataset.discount || 0);
                        preview.style.display = '';
                    };
                    sel.addEventListener('change', sync);
                    sync();
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bedRadio = document.getElementById('alloc_bed');
                const icuRadio = document.getElementById('alloc_icu');
                const bedField = document.querySelector('.alloc-bed-field');
                const icuField = document.querySelector('.alloc-icu-field');
                const bedSelect = bedField ? bedField.querySelector('select[name="bed_id"]') : null;
                const icuSelect = icuField ? icuField.querySelector('select[name="icu_bed_id"]') : null;

                function syncAllocation() {
                    const isIcu = icuRadio && icuRadio.checked;
                    if (bedField) bedField.style.display = isIcu ? 'none' : '';
                    if (icuField) icuField.style.display = isIcu ? '' : 'none';
                    if (bedSelect) {
                        if (isIcu) bedSelect.removeAttribute('required');
                        else bedSelect.setAttribute('required', 'required');
                    }
                    if (icuSelect) {
                        if (isIcu) icuSelect.setAttribute('required', 'required');
                        else icuSelect.removeAttribute('required');
                    }
                }
                if (bedRadio) bedRadio.addEventListener('change', syncAllocation);
                if (icuRadio) icuRadio.addEventListener('change', syncAllocation);
                syncAllocation();
            });
        </script>
    @endpush

    <hr>

    {{-- ===== Patient Documents ===== --}}
    <div class="col-12 mt-2">
        <div class="accordion" id="documentsAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="documentsHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#documentsCollapse" aria-expanded="true"
                        aria-controls="documentsCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Patient Documents</span>
                            </div>
                            <span class="badge text-bg-light border">Step 3</span>
                        </div>
                    </button>
                </h2>

                <div id="documentsCollapse" class="accordion-collapse collapse show"
                    aria-labelledby="documentsHeading" data-bs-parent="#documentsAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-2" id="documentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Title</th>
                                        <th style="width: 30%;">File</th>
                                        <th>Remarks</th>
                                        <th style="width: 60px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="documentsTbody">
                                    <tr class="document-row">
                                        <td>
                                            <input type="text" name="documents[0][title]" class="form-control"
                                                placeholder="e.g. Lab Report">
                                        </td>
                                        <td>
                                            <input type="file" name="documents[0][file]" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" name="documents[0][remarks]" class="form-control"
                                                placeholder="Optional notes">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-document"
                                                title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addDocumentBtn">
                            <i class="bi bi-plus-lg"></i> Add Document
                        </button>
                        @error('documents')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('documents.*.file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tbody = document.getElementById('documentsTbody');
                const addBtn = document.getElementById('addDocumentBtn');
                let docIndex = 1;

                function rebuildIndexes() {
                    Array.from(tbody.querySelectorAll('tr.document-row')).forEach(function(row, i) {
                        row.querySelectorAll('input').forEach(function(input) {
                            const name = input.getAttribute('name');
                            if (!name) return;
                            input.setAttribute('name', name.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
                        });
                    });
                }

                addBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.className = 'document-row';
                    row.innerHTML = `
                        <td><input type="text" name="documents[${docIndex}][title]" class="form-control" placeholder="e.g. Lab Report"></td>
                        <td><input type="file" name="documents[${docIndex}][file]" class="form-control"></td>
                        <td><input type="text" name="documents[${docIndex}][remarks]" class="form-control" placeholder="Optional notes"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>`;
                    tbody.appendChild(row);
                    docIndex++;
                });

                tbody.addEventListener('click', function(e) {
                    const btn = e.target.closest('.remove-document');
                    if (!btn) return;
                    const rows = tbody.querySelectorAll('tr.document-row');
                    if (rows.length <= 1) {
                        btn.closest('tr').querySelectorAll('input').forEach(function(i) {
                            if (i.type === 'file') i.value = '';
                            else i.value = '';
                        });
                    } else {
                        btn.closest('tr').remove();
                        rebuildIndexes();
                    }
                });
            });
        </script>
    @endpush

    <hr>

    {{-- ===== Advance Payment ===== --}}
    <div class="col-12 mt-2">
        <div class="accordion" id="advanceAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="advanceHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d; color: white;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#advanceCollapse"
                        aria-expanded="true" aria-controls="advanceCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Advance Payment</span>
                            </div>
                            <span class="badge text-bg-light border">Step 4</span>
                        </div>
                    </button>
                </h2>

                <div id="advanceCollapse" class="accordion-collapse collapse show" aria-labelledby="advanceHeading"
                    data-bs-parent="#advanceAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">

                            {{-- Amount --}}
                            <div class="col-md-4">
                                <label class="form-label">Amount</label>
                                <input type="number" name="amount" value="{{ old('amount', 0) }}"
                                    class="form-control" min="0">

                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- VAT --}}
                            <div class="col-md-4">
                                <label class="form-label">VAT (%)</label>
                                <input type="number" name="vat" value="{{ old('vat') }}"
                                    class="form-control">
                                @error('vat')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- TAX --}}
                            <div class="col-md-4">
                                <label class="form-label">TAX (%)</label>
                                <input type="number" name="tax" value="{{ old('tax') }}"
                                    class="form-control">
                                @error('tax')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Via --}}
                            <div class="col-md-4">
                                <label class="form-label">Payment Via</label>
                                <select name="payment_via" id="payment_via" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="cash" @selected(old('payment_via') == 'cash')>Cash</option>
                                    <option value="card" @selected(old('payment_via') == 'card')>Card</option>
                                    <option value="cheque" @selected(old('payment_via') == 'cheque')>Cheque</option>
                                    <option value="mfs" @selected(old('payment_via') == 'mfs')>MFS</option>
                                    <option value="other" @selected(old('payment_via') == 'other')>Other</option>
                                </select>
                                @error('payment_via')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Payment Date --}}
                            <div class="col-md-4">
                                <label class="form-label">Payment Date</label>
                                <input type="datetime-local" name="payment_date"
                                    value="{{ old('payment_date', isset($ipdPatient->payment_date) ? \Carbon\Carbon::parse($ipdPatient->payment_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                    class="form-control">
                                @error('payment_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Received By --}}
                            <div class="col-md-4">
                                <label class="form-label">Received By</label>
                                <input type="text" name="received_by" value="{{ old('received_by') }}"
                                    class="form-control" placeholder="Cashier / Staff name">
                                @error('received_by')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ===== CARD FIELDS ===== --}}
                            <div class="col-12 d-none" id="cardFields">
                                <div class="border rounded-3 p-3 bg-light bg-light">
                                    <div class="fw-semibold mb-2">Card Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Card No</label>
                                            <input type="text" name="card_no" value="{{ old('card_no') }}"
                                                class="form-control" placeholder="**** **** **** 1234">
                                            @error('card_no')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Card Type</label>
                                            <select name="card_type" class="form-select">
                                                <option value="">-- Select --</option>
                                                <option value="visa" @selected(old('card_type') == 'visa')>Visa</option>
                                                <option value="master" @selected(old('card_type') == 'master')>Master</option>
                                                <option value="american_express" @selected(old('card_type') == 'american_express')>American
                                                    Express</option>
                                                <option value="other" @selected(old('card_type') == 'other')>Other</option>
                                            </select>
                                            @error('card_type')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== CHEQUE FIELDS ===== --}}
                            <div class="col-12 d-none" id="chequeFields">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="fw-semibold mb-2">Cheque Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Cheque Name</label>
                                            <input type="text" name="cheque_name"
                                                value="{{ old('cheque_name') }}" class="form-control"
                                                placeholder="Account holder name">
                                            @error('cheque_name')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Cheque No</label>
                                            <input type="text" name="cheque_no" value="{{ old('cheque_no') }}"
                                                class="form-control" placeholder="Cheque number">
                                            @error('cheque_no')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Cheque Date</label>
                                            <input type="date" name="cheque_date"
                                                value="{{ old('cheque_date') }}" class="form-control">
                                            @error('cheque_date')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MFS FIELDS ===== --}}
                            <div class="col-12 d-none" id="mfsFields">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="fw-semibold mb-2">MFS Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">MFS Type</label>
                                            <select name="mfs_type" class="form-select">
                                                <option value="">-- Select --</option>
                                                <option value="bkash" @selected(old('mfs_type') == 'bkash')>bKash</option>
                                                <option value="nagad" @selected(old('mfs_type') == 'nagad')>Nagad</option>
                                                <option value="rocket" @selected(old('mfs_type') == 'rocket')>Rocket</option>
                                                <option value="other" @selected(old('mfs_type') == 'other')>Other</option>
                                            </select>
                                            @error('mfs_type')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">MFS No</label>
                                            <input type="text" name="mfs_no" value="{{ old('mfs_no') }}"
                                                class="form-control" placeholder="01XXXXXXXXX">
                                            @error('mfs_no')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Transaction ID</label>
                                            <input type="text" name="mfs_transaction_id"
                                                value="{{ old('mfs_transaction_id') }}" class="form-control"
                                                placeholder="TXN123456789">
                                            @error('mfs_transaction_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-md-8">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any note for this advance payment">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="successed" @selected(old('payment_status', 'successed') == 'successed')>Successed</option>
                                    <option value="pending" @selected(old('payment_status') == 'pending')>Pending</option>
                                    <option value="failed" @selected(old('payment_status') == 'failed')>Failed</option>
                                    <option value="canceled" @selected(old('payment_status') == 'canceled')>Canceled</option>
                                </select>
                                @error('payment_status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Files --}}
                            <div class="col-md-12">
                                <label class="form-label">Files</label>
                                <input type="file" name="files[]" class="form-control" multiple>
                                <div class="form-text">Upload receipt / cheque scan / supporting documents.</div>
                                @error('files')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                @error('files.*')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<style>
    .rounded-box {
        background: #F4F4F4;
        border: 1px solid #a6a6a6;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .04);
    }

    #btnExisting.active,
    #btnNew.active {
        color: #fff !important;
    }
</style>
