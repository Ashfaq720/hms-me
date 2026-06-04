<div class="card-body bg-white p-3">

    {{-- Toolbar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="search-box-wrap">
            <input type="text" class="form-control form-control-sm search-input"
                placeholder="Search patient...">
        </div>
    </div>

    {{-- Patient View Table --}}
    <div class="table-responsive apt-table-wrap">
        <table class="table table-sm align-middle apt-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Mobile</th>
                    <th>Last Doctor</th>
                    <th>Last Visit</th>
                    <th>Priority</th>
                    <th>Specialist</th>
                    <th>Source</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient_view_list ?? [] as $key => $ap)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $ap->patient?->patient_name ?? 'N/A' }}</td>
                        <td>{{ $ap->patient?->mobileno ?? 'N/A' }}</td>
                        <td>{{ $ap->doctorRelation?->name ?? 'N/A' }}</td>
                        <td>{{ $ap->date?->format('d M Y h:i A') ?? '-' }}</td>
                        <td>
                            @if ($ap->priority === 'Urgent')
                                <span class="badge bg-danger-subtle text-danger">Urgent</span>
                            @else
                                <span class="badge bg-info-subtle text-info">Normal</span>
                            @endif
                        </td>
                        <td>{{ $ap->specialist }}</td>
                        <td>{{ $ap->source }}</td>
                        <td class="text-end">
                            <a href="{{ route('appointments.edit', $ap->id) }}"
                                class="btn btn-sm btn-outline-info action-btn" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No patients found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 table-footer mt-2 pt-3 border-top">
        <div class="record-info">
            Records: {{ isset($patient_view_list) ? $patient_view_list->count() : 0 }}
        </div>
    </div>
</div>
