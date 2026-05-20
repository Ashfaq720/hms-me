<div class="card-body bg-white p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="search-box-wrap">
            <input type="text" class="form-control form-control-sm search-input"
                placeholder="Search patient...">
        </div>
    </div>

    <div class="table-responsive opd-table-wrap">
        <table class="table table-sm align-middle opd-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>MRN</th>
                    <th>Mobile</th>
                    <th>Last Case ID</th>
                    <th>Last Visit</th>
                    <th>Last Consultant</th>
                    <th>Visit Type</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient_view_list ?? [] as $key => $patient)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $patient?->patient->patient_name ?? 'N/A' }}</td>
                        <td>{{ $patient?->patient->mrn ?? 'N/A' }}</td>
                        <td>{{ $patient?->patient->mobileno ?? 'N/A' }}</td>
                        <td>{{ $patient->case_id ?? 'N/A' }}</td>
                        <td>{{ !empty($patient->date) ? \Carbon\Carbon::parse($patient->date)->format('d M Y h:i A') : 'N/A' }}
                        </td>
                        <td>{{ $patient?->doctor->name ?? 'N/A' }}</td>
                        <td>
                            @switch($patient->visit_type)
                                @case('new')
                                    <span class="badge bg-primary-subtle text-primary">New</span>
                                    @break
                                @case('follow_up')
                                    <span class="badge bg-warning-subtle text-warning">Follow Up</span>
                                    @break
                                @case('recheckup')
                                    <span class="badge bg-info-subtle text-info">Recheckup</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary-subtle text-secondary">N/A</span>
                            @endswitch
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('opd-patients.show', $patient->id) }}"
                                    class="btn btn-sm btn-outline-info action-btn" title="Show">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('opd-patients.edit', $patient->id) }}"
                                    class="btn btn-sm btn-outline-secondary action-btn" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
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
    <div
        class="d-flex flex-wrap justify-content-between align-items-center gap-2 table-footer mt-2 pt-3 border-top">
        <div class="record-info">Records:
            {{ isset($patient_view_list) ? $patient_view_list->count() : 0 }}</div>
    </div>
</div>
