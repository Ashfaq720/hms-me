@extends('backend.layouts.master')
@section('title', 'OPD Patient Department')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">OPD Patient Department</h3>
        <a href="{{ route('opd-patient-departments.create') }}" class="btn btn-primary">Create</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>OPD No</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Appointment</th>
                    <th>Charge</th>
                    <th>Payment</th>
                    <th>Old?</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows ?? [] as $row)
                <tr>
                    <td>{{ $loop->iteration + ($rows->currentPage()-1)*$rows->perPage() }}</td>
                    <td>{{ $row->opd_number }}</td>
                    <td>{{ $row->patient->patient_name ?? '-' }}</td>
                    <td>{{ $row->doctor->name ?? '-' }}</td>
                    <td>{{ optional($row->appointment_date)->format('d M Y, h:i A') }}</td>
                    <td>{{ number_format((float)$row->standard_charge, 2) }}</td>
                    <td>{{ $row->payment_mode }}</td>
                    <td>
                        <span class="badge {{ $row->is_old_patient ? 'bg-success' : 'bg-secondary' }}">
                            {{ $row->is_old_patient ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-sm btn-info" href="{{ route('opd-patient-departments.show', $row) }}">View</a>
                        <a class="btn btn-sm btn-warning" href="{{ route('opd-patient-departments.edit', $row) }}">Edit</a>
                        <form method="POST" action="{{ route('opd-patient-departments.destroy', $row) }}"
                              onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">No records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $rows->links() }}
</div>
@endsection
