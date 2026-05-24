@extends('backend.layouts.master')

@section('title', 'Nursing Notes — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Nursing Notes</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="card-title">Add Hourly Observation</h6>
                <form method="POST" action="{{ route('icu.admissions.nursing-notes.store', $admission->id) }}"
                    class="row g-2">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small">Shift</label>
                        <select name="shift" class="form-select form-select-sm">
                            <option value="">--</option>
                            @foreach (['Morning', 'Evening', 'Night'] as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Observation Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="observation_time"
                            value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Consciousness</label>
                        <input type="text" name="consciousness_level" class="form-control form-control-sm"
                            placeholder="Alert / GCS 13">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Pain (0–10)</label>
                        <input type="number" min="0" max="10" name="pain_score"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Respiratory Support</label>
                        <input type="text" name="respiratory_support" class="form-control form-control-sm"
                            placeholder="O2 / NIV / Vent">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small">Oxygen Flow</label>
                        <input type="text" name="oxygen_flow" class="form-control form-control-sm"
                            placeholder="4 L/min">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Position</label>
                        <input type="text" name="position" class="form-control form-control-sm"
                            placeholder="Supine">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Skin</label>
                        <input type="text" name="skin_condition" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">General</label>
                        <input type="text" name="general_condition" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Save Note</button>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Nursing Queue</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:140px;">Time</th>
                            <th style="width:80px;">Shift</th>
                            <th>Consciousness</th>
                            <th style="width:60px;">Pain</th>
                            <th>Resp. Support</th>
                            <th style="width:90px;">O₂ Flow</th>
                            <th>Position</th>
                            <th>Skin</th>
                            <th>General</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notes as $n)
                            <tr>
                                <td class="ps-2"><small>{{ $n->observation_time?->format('Y-m-d H:i') }}</small></td>
                                <td>{{ $n->shift ?? '-' }}</td>
                                <td>{{ $n->consciousness_level ?? '-' }}</td>
                                <td>{{ $n->pain_score ?? '-' }}</td>
                                <td>{{ $n->respiratory_support ?? '-' }}</td>
                                <td>{{ $n->oxygen_flow ?? '-' }}</td>
                                <td>{{ $n->position ?? '-' }}</td>
                                <td>{{ $n->skin_condition ?? '-' }}</td>
                                <td>{{ $n->general_condition ?? '-' }}</td>
                                <td><small>{{ $n->remarks ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No notes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
