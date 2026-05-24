@extends('backend.layouts.master')

@section('title', 'Assign Ambulance')

@section('content')
<div class="container">
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Assign Ambulance (Request #{{ $request->id }})</h1>
            <div class="text-muted">Pickup: {{ $request->pickup_location }}</div>
        </div>
        <a href="{{ route('amb.requests.show', $request) }}" class="btn btn-light">Back</a>
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

                    <form method="POST" action="{{ route('amb.trips.assignStore', $request) }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ambulance</label>
                                <select name="ambulance_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach($ambulances as $a)
                                        <option value="{{ $a->id }}">
                                            {{ $a->reg_no }} ({{ $a->type }}) - {{ $a->status }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Only AVAILABLE shown. Compliance checks happen on Save.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Driver</label>
                                <select name="driver_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach($drivers as $d)
                                        <option value="{{ $d->id }}">
                                            {{ $d->name }} (exp: {{ $d->license_expiry ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Paramedic (Required for ALS/ICU/NEONATAL)</label>
                                <select name="paramedic_id" class="form-select">
                                    <option value="">-- Optional --</option>
                                    @foreach($paramedics as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} ({{ $p->certification }} exp: {{ $p->cert_expiry ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Reason (optional)</label>
                                <input type="text" name="reason" class="form-control" placeholder="If override / special case, write reason">
                            </div>

                            <div class="col-12">
                                <button class="btn btn-success">
                                    <i class="fa-solid fa-truck-medical me-1"></i> Assign Now
                                </button>
                                <a href="{{ route('amb.requests.show', $request) }}" class="btn btn-light ms-2">Cancel</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
