@extends('backend.layouts.master')

@section('title', 'Intake / Output — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Intake / Output Chart</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        {{-- Date filter + totals --}}
        <form method="GET" class="row g-2 align-items-end mt-2">
            <div class="col-md-3">
                <label class="form-label small">Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-outline-primary">Apply</button>
            </div>
            <div class="col-md-7 text-end">
                <span class="badge bg-success me-2">Intake: {{ $totalIntake }} ml</span>
                <span class="badge bg-warning me-2">Output: {{ $totalOutput }} ml</span>
                <span class="badge bg-{{ $balance >= 0 ? 'primary' : 'danger' }}">
                    Balance: {{ $balance >= 0 ? '+' : '' }}{{ $balance }} ml
                </span>
            </div>
        </form>

        {{-- Two side-by-side entry forms --}}
        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-success">Add Intake</h6>
                        <form method="POST" action="{{ route('icu.admissions.intake-output.store', $admission->id) }}"
                            class="row g-2">
                            @csrf
                            <input type="hidden" name="entry_type" value="Intake">
                            <div class="col-md-5">
                                <input type="datetime-local" name="entry_time"
                                    value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select form-select-sm" required>
                                    @foreach (['IVFluid', 'OralFluid', 'Blood', 'MedFluid', 'TubeFeeding'] as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="quantity_ml" class="form-control form-control-sm"
                                    placeholder="ml" min="1" required>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="remarks" class="form-control form-control-sm"
                                    placeholder="Remarks">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success btn-sm w-100">Save Intake</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title text-warning">Add Output</h6>
                        <form method="POST" action="{{ route('icu.admissions.intake-output.store', $admission->id) }}"
                            class="row g-2">
                            @csrf
                            <input type="hidden" name="entry_type" value="Output">
                            <div class="col-md-5">
                                <input type="datetime-local" name="entry_time"
                                    value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select form-select-sm" required>
                                    @foreach (['Urine', 'Drain', 'Vomiting', 'Stool', 'BloodLoss', 'Other'] as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="quantity_ml" class="form-control form-control-sm"
                                    placeholder="ml" min="1" required>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="remarks" class="form-control form-control-sm"
                                    placeholder="Remarks">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning btn-sm w-100">Save Output</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="card mt-2">
            <div class="card-body p-2">
                <h6 class="card-title px-2 pt-2">Observation Queue {{ $date }}</h6>
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:140px;">Time</th>
                            <th style="width:90px;">Type</th>
                            <th style="width:120px;">Category</th>
                            <th style="width:100px;">Qty (ml)</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $e)
                            <tr>
                                <td class="ps-2"><small>{{ $e->entry_time?->format('Y-m-d H:i') }}</small></td>
                                <td>
                                    <span class="badge bg-{{ $e->entry_type === 'Intake' ? 'success' : 'warning' }}">
                                        {{ $e->entry_type }}
                                    </span>
                                </td>
                                <td>{{ $e->category }}</td>
                                <td>{{ $e->quantity_ml }}</td>
                                <td><small>{{ $e->remarks ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No entries on this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
