@php
    $histories = $iPDPatient->treatmentHistories()->with('doctor')->latest()->get();
@endphp
<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Treatment History</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a href="{{ route('ipd-patients.treatment-histories.create', $iPDPatient->id) }}"
                    class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Treatment History
                </a>
            @endif
        </div>
    </div>
    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>SN</th>
                <th>Date</th>
                <th>Doctor</th>
                <th>Diagnosis</th>
                <th>Prescribe Medicine</th>
                <th>Tx Note</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($histories as $history)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $history->date ? format_datetime($history->date) : '-' }}</td>
                    <td>{{ $history->doctor->name ?? '-' }}</td>
                    <td>{{ $history->diagnosis ?? '-' }}</td>
                    <td>{{ $history->prescribe_medicine ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($history->tx_note ?? ''), 60) }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-info" title="View Details"
                                data-bs-toggle="modal" data-bs-target="#treatmentHistoryModal{{ $history->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('ipd-patients.treatment-histories.edit', [$iPDPatient->id, $history->id]) }}"
                                class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form
                                action="{{ route('ipd-patients.treatment-histories.destroy', [$iPDPatient->id, $history->id]) }}"
                                method="POST" onsubmit="return confirm('Delete this treatment history?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No treatment history available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @foreach ($histories as $history)
        <div class="modal fade" id="treatmentHistoryModal{{ $history->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Treatment History —
                            {{ $history->date ? format_datetime($history->date) : '' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th style="width:30%">Date</th>
                                    <td>{{ $history->date ? format_datetime($history->date) : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Doctor</th>
                                    <td>{{ $history->doctor->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Diagnosis</th>
                                    <td>{{ $history->diagnosis ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Prescribe Medicine</th>
                                    <td>{{ $history->prescribe_medicine ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tx Note</th>
                                    <td>{!! nl2br(e($history->tx_note ?? '-')) !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ format_datetime($history->created_at) ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
