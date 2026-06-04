@extends('backend.layouts.master')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Patient Details</h3>
            <div class="d-flex gap-2">
                <a class="btn btn-warning" href="{{ route('patients.edit', $patient) }}">Edit</a>
                <a class="btn btn-light" href="{{ route('patients.index') }}">Back</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        @if ($patient->image)
                            <img src="{{ asset('storage/' . $patient->image) }}" class="img-fluid rounded">
                        @else
                            <div class="text-muted">No image</div>
                        @endif
                    </div>

                    <div class="col-md-9">
                        <h2 class="mb-4">{{ $patient->patient_name }}</h2>
                        <div class="row">
                            <div class="col-md-6"><strong>Mobile:</strong> {{ $patient->mobileno ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Email:</strong> {{ $patient->email ?? ' ' }}</div>
                            <div class="col-md-6"><strong>DOB:</strong> {{ $patient->dob?->format('Y-m-d') ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Gender:</strong> {{ $patient->gender ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Marital Status:</strong> {{ $patient->marital_status ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Blood Group:</strong> {{ $patient->blood_group ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Patient Type:</strong> {{ $patient->patient_type ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Identification:</strong>
                                {{ $patient->identification_number ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Insurance:</strong> {{ $patient->insurance_id ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Insurance Validity:</strong>
                                {{ $patient->insurance_validity?->format('Y-m-d') ?? ' ' }}</div>

                            <div class="col-md-6"><strong>Address:</strong> {{ $patient->address ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Guardian:</strong> {{ $patient->guardian_name ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Allergies:</strong> {{ $patient->known_allergies ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Note:</strong> {{ $patient->note ?? ' ' }}</div>

                            <div class="col-md-12 mt-3 d-flex gap-2">
                                <span class="badge bg-info">Ipd: {{ $patient->is_ipd ? 'Yes' : 'No' }}</span>
                                <span class="badge bg-danger">Dead: {{ $patient->is_dead ? 'Yes' : 'No' }}</span>
                                <span class="badge bg-success">Active: {{ $patient->is_active ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <form method="POST" action="{{ route('patients.destroy', $patient) }}"
                    onsubmit="return confirm('Delete this patient?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
