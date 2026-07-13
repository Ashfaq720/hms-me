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

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const s = document.getElementById('opdEnrollPkg');
                    const pv = document.getElementById('opdPkgPreview');
                    const p  = document.getElementById('opdPkgPrice');
                    const d  = document.getElementById('opdPkgDisc');
                    if (!s) return;
                    const sync = () => {
                        const o = s.options[s.selectedIndex];
                        if (!o || !o.value) { pv.style.display = 'none'; return; }
                        p.textContent = Number(o.dataset.amount || 0).toLocaleString();
                        d.textContent = Number(o.dataset.discount || 0);
                        pv.style.display = '';
                    };
                    s.addEventListener('change', sync);
                    sync();
                });
            </script>
        @endpush

        <form method="POST" action="{{ route('opd-patients.store') }}" enctype="multipart/form-data">
            @csrf
            @include('opd_patients._form', [
                'opd'              => null,
                'patients'         => $patients,
                'yesno_condition'  => $yesno_condition,
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

    {{-- Patient mode & info lookup --}}
    <script>
        $(function () {
            const $patient      = $('#patient_id');
            const $existingBox  = $('#existingBox');
            const $newBox       = $('#newBox');
            const $btnExisting  = $('#btnExisting');
            const $btnNew       = $('#btnNew');
            const $btnCard      = $('#btnCard');
            const $cardBox      = $('#cardBox');

            // ---- collapse toggle label for "Show more details" ----
            $('#patientMoreInfo').on('show.bs.collapse', function () {
                $('#moreInfoChevron').removeClass('bi-chevron-down').addClass('bi-chevron-up');
                $('#moreInfoText').text('Hide details');
            }).on('hide.bs.collapse', function () {
                $('#moreInfoChevron').removeClass('bi-chevron-up').addClass('bi-chevron-down');
                $('#moreInfoText').text('Show more details');
            });

            // ---- patient mode button states ----
            function setActiveButton(mode) {
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

            // ---- health card lookup ----
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
                            (data.known_allergies ? '<br><span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Allergy: ' + data.known_allergies + '</span>' : '') +
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
                $existingBox.show();
                $patient.prop('disabled', false);
                $newBox.hide();
                $newBox.find('input, select, textarea').prop('disabled', true);
                if ($patient.val()) { $patient.trigger('change'); }
                setActiveButton('existing');
            }

            function setNewMode(clearFields) {
                $existingBox.show();
                $patient.val('').prop('disabled', true);
                if ($patient.hasClass('select2-hidden-accessible')) {
                    $patient.trigger('change.select2');
                }
                $('#patientInfoBox').hide();
                clearAllergyHighlight();
                $('#visitHistoryBox, #visitHistoryLoading').hide();
                $newBox.show();
                $newBox.find('input, select, textarea').prop('disabled', false);
                if (clearFields !== false) {
                    $newBox.find('input[type="text"], input[type="date"], input[type="number"], input[type="file"], textarea').val('');
                    $newBox.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).trigger('change.select2');
                        } else {
                            $(this).trigger('change');
                        }
                    });
                    $('#mobilenoFeedbackIcon').html('');
                    $('#mobilenoFeedbackMsg').html('');
                    $('#mobilenoPhoneWarning').hide();
                    $('#dobAgeDisplay').text('');
                }
                $newBox.find('input, textarea').prop('readonly', false);
                $('#patient_mode').val('new');
                setActiveButton('new');
            }

            function clearAllergyHighlight() {
                $('#allergyCell').removeClass('has-allergy');
                $('#patientAllergyBadge').addClass('d-none');
            }

            // default mode — restore from old submission if present
            var _initPatientMode = "{{ old('patient_mode', $restoredMode ?? 'existing') }}";
            if (_initPatientMode === 'new') {
                setNewMode(false); // false = don't clear old field values
            } else {
                setExistingMode();
            }
            $btnExisting.on('click', setExistingMode);
            $btnNew.on('click', function () { setNewMode(true); });

            // ---- when existing patient selected — fetch profile ----
            $patient.on('change', function () {
                const id        = $(this).val();
                const $infoBox  = $('#patientInfoBox');
                if (!id) {
                    $infoBox.hide();
                    clearAllergyHighlight();
                    return;
                }
                $.ajax({
                    url: '/patients/' + id,
                    type: 'GET',
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (data) {
                        $('#info_name').text(data.patient_name || '—');
                        $('#info_mobile').text(data.mobileno || '—');
                        $('#info_age').text(data.age || '—');
                        $('#info_gender').text(data.gender || '—');
                        $('#info_blood').text(data.blood_group || '—');
                        $('#info_marital_status').text(data.marital_status || '—');
                        $('#info_organization_name').text(data.organization_name || '—');
                        $('#info_organization_id').text(data.organization_id || '—');
                        $('#info_organization_api_link').text(data.organization_api_link || '—');
                        $('#info_address').text(data.address || '—');
                        $('#info_identification_number').text(data.identification_number || '—');
                        $('#info_insurance').text(data.insurance || '—');
                        $('#info_insurance_validity').text(data.insurance_validity || '—');

                        const allergy = data.known_allergies || '';
                        $('#info_known_allergies').text(allergy || '—');
                        if (allergy) {
                            $('#allergyCell').addClass('has-allergy');
                            $('#patientAllergyBadge').removeClass('d-none');
                        } else {
                            clearAllergyHighlight();
                        }
                        $infoBox.show();
                    }
                });
            });
        });
    </script>

    {{-- OPD form logic (fees, billing, shifts, slots, visit history) --}}
    <script>
        $(document).ready(function () {

            let paidAmountTouched = false;

            // ---- payment mode fields ----
            function resetPaymentFields() {
                $('.payment-extra').addClass('d-none');
                $('.payment-extra').find('input, select, textarea').prop('disabled', true);
            }

            function togglePaymentFields() {
                const mode = $('#payment_mode').val();
                resetPaymentFields();
                if (mode === 'cheque') {
                    $('#cheque_no_div, #cheque_date_div').removeClass('d-none');
                    $('#cheque_no, #cheque_date').prop('disabled', false);
                } else if (mode === 'bank') {
                    $('#bank_name_div, #account_no_div, #transaction_id_div').removeClass('d-none');
                    $('#bank_name, #account_no, #transaction_id').prop('disabled', false);
                } else if (mode === 'upi') {
                    $('#upi_id_div, #transaction_id_div').removeClass('d-none');
                    $('#upi_id, #transaction_id').prop('disabled', false);
                } else if (mode === 'online') {
                    $('#transaction_id_div').removeClass('d-none');
                    $('#transaction_id').prop('disabled', false);
                } else if (mode === 'other') {
                    $('#other_payment_div').removeClass('d-none');
                    $('#other_payment_details').prop('disabled', false);
                }
            }

            // ---- balance due ----
            function updateBalanceDue() {
                const total = parseFloat($('#amount').val()) || 0;
                const paid  = parseFloat($('#paid_amount').val()) || 0;
                const due   = total - paid;
                $('#balance_due').val(due.toFixed(2));

                const $icon  = $('#balanceDueIcon');
                const $field = $('#balance_due');
                $field.removeClass('balance-due balance-paid balance-credit');
                $icon.removeClass('due-icon-danger due-icon-success due-icon-warning');

                if (due > 0.005) {
                    $field.addClass('balance-due');
                    $icon.addClass('due-icon-danger');
                } else if (due < -0.005) {
                    $field.addClass('balance-credit');
                    $icon.addClass('due-icon-warning');
                } else {
                    $field.addClass('balance-paid');
                    $icon.addClass('due-icon-success');
                }
            }

            // ---- amount calculation ----
            function calculateAmount() {
                const appliedCharge  = parseFloat($('#applied_charge').val()) || 0;
                const discount       = parseFloat($('#discount').val()) || 0;
                const tax            = parseFloat($('#tax').val()) || 0;
                let   discounted     = appliedCharge - discount;
                if (discounted < 0) discounted = 0;
                const taxAmount  = (discounted * tax) / 100;
                const finalAmount = discounted + taxAmount;
                $('#amount').val(finalAmount.toFixed(2));
                if (!paidAmountTouched || !$('#paid_amount').val()) {
                    $('#paid_amount').val(finalAmount.toFixed(2));
                }
                updateBalanceDue();
            }

            function updatePatientMode() {
                $('#patient_mode').val($('#patient_id').val() ? 'existing' : 'new');
            }

            function clearPatientFields() {
                $('#patient_name, #mobileno, #organization_name, #organization_id, #organization_api_link, #dob').val('');
                $('#mobilenoFeedbackIcon').html('');
                $('#mobilenoFeedbackMsg').html('');
                $('#mobilenoPhoneWarning').hide();
                $('#dobAgeDisplay').text('');
                ['#discount_type', '#gender', '#blood_group'].forEach(function (sel) {
                    $(sel).val('');
                    if ($(sel).hasClass('select2-hidden-accessible')) {
                        $(sel).trigger('change.select2');
                    } else {
                        $(sel).trigger('change');
                    }
                });
            }

            function setPatientFieldsReadonly(isExisting) {
                $('#patient_name, #mobileno, #organization_name, #organization_id, #organization_api_link, #dob')
                    .prop('readonly', isExisting);
                $('#discount_type, #gender, #blood_group').prop('disabled', isExisting);
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
                    type: 'GET',
                    data: { id: patientId },
                    success: function (response) {
                        $('#patient_mode').val('existing');
                        fillPatientFields(response);
                        setPatientFieldsReadonly(true);
                    },
                    error: function (xhr) {
                        $('#patient_mode').val('new');
                        clearPatientFields();
                        setPatientFieldsReadonly(false);
                        console.error(xhr.responseText);
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
                $('#visitTypeFeeHint').text('');
                $('#feeTypeLabel').text('');
                paidAmountTouched = false;
                updateBalanceDue();
            }

            // ---- init ----
            togglePaymentFields();
            calculateAmount();
            updatePatientMode();

            // ---- payment mode change ----
            $('#payment_mode').on('change', togglePaymentFields);

            // ---- patient selection (form field fill) ----
            $('#patient_id').on('change', function () {
                updatePatientMode();
                fetchPatientDetails($(this).val());
            });

            // ---- charge recalculation ----
            $('#applied_charge, #discount, #tax').on('keyup change', calculateAmount);

            // ---- paid amount manual edit ----
            $('#paid_amount').on('input', function () {
                paidAmountTouched = true;
                updateBalanceDue();
            });

            // ---- init fill if patient pre-selected ----
            const _initPatient = $('#patient_id').val();
            if (_initPatient) {
                fetchPatientDetails(_initPatient);
            } else {
                setPatientFieldsReadonly(false);
            }

            // ---- re-enable selects on submit (they were disabled for readonly display) ----
            $('form').on('submit', function () {
                $('#discount_type, #gender, #blood_group').prop('disabled', false);
            });

            // ================================================================
            // Visit history
            // ================================================================
            var _visitHistoryXhr = null;

            function renderVisitHistory(data) {
                var html;
                if (!data.has_visited) {
                    html = '<div class="vh-card">' +
                        '<div class="vh-card__head">' +
                        '<i class="bi bi-person-plus-fill text-info fs-5"></i>' +
                        '<strong>First Visit to this Doctor</strong>' +
                        '<span class="badge bg-info text-dark">New Patient for Doctor</span>' +
                        '</div>' +
                        '<div class="vh-card__meta">' +
                        '<span>No previous OPD visits with this doctor on record.</span>' +
                        '</div>' +
                        '<div class="vh-card__hint text-info">' +
                        '<i class="bi bi-info-circle me-1"></i>' +
                        '<strong>First Visit fee</strong> will apply. Visit type set to <strong>New Visit</strong>.' +
                        '</div>' +
                        '</div>';
                    $('#visit_type').val('new').trigger('change');
                } else {
                    var windowText = data.follow_up_window ? data.follow_up_window + ' days' : 'Not configured';
                    var meta = '<div class="vh-card__meta">' +
                        '<span>Last visit: <strong>' + data.last_visit_date_fmt + '</strong></span>' +
                        '<span>Days since: <strong>' + data.days_since + ' days</strong></span>' +
                        '<span>Last type: <strong>' + data.last_visit_type_label + '</strong></span>' +
                        '<span>Total visits: <strong>' + data.total_visits + '</strong></span>' +
                        '<span>Follow-up window: <strong>' + windowText + '</strong></span>' +
                        '</div>';

                    if (data.within_follow_up_window) {
                        html = '<div class="vh-card vh-card--followup">' +
                            '<div class="vh-card__head">' +
                            '<i class="bi bi-check-circle-fill text-success fs-5"></i>' +
                            '<strong>Returning Patient</strong>' +
                            '<span class="badge bg-success">Within Follow-up Window</span>' +
                            '<span class="badge bg-success-subtle text-success border border-success-subtle">Follow-up Fee Applies</span>' +
                            '</div>' +
                            meta +
                            '<div class="vh-card__hint text-success">' +
                            '<i class="bi bi-arrow-right-circle me-1"></i>' +
                            'Visit type auto-set to <strong>Follow-up</strong>. Override using the Visit Type dropdown if needed.' +
                            '</div>' +
                            '</div>';
                        $('#visit_type').val('follow_up').trigger('change');
                    } else {
                        html = '<div class="vh-card vh-card--expired">' +
                            '<div class="vh-card__head">' +
                            '<i class="bi bi-exclamation-circle-fill text-warning fs-5"></i>' +
                            '<strong>Returning Patient</strong>' +
                            '<span class="badge bg-warning text-dark">Outside Follow-up Window</span>' +
                            '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">First Visit Fee Applies</span>' +
                            '</div>' +
                            meta +
                            '<div class="vh-card__hint text-warning">' +
                            '<i class="bi bi-arrow-right-circle me-1"></i>' +
                            'Follow-up window has passed — <strong>First Visit fee</strong> will apply. Override if needed.' +
                            '</div>' +
                            '</div>';
                        $('#visit_type').val('new').trigger('change');
                    }
                }
                $('#visitHistoryContent').html(html);
            }

            function loadVisitHistory() {
                const patientId  = $('#patient_id').val();
                const doctorId   = $('#consultant_doctor').val();
                const patientMode = $('#patient_mode').val();

                if (_visitHistoryXhr) { _visitHistoryXhr.abort(); _visitHistoryXhr = null; }
                $('#visitHistoryBox').hide();
                $('#visitHistoryLoading').hide();

                if (!patientId || !doctorId || patientMode !== 'existing') return;

                $('#visitHistoryLoading').show();
                _visitHistoryXhr = $.get('{{ route('opd-patients.patient-visit-history') }}', {
                    patient_id: patientId,
                    doctor_id: doctorId
                })
                .done(function (data) {
                    $('#visitHistoryLoading').hide();
                    renderVisitHistory(data);
                    $('#visitHistoryBox').show();
                })
                .fail(function (xhr) {
                    if (xhr.statusText !== 'abort') $('#visitHistoryLoading').hide();
                });
            }

            $('#patient_id').on('change', function () { loadVisitHistory(); });
            $('#consultant_doctor').on('change', function () { loadVisitHistory(); });

            // ================================================================
            // Doctor fee
            // ================================================================
            var _doctorFeeData = null;

            function applyDoctorFeeByVisitType() {
                if (!_doctorFeeData) {
                    $('#visitTypeFeeHint').text('');
                    $('#feeTypeLabel').text('');
                    return;
                }
                const visitType = $('#visit_type').val();
                var fee = null, feeLabel = '';

                if (visitType === 'follow_up') {
                    fee = _doctorFeeData.follow_up_fee;
                    feeLabel = 'Follow-up';
                } else if (visitType === 'new') {
                    fee = _doctorFeeData.first_visit_fee;
                    feeLabel = 'First Visit';
                } else {
                    fee = _doctorFeeData.opd_visit_fee;
                    feeLabel = 'OPD Visit';
                }

                if (fee === null) {
                    fee = _doctorFeeData.first_visit_fee ?? _doctorFeeData.opd_visit_fee ?? _doctorFeeData.follow_up_fee ?? 0;
                }

                const feeVal = fee !== null ? parseFloat(fee) : 0;
                $('#standard_charge').val(feeVal.toFixed(2));
                $('#applied_charge').val(feeVal.toFixed(2));

                if (feeVal > 0) {
                    $('#visitTypeFeeHint').text('Fee: ৳' + feeVal.toFixed(2) + ' (' + feeLabel + ')');
                    $('#feeTypeLabel').text('(' + feeLabel + ')');
                } else {
                    $('#visitTypeFeeHint').text('');
                    $('#feeTypeLabel').text('');
                }
                calculateAmount();
            }

            function loadDoctorFee(doctorId) {
                $('#doctorFeeBox, #doctorFeeNone').hide();
                _doctorFeeData = null;
                if (!doctorId) { resetChargeFields(); return; }

                $('#doctorFeeLoading').show();
                $.get('{{ route('opd-patients.get-doctor-opd-fee') }}', { doctor_id: doctorId })
                    .done(function (data) {
                        $('#doctorFeeLoading').hide();
                        const hasAny = data.opd_visit_fee !== null || data.first_visit_fee !== null || data.follow_up_fee !== null;
                        if (!hasAny) { $('#doctorFeeNone').show(); resetChargeFields(); return; }

                        _doctorFeeData = data;
                        const fmt = function (v) { return v !== null ? parseFloat(v).toFixed(2) : '—'; };
                        $('#fee_opd_visit').text(fmt(data.opd_visit_fee));
                        $('#fee_first_visit').text(fmt(data.first_visit_fee));
                        $('#fee_follow_up').text(fmt(data.follow_up_fee));
                        $('#fee_follow_up_window').text(data.follow_up_window !== null ? data.follow_up_window + ' days' : '—');
                        $('#doctorFeeBox').show();
                        applyDoctorFeeByVisitType();
                    })
                    .fail(function () { $('#doctorFeeLoading').hide(); });
            }

            $('#consultant_doctor').on('change', function () { loadDoctorFee($(this).val()); });
            $('#visit_type').on('change', function () {
                applyDoctorFeeByVisitType();
                toggleReferralSource();
            });

            // ================================================================
            // Shift & Slot
            // ================================================================
            var $opdShift    = $('#opd_shift_id');
            var $opdSlot     = $('#opd_slot');
            var $opdDate     = $('#appointment_date');
            var _opdShiftXhr = null;
            var _opdSlotXhr  = null;

            function fmt12opd(t) {
                var parts = t.split(':');
                var h  = parseInt(parts[0], 10);
                var m  = parseInt(parts[1], 10);
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
                if (!doctorId) { $opdShift.html('<option value="">— Select Doctor First —</option>'); return; }

                _opdShiftXhr = $.ajax({
                    url: "{{ route('appointments.get-doctor-shifts') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", doctor_id: doctorId },
                    dataType: 'json',
                    success: function (list) {
                        if (!list || !list.length) {
                            $opdShift.html('<option value="">No shifts configured for this doctor</option>');
                            return;
                        }
                        $opdShift.html('<option value="">— Select Shift —</option>');
                        $.each(list, function (i, s) {
                            $opdShift.append($('<option>', { value: s.id, text: s.name }));
                        });
                        var oldShift = "{{ old('shift_id') }}";
                        if (oldShift) { $opdShift.val(oldShift); refreshOpdSlots(); }
                    },
                    error: function (xhr) {
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
                    url: "{{ route('front_desk.opd.slots') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", doctor_id: doctorId, shift_id: shiftId, date: dateVal.substring(0, 10) },
                    dataType: 'json',
                    success: function (list) {
                        if (!list || !list.length) {
                            $opdSlot.html('<option value="">No slots available for this day</option>');
                            return;
                        }
                        $opdSlot.html('<option value="">— Select Slot —</option>');
                        $.each(list, function (i, s) {
                            var val    = s.time_from + '|' + s.time_to;
                            var label  = fmt12opd(s.time_from) + ' – ' + fmt12opd(s.time_to);
                            var booked = s.booked_count || 0;
                            var suffix = booked === 0 ? ' (available)' : ' (' + booked + ' booked)';
                            $opdSlot.append($('<option>', { value: val, text: label + suffix, 'data-booked': booked }));
                        });
                        var oldSlot = "{{ old('slot') }}";
                        if (oldSlot) { $opdSlot.val(oldSlot); $('#opd_slot_time').val(oldSlot.split('|')[0]); }
                        applyOpdSlotColor();
                    },
                    error: function (xhr) {
                        if (xhr.statusText === 'abort') return;
                        $opdSlot.html('<option value="">Failed to load slots (' + xhr.status + ')</option>');
                    }
                });
            }

            function applyOpdSlotColor() {
                var selected = $opdSlot.find('option:selected');
                var booked = parseInt(selected.data('booked') ?? -1);
                $opdSlot.removeClass('border-success border-warning border-danger');
                if (booked < 0 || !$opdSlot.val()) return;
                if (booked === 0)      $opdSlot.addClass('border-success');
                else if (booked <= 2)  $opdSlot.addClass('border-warning');
                else                   $opdSlot.addClass('border-danger');
            }

            $('#consultant_doctor').on('change', refreshOpdShifts);
            $opdShift.on('change', refreshOpdSlots);
            $opdDate.on('change', refreshOpdSlots);
            $opdSlot.on('change', function () {
                var v = $opdSlot.val();
                $('#opd_slot_time').val(v && v.indexOf('|') !== -1 ? v.split('|')[0] : '');
                applyOpdSlotColor();
            });

            // ================================================================
            // Referral source visibility
            // ================================================================
            function toggleReferralSource() {
                var type = $('#visit_type').val();
                if (type === 'referred' || type === 'emergency') {
                    $('#referralSourceBox').show();
                } else {
                    $('#referralSourceBox').hide();
                    $('#referral_source').val('');
                }
            }
            toggleReferralSource();

            // ================================================================
            // Department → Doctor filtering
            // ================================================================
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
                if ($doc.hasClass('select2-hidden-accessible')) { $doc.trigger('change.select2'); }
            }

            $('#department_id').on('change', function () {
                var prevDoctor = $('#consultant_doctor').val();
                filterDoctorsByDept($(this).val());
                if (prevDoctor && $('#consultant_doctor option[value="' + prevDoctor + '"]').length) {
                    $('#consultant_doctor').val(prevDoctor);
                    if ($('#consultant_doctor').hasClass('select2-hidden-accessible')) {
                        $('#consultant_doctor').trigger('change.select2');
                    }
                } else {
                    $('#consultant_doctor').val('').trigger('change');
                }
            });

            // ================================================================
            // Contact No — format + real-time uniqueness check (new patient mode)
            // ================================================================
            (function () {
                var iconEl  = document.getElementById('mobilenoFeedbackIcon');
                var msgEl   = document.getElementById('mobilenoFeedbackMsg');
                var warnEl  = document.getElementById('mobilenoPhoneWarning');
                var warnName= document.getElementById('mobilenoWarningPatient');
                var input   = document.getElementById('mobileno');
                var _timer  = null;
                var _xhr    = null;

                function phoneSetState(state, msg) {
                    var icons = {
                        checking: '<span class="spinner-border spinner-border-sm text-secondary" style="width:.85rem;height:.85rem;"></span>',
                        ok:    '<i class="bi bi-check-circle-fill text-success"></i>',
                        warn:  '<i class="bi bi-exclamation-triangle-fill text-warning"></i>',
                        error: '<i class="bi bi-x-circle-fill text-danger"></i>',
                        '': ''
                    };
                    if (iconEl) iconEl.innerHTML = icons[state] || '';
                    if (msgEl)  msgEl.innerHTML  = msg || '';
                }

                function validatePhoneFormat(phone) {
                    if (!phone) { phoneSetState('', ''); return false; }
                    var digits = phone.replace(/[\s\-\(\)\+]/g, '');
                    if (!/^[\d\+\-\(\)\s]+$/.test(phone)) {
                        phoneSetState('error', '<span class="text-danger">Only digits, +, -, () are allowed.</span>');
                        return false;
                    }
                    if (digits.length < 7) {
                        phoneSetState('error', '<span class="text-danger">Too short — minimum 7 digits.</span>');
                        return false;
                    }
                    if (digits.length > 15) {
                        phoneSetState('error', '<span class="text-danger">Too long — maximum 15 digits.</span>');
                        return false;
                    }
                    return true;
                }

                function checkPhoneUniqueness(phone) {
                    phoneSetState('checking', '');
                    if (warnEl) warnEl.style.display = 'none';
                    if (_xhr) { _xhr.abort(); _xhr = null; }
                    _xhr = $.ajax({
                        url: "{{ route('front_desk.check.phone') }}",
                        type: 'GET',
                        data: { phone: phone },
                        dataType: 'json',
                        success: function (res) {
                            _xhr = null;
                            if (res.exists) {
                                phoneSetState('warn', '');
                                if (warnName)  warnName.textContent = res.patient_name + '  ·  MRN: ' + (res.mrn ?? '—');
                                if (warnEl)    warnEl.style.display = 'flex';
                            } else {
                                phoneSetState('ok', '<span class="text-success">Phone number is available.</span>');
                            }
                        },
                        error: function (xhr) {
                            if (xhr.statusText === 'abort') return;
                            _xhr = null;
                            phoneSetState('', '');
                        }
                    });
                }

                if (input) {
                    input.addEventListener('input', function () {
                        if ($('#patient_mode').val() !== 'new') return;
                        var phone = this.value.trim();
                        if (warnEl) warnEl.style.display = 'none';
                        clearTimeout(_timer);
                        if (!validatePhoneFormat(phone)) return;
                        _timer = setTimeout(function () { checkPhoneUniqueness(phone); }, 600);
                    });
                }
            }());

            // ================================================================
            // Active token check
            // ================================================================
            var _activeTokenXhr = null;

            function checkActiveToken() {
                var patientId = $('#patient_id').val();
                var deptId    = $('#department_id').val();
                var dateVal   = $opdDate.val();
                var mode      = $('#patient_mode').val();

                $('#activeTokenWarningOpd').hide();

                if (!patientId || !deptId || !dateVal || mode !== 'existing') return;
                if (_activeTokenXhr) { _activeTokenXhr.abort(); _activeTokenXhr = null; }

                _activeTokenXhr = $.get("{{ route('front_desk.check.active.token') }}", {
                    patient_id: patientId, department_id: deptId, date: dateVal
                }).done(function (data) {
                    if (data.has_active) {
                        var msg = 'This patient already has an active visit in this department today.';
                        if (data.token_no)  msg += ' Token: <strong>' + data.token_no + '</strong>.';
                        if (data.status)    msg += ' Status: <strong>' + data.status + '</strong>.';
                        if (data.doctor)    msg += ' Doctor: <strong>' + data.doctor + '</strong>.';
                        $('#activeTokenWarningOpdText').html(msg);
                        $('#activeTokenWarningOpd').show();
                    }
                });
            }

            $('#patient_id').on('change', checkActiveToken);
            $('#department_id').on('change', checkActiveToken);
            $opdDate.on('change', checkActiveToken);

            // ================================================================
            // DOB → age display (new patient mode)
            // ================================================================
            function calcAgeOpd(dobStr) {
                if (!dobStr) return '';
                var dob  = new Date(dobStr);
                var now  = new Date();
                var yrs  = now.getFullYear() - dob.getFullYear();
                var mos  = now.getMonth() - dob.getMonth();
                if (mos < 0 || (mos === 0 && now.getDate() < dob.getDate())) { yrs--; mos += 12; }
                if (yrs < 0) return '';
                if (yrs === 0) return mos + ' month' + (mos !== 1 ? 's' : '') + ' old';
                return yrs + ' yr' + (yrs !== 1 ? 's' : '') + (mos > 0 ? ' ' + mos + ' mo' : '') + ' old';
            }

            $('#dob').on('change', function () {
                var age = calcAgeOpd($(this).val());
                $('#dobAgeDisplay').text(age ? 'Age: ' + age : '');
            });

            // ================================================================
            // Page init — filter doctors, load fee, shifts, visit history
            // ================================================================
            (function () {
                var initDeptVal = $('#department_id').val();
                var initDocVal  = $('#consultant_doctor').val();

                if (initDeptVal) {
                    filterDoctorsByDept(initDeptVal);
                    if (initDocVal && $('#consultant_doctor option[value="' + initDocVal + '"]').length) {
                        $('#consultant_doctor').val(initDocVal);
                    } else {
                        initDocVal = null;
                    }
                    if ($('#consultant_doctor').hasClass('select2-hidden-accessible')) {
                        $('#consultant_doctor').trigger('change.select2');
                    }
                }

                var resolvedDoc = initDocVal || $('#consultant_doctor').val();
                if (resolvedDoc) {
                    loadDoctorFee(resolvedDoc);
                    refreshOpdShifts();
                    if ($('#patient_id').val()) {
                        loadVisitHistory();
                        checkActiveToken();
                    }
                }

                // Restore age display from old DOB value
                var initDob = $('#dob').val();
                if (initDob) {
                    var initAge = calcAgeOpd(initDob);
                    $('#dobAgeDisplay').text(initAge ? 'Age: ' + initAge : '');
                }
            }());

        });
    </script>
@endpush
