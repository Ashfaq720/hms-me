@extends('backend.layouts.master')

@section('title', 'Exposure — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Exposure Tracking</h1>
                <div class="text-muted">{{ $admission->icu_case_id }}</div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Record Exposure</h6>
                <form method="POST" action="{{ route('icu.admissions.exposure.store', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Type <span class="text-danger">*</span></label>
                        <select name="exposure_type" class="form-select form-select-sm" required>
                            @foreach (['SamePatient', 'SameBed', 'SameUnit', 'SameStaff', 'SameEquipment', 'Other'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="exposure_time"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Linked Infection Record</label>
                        <select name="infection_record_id" class="form-select form-select-sm">
                            <option value="">--</option>
                            @foreach ($infectionRecords as $r)
                                <option value="{{ $r->id }}">
                                    [{{ $r->infection_status }}]
                                    {{ $r->infection_name ?? '?' }} / {{ $r->isolation_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3"><label class="form-label small">Patient ID</label>
                        <input type="number" name="related_patient_id" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><label class="form-label small">Bed ID</label>
                        <input type="number" name="related_bed_id" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><label class="form-label small">Equipment ID</label>
                        <input type="number" name="related_equipment_id" class="form-control form-control-sm"></div>
                    <div class="col-md-3"><label class="form-label small">Staff ID</label>
                        <input type="number" name="related_staff_id" class="form-control form-control-sm"></div>

                    <div class="col-md-12"><label class="form-label small">Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-sm"></div>
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary btn-sm">Save Exposure</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Exposure History</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:160px;">Time</th>
                            <th style="width:130px;">Type</th>
                            <th>Linked Records</th>
                            <th>Linked Infection</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $l)
                            <tr>
                                <td class="ps-2"><small>{{ $l->exposure_time?->format('Y-m-d H:i') }}</small></td>
                                <td>{{ $l->exposure_type }}</td>
                                <td>
                                    <small>
                                        @if ($l->related_patient_id) Pt #{{ $l->related_patient_id }} @endif
                                        @if ($l->related_bed_id) · Bed #{{ $l->related_bed_id }} @endif
                                        @if ($l->related_equipment_id) · Eq #{{ $l->related_equipment_id }} @endif
                                        @if ($l->related_staff_id) · Staff #{{ $l->related_staff_id }} @endif
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        @if ($l->infectionRecord)
                                            {{ $l->infectionRecord->infection_name ?? '-' }} /
                                            {{ $l->infectionRecord->isolation_type }}
                                        @else
                                            -
                                        @endif
                                    </small>
                                </td>
                                <td><small>{{ $l->remarks ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No exposures recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
