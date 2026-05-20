@extends('backend.layouts.master')
@section('title', 'Edit ER Patient')
@section('content')

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-semibold mb-0">Edit ER Patient</h5>
        <a href="{{ route('front_desk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('front_desk.er_registration.update', $erPatient->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- Patient Info (read-only) --}}
            @if($erPatient->patient)
            <div class="col-12">
                <div class="p-3 border rounded bg-light">
                    <h6 class="mb-2 text-secondary">Patient</h6>
                    <div class="d-flex gap-3 flex-wrap">
                        <span><strong>Name:</strong> {{ $erPatient->patient->patient_name }}</span>
                        <span><strong>Contact:</strong> {{ $erPatient->patient->mobileno }}</span>
                        @if($erPatient->patient->mrn)
                            <span><strong>MRN:</strong> {{ $erPatient->patient->mrn }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Clinical --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-danger">Patient Information</h6>
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control @error('age') is-invalid @enderror"
                                   value="{{ old('age', $erPatient->age) }}" min="0" max="150">
                            @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach(['Male','Female','Other'] as $g)
                                    <option value="{{ $g }}" @selected(old('gender', $erPatient->gender) === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $b)
                                    <option value="{{ $b }}" @selected(old('blood_group', $erPatient->blood_group) === $b)>{{ $b }}</option>
                                @endforeach
                            </select>
                            @error('blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Arrival Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="arrival_time"
                                   class="form-control @error('arrival_time') is-invalid @enderror"
                                   value="{{ old('arrival_time', $erPatient->arrival_time ? \Carbon\Carbon::parse($erPatient->arrival_time)->format('Y-m-d\TH:i') : '') }}"
                                   required>
                            @error('arrival_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach(['CRITICAL','HIGH','NORMAL'] as $p)
                                    <option value="{{ $p }}" @selected(old('priority', $erPatient->priority) === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(['PENDING','ACTIVE','ADMITTED','DISCHARGED','CANCELLED','Registered'] as $s)
                                    <option value="{{ $s }}" @selected(old('status', $erPatient->status) === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Financial / Third Party --}}
            <div class="col-12">
                <div class="p-3 border rounded">
                    <h6 class="mb-3 text-danger">Financial / Third Party Information</h6>
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach(['CORPORATE','INSURANCE','STUFF','SELF'] as $d)
                                    <option value="{{ $d }}" @selected(old('discount_type', $erPatient->discount_type) === $d)>{{ $d }}</option>
                                @endforeach
                            </select>
                            @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Third Party Name</label>
                            <input type="text" name="third_party_name"
                                   class="form-control @error('third_party_name') is-invalid @enderror"
                                   value="{{ old('third_party_name', $erPatient->third_party_name) }}">
                            @error('third_party_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Third Party Contact</label>
                            <input type="text" name="third_party_contact"
                                   class="form-control @error('third_party_contact') is-invalid @enderror"
                                   value="{{ old('third_party_contact', $erPatient->third_party_contact) }}">
                            @error('third_party_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Relation</label>
                            <input type="text" name="relation"
                                   class="form-control @error('relation') is-invalid @enderror"
                                   value="{{ old('relation', $erPatient->relation) }}">
                            @error('relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Description / Remarks --}}
            <div class="col-md-6">
                <label class="form-label">Description / Chief Complaint</label>
                <textarea name="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $erPatient->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="3"
                          class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $erPatient->remarks) }}</textarea>
                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Actions --}}
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('front_desk.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-danger">Update ER Patient</button>
            </div>

        </div>
    </form>
</div>

@endsection
