@props(['opdPatient'])

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-capsule-pill me-2 text-success"></i>Medication List
            <span class="badge bg-success-subtle text-success ms-2">{{ $opdPatient->medications->count() }}</span>
        </h6>
        <a href="javascript:void(0);" class="btn btn-sm btn-primary"
            data-url="{{ route('opd-patients.medications.create', $opdPatient->id) }}" data-ajax-popup="true"
            data-title="Add Medication" data-size="xl">
            <i class="bi bi-plus"></i> Add Medication
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 custom-table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Medicine</th>
                        <th>Unit</th>
                        <th>Date & Time</th>
                        <th>Dosage</th>
                        <th>Medicated By</th>
                        <th>Remarks</th>
                        <th>Notes</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opdPatient->medications as $index => $medication)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $medication->medicine->medicine_name ?? 'N/A' }}</td>
                            <td>{{ $medication->medicine->unit?->name ?? '-' }}</td>
                            <td>{{ $medication->datetime->format('d M Y, h:i A') }}</td>
                            <td>{{ $medication->dosage ?? '-' }}</td>
                            <td>{{ $medication->medicated_by ?? '-' }}</td>
                            <td>{{ $medication->remarks ?? '-' }}</td>
                            <td>{{ $medication->notes ?? '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('opd-patients.medications.destroy', [$opdPatient->id, $medication->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this medication?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No medications recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
