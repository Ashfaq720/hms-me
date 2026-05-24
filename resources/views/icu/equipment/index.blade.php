@extends('backend.layouts.master')

@php
    $unitParam = request('icu_type');
    $unitLabel = $unitParam ?: 'ICU';
    $unitQuery = $unitParam ? ['icu_type' => $unitParam] : [];
@endphp

@section('title', $unitLabel . ' Equipment')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <h1 class="app-page-title">{{ $unitLabel }} Equipment</h1>
            <a href="{{ route('icu.equipment.create', $unitQuery) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Equipment
            </a>
        </div>

        <form method="GET" class="row g-2 mt-1 mb-2">
            @if ($unitParam)
                <input type="hidden" name="icu_type" value="{{ $unitParam }}">
            @endif
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach (['Ventilator', 'Monitor', 'InfusionPump', 'SyringePump', 'OxygenSupport', 'DialysisMachine', 'ECG', 'PulseOximeter', 'TemperatureSensor', 'Other'] as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['Available', 'InUse', 'Maintenance', 'Cleaning', 'Damaged', 'Reserved'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('icu.equipment.index', $unitQuery) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

        <div class="card">
            <div class="card-body p-2">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-2" style="width:35px;">SN</th>
                            <th style="width:120px;">Code</th>
                            <th>Name</th>
                            <th style="width:130px;">Type</th>
                            <th style="width:120px;">Serial</th>
                            <th>Default Bed</th>
                            <th style="width:80px;">Rate</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:130px;" class="text-end pe-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipment as $e)
                            <tr>
                                <td class="ps-2 text-muted">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $e->equipment_code }}</td>
                                <td>{{ $e->equipment_name }}</td>
                                <td>{{ $e->equipment_type }}</td>
                                <td>{{ $e->serial_no ?? '-' }}</td>
                                <td>{{ $e->defaultBed?->name ?? '-' }}</td>
                                <td>৳ {{ number_format($e->charge_rate, 2) }}/{{ strtolower($e->charge_type) }}</td>
                                <td>
                                    @php
                                        $color = match ($e->status) {
                                            'Available' => 'success',
                                            'InUse'     => 'warning',
                                            'Maintenance', 'Cleaning' => 'info',
                                            'Damaged'   => 'danger',
                                            default     => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $e->status }}</span>
                                </td>
                                <td class="text-end pe-2">
                                    <a href="{{ route('icu.equipment.edit', array_merge([$e->id], $unitQuery)) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('icu.equipment.destroy', array_merge([$e->id], $unitQuery)) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete this equipment?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Del</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No equipment yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
