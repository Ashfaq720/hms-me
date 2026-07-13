@extends('backend.layouts.master')

@section('title', 'Thresholds — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Vital Thresholds</h1>
                <small class="text-muted">Patient-specific thresholds for {{ $admission->icu_case_id }}. Reverting clears
                    the row and falls back to defaults.</small>
            </div>
            <a href="{{ route('icu.admissions.vitals.index', $admission->id) }}"
                class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif

        <div class="card mt-2">
            <div class="card-body p-2">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2">Vital</th>
                            <th>Normal Min</th><th>Normal Max</th>
                            <th>Warning Min</th><th>Warning Max</th>
                            <th>Critical Min</th><th>Critical Max</th>
                            <th class="text-end pe-2" style="width:160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $type => $r)
                            <tr>
                                <form method="POST"
                                    action="{{ route('icu.admissions.thresholds.store', $admission->id) }}">
                                    @csrf
                                    <input type="hidden" name="vital_type" value="{{ $type }}">
                                    <td class="ps-2 fw-semibold">{{ $type }}</td>
                                    <td><input type="number" step="0.01" name="normal_min"
                                            value="{{ $r->normal_min }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="normal_max"
                                            value="{{ $r->normal_max }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="warning_min"
                                            value="{{ $r->warning_min }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="warning_max"
                                            value="{{ $r->warning_max }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="critical_min"
                                            value="{{ $r->critical_min }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01" name="critical_max"
                                            value="{{ $r->critical_max }}" class="form-control form-control-sm"></td>
                                    <td class="text-end pe-2">
                                        <button class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                                <form method="POST"
                                    action="{{ route('icu.admissions.thresholds.destroy', [$admission->id, $type]) }}"
                                    class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-secondary">Reset</button>
                                </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
