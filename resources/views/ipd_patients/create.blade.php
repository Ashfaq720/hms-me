@extends('backend.layouts.master')
@section('title', 'Create Ipd Patient')
@section('content')
    <div class="container">
        <h3 class="mb-3">Create Ipd Patient</h3>

        <form method="POST" action="{{ route('ipd-patients.store') }}" enctype="multipart/form-data">
            @csrf
            @include('ipd_patients._form', ['patient' => null])

            <div class="mt-3 d-flex gap-2 justify-content-end">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('ipd-patients.index') }}" class="btn btn-light">Back</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    {{-- Simple JS: show/hide payment fields --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentVia = document.getElementById('payment_via');
            const cardFields = document.getElementById('cardFields');
            const chequeFields = document.getElementById('chequeFields');
            const mfsFields = document.getElementById('mfsFields');

            function toggleFields() {
                const v = (paymentVia.value || '').toLowerCase();

                cardFields.classList.add('d-none');
                chequeFields.classList.add('d-none');
                mfsFields.classList.add('d-none');

                if (v === 'card') cardFields.classList.remove('d-none');
                if (v === 'cheque') chequeFields.classList.remove('d-none');
                if (v === 'mfs') mfsFields.classList.remove('d-none');
                // cash/other => show none
            }

            paymentVia.addEventListener('change', toggleFields);
            toggleFields(); // run on load (keeps old() values visible)
        });
    </script>
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

                // hide patient info box
                $('#patientInfoBox').hide();

                // show new patient area
                $newBox.show();
                $newBox.find('input, select, textarea').prop('disabled', false);

                setActiveButton('new');
            }

            // default mode
            setExistingMode();

            // If a patient is preselected (e.g. from OPD "Move to Ipd"),
            // fetch and show their info immediately on page load.
            if ($patient.val()) {
                $patient.trigger('change');
            }

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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
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
                        $('#info_known_allergies').text(data.known_allergies || '—');
                        $('#info_insurance').text(data.insurance || '—');
                        $('#info_insurance_validity').text(data.insurance_validity || '—');
                        $infoBox.show();
                    }
                });
            });
        });
    </script>
@endpush
