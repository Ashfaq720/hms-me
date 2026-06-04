@extends('backend.layouts.master')
@section('title', 'Create OPD Record')
@section('content')
<div class="container">
    <h3 class="mb-3">Create OPD Record</h3>

    <form method="POST" action="{{ route('opd-patient-departments.store') }}">
        @csrf
        @include('opd_patient_departments._form', ['row' => null])
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('opd-patient-departments.index') }}" class="btn btn-light">Back</a>
        </div>
    </form>
</div>
@endsection
