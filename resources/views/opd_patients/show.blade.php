@extends('backend.layouts.master')

@section('title', 'OPD Patient Details')

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="card border-0 shadow-sm rounded-3 opd-view-card">
            {{-- Header --}}
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-1 fw-bold text-primary">
                            <i class="bi bi-person-vcard-fill me-2"></i>OPD Patient Details
                        </h5>
                        <div class="text-muted small d-flex flex-wrap gap-2">
                            <span>Patient ID: <strong>#PAT-{{ str_pad($opdPatient->patient_id ?? 0, 4, '0', STR_PAD_LEFT) }}</strong></span>
                            <span class="text-muted d-none d-sm-inline">|</span>
                            <span>OPD No: <strong>#OPD-{{ str_pad($opdPatient->id ?? 0, 4, '0', STR_PAD_LEFT) }}</strong></span>
                            <span class="text-muted d-none d-sm-inline">|</span>
                            <span>Case ID: <strong>{{ $opdPatient->case_id ?? '-' }}</strong></span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 header-actions">
                        <a href="{{ route('opd-patients.print', $opdPatient->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-printer me-1"></i><span class="btn-text">Print</span>
                        </a>
                        <a href="{{ route('opd-patients.prescriptions.create', $opdPatient->id) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-prescription2 me-1"></i><span class="btn-text">Prescription</span>
                        </a>
                        <a href="{{ route('ipd-patients.create', ['patient_id' => $opdPatient->patient_id, 'from_opd_id' => $opdPatient->id]) }}"
                            class="btn btn-sm btn-outline-info">
                            <i class="bi bi-arrow-left-right me-1"></i><span class="btn-text">Move to Ipd</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#opdRadiologyModal">
                            <i class="bi bi-radioactive me-1"></i><span class="btn-text">Radiology Entry</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#opdPathologyModal">
                            <i class="bi bi-eyedropper me-1"></i><span class="btn-text">Pathology Entry</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs custom-tabs mb-0" id="patientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                            data-bs-target="#overview-pane" type="button" role="tab" aria-controls="overview-pane"
                            aria-selected="true">
                            <i class="bi bi-grid-1x2-fill me-1"></i>Overview
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visits-pane"
                            type="button" role="tab" aria-controls="visits-pane" aria-selected="false">
                            <i class="bi bi-calendar2-check-fill me-1"></i>Visits
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prescription-tab" data-bs-toggle="tab" data-bs-target="#prescription-pane"
                            type="button" role="tab" aria-controls="prescription-pane" aria-selected="false">
                            <i class="bi bi-capsule me-1"></i>Prescription
                        </button>
                    </li>

                    {{-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="medication-tab" data-bs-toggle="tab" data-bs-target="#medication-pane"
                            type="button" role="tab" aria-controls="medication-pane" aria-selected="false">
                            <i class="bi bi-capsule-pill me-1"></i>Medication
                        </button>
                    </li> --}}

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab-pane"
                            type="button" role="tab" aria-controls="lab-pane" aria-selected="false">
                            <i class="bi bi-clipboard2-pulse-fill me-1"></i>Lab Investigation
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="charges-tab" data-bs-toggle="tab" data-bs-target="#charges-pane"
                            type="button" role="tab" aria-controls="charges-pane" aria-selected="false">
                            <i class="bi bi-receipt-cutoff me-1"></i>Charges
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-pane"
                            type="button" role="tab" aria-controls="payments-pane" aria-selected="false">
                            <i class="bi bi-cash-coin me-1"></i>Payments
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="treatment-tab" data-bs-toggle="tab"
                            data-bs-target="#treatment-pane" type="button" role="tab"
                            aria-controls="treatment-pane" aria-selected="false">
                            <i class="bi bi-journal-medical me-1"></i>Treatment History
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-pane"
                            type="button" role="tab" aria-controls="timeline-pane" aria-selected="false">
                            <i class="bi bi-clock-history me-1"></i>Timeline
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals-pane"
                            type="button" role="tab" aria-controls="vitals-pane" aria-selected="false">
                            <i class="bi bi-heart-pulse-fill me-1"></i>Vitals
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="soap-tab" data-bs-toggle="tab" data-bs-target="#soap-pane"
                            type="button" role="tab" aria-controls="soap-pane" aria-selected="false">
                            <i class="bi bi-clipboard2-pulse me-1"></i>SOAP Note
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane"
                            type="button" role="tab" aria-controls="history-pane" aria-selected="false">
                            <i class="bi bi-journal-medical me-1"></i>Patient History
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body bg-light-subtle p-2 p-md-3">
                <div class="tab-content">

                    {{-- OVERVIEW --}}
                    <div class="tab-pane fade show active" id="overview-pane" role="tabpanel"
                        aria-labelledby="overview-tab">
                        <x-opd.overview :opdPatient="$opdPatient" />
                    </div>

                    {{-- VISITS --}}
                    <div class="tab-pane fade" id="visits-pane" role="tabpanel" aria-labelledby="visits-tab">
                        <x-opd.visit :opdPatient="$opdPatient" />
                    </div>

                    {{-- PRESCRIPTION --}}
                    <div class="tab-pane fade" id="prescription-pane" role="tabpanel" aria-labelledby="prescription-tab">
                        <x-opd.prescriptions :opdPatient="$opdPatient" />
                    </div>

                    {{-- MEDICATION --}}
                    <div class="tab-pane fade" id="medication-pane" role="tabpanel" aria-labelledby="medication-tab">
                        <x-opd.medications :opdPatient="$opdPatient" />
                    </div>

                    {{-- LAB --}}
                    <div class="tab-pane fade" id="lab-pane" role="tabpanel" aria-labelledby="lab-tab">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-clipboard2-pulse-fill me-2 text-danger"></i>Lab Investigation History
                                </h6>
                                <span class="badge bg-secondary">{{ $labOrders->count() }} order(s)</span>
                            </div>
                            <div class="card-body p-0">
                                @if ($labOrders->isEmpty())
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-clipboard2-x fs-3 d-block mb-2"></i>No lab orders found for this patient.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0 custom-table">
                                            <thead>
                                                <tr>
                                                    <th>Order No</th>
                                                    <th>Type</th>
                                                    <th>Investigations</th>
                                                    <th>Doctor</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($labOrders as $order)
                                                    @php
                                                        $total     = $order->requests->count();
                                                        $completed = $order->requests->where('status', 'Result Ready')->count()
                                                                   + $order->requests->where('status', 'Completed')->count();
                                                        $pending   = $total - $completed;
                                                        if ($total === 0) {
                                                            $statusBadge = '<span class="badge bg-secondary">No Tests</span>';
                                                        } elseif ($pending === 0) {
                                                            $statusBadge = '<span class="badge bg-success">Completed</span>';
                                                        } elseif ($completed === 0) {
                                                            $statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                                                        } else {
                                                            $statusBadge = "<span class=\"badge bg-info text-dark\">{$completed}/{$total} Done</span>";
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td><span class="fw-semibold">{{ $order->order_number }}</span></td>
                                                        <td>
                                                            @if ($order->type === 'radiology')
                                                                <span class="badge bg-warning text-dark">Radiology</span>
                                                            @else
                                                                <span class="badge bg-danger">Pathology</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @forelse ($order->requests as $req)
                                                                <div class="text-truncate" style="max-width:220px;" title="{{ $req->labInvestigation->name ?? '-' }}">
                                                                    <small>
                                                                        @if ($req->labInvestigationCategory)
                                                                            <span class="text-muted">{{ $req->labInvestigationCategory->name }}:</span>
                                                                        @endif
                                                                        {{ $req->labInvestigation->name ?? '-' }}
                                                                    </small>
                                                                </div>
                                                            @empty
                                                                <span class="text-muted small">—</span>
                                                            @endforelse
                                                        </td>
                                                        <td>{{ $order->doctor->name ?? '—' }}</td>
                                                        <td>{{ $order->datetime ? $order->datetime->format('d M Y H:i') : '—' }}</td>
                                                        <td>{!! $statusBadge !!}</td>
                                                        <td class="text-center">
                                                            <a href="{{ route(strtolower($order->type) . '.show', $order->id) }}"
                                                               target="_blank"
                                                               class="btn btn-xs btn-outline-secondary"
                                                               title="View Order">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- CHARGES --}}
                    <div class="tab-pane fade" id="charges-pane" role="tabpanel" aria-labelledby="charges-tab">
                        <x-opd.charges :opdPatient="$opdPatient" />
                    </div>

                    {{-- PAYMENTS --}}
                    <div class="tab-pane fade" id="payments-pane" role="tabpanel" aria-labelledby="payments-tab">
                        <x-opd.payments :opdPatient="$opdPatient" />
                    </div>

                    {{-- TREATMENT --}}
                    <div class="tab-pane fade" id="treatment-pane" role="tabpanel" aria-labelledby="treatment-tab">
                        <x-opd.treatment-history :opdPatient="$opdPatient" />
                    </div>

                    {{-- TIMELINE --}}
                    <div class="tab-pane fade" id="timeline-pane" role="tabpanel" aria-labelledby="timeline-tab">
                        <x-opd.timeline :opdPatient="$opdPatient" />
                    </div>

                    {{-- VITALS --}}
                    <div class="tab-pane fade" id="vitals-pane" role="tabpanel" aria-labelledby="vitals-tab">
                        <x-opd.vital-checks :opdPatient="$opdPatient" />
                    </div>

                    {{-- SOAP NOTE --}}
                    <div class="tab-pane fade" id="soap-pane" role="tabpanel" aria-labelledby="soap-tab">
                        @include('opd_patients.consultation-note', ['opdPatient' => $opdPatient])
                    </div>

                    {{-- PATIENT HISTORY --}}
                    <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
                        @include('opd_patients.patient-history', ['opdPatient' => $opdPatient, 'patientHistories' => $patientHistories])
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Radiology Entry Modal --}}
    <div class="modal fade" id="opdRadiologyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('radiology.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-radioactive me-2 text-warning"></i>Radiology Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="patient_id" value="{{ $opdPatient->patient_id }}">
                        <input type="hidden" name="opd_id" value="{{ $opdPatient->id }}">
                        <input type="hidden" name="case_id" value="{{ $opdPatient->case_id }}">
                        <input type="hidden" name="redirect_to" value="{{ parse_url(route('opd-patients.show', $opdPatient->id), PHP_URL_PATH) }}?tab=lab">
                        @if ($radiologyType)
                            <input type="hidden" name="lab_inv_type_id" value="{{ $radiologyType->id }}">
                        @else
                            <div class="alert alert-warning py-2">Radiology investigation type not configured.</div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient</label>
                                <input type="text" class="form-control" value="{{ $opdPatient->patient->patient_name ?? '' }} ({{ $opdPatient->patient->mrn ?? '' }})" disabled>
                            </div>

                            <div class="col-md-6">
                                <label for="rad_doctor_id" class="form-label">Doctor</label>
                                <select name="doctor_id" id="rad_doctor_id" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected($opdPatient->doctor_id == $doctor->id)>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="rad_datetime" class="form-label">Date/Time</label>
                                <input type="datetime-local" name="datetime" id="rad_datetime" class="form-control"
                                    value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="rad_lab_name" class="form-label">Lab Name</label>
                                <input type="text" name="lab_name" id="rad_lab_name" class="form-control" placeholder="Enter lab name">
                            </div>

                            <div class="col-md-6">
                                <label for="rad_collected_by" class="form-label">Collected By</label>
                                <input type="text" name="collected_by" id="rad_collected_by" class="form-control" placeholder="Enter collector name">
                            </div>

                            <div class="col-md-6">
                                <label for="rad_remarks" class="form-label">Remarks</label>
                                <input type="text" name="remarks" id="rad_remarks" class="form-control" placeholder="Enter remarks">
                            </div>

                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <label class="form-label mb-0 fw-bold">Investigations</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="opd-rad-add-row">
                                            <i class="bi bi-plus-lg"></i> Add Investigation
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="table-responsive" style="overflow: visible;">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:45%;">Category</th>
                                                        <th style="width:45%;">Investigation</th>
                                                        <th style="width:10%;" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="opd-rad-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Save Radiology Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pathology Entry Modal --}}
    <div class="modal fade" id="opdPathologyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('pathology.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-eyedropper me-2 text-danger"></i>Pathology Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="patient_id" value="{{ $opdPatient->patient_id }}">
                        <input type="hidden" name="opd_id" value="{{ $opdPatient->id }}">
                        <input type="hidden" name="case_id" value="{{ $opdPatient->case_id }}">
                        <input type="hidden" name="redirect_to" value="{{ parse_url(route('opd-patients.show', $opdPatient->id), PHP_URL_PATH) }}?tab=lab">
                        @if ($pathologyType)
                            <input type="hidden" name="lab_inv_type_id" value="{{ $pathologyType->id }}">
                        @else
                            <div class="alert alert-warning py-2">Pathology investigation type not configured.</div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient</label>
                                <input type="text" class="form-control" value="{{ $opdPatient->patient->patient_name ?? '' }} ({{ $opdPatient->patient->mrn ?? '' }})" disabled>
                            </div>

                            <div class="col-md-6">
                                <label for="path_doctor_id" class="form-label">Doctor</label>
                                <select name="doctor_id" id="path_doctor_id" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected($opdPatient->doctor_id == $doctor->id)>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="path_datetime" class="form-label">Date/Time</label>
                                <input type="datetime-local" name="datetime" id="path_datetime" class="form-control"
                                    value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="path_priority" class="form-label">Priority</label>
                                <select name="priority" id="path_priority" class="form-select">
                                    <option value="Regular">Regular</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="STAT">STAT</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="path_lab_name" class="form-label">Lab Name</label>
                                <input type="text" name="lab_name" id="path_lab_name" class="form-control" placeholder="Enter lab name">
                            </div>

                            <div class="col-md-6">
                                <label for="path_collected_by" class="form-label">Collected By</label>
                                <input type="text" name="collected_by" id="path_collected_by" class="form-control" placeholder="Enter collector name">
                            </div>

                            <div class="col-12">
                                <label for="path_remarks" class="form-label">Remarks</label>
                                <input type="text" name="remarks" id="path_remarks" class="form-control" placeholder="Enter remarks">
                            </div>

                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <label class="form-label mb-0 fw-bold">Investigations</label>
                                        <button type="button" class="btn btn-sm btn-primary" id="opd-path-add-row">
                                            <i class="bi bi-plus-lg"></i> Add Investigation
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="table-responsive" style="overflow: visible;">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:45%;">Category</th>
                                                        <th style="width:45%;">Investigation</th>
                                                        <th style="width:10%;" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="opd-path-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Pathology Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initLabModal(tbodyId, addBtnId, categories, investigations) {
                const tbody = document.getElementById(tbodyId);
                const addBtn = document.getElementById(addBtnId);
                if (!tbody || !addBtn) return;
                let rowIndex = 0;

                function buildOptions(el, items, placeholder, selectedVal) {
                    el.innerHTML = `<option value="">${placeholder}</option>` +
                        items.map(i => `<option value="${i.id}" ${String(selectedVal) === String(i.id) ? 'selected' : ''}>${i.name}</option>`).join('');
                }

                function refreshInv(catSel, invSel, selectedInv) {
                    const cid = catSel.value;
                    buildOptions(invSel, investigations.filter(i => !cid || String(i.category_id) === String(cid)), '-- Select Investigation --', selectedInv || '');
                }

                function buildRow() {
                    const idx = rowIndex++;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><select name="requests[${idx}][lab_inv_category_id]" class="form-select form-select-sm js-cat"></select></td>
                        <td><select name="requests[${idx}][lab_inv]" class="form-select form-select-sm js-inv" required></select></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger js-rm" title="Remove"><i class="bi bi-trash"></i></button></td>`;
                    tbody.appendChild(tr);
                    const catSel = tr.querySelector('.js-cat');
                    const invSel = tr.querySelector('.js-inv');
                    buildOptions(catSel, categories, '-- Select Category --', '');
                    refreshInv(catSel, invSel, '');
                }

                tbody.addEventListener('change', e => {
                    if (e.target.classList.contains('js-cat')) {
                        refreshInv(e.target, e.target.closest('tr').querySelector('.js-inv'), '');
                    }
                });
                tbody.addEventListener('click', e => {
                    const btn = e.target.closest('.js-rm');
                    if (btn) btn.closest('tr').remove();
                });
                addBtn.addEventListener('click', buildRow);
                buildRow();
            }

            // Restore active tab from ?tab= query param (survives redirects & refresh)
            const tabParam = new URLSearchParams(window.location.search).get('tab');
            if (tabParam) {
                const tabEl = document.querySelector(`#patientTabs button[data-bs-target="#${tabParam}-pane"]`);
                if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
            }

            // Persist active tab in URL so refresh stays on same tab
            document.querySelectorAll('#patientTabs button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', function (e) {
                    const pane  = e.target.getAttribute('data-bs-target'); // e.g. "#soap-pane"
                    const name  = pane.replace('#', '').replace('-pane', ''); // e.g. "soap"
                    const url   = new URL(window.location);
                    url.searchParams.set('tab', name);
                    history.replaceState(null, '', url.toString());
                });
            });

            initLabModal(
                'opd-rad-tbody', 'opd-rad-add-row',
                @json($radCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
                @json($radInvestigations->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'category_id' => $i->category_id])->values())
            );

            initLabModal(
                'opd-path-tbody', 'opd-path-add-row',
                @json($pathCategories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
                @json($pathInvestigations->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'category_id' => $i->category_id])->values())
            );
        });
    </script>
