@props(['opdPatient'])

@php
    $visits = \App\Models\OpdPatient::with(['doctor', 'department'])
        ->where('patient_id', $opdPatient->patient_id)
        ->latest('date')
        ->get();
@endphp

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar2-check-fill text-primary me-2"></i>Visit History
            <span class="badge bg-primary-subtle text-primary ms-2">{{ $visits->count() }}</span>
        </h6>
        <div class="d-flex gap-2">
            <form action="{{ route('opd-patients.recheckup', $opdPatient->id) }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-repeat me-1"></i> Add Recheckup
                </button>
            </form>
            <a href="{{ route('opd-patients.create', ['patient_id' => $opdPatient->patient_id]) }}"
                class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Visit Checkup
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 custom-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Case ID</th>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Serial</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr @class(['table-active' => $visit->id === $opdPatient->id])>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $visit->case_id ?? '-' }}</strong></td>
                            <td>{{ $visit->date ? format_datetime($visit->date) : '-' }}</td>
                            <td>{{ $visit->doctor->name ?? '-' }}</td>
                            <td>{{ $visit->department->name ?? '-' }}</td>
                            <td>{{ $visit->serial_no ?? '-' }}</td>
                            <td>{{ $visit->token_no ?? '-' }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success text-capitalize">
                                    {{ $visit->status ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $visit->remarks ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('opd-patients.show', $visit->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No visit history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
