@extends('backend.layouts.master')

@section('title', 'Doctor Wise Appointment')

@section('content')
    <div class="container-fluid py-4 apt-doctor-wise">
        <div class="page-head d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1 fw-bold text-dark">Doctor Wise Appointment</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Doctor Wise</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        {{-- Filter Card --}}
        <section class="filter-card mb-4">
            <form action="{{ route('appointments.doctor-wise') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Doctor <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="doctor_id" data-placeholder="--Select Doctor--" required>
                            <option value="">---Select Doctor---</option>
                            @foreach ($doctors as $d)
                                <option value="{{ $d->id }}" {{ request('doctor_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}" placeholder="dd/mm/yyyy">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" placeholder="dd/mm/yyyy">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        @if (request('doctor_id') || request('from_date') || request('to_date'))
                            <a href="{{ route('appointments.doctor-wise') }}" class="btn btn-light" title="Reset">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        {{-- Appointment Info Card --}}
        <section class="info-card">
            <header class="info-card__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="info-icon"><i class="bi bi-clipboard2-check"></i></span>
                    <h5 class="mb-0">Appointment Info</h5>
                </div>
                <div class="record-info text-muted small">
                    Records: {{ $appointments->count() }}
                </div>
            </header>

            <div class="table-responsive">
                <table class="table table-sm align-middle apt-table mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Patient Name</th>
                            <th>Token No</th>
                            <th>Appointment Date</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Live Consultation</th>
                            <th>Visit Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $key => $ap)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $ap->patient?->patient_name ?? 'N/A' }}</td>
                                <td>{{ str_pad($ap->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $ap->date?->format('d/m/y') ?? '-' }}</td>
                                <td>{{ $ap->patient?->mobileno ?? 'N/A' }}</td>
                                <td>{{ $ap->patient?->email ?? 'N/A' }}</td>
                                <td>{{ $ap->source }}</td>
                                <td>
                                    @if ($ap->live_consult && $ap->live_consult !== 'None')
                                        <span class="badge bg-success-subtle text-success">Yes ({{ $ap->live_consult }})</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @php($vs = $ap->visit_status ?? 'booked')
                                    <span class="badge
                                        {{ match($vs) {
                                            'booked'          => 'bg-primary-subtle text-primary',
                                            'checked_in'      => 'bg-info-subtle text-info',
                                            'in_consultation' => 'bg-warning-subtle text-warning',
                                            'completed'       => 'bg-success-subtle text-success',
                                            'cancelled'       => 'bg-danger-subtle text-danger',
                                            'no_show'         => 'bg-secondary-subtle text-secondary',
                                            default           => 'bg-secondary-subtle text-secondary',
                                        } }}">
                                        {{ str_replace('_', ' ', ucfirst($vs)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('appointments.edit', $ap->id) }}"
                                        class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    @if (request('doctor_id'))
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No appointments found for selected criteria.
                                    @else
                                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                                        Please select a doctor to view appointments.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .apt-doctor-wise .page-head h3 { color: #2b335d; }
        .apt-doctor-wise .breadcrumb-item a { color: #6b7390; text-decoration: none; }
        .apt-doctor-wise .breadcrumb-item.active { color: #2b335d; }

        .filter-card {
            background: #f4f6f9;
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            padding: 24px 24px 28px;
        }
        .filter-card .form-label {
            font-size: 13px;
            color: #2f3b4a;
        }

        .info-card {
            background: #fff;
            border: 1px solid #e3e6ef;
            border-radius: 12px;
            overflow: hidden;
        }
        .info-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e3e6ef;
        }
        .info-card__header h5 {
            font-size: 15px;
            font-weight: 600;
            color: #2b335d;
        }
        .info-icon {
            width: 32px;
            height: 32px;
            background: #fff3cd;
            color: #b98900;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .apt-table {
            font-size: 12.5px;
            color: #2f3b4a;
        }
        .apt-table thead th {
            font-size: 12px;
            font-weight: 700;
            color: #1f2937;
            background: #f8f9fb;
            border-bottom: 1px solid #dfe5eb;
            padding: 12px 14px;
            white-space: nowrap;
        }
        .apt-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #eef2f6;
            vertical-align: middle;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.$ && $.fn.select2) {
                $('.select2').select2({
                    width: '100%',
                    placeholder: function() {
                        return $(this).data('placeholder') || 'Select';
                    },
                    allowClear: true
                });
            }
        });
    </script>
@endpush