@endpush

@push('styles')
    <style>
        .opd-view-card {
            background: #f8fafc;
        }

        .opd-view-card .card-header h5 {
            font-size: clamp(15px, 1.6vw, 18px);
        }

        .header-actions .btn {
            white-space: nowrap;
        }

        .custom-tabs {
            padding: 0 10px;
            overflow-x: auto;
            overflow-y: hidden;
            flex-wrap: nowrap;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        .custom-tabs::-webkit-scrollbar {
            height: 6px;
        }

        .custom-tabs::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-tabs .nav-link {
            border: 0;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            color: #495057;
            font-size: 13px;
            font-weight: 600;
            padding: 12px 14px;
            background: transparent;
        }

        .custom-tabs .nav-link:hover {
            color: #0d6efd;
            background: rgba(13, 110, 253, 0.04);
        }

        .custom-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background: #fff;
        }

        .patient-photo {
            width: 130px;
            height: 130px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .patient-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-item {
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            gap: 10px;
            font-size: 13px;
            align-items: start;
        }

        .info-label {
            font-weight: 700;
            color: #344054;
            min-width: 0;
            word-break: break-word;
        }

        .info-value {
            color: #475467;
            min-width: 0;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .mini-box {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
            background: #fff;
        }

        .mini-title {
            font-size: 12px;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .barcode-demo {
            font-size: 24px;
            line-height: 1;
            letter-spacing: 1px;
        }

        .barcode-text {
            font-size: 11px;
            margin-top: 4px;
            font-weight: 700;
        }

        .qr-demo {
            width: 90px;
            height: 90px;
            border: 1px solid #dee2e6;
            padding: 4px;
            border-radius: 8px;
            background: #fff;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
            height: 100%;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }

        .custom-table thead th {
            font-size: 12px;
            font-weight: 700;
            color: #344054;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
            padding: 12px 10px;
        }

        .custom-table tbody td,
        .custom-table tfoot th,
        .custom-table tfoot td {
            font-size: 12.5px;
            color: #495057;
            padding: 12px 10px;
            vertical-align: middle;
        }

        .timeline-simple {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .timeline-item {
            position: relative;
            padding: 12px 14px;
            border-left: 4px solid #0d6efd;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 13px;
        }

        @media (min-width: 1200px) and (max-width: 1399.98px) {
            .info-item {
                grid-template-columns: 100px minmax(0, 1fr);
                font-size: 12.5px;
            }
        }

        @media (max-width: 991.98px) {
            .info-item {
                grid-template-columns: 110px minmax(0, 1fr);
            }
        }

        @media (max-width: 767.98px) {
            .custom-tabs .nav-link {
                font-size: 12px;
                padding: 10px 12px;
            }

            .info-item {
                grid-template-columns: 110px minmax(0, 1fr);
                gap: 6px;
            }

            .patient-photo {
                width: 110px;
                height: 110px;
            }

            .stat-value {
                font-size: 18px;
            }

            .stat-card {
                padding: 10px;
            }
        }

        .btn-xs {
            padding: .15rem .4rem;
            font-size: .75rem;
            line-height: 1.4;
            border-radius: .2rem;
        }

        #opdRadiologyModal .modal-body,
        #opdPathologyModal .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        @media (max-width: 575.98px) {
            .info-item {
                grid-template-columns: 1fr;
                gap: 2px;
            }

            .info-label {
                font-size: 11.5px;
                text-transform: uppercase;
                letter-spacing: .3px;
                color: #6c757d;
            }

            .header-actions .btn {
                padding: .25rem .5rem;
                font-size: 12px;
            }

            .header-actions .btn .btn-text {
                display: none;
            }

            .header-actions .btn i {
                margin-right: 0 !important;
            }
        }
    </style>
@endpush
