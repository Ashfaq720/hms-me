<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Medication Log</h6>
        </div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a href="javascript:void(0);" class="btn btn-sm btn-primary"
                    data-url="{{ route('ipd-patients.medications.create', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Medication" data-size="xl">
                    <i class="bi bi-plus"></i> Add Medication
                </a>
            @endif
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th>Medicine</th>
                <th>Unit</th>
                <th>Date & Time</th>
                <th>Dosage</th>
                <th>Medicated By</th>
                <th>Remarks</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->medications as $index => $medication)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $medication->medicine->medicine_name ?? 'N/A' }}</td>
                    <td>{{ $medication->medicine->unit?->name ?? '-' }}</td>
                    <td>{{ $medication->datetime->format('d M Y, h:i A') }}</td>
                    <td>{{ $medication->dosage ?? '-' }}</td>
                    <td>{{ $medication->medicated_by ?? '-' }}</td>
                    <td>{{ $medication->remarks ?? '-' }}</td>
                    <td>{{ $medication->notes ?? '-' }}</td>
                    <td>
                        <form action="{{ route('ipd-patients.medications.destroy', [$iPDPatient->id, $medication->id]) }}"
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
                    <td colspan="8" class="text-center">No medications recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
