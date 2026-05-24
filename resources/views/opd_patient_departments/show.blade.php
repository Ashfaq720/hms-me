@extends('backend.layouts.master')
@section('title', 'OPD Record Details')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">OPD Record Details</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('opd-patient-departments.edit', $opdPatientDepartment) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('opd-patient-departments.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p><strong>OPD No:</strong> {{ $opdPatientDepartment->opd_number }}</p>
            <p><strong>Patient:</strong> {{ $opdPatientDepartment->patient->patient_name ?? '-' }}</p>
            <p><strong>Doctor:</strong> {{ $opdPatientDepartment->doctor->name ?? '-' }}</p>
            <p><strong>Appointment:</strong> {{ optional($opdPatientDepartment->appointment_date)->format('d M Y, h:i A') }}</p>
            <p><strong>Case:</strong> {{ $opdPatientDepartment->case }}</p>
            <p><strong>Height:</strong> {{ $opdPatientDepartment->height }}</p>
            <p><strong>Weight:</strong> {{ $opdPatientDepartment->weight }}</p>
            <p><strong>BP:</strong> {{ $opdPatientDepartment->bp }}</p>
            <p><strong>Charge:</strong> {{ number_format((float)$opdPatientDepartment->standard_charge, 2) }}</p>
            <p><strong>Payment:</strong> {{ $opdPatientDepartment->payment_mode }}</p>
            <p><strong>Old Patient:</strong> {{ $opdPatientDepartment->is_old_patient ? 'Yes' : 'No' }}</p>
            <p><strong>Symptoms:</strong><br>{{ $opdPatientDepartment->symptoms }}</p>
            <p><strong>Notes:</strong><br>{{ $opdPatientDepartment->notes }}</p>
        </div>
    </div>
</div>
@endsection
