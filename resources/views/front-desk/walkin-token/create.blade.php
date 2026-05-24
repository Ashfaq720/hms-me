@php
    $genders = ['Male', 'Female', 'Other'];
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<form action="{{ route('front_desk.walkintoken.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">

        <div class="rounded-box mb-3">
            {{-- Mode Toggle --}}
            <div class="col-12 d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExisting">Existing Patient</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnNew">New Patient</button>
            </div>

            {{-- Existing Patient Dropdown --}}
            <div class="col-md-12 mt-2" id="existingBox">
                <label for="patient_id" class="form-label">Patient</label>
                <select name="patient_id" id="patient_id"
                    class="form-select select2 @error('patient_id') is-invalid @enderror"
                    style="width:100%"
                    data-placeholder="Search by name, MRN or mobile">
                    <option value=""></option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" data-name="{{ $patient->patient_name }}"
                            data-mobile="{{ $patient->mobileno }}"
                            data-dob="{{ optional($patient->dob)->format('Y-m-d') }}"
                            data-gender="{{ $patient->gender }}" data-blood="{{ $patient->blood_group }}"
                            @selected(old('patient_id') == $patient->id)>
                            {{ $patient->patient_name }} ({{ $patient->mrn }})
                        </option>
                    @endforeach
                </select>
                @error('patient_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- New Patient Fields --}}
            <div id="newBox" class="row g-3 p-0 m-0">
                <input type="hidden" name="patient_mode" id="patient_mode" value="existing">
                <div class="col-md-6">
                    <label for="patient_name" class="form-label">Patient Name <span class="text-danger">*</span></label>
                    <input required type="text" name="patient_name" id="patient_name"
                        class="form-control @error('patient_name') is-invalid @enderror"
                        value="{{ old('patient_name') }}" placeholder="Enter patient name">
                    @error('patient_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="mobileno" class="form-label">Contact No <span class="text-danger">*</span></label>
                    <input required type="text" name="mobileno" id="mobileno"
                        class="form-control @error('mobileno') is-invalid @enderror" value="{{ old('mobileno') }}"
                        placeholder="Enter Contact No">
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
                            <option value="{{ $g }}" @selected(old('gender') === $g)>{{ $g }}
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
                            <option value="{{ $b }}" @selected(old('blood_group') === $b)>{{ $b }}
                            </option>
                        @endforeach
                    </select>
                    @error('blood_group')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Discount Type</label>
                    <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                        <option value="">--Select Discount Type--</option>
                        <option value="CORPORATE" {{ old('discount_type') == 'CORPORATE' ? 'selected' : '' }}>Corporate
                        </option>
                        <option value="INSURANCE" {{ old('discount_type') == 'INSURANCE' ? 'selected' : '' }}>Insurance
                        </option>
                        <option value="STUFF" {{ old('discount_type') == 'STUFF' ? 'selected' : '' }}>Stuff</option>
                        <option value="SELF" {{ old('discount_type') == 'SELF' ? 'selected' : '' }}>Self</option>
                    </select>
                    @error('discount_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Organization Name</label>
                    <input type="text" name="organization_name"
                        class="form-control @error('organization_name') is-invalid @enderror"
                        value="{{ old('organization_name') }}" placeholder="Enter organization name">
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
                        value="{{ old('organization_api_link') }}" placeholder="www.organization.com">
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


        {{-- Department --}}
        <div class="col-md-4">
            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
            <select name="department_id" id="department_id"
                class="form-select @error('department_id') is-invalid @enderror" required>
                <option value="" selected disabled>-- Select Department --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror"
                required>
                <option value="">-- Select Doctor --</option>
            </select>
            @error('doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="date">Date</label>
            <input type="datetime-local" name="date" id="date" class="form-control"
                value="{{ old('date', date('Y-m-d\TH:i')) }}">
            @error('date')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-12">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" rows="3" id="remarks" class="form-control" placeholder="Enter remarks here">{{ old('remarks') }}</textarea>
        </div>



        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<style>
    .rounded-box {
        background: #eef1f3;
        border: 1px solid #555656;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .04);
    }

    #btnExisting.active,
    #btnNew.active {
        color: #fff !important;
    }
</style>
<script>
    $(function() {
        const $patient = $('#patient_id');
        const $existingBox = $('#existingBox');
        const $newBox = $('#newBox');

        const $btnExisting = $('#btnExisting');
        const $btnNew = $('#btnNew');

        function setActiveButton(mode) {
            if (mode === 'existing') {
                $btnExisting
                    .removeClass('btn-outline-primary btn-outline-secondary')
                    .addClass('btn-primary active');

                $btnNew
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
            } else {
                $btnNew
                    .removeClass('btn-outline-secondary btn-outline-primary')
                    .addClass('btn-primary active');

                $btnExisting
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-primary');
            }
        }

        function setExistingMode() {
            // show existing patient dropdown
            $existingBox.show();
            $patient.prop('disabled', false);

            // hide new patient area
            $newBox.hide();
            $newBox.find('input, select, textarea').prop('disabled', true);

            setActiveButton('existing');
        }

        function setNewMode() {
            // show existing box too if you want toggle buttons always visible
            $existingBox.show();

            // clear and disable existing patient dropdown
            $patient.val('').prop('disabled', true);

            // show new patient area
            $newBox.show();
            $newBox.find('input, select, textarea').prop('disabled', false);

            setActiveButton('new');
        }

        // default mode
        setExistingMode();

        // button click
        $btnExisting.on('click', function() {
            setExistingMode();
        });

        $btnNew.on('click', function() {
            setNewMode();
        });

        // if existing patient selected, optionally fill new fields
        $patient.on('change', function() {
            const id = $(this).val();

            if (!id) return;

            const $opt = $(this).find('option:selected');

            $('#patient_name').val($opt.data('name') || '');
            $('#mobileno').val($opt.data('mobile') || '');
            $('#dob').val($opt.data('dob') || '');
            $('#gender').val($opt.data('gender') || '');
            $('#blood_group').val($opt.data('blood') || '');
        });
    });

    // Department -> Doctors dynamic dropdown
    $(document).ready(function() {
        $('#department_id').on('change', function() {
            let departmentId = $(this).val();
            let doctorSelect = $('#doctor_id');

            doctorSelect.html('<option value="">Loading...</option>');

            if (departmentId) {
                $.ajax({
                    url: "{{ url('front_desk/get-doctors-by-department') }}/" + departmentId,
                    type: 'GET',
                    success: function(data) {
                        doctorSelect.html('<option value="">-- Select Doctor --</option>');

                        $.each(data, function(key, doctor) {
                            doctorSelect.append(
                                `<option value="${doctor.id}">${doctor.name}</option>`
                            );
                        });
                    },
                    error: function() {
                        doctorSelect.html(
                            '<option value="">-- No Doctor Found --</option>');
                    }
                });
            } else {
                doctorSelect.html('<option value="">-- Select Doctor --</option>');
            }
        });
    });
</script>
