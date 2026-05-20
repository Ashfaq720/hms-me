@extends('backend.layouts.master')
@section('title', 'View Doctor')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Doctor Details</h3>
            <div class="d-flex gap-2">
                <a class="btn btn-warning" href="{{ route('doctors.edit', $doctor) }}">Edit</a>
                <a class="btn btn-light" href="{{ route('doctors.index') }}">Back</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        @if ($doctor->image)
                            <img src="{{ asset('storage/' . $doctor->image) }}" class="img-fluid rounded">
                        @else
                            <div class="text-muted">No image</div>
                        @endif
                    </div>

                    <div class="col-md-9">
                        <h2 class="mb-1">{{ $doctor->name }}</h2>
                        <div class="text-muted mb-3">
                            <strong>Code:</strong> {{ $doctor->doctor_code ?? ' ' }}
                            @if (!empty($doctor->designation?->name))
                                | <strong>Designation:</strong> {{ $doctor->designation->name }}
                            @endif
                            @if (!empty($doctor->specialist?->name))
                                | <strong>Specialist:</strong> {{ $doctor->specialist->name }}
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6"><strong>Phone:</strong> {{ $doctor->phone ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Emergency Contact:</strong> <span
                                    class="bg-warning">{{ $doctor->emergency_phone ?? ' ' }}</span>
                            </div>

                            <div class="col-md-6"><strong>Email:</strong> {{ $doctor->email ?? ' ' }}</div>

                            <div class="col-md-6"><strong>Department:</strong> {{ $doctor->department?->name ?? ' ' }}
                            </div>
                            <div class="col-md-6"><strong>Qualification:</strong> {{ $doctor->qualification ?? ' ' }}</div>

                            <div class="col-md-6"><strong>Registration No:</strong> {{ $doctor->registration_no ?? ' ' }}
                            </div>
                            <div class="col-md-6"><strong>License No:</strong> {{ $doctor->license_no ?? ' ' }}</div>

                            <div class="col-md-6"><strong>License Expiry:</strong>
                                {{ $doctor->license_expiry_date ?? ' ' }}
                            </div>
                            <div class="col-md-6"><strong>Doctor Type:</strong> {{ $doctor->doctor_type ?? ' ' }}</div>

                            <div class="col-md-6"><strong>Joining Date:</strong>
                                {{ $doctor->joining_date ?? ' ' }}
                            </div>
                            <div class="col-md-6"><strong>Leaving Date:</strong>
                                {{ $doctor->leaving_date ?? ' ' }}
                            </div>

                            <div class="col-md-6"><strong>Gender:</strong> {{ $doctor->gender ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Marital Status:</strong> {{ $doctor->marital_status ?? ' ' }}
                            </div>

                            <div class="col-md-6"><strong>Blood Group:</strong> {{ $doctor->blood_group ?? ' ' }}</div>
                            <div class="col-md-6"><strong>Identification:</strong>
                                {{ $doctor->identification_number ?? ' ' }}</div>

                            <div class="col-md-12 mt-2"><strong>Address:</strong> {{ $doctor->address ?? ' ' }}</div>

                            <div class="col-md-12 mt-2"><strong>Notes:</strong> {{ $doctor->notes ?? ' ' }}</div>

                            <div class="col-md-12 mt-3 d-flex gap-2">
                                <span class="badge bg-success">Active: {{ $doctor->is_active ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <form method="POST" action="{{ route('doctors.destroy', $doctor) }}"
                    onsubmit="return confirm('Delete this doctor?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
