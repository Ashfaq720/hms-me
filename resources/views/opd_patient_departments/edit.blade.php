@extends('backend.layouts.master')
@section('title', 'Edit OPD Record')
@section('content')
<div class="container">
    <h3 class="mb-3">Edit OPD Record</h3>

    <form method="POST" action="{{ route('opd-patient-departments.update', $opdPatientDepartment) }}">
        @csrf
        @method('PUT')

        @include('opd_patient_departments._form', ['row' => $opdPatientDepartment])

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-warning">Update</button>
            <a href="{{ route('opd-patient-departments.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
@endsection
