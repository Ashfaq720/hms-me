@extends('backend.layouts.master')
@section('title', 'Edit Ipd Patient')
@section('content')
    <div class="container">
        <h3 class="mb-3">Edit Ipd Patient</h3>

        <form method="POST" action="{{ route('ipd-patients.update', $ipdPatient->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('ipd_patients._editform', ['patient' => null])

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
    </script>
@endpush
