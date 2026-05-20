@extends('backend.layouts.master')

@section('content')
    <div class="container-fluid py-4 opd-create-page">
        <div class="page-head d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Create OPD Patient</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('opd-patients.index') }}">OPD Patients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('opd-patients.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <form method="POST" action="{{ route('opd-patients.store') }}" enctype="multipart/form-data">
            @csrf
            @include('opd_patients._form', [
                'opd' => null,
                'patients' => $patients,
                'yesno_condition' => $yesno_condition,
            ])

            <div class="form-actions d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('opd-patients.index') }}" class="btn btn-light px-4">Cancel</a>
                <button type="reset" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check2-circle"></i> Save Patient
                </button>
            </div>
        </form>
    </div>

    <style>
        .opd-create-page .page-head h3 { color: #2b335d; }
        .opd-create-page .breadcrumb-item a { color: #6b7390; text-decoration: none; }
        .opd-create-page .breadcrumb-item.active { color: #2b335d; }
        .opd-create-page .form-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 14px 20px;
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            box-shadow: 0 -4px 12px rgba(43, 51, 93, .05);
            z-index: 10;
        }
        .opd-create-page .form-actions .btn-primary {
            background: #2b335d;
            border-color: #2b335d;
        }
        .opd-create-page .form-actions .btn-primary:hover {
            background: #1f254a;
            border-color: #1f254a;
        }
    </style>
@endsection

@push('scripts')
    {{-- ipd script  --}}
    <script>
        $(function() {
            const $patient = $('#patient_id');
            const $existingBox = $('#existingBox');
            const $newBox = $('#newBox');

            const $btnExisting = $('#btnExisting');
            const $btnNew      = $('#btnNew');
            const $btnCard     = $('#btnCard');
            const $cardBox     = $('#cardBox');

            function setActiveButton(mode) {
                // reset all three
                $btnExisting.removeClass('btn-primary btn-secondary active').addClass('btn-outline-primary');
                $btnNew.removeClass('btn-primary active').addClass('btn-outline-primary');
                $btnCard.removeClass('btn-success active').addClass('btn-outline-success');
                $cardBox.hide();

                if (mode === 'existing') {
                    $btnExisting.removeClass('btn-outline-primary').addClass('btn-primary active');
                } else if (mode === 'card') {
                    $btnCard.removeClass('btn-outline-success').addClass('btn-success active');
                    $cardBox.show();
                } else {
                    $btnNew.removeClass('btn-outline-primary').addClass('btn-primary active');
                }
            }

            // Health card lookup for OPD form
            $('#hcSearchBtnOpd').on('click', function () { doHcLookup(); });
            $('#hc_input_opd').on('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); doHcLookup(); }
            });

            function doHcLookup() {
                const cardNo = $('#hc_input_opd').val().trim().toUpperCase();
                if (!cardNo) return;
                $('#hcResultOpd').html('<span class="text-muted small">Searching…</span>');

                $.get('{{ route('health-card.find') }}', { card_no: cardNo })
                    .done(function (data) {
                        // inject into existing patient dropdown and select
                        if (!$('#patient_id option[value="' + data.id + '"]').length) {
                            $('#patient_id').append(new Option(data.patient_name + ' (' + data.mrn + ')', data.id));
                        }
                        $('#patient_id').val(data.id).trigger('change');
                        $('#patient_mode').val('existing');

                        $('#hcResultOpd').html(
                            '<div class="alert alert-success py-2 px-3 small mb-0">' +
                            '<strong>' + data.patient_name + '</strong>' +
                            ' &nbsp;|&nbsp; ' + data.mrn +
                            ' &nbsp;|&nbsp; ' + (data.gender ?? '') +
                            ' &nbsp;|&nbsp; Blood: ' + (data.blood_group ?? '—') +
                            (data.known_allergies ? '<br><span class="text-danger">⚠ Allergy: ' + data.known_allergies + '</span>' : '') +
                            '</div>'
                        );
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON?.error ?? 'Lookup failed.';
                        $('#hcResultOpd').html('<div class="alert alert-danger py-1 px-2 small">' + msg + '</div>');
                    });
            }

            $btnCard.on('click', function () { setActiveButton('card'); });

            function setExistingMode() {
                // show existing patient dropdown
                $existingBox.show();
                $patient.prop('disabled', false);

                // hide new patient area
                $newBox.hide();
                $newBox.find('input, select, textarea').prop('disabled', true);

                // show patient info if a patient is already selected
                if ($patient.val()) {
                    $patient.trigger('change');
                }

                setActiveButton('existing');
            }

            function setNewMode() {
                // show existing box too if you want toggle buttons always visible
                $existingBox.show();

                // clear and disable existing patient dropdown
                $patient.val('').prop('disabled', true);
                if ($patient.hasClass('select2-hidden-accessible')) {
                    $patient.trigger('change.select2');
                }

                // hide patient info box
                $('#patientInfoBox').hide();

                // show new patient area
                $newBox.show();
                $newBox.find('input, select, textarea').prop('disabled', false);

                // clear all new patient fields so user can enter manually
                $newBox.find('input[type="text"], input[type="date"], input[type="number"], input[type="file"], textarea').val('');
                $newBox.find('select').each(function() {
                    $(this).val('');
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).trigger('change.select2');
                    } else {
                        $(this).trigger('change');
                    }
                });

                // make sure fields are editable (not readonly from existing-mode)
                $newBox.find('input, textarea').prop('readonly', false);

                $('#patient_mode').val('new');
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

            // when existing patient selected, fetch info from DB
            $patient.on('change', function() {
                const id = $(this).val();
                const $infoBox = $('#patientInfoBox');

                if (!id) {
                    $infoBox.hide();
                    return;
                }

                $.ajax({
                    url: '/patients/' + id,
                    type: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(data) {
                        $('#info_name').text(data.patient_name || '—');
                        $('#info_mobile').text(data.mobileno || '—');
                        $('#info_age').text(data.age || '—');
                        $('#info_gender').text(data.gender || '—');
                        $('#info_blood').text(data.blood_group || '—');
                        $('#info_marital_status').text(data.marital_status || '—');
                        $('#info_organization_name').text(data.organization_name || '—');
                        $('#info_organization_id').text(data.organization_id || '—');
                        $('#info_organization_api_link').text(data.organization_api_link ||
                            '—');
                        $('#info_address').text(data.address || '—');
                        $('#info_identification_number').text(data.identification_number ||
                            '—');
                        $('#info_known_allergies').text(data.known_allergies || '—');
                        $('#info_insurance').text(data.insurance || '—');
                        $('#info_insurance_validity').text(data.insurance_validity || '—');
                        $infoBox.show();
                    }
                });
            });
        });
    </script>



    {{-- opd script  --}}
    <script>
        $(document).ready(function() {

            let paidAmountTouched = false;

            function resetPaymentFields() {
                $('.payment-extra').addClass('d-none');
                $('.payment-extra').find('input, select, textarea').prop('disabled', true);
            }

            function togglePaymentFields() {
                let paymentMode = $('#payment_mode').val();

                resetPaymentFields();

                if (paymentMode === 'cheque') {
                    $('#cheque_no_div, #cheque_date_div').removeClass('d-none');
                    $('#cheque_no, #cheque_date').prop('disabled', false);
                } else if (paymentMode === 'bank') {
                    $('#bank_name_div, #account_no_div, #transaction_id_div').removeClass('d-none');
                    $('#bank_name, #account_no, #transaction_id').prop('disabled', false);
                } else if (paymentMode === 'upi') {
                    $('#upi_id_div, #transaction_id_div').removeClass('d-none');
                    $('#upi_id, #transaction_id').prop('disabled', false);
                } else if (paymentMode === 'online') {
                    $('#transaction_id_div').removeClass('d-none');
                    $('#transaction_id').prop('disabled', false);
                } else if (paymentMode === 'other') {
                    $('#other_payment_div').removeClass('d-none');
                    $('#other_payment_details').prop('disabled', false);
                }
            }

            function calculateAmount() {
                let appliedCharge = parseFloat($('#applied_charge').val()) || 0;
                let discount = parseFloat($('#discount').val()) || 0;
                let tax = parseFloat($('#tax').val()) || 0;

                let discountedAmount = appliedCharge - discount;
                if (discountedAmount < 0) {
                    discountedAmount = 0;
                }

                let taxAmount = (discountedAmount * tax) / 100;
                let finalAmount = discountedAmount + taxAmount;

                $('#amount').val(finalAmount.toFixed(2));

                if (!paidAmountTouched || !$('#paid_amount').val()) {
                    $('#paid_amount').val(finalAmount.toFixed(2));
                }
            }

            function updatePatientMode() {
                let patientId = $('#patient_id').val();
                $('#patient_mode').val(patientId ? 'existing' : 'new');
            }

            function clearPatientFields() {
                $('#patient_name').val('');
                $('#mobileno').val('');
                $('#organization_name').val('');
                $('#organization_id').val('');
                $('#organization_api_link').val('');
                $('#discount_type').val('');
                $('#dob').val('');
                $('#gender').val('');
                $('#blood_group').val('');

                if ($('#discount_type').hasClass('select2-hidden-accessible')) {
                    $('#discount_type').trigger('change.select2');
                } else {
                    $('#discount_type').trigger('change');
                }

                if ($('#gender').hasClass('select2-hidden-accessible')) {
                    $('#gender').trigger('change.select2');
                } else {
                    $('#gender').trigger('change');
                }

                if ($('#blood_group').hasClass('select2-hidden-accessible')) {
                    $('#blood_group').trigger('change.select2');
                } else {
                    $('#blood_group').trigger('change');
                }
            }

            function setPatientFieldsReadonly(isExisting) {
                $('#patient_name').prop('readonly', isExisting);
                $('#mobileno').prop('readonly', isExisting);
                $('#organization_name').prop('readonly', isExisting);
                $('#organization_id').prop('readonly', isExisting);
                $('#organization_api_link').prop('readonly', isExisting);

                $('#discount_type').prop('disabled', isExisting);
                $('#gender').prop('disabled', isExisting);
                $('#blood_group').prop('disabled', isExisting);
                $('#dob').prop('readonly', isExisting);
            }

            function fillPatientFields(response) {
                $('#patient_name').val(response.patient_name ?? '');
                $('#mobileno').val(response.mobileno ?? '');
                $('#organization_name').val(response.organization_name ?? '');
                $('#organization_id').val(response.organization_id ?? '');
                $('#organization_api_link').val(response.organization_api_link ?? '');
                $('#discount_type').val(response.discount_type ?? '').trigger('change');
                $('#dob').val(response.dob ?? '');
                $('#gender').val(response.gender ?? '').trigger('change');
                $('#blood_group').val(response.blood_group ?? '').trigger('change');
            }

            function fetchPatientDetails(patientId) {
                if (!patientId) {
                    $('#patient_mode').val('new');
                    clearPatientFields();
                    setPatientFieldsReadonly(false);
                    return;
                }

                $.ajax({
                    url: "{{ route('front_desk.patients.search') }}",
                    type: "GET",
                    data: {
                        id: patientId
                    },
                    success: function(response) {
                        $('#patient_mode').val('existing');
                        fillPatientFields(response);
                        setPatientFieldsReadonly(true);
                    },
                    error: function(xhr) {
                        $('#patient_mode').val('new');
                        clearPatientFields();
                        setPatientFieldsReadonly(false);
                        console.log(xhr.responseText);
                        alert('Unable to fetch patient details.');
                    }
                });
            }

            function resetChargeFields() {
                $('#standard_charge').val('');
                $('#applied_charge').val('');
                $('#discount').val(0);
                $('#tax').val(0);
                $('#amount').val('');
                $('#paid_amount').val('');
                paidAmountTouched = false;
            }

            togglePaymentFields();
            calculateAmount();
            updatePatientMode();

            $('#payment_mode').on('change', function() {
                togglePaymentFields();
            });

            $('#patient_id').on('change', function() {
                let patientId = $(this).val();
                updatePatientMode();
                fetchPatientDetails(patientId);
            });

            $('#applied_charge, #discount, #tax').on('keyup change', function() {
                calculateAmount();
            });

            $('#paid_amount').on('input', function() {
                paidAmountTouched = true;
            });

            let selectedPatientId = $('#patient_id').val();
            if (selectedPatientId) {
                fetchPatientDetails(selectedPatientId);
            } else {
                setPatientFieldsReadonly(false);
            }

            $('form').on('submit', function() {
                $('#discount_type').prop('disabled', false);
                $('#gender').prop('disabled', false);
                $('#blood_group').prop('disabled', false);
            });

            // Store doctor fee data after lookup so visit-type change can re-apply it
            var _doctorFeeData = null;

            function applyDoctorFeeByVisitType() {
                if (!_doctorFeeData) return;

                var visitType = $('#visit_type').val();
                var fee = null;

                if (visitType === 'follow_up') {
                    fee = _doctorFeeData.follow_up_fee;
                } else if (visitType === 'new') {
                    fee = _doctorFeeData.first_visit_fee;
                } else {
                    fee = _doctorFeeData.opd_visit_fee;
                }

                // Fall back to any available fee
                if (fee === null) {
                    fee = _doctorFeeData.first_visit_fee ?? _doctorFeeData.opd_visit_fee ?? _doctorFeeData.follow_up_fee ?? 0;
                }

                var feeVal = fee !== null ? parseFloat(fee) : 0;
                $('#standard_charge').val(feeVal.toFixed(2));
                $('#applied_charge').val(feeVal.toFixed(2));
                calculateAmount();
            }

            function loadDoctorFee(doctorId) {
                $('#doctorFeeBox, #doctorFeeNone').hide();
                _doctorFeeData = null;

                if (!doctorId) {
                    resetChargeFields();
                    return;
                }

                $('#doctorFeeLoading').show();

                $.get('{{ route('opd-patients.get-doctor-opd-fee') }}', { doctor_id: doctorId })
                    .done(function (data) {
                        $('#doctorFeeLoading').hide();

                        var hasAny = data.opd_visit_fee !== null || data.first_visit_fee !== null || data.follow_up_fee !== null;

                        if (!hasAny) {
                            $('#doctorFeeNone').show();
                            resetChargeFields();
                            return;
                        }

                        _doctorFeeData = data;

                        var fmt = function(v) { return v !== null ? parseFloat(v).toFixed(2) : '—'; };
                        $('#fee_opd_visit').text(fmt(data.opd_visit_fee));
                        $('#fee_first_visit').text(fmt(data.first_visit_fee));
                        $('#fee_follow_up').text(fmt(data.follow_up_fee));
                        $('#fee_follow_up_window').text(data.follow_up_window !== null ? data.follow_up_window + ' days' : '—');
                        $('#doctorFeeBox').show();

                        applyDoctorFeeByVisitType();
                    })
                    .fail(function () {
                        $('#doctorFeeLoading').hide();
                    });
            }

            $('#consultant_doctor').on('change', function () {
                loadDoctorFee($(this).val());
            });

            $('#visit_type').on('change', function () {
                applyDoctorFeeByVisitType();
            });

            // Fee & shift init deferred — runs after department filtering below

            // Shift & Slot loading
            var $opdShift   = $('#opd_shift_id');
            var $opdSlot    = $('#opd_slot');
            var $opdDate    = $('#appointment_date');
            var _opdShiftXhr = null;
            var _opdSlotXhr  = null;

            function fmt12opd(t) {
                var parts = t.split(':');
                var h = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10);
                var ap = h >= 12 ? 'PM' : 'AM';
                var hh = ((h + 11) % 12 + 1);
                return String(hh).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ap;
            }

            function refreshOpdShifts() {
                var doctorId = $('#consultant_doctor').val();

                if (_opdShiftXhr) { _opdShiftXhr.abort(); _opdShiftXhr = null; }
                if (_opdSlotXhr)  { _opdSlotXhr.abort();  _opdSlotXhr  = null; }

                $opdShift.html('<option value="">Loading shifts…</option>');
                $opdSlot.html('<option value="">— Select Shift First —</option>');
                $('#opd_slot_time').val('');

                if (!doctorId) {
                    $opdShift.html('<option value="">— Select Doctor First —</option>');
                    return;
                }

                _opdShiftXhr = $.ajax({
                    url: "{{ route('appointments.get-doctor-shifts') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", doctor_id: doctorId },
                    dataType: 'json',
                    success: function(list) {
                        if (!list || !list.length) {
                            $opdShift.html('<option value="">No shifts configured for this doctor</option>');
                            return;
                        }
                        $opdShift.html('<option value="">— Select Shift —</option>');
                        $.each(list, function(i, s) {
                            $opdShift.append($('<option>', { value: s.id, text: s.name }));
                        });
                        var oldShift = "{{ old('shift_id') }}";
                        if (oldShift) {
                            $opdShift.val(oldShift);
                            refreshOpdSlots();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.statusText === 'abort') return;
                        $opdShift.html('<option value="">Failed to load shifts (' + xhr.status + ')</option>');
                    }
                });
            }

            function refreshOpdSlots() {
                var doctorId = $('#consultant_doctor').val();
                var shiftId  = $opdShift.val();
                var dateVal  = $opdDate.val();

                if (_opdSlotXhr) { _opdSlotXhr.abort(); _opdSlotXhr = null; }

                $opdSlot.html('<option value="">— Select Slot —</option>');
                $('#opd_slot_time').val('');

                if (!doctorId || !shiftId || !dateVal) return;

                _opdSlotXhr = $.ajax({
                    url: "{{ route('appointments.get-slots') }}",
                    type: 'POST',
                    data: {
                        _token:    "{{ csrf_token() }}",
                        doctor_id: doctorId,
                        shift_id:  shiftId,
                        date:      dateVal.substring(0, 10)
                    },
                    dataType: 'json',
                    success: function(list) {
                        if (!list || !list.length) {
                            $opdSlot.html('<option value="">No slots available for this day</option>');
                            return;
                        }
                        $opdSlot.html('<option value="">— Select Slot —</option>');
                        $.each(list, function(i, s) {
                            var val  = s.time_from + '|' + s.time_to;
                            var text = fmt12opd(s.time_from) + ' – ' + fmt12opd(s.time_to);
                            $opdSlot.append($('<option>', { value: val, text: text }));
                        });
                        var oldSlot = "{{ old('slot') }}";
                        if (oldSlot) {
                            $opdSlot.val(oldSlot);
                            $('#opd_slot_time').val(oldSlot.split('|')[0]);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.statusText === 'abort') return;
                        $opdSlot.html('<option value="">Failed to load slots (' + xhr.status + ')</option>');
                    }
                });
            }

            $('#consultant_doctor').on('change', refreshOpdShifts);
            $opdShift.on('change', refreshOpdSlots);
            $opdDate.on('change', refreshOpdSlots);

            $opdSlot.on('change', function () {
                var v = $opdSlot.val();
                $('#opd_slot_time').val(v && v.indexOf('|') !== -1 ? v.split('|')[0] : '');
            });

            // Shift init deferred — runs after department filtering below

            // Show referral source when visit type is referred or emergency
            function toggleReferralSource() {
                var type = $('#visit_type').val();
                if (type === 'referred' || type === 'emergency') {
                    $('#referralSourceBox').show();
                } else {
                    $('#referralSourceBox').hide();
                    $('#referral_source').val('');
                }
            }
            $('#visit_type').on('change', toggleReferralSource);
            toggleReferralSource();

            // Filter consultant doctor dropdown based on selected department
            var allDoctorOpts = $('#consultant_doctor').find('option[data-dept]').clone();

            function filterDoctorsByDept(deptId) {
                var $doc = $('#consultant_doctor');
                $doc.find('option[data-dept]').remove();
                var matches = [];
                allDoctorOpts.each(function () {
                    if (!deptId || String($(this).data('dept')) === String(deptId)) {
                        matches.push($(this).clone());
                    }
                });
                var placeholder = !deptId
                    ? '— Select Department First —'
                    : (matches.length ? 'Select doctor' : '— No doctors in this department —');
                $doc.find('option:first').text(placeholder);
                matches.forEach(function (o) { $doc.append(o); });
                if ($doc.hasClass('select2-hidden-accessible')) {
                    $doc.trigger('change.select2');
                }
            }

            $('#department_id').on('change', function () {
                var prevDoctor = $('#consultant_doctor').val();
                filterDoctorsByDept($(this).val());

                if (prevDoctor && $('#consultant_doctor option[value="' + prevDoctor + '"]').length) {
                    // Doctor still belongs to the new department — keep selection, refresh select2 UI
                    $('#consultant_doctor').val(prevDoctor);
                    if ($('#consultant_doctor').hasClass('select2-hidden-accessible')) {
                        $('#consultant_doctor').trigger('change.select2');
                    }
                } else {
                    // Doctor not in new department — clear it and reset shifts/slots/fee
                    $('#consultant_doctor').val('').trigger('change');
                }
            });

            // On page init: filter doctors by department first, then load fee & shifts
            (function () {
                var initDeptVal = $('#department_id').val();
                var initDocVal  = $('#consultant_doctor').val();

                if (initDeptVal) {
                    filterDoctorsByDept(initDeptVal);
                    // Restore doctor selection and refresh select2 display
                    if (initDocVal && $('#consultant_doctor option[value="' + initDocVal + '"]').length) {
                        $('#consultant_doctor').val(initDocVal);
                    } else {
                        initDocVal = null; // doctor not in this department — treated as cleared
                    }
                    if ($('#consultant_doctor').hasClass('select2-hidden-accessible')) {
                        $('#consultant_doctor').trigger('change.select2');
                    }
                }

                // Load fee and shifts using the resolved doctor (after filtering)
                var resolvedDoc = initDocVal || $('#consultant_doctor').val();
                if (resolvedDoc) {
                    loadDoctorFee(resolvedDoc);
                    refreshOpdShifts();
                }
            }());
        });
    </script>
@endpush
