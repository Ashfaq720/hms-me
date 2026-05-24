@extends('backend.layouts.master')
@section('title', 'Edit Treatment History - Ipd')

@section('content')
    <div class="container-fluid py-3">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Edit Treatment History</h4>
                    <div class="text-muted small">
                        {{ $ipdPatient->patient->patient_name ?? 'N/A' }} —
                        Ipd <strong>{{ $ipdPatient->ipd_no ?? '-' }}</strong>
                    </div>
                </div>
                <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}"
                    class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Patient
                </a>
            </div>
        </div>

        <form action="{{ route('ipd-patients.treatment-histories.update', [$ipdPatient->id, $history->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clipboard2-pulse text-primary"></i> Treatment Details</h6>
                </div>
                <div class="card-body">
                    @include('ipd_patients.treatment-histories._fields', ['history' => $history])
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2 pb-3">
                    <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
@endsection
