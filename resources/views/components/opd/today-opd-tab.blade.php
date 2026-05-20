<div class="card-body bg-white p-3">

    {{-- Toolbar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="search-box-wrap">
            <input type="text" class="form-control form-control-sm search-input"
                placeholder="Search today's OPD...">
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end toolbar-actions">
            <div>
                <select class="form-select form-select-sm page-size-select">
                    <option>100</option>
                    <option>50</option>
                    <option>25</option>
                    <option>10</option>
                </select>
            </div>

            <div class="table-icons d-flex align-items-center gap-3">
                <a href="javascript:void(0)" class="icon-btn"><i class="bi bi-copy"></i></a>
                <a href="javascript:void(0)" class="icon-btn"><i
                        class="bi bi-file-earmark-excel"></i></a>
                <a href="javascript:void(0)" class="icon-btn"><i
                        class="bi bi-file-earmark-text"></i></a>
                <a href="javascript:void(0)" class="icon-btn"><i class="bi bi-file-earmark-pdf"></i></a>
                <a href="javascript:void(0)" class="icon-btn"><i class="bi bi-printer"></i></a>
            </div>
        </div>
    </div>

    {{-- Today OPD Table --}}
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
                    <th>Visit Type</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($today_opd_patients ?? [] as $key=> $patient)
                {{-- @dd($patient) --}}
                    <tr>
                        <td><a
                                href="{{ route('opd-patients.show', $patient->id) }}">OPDN{{ $key + 1 }}</a>
                        </td>
                        <td>{{ $patient?->patient->patient_name ?? 'N/A' }}</td>
                        <td>{{ $patient?->patient->mrn ?? 'N/A' }}</td>
                        <td>{{ $patient->case_id ?? 'N/A' }}</td>
                        <td>
                            {{ !empty($patient->date) ? \Carbon\Carbon::parse($patient->date)->format('d M Y h:i A') : 'N/A' }}
                        </td>
                        <td>{{ $patient?->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $patient->remarks ?? 'N/A' }}</td>
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
                        <td>
                            <span class="badge status-badge bg-success-subtle text-success">Today</span>
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
                        <td colspan="9" class="p-0 border-0">
                            <div class="empty-state-wrapper">
                                <div class="empty-title">No data available in table</div>
                                <div class="empty-illustration">
                                    <div class="folder-box">
                                        <div class="paper paper-1"></div>
                                        <div class="paper paper-2"></div>
                                        <div class="paper paper-3"></div>
                                        <div class="folder-front"></div>
                                    </div>
                                </div>
                                <div class="empty-message">
                                    <i class="bi bi-arrow-left-short"></i>
                                    Add new record or search with different criteria.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div
        class="d-flex flex-wrap justify-content-between align-items-center gap-2 table-footer mt-2 pt-3 border-top">
        <div class="record-info">
            Records:
            {{ isset($today_opd_patients) ? $today_opd_patients->count() : 0 }}
        </div>

        <div class="pagination-icons d-flex align-items-center gap-3">
            <a href="javascript:void(0)" class="page-nav"><i class="bi bi-chevron-left"></i></a>
            <a href="javascript:void(0)" class="page-nav"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>
</div>
