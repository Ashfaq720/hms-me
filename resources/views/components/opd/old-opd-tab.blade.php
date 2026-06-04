<div class="card-body bg-white p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="search-box-wrap">
            <input type="text" class="form-control form-control-sm search-input"
                placeholder="Search old OPD...">
        </div>
    </div>

    <div class="table-responsive opd-table-wrap">
        <table class="table table-sm align-middle opd-table mb-0">
            <thead>
                <tr>
                    <th>OPD No</th>
                    <th>Patient Name</th>
                    <th>MRN</th>
                    <th>Case ID</th>
                    <th>Appointment Date</th>
                    <th>Consultant</th>
                    <th>Reference</th>
                    <th>Symptoms</th>
                    <th>Visit Type</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($old_opd_patients ?? [] as $key => $patient)
                    <tr>
                        <td><a
                                href="{{ route('opd-patients.show', $patient->id) }}">OPDN{{ $key + 1 }}</a>
                        </td>
                        <td>{{ $patient?->patient->patient_name ?? 'N/A' }}</td>
                        <td>{{ $patient?->patient->mrn ?? 'N/A' }}</td>
                        <td>{{ $patient->case_id ?? 'N/A' }}</td>
                        <td>{{ !empty($patient->date) ? \Carbon\Carbon::parse($patient->date)->format('d M Y h:i A') : 'N/A' }}
                        </td>
                        <td>{{ $patient?->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $patient->reference ?? 'N/A' }}</td>
                        <td>{{ $patient->symptoms ?? 'N/A' }}</td>
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
                        <td><span
                                class="badge status-badge bg-secondary-subtle text-secondary">Old</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('opd-patients.bill', $patient->id) }}"
                                    class="btn btn-sm btn-outline-info action-btn" title="Print Details"
                                    target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>

                                <a href="{{ route('opd-patients.prescriptions.create', $patient->id) }}"
                                    class="btn btn-sm btn-outline-warning action-btn"
                                    title="Add Prescription">
                                    <i class="bi bi-capsule-pill"></i>
                                </a>

                                <button type="button"
                                    class="btn btn-sm btn-outline-primary action-btn open-ajax-modal"
                                    title="Write Prescription"
                                    data-url="{{ route('opd-patients.manual-prescription.modal', $patient->id) }}"
                                    data-modal-title="Write Prescription"
                                    data-auto-print="true">
                                    <i class="bi bi-journal-medical"></i>
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-outline-info action-btn open-ajax-modal"
                                    title="Show"
                                    data-url="{{ route('opd-patients.details.modal', $patient->id) }}"
                                    data-modal-title="Visit Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('opd-patients.edit', $patient->id) }}"
                                    class="btn btn-sm btn-outline-secondary action-btn"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="{{ route('ipd-patients.create', ['patient_id' => $patient->patient_id, 'from_opd_id' => $patient->id]) }}"
                                    class="btn btn-sm btn-outline-success action-btn"
                                    title="Move in Ipd">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No old OPD records
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div
        class="d-flex flex-wrap justify-content-between align-items-center gap-2 table-footer mt-2 pt-3 border-top">
        <div class="record-info">Records:
            {{ isset($old_opd_patients) ? $old_opd_patients->count() : 0 }}</div>
    </div>
</div>
