
@extends('backend.layouts.master')

@section('title', 'Add Ambulance Request')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Add Ambulance Request</h1>
        </div>
        <a href="{{ route('amb.requests.index') }}" class="btn btn-light">Back</a>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('amb.requests.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Source</label>
                                <select name="source" class="form-select" required>
                                    @foreach(['ER_DESK','OPD','Ipd','CALL_CENTER','REFERRAL'] as $s)
                                        <option value="{{ $s }}" @selected(old('source')==$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Request Type</label>
                                <select name="request_type" class="form-select" required>
                                    @foreach(['EMERGENCY','NORMAL','TRANSFER','SCHEDULED'] as $t)
                                        <option value="{{ $t }}" @selected(old('request_type')==$t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    @foreach(['CRITICAL','HIGH','NORMAL'] as $p)
                                        <option value="{{ $p }}" @selected(old('priority')==$p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Patient Condition</label>
                                <select name="patient_condition" class="form-select" required>
                                    @foreach(['CRITICAL','STABLE'] as $c)
                                        <option value="{{ $c }}" @selected(old('patient_condition')==$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Pickup Location</label>
                                <input type="text" name="pickup_location" class="form-control" value="{{ old('pickup_location') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Drop Location</label>
                                <input type="text" name="drop_location" class="form-control" value="{{ old('drop_location') }}">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Known Patient (Optional)</label>
                                <select name="patient_id" class="form-select">
                                    <option value="">-- Select Patient --</option>
                                    @foreach($patients as $pt)
                                        <option value="{{ $pt->id }}" @selected(old('patient_id')==$pt->id)>
                                            {{ $pt->patient_name }} ({{ $pt->mobileno ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">If unknown, tick the checkbox below.</div>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="is_unknown_patient" id="unknownPatient"
                                           @checked(old('is_unknown_patient'))>
                                    <label class="form-check-label" for="unknownPatient">
                                        Unknown patient (create temporary ID)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <button class="btn btn-primary">
                                    <i class="fi fi-rr-check me-1"></i> Save Request
                                </button>
                                <a href="{{ route('amb.requests.index') }}" class="btn btn-light ms-2">Cancel</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
