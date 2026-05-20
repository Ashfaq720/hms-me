@php
    $statuses = ['Admitted', 'Discharged', 'Transferred', 'Cancelled'];
    $types = ['Emergency', 'Planned', 'Unplanned', 'Transfer(Internal)', 'Transfer(External)'];
    $genders = ['Male', 'Female', 'Other'];
    $bloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
@endphp

<div class="row g-3">

    {{-- ===== Ipd ADMISSION INFO ===== --}}
    <div class="col-12 mt-4">
        <div class="accordion" id="ipdAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="ipdHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#ipdCollapse" aria-expanded="true"
                        aria-controls="ipdCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Ipd Admission</span>
                            </div>
                            <span class="badge text-bg-light border">Step 1</span>
                        </div>
                    </button>
                </h2>

                <div id="ipdCollapse" class="accordion-collapse collapse show" aria-labelledby="ipdHeading"
                    data-bs-parent="#ipdAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">


                            {{-- Patient Name  --}}
                            <div class="col-md-12">
                                <label class="form-label">Patient Name <span class="text-danger">*</span></label>
                                <select name="patient_id" class="form-select" required disabled>
                                    <option value="">-- Select --</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}" @selected(old('patient_id', $ipdPatient->patient_id ?? '') == $patient->id)>
                                            {{ $patient->patient_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" name="patient_id" value="{{ old('patient_id', $ipdPatient->patient_id ?? '') }}">
                            {{-- Department --}}
                            <div class="col-md-4">
                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($departments as $dep)
                                        <option value="{{ $dep->id }}" @selected(old('department_id', $ipdPatient->department_id ?? '') == $dep->id)>
                                            {{ $dep->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Doctor --}}
                            <div class="col-md-4">
                                <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select name="doctor_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}" @selected(old('doctor_id', $ipdPatient->doctor_id ?? '') == $d->id)>
                                            {{ $d->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Admission Date --}}
                            <div class="col-md-4">
                                <label class="form-label">Admission Date <span class="text-danger">*</span></label>

                                <input type="datetime-local" name="admission_date"
                                    value="{{ old('admission_date', isset($ipdPatient) ? $ipdPatient->admission_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                    class="form-control" required>

                                @error('admission_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>


                            {{-- Possible Discharge --}}
                            <div class="col-md-4">
                                <label class="form-label">Possible Discharge Date</label>
                                <input type="datetime-local" name="possible_discharge_date"
                                    value="{{ old('possible_discharge_date', optional($ipdPatient->possible_discharge_date ?? null)->format('Y-m-d H:i')) }}"
                                    class="form-control">
                                @error('possible_discharge_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Admission Type --}}
                            <div class="col-md-4">
                                <label class="form-label">Admission Type </label>
                                <select name="admission_type" class="form-select">
                                    <option value="">-- Select --</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old('admission_type', $ipdPatient->admission_type ?? '') === $type)>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('admission_type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="ipd_status" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($statuses as $st)
                                        <option value="{{ $st }}" @selected(old('status', $ipdPatient->status ?? 'Admitted') === $st)>
                                            {{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ipd_status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- Patient History --}}
                            <div class="col-md-12">
                                <label class="form-label">Patient History</label>
                                <textarea type="text" name="patient_history" class="form-control" placeholder="Short history / Reason for admission"
                                    cols="4">{{ old('patient_history', $ipdPatient->patient_history ?? '') }}</textarea>
                                @error('patient_history')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea type="text" name="remarks" class="form-control" placeholder="Notes / Initial Diagnosis" cols="4">{{ old('remarks', $ipdPatient->remarks ?? '') }}</textarea>
                                @error('remarks')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    {{-- ===== BED ALLOCATION ===== --}}
    <div class="col-12 mt-2">
        <div class="accordion" id="bedAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="bedHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#bedCollapse" aria-expanded="true"
                        aria-controls="bedCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Bed Allocation</span>
                            </div>
                            <span class="badge text-bg-light border">Step 2</span>
                        </div>
                    </button>
                </h2>

                <div id="bedCollapse" class="accordion-collapse collapse show" aria-labelledby="bedHeading"
                    data-bs-parent="#bedAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">

                            {{-- Bed --}}
                            <div class="col-md-6">
                                <label class="form-label">Bed <span class="text-danger">*</span></label>
                                <select name="bed_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($beds as $b)
                                        <option value="{{ $b->id }}" @selected(old('bed_id', $bedAllocation->bed_id ?? '') == $b->id)>
                                            {{ $b->name }} (৳ {{ $b->rent }})
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Hidden input so value submits --}}
                                {{-- <input type="hidden" name="bed_id"
                                    value="{{ old('bed_id', $bedAllocation->bed_id ?? '') }}"> --}}

                                @error('bed_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- From --}}
                            <div class="col-md-6">
                                <label class="form-label">From <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="from"
                                    value="{{ old('from', isset($bedAllocation->from) ? \Carbon\Carbon::parse($bedAllocation->from)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                    class="form-control" required readonly>
                                @error('from')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- To --}}
                            {{-- <div class="col-md-4">
                                <label class="form-label">To</label>
                                <input type="datetime-local" name="to"
                                    value="{{ old('to', isset($bedAllocation->to) ? \Carbon\Carbon::parse($bedAllocation->to)->format('Y-m-d\TH:i') : '') }}"
                                    class="form-control">
                                @error('to')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            {{-- Bed Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="bed_remarks" class="form-control" placeholder="Shifting reason / bed note" cols="3">{{ old('bed_remarks', $bedAllocation->remarks ?? '') }}</textarea>
                                @error('bed_remarks')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    {{-- ===== Patient Documents ===== --}}
    <div class="col-12 mt-2">
        <div class="accordion" id="documentsAccordion">
            <div class="accordion-item border shadow-sm rounded-3 mb-2">
                <h2 class="accordion-header" id="documentsHeading">
                    <button class="accordion-button text-white" style="background-color:#2b335d;" type="button"
                        data-bs-toggle="collapse" data-bs-target="#documentsCollapse" aria-expanded="true"
                        aria-controls="documentsCollapse">
                        <div class="d-flex w-100 align-items-center justify-content-between pe-2">
                            <div>
                                <span class="fw-bold">Patient Documents</span>
                            </div>
                            <span class="badge text-bg-light border">Step 3</span>
                        </div>
                    </button>
                </h2>

                <div id="documentsCollapse" class="accordion-collapse collapse show"
                    aria-labelledby="documentsHeading" data-bs-parent="#documentsAccordion">
                    <div class="accordion-body">
                        @if (!empty($ipdPatient->documents) && $ipdPatient->documents->count())
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Title</th>
                                            <th style="width: 30%;">File</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ipdPatient->documents as $doc)
                                            <tr>
                                                <td>{{ $doc->title ?? '—' }}</td>
                                                <td>
                                                    @if (!empty($doc->file))
                                                        <a href="{{ asset('storage/' . $doc->file) }}" target="_blank">
                                                            <i class="bi bi-file-earmark"></i> View
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $doc->remarks ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-2" id="documentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Title</th>
                                        <th style="width: 30%;">File</th>
                                        <th>Remarks</th>
                                        <th style="width: 60px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="documentsTbody">
                                    <tr class="document-row">
                                        <td>
                                            <input type="text" name="documents[0][title]" class="form-control"
                                                placeholder="e.g. Lab Report">
                                        </td>
                                        <td>
                                            <input type="file" name="documents[0][file]" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" name="documents[0][remarks]" class="form-control"
                                                placeholder="Optional notes">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-document"
                                                title="Remove">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addDocumentBtn">
                            <i class="bi bi-plus-lg"></i> Add Document
                        </button>
                        @error('documents')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('documents.*.file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tbody = document.getElementById('documentsTbody');
                const addBtn = document.getElementById('addDocumentBtn');
                let docIndex = 1;

                function rebuildIndexes() {
                    Array.from(tbody.querySelectorAll('tr.document-row')).forEach(function(row, i) {
                        row.querySelectorAll('input').forEach(function(input) {
                            const name = input.getAttribute('name');
                            if (!name) return;
                            input.setAttribute('name', name.replace(/documents\[\d+\]/, 'documents[' + i + ']'));
                        });
                    });
                }

                addBtn.addEventListener('click', function() {
                    const row = document.createElement('tr');
                    row.className = 'document-row';
                    row.innerHTML = `
                        <td><input type="text" name="documents[${docIndex}][title]" class="form-control" placeholder="e.g. Lab Report"></td>
                        <td><input type="file" name="documents[${docIndex}][file]" class="form-control"></td>
                        <td><input type="text" name="documents[${docIndex}][remarks]" class="form-control" placeholder="Optional notes"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>`;
                    tbody.appendChild(row);
                    docIndex++;
                });

                tbody.addEventListener('click', function(e) {
                    const btn = e.target.closest('.remove-document');
                    if (!btn) return;
                    const rows = tbody.querySelectorAll('tr.document-row');
                    if (rows.length <= 1) {
                        btn.closest('tr').querySelectorAll('input').forEach(function(i) {
                            if (i.type === 'file') i.value = '';
                            else i.value = '';
                        });
                    } else {
                        btn.closest('tr').remove();
                        rebuildIndexes();
                    }
                });
            });
        </script>
    @endpush

</div>

<style>
    .rounded-box {
        background: #F4F4F4;
        border: 1px solid #a6a6a6;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .04);
    }

    #btnExisting.active,
    #btnNew.active {
        color: #fff !important;
    }
</style>
