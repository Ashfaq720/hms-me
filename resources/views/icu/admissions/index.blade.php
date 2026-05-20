@extends('backend.layouts.master')

@php
    $unitLabel = request('icu_type') ?: 'ICU / CCU';
@endphp

@section('title', $unitLabel . ' Admissions')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">{{ $unitLabel }} Admissions</h1>
            </div>

            <a href="{{ route('icu.admissions.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="bi bi-heart-pulse me-1"></i> New ICU / CCU Admission
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="row g-2 mt-1 mb-2">
            {{-- <div class="col-md-3">
                <select name="icu_type" class="form-select form-select-sm">
                    <option value="">All ICU Types</option>
                    @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                        <option value="{{ $t }}" {{ request('icu_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['Requested', 'Approved', 'Admitted', 'Transferred', 'Discharged', 'Cancelled', 'Expired'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('icu.admissions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>--}}
        </form>

        <div class="row mt-1">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title mb-0">Admissions</h6>
                    </div>

                    <div class="card-body px-2 pt-2 pb-0 gradient-layer" style="min-height: 300px;">
                        <table class="table table-sm table-hover display table-row-rounded mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-2" style="width:40px;">SN</th>
                                    <th style="width:110px;">Case ID</th>
                                    <th style="min-width:160px;">Patient</th>
                                    <th style="width:110px;">Mobile</th>
                                    <th style="width:70px;">Gender</th>
                                    <th style="width:55px;">Age</th>
                                    <th style="width:70px;">Type</th>
                                    <th style="width:90px;">Source</th>
                                    <th style="width:80px;">Bed</th>
                                    <th style="width:85px;">Isolation</th>
                                    <th style="width:65px;">Vent</th>
                                    <th style="width:130px;">Admitted</th>
                                    <th style="width:95px;">Status</th>
                                    <th style="width:70px;" class="text-end pe-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admissions as $i => $a)
                                    <tr>
                                        <td class="ps-2 text-muted">{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('icu.admissions.show', $a->id) }}"
                                                class="fw-semibold">{{ $a->icu_case_id }}</a></td>
                                        <td>
                                            <div class="fw-semibold lh-sm">{{ $a->patient?->patient_name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ $a->patient?->mrn ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $a->patient?->mobileno ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $a->patient?->gender ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div>{{ calculateAgeFromDob($a->patient?->dob) ?? '' }}</div>
                                        </td>
                                        <td><span class="badge bg-danger-subtle text-danger">{{ $a->icu_type }}</span></td>
                                        <td>{{ $a->source_type }}</td>
                                        <td>{{ $a->bed?->name ?? '-' }}</td>
                                        <td>{{ $a->isolation_type }}</td>
                                        <td>{!! $a->ventilator_required
                                            ? '<span class="badge bg-warning-subtle text-warning">Yes</span>'
                                            : '<span class="text-muted small">No</span>' !!}</td>
                                        <td><small>{{ $a->admission_time?->format('Y-m-d H:i') }}</small></td>
                                        <td>
                                            @php
                                                $color = match ($a->status) {
                                                    'Admitted' => 'success',
                                                    'Approved' => 'primary',
                                                    'Discharged' => 'secondary',
                                                    'Transferred' => 'info',
                                                    'Expired' => 'dark',
                                                    'Cancelled' => 'danger',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $a->status }}</span>
                                        </td>
                                        <td class="text-end pe-2">
                                            <a href="{{ route('icu.admissions.show', $a->id) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center text-muted py-4">No ICU admissions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
