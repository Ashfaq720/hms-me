@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add Prescription</h5>
        </div>

        <div class="card-body">

            {{-- Patient Info --}}
            <div class="mb-4">
                <h6 class="fw-bold">Patient Info</h6>
                <p>
                    <strong>Name:</strong> {{ $patient->patient->patient_name ?? '' }} <br>
                    <strong>Age:</strong> {{ $patient->patient->age ?? '' }} <br>
                    <strong>Gender:</strong> {{ $patient->patient->gender ?? '' }} <br>
                    <strong>Doctor:</strong> {{ $patient->doctor->name ?? '' }}
                </p>
            </div>

            <form action="{{ route('opd-patients.manual-prescription.store', $patient->id) }}" method="POST">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Finding</label>
                        <textarea name="finding" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Symptoms</label>
                        <textarea name="symptoms" class="form-control"></textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Medicine</label>
                        <textarea name="medicine" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Test</label>
                        <textarea name="test" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Advice</label>
                        <textarea name="advice" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Next Visit</label>
                        <input type="date" name="next_visit" class="form-control">
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        Save Prescription
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
