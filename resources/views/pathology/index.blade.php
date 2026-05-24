@extends('backend.layouts.master')

@section('title', 'Pathology')

@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="clearfix">
                <h1 class="app-page-title">Pathology Orders</h1>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Pathology Order
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mt-4">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div
                        class="card-header mb-2 d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                        <h6 class="card-title mb-2">Pathology Order List</h6>
                        <input type="text" id="pathologySearch" class="form-control form-control-sm mb-2"
                            style="max-width: 260px;" placeholder="Search...">
                    </div>

                    <div class="card-body px-3 pt-2 pb-0 gradient-layer">
                        <table id="pathologyTable" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order No.</th>
                                    <th>Date/Time</th>
                                    <th>Patient</th>
                                    <th>MRN</th>
                                    <th>Doctor</th>
                                    <th>Source</th>
                                    <th>Priority</th>
                                    <th>Lab</th>
                                    <th>Tests</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="pathologyTbody">
                                @forelse($orders as $i => $o)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="fw-bold">{{ $o->order_number }}</span></td>
                                        <td>{{ optional($o->datetime)->format('Y-m-d H:i') }}</td>
                                        <td>{{ optional($o->patient)->patient_name ?? '-' }}</td>
                                        <td>{{ optional($o->patient)->mrn ?? '-' }}</td>
                                        <td>{{ optional($o->doctor)->name ?? '-' }}</td>
                                        <td>{{ $o->source ?? '-' }}</td>
                                        <td>
                                            @if ($o->priority)
                                                @php
                                                    $priorityClass = match (strtolower($o->priority)) {
                                                        'urgent' => 'bg-danger',
                                                        'regular' => 'bg-success',
                                                        'stat' => 'bg-info',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge {{ $priorityClass }}">{{ ucfirst($o->priority) ?? '' }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $o->lab_name ?? '-' }}</td>
                                        <td>
                                            @forelse($o->requests as $r)
                                                <span
                                                    class="badge bg-success">{{ optional($r->labInvestigation)->name ?? '-' }}</span>
                                            @empty
                                                -
                                            @endforelse
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('pathology.show', $o->id) }}"
                                                class="btn btn-sm btn-info" title="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No pathology orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between p-3">
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0 small">Rows per page:</label>
                                <select id="rowsPerPage" class="form-select form-select-sm" style="width:auto;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Create Modal --}}
        <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('pathology.store') }}" method="POST">
                        @csrf
                        <div class="modal-header d-flex align-items-center">
                            <h5 class="modal-title">Create Pathology Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if ($pathologyType)
                                <input type="hidden" name="lab_inv_type_id" value="{{ $pathologyType->id }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Investigation Type</label>
                                    <select class="form-select" disabled>
                                        <option selected>{{ $pathologyType->name ?? 'Pathology' }}</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="patient_id" class="form-label">Patient <span
                                            class="text-danger">*</span></label>
                                    <select name="patient_id" id="patient_id" class="form-select" required>
                                        <option value="">-- Select Patient --</option>
                                        @foreach ($patients as $p)
                                            <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                                                {{ $p->patient_name }} ({{ $p->mrn }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="case_id" class="form-label">Case ID</label>
                                    <input type="number" name="case_id" id="case_id" class="form-control"
                                        value="{{ old('case_id') }}" placeholder="Leave blank to auto-generate">
                                    <small class="text-muted">Enter an existing case ID or leave empty to create a new
                                        case.</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="doctor_id" class="form-label">Doctor</label>
                                    <select name="doctor_id" id="doctor_id" class="form-select">
                                        <option value="">-- Select Doctor --</option>
                                        @foreach ($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                                {{ $doctor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="datetime" class="form-label">Date/Time</label>
                                    <input type="datetime-local" name="datetime" id="datetime" class="form-control"
                                        value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="lab_name" class="form-label">Lab Name</label>
                                    <input type="text" name="lab_name" id="lab_name" class="form-control"
                                        value="{{ old('lab_name') }}" placeholder="Enter lab name">
                                </div>

                                <div class="col-md-6">
                                    <label for="collected_by" class="form-label">Collected By</label>
                                    <input type="text" name="collected_by" id="collected_by" class="form-control"
                                        value="{{ old('collected_by') }}" placeholder="Enter collector name">
                                </div>

                                {{-- Priority --}}
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority"
                                        class="form-select @error('priority') is-invalid @enderror">
                                        <option value="Regular" @selected(old('priority', 'Regular') === 'Regular')>Regular</option>
                                        <option value="Urgent" @selected(old('priority') === 'Urgent')>Urgent</option>
                                        <option value="STAT" @selected(old('priority') === 'STAT')>STAT</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea name="remarks" id="remarks" rows="2" class="form-control" placeholder="Enter remarks">{{ old('remarks') }}</textarea>
                                </div>

                                <div class="col-12">
                                    <div class="card border">
                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <label class="form-label mb-0 fw-bold">Investigations</label>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                id="add-investigation-row">
                                                <i class="bi bi-plus-lg"></i> Add Investigation
                                            </button>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive" style="overflow: visible;">
                                                <table class="table table-bordered align-middle mb-0"
                                                    id="investigations-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 45%;">Category</th>
                                                            <th style="width: 45%;">Investigation</th>
                                                            <th style="width: 10%;" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="investigations-body">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create Modal - dynamic investigation rows
            (function() {
                const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());
                const investigations = @json($investigations->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'category_id' => $i->category_id])->values());
                const oldRequests = @json(old('requests', []));

                const tbody = document.getElementById('investigations-body');
                const addBtn = document.getElementById('add-investigation-row');
                const modalEl = document.getElementById('createModal');
                if (!tbody || !addBtn || !modalEl) return;
                let rowIndex = 0;

                function buildOptions(selectEl, items, placeholder, selectedVal) {
                    selectEl.innerHTML = `<option value="">${placeholder}</option>` +
                        items.map(i =>
                            `<option value="${i.id}" ${String(selectedVal) === String(i.id) ? 'selected' : ''}>${i.name}</option>`
                        ).join('');
                }

                function buildRow(catId = '', invId = '') {
                    const idx = rowIndex++;
                    const tr = document.createElement('tr');
                    tr.dataset.idx = idx;
                    tr.innerHTML = `
                        <td>
                            <select name="requests[${idx}][lab_inv_category_id]" class="form-select form-select-sm js-cat-select"></select>
                        </td>
                        <td>
                            <select name="requests[${idx}][lab_inv]" class="form-select form-select-sm js-inv-select" required></select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);

                    const catSelect = tr.querySelector('.js-cat-select');
                    const invSelect = tr.querySelector('.js-inv-select');

                    buildOptions(catSelect, categories, '-- Select Category --', catId);
                    refreshInvestigations(catSelect, invSelect, invId);
                }

                function refreshInvestigations(catSelect, invSelect, selectedInv = '') {
                    const cid = catSelect.value;
                    const filtered = investigations.filter(i => !cid || String(i.category_id) === String(cid));
                    buildOptions(invSelect, filtered, '-- Select Investigation --', selectedInv);
                }

                // Event delegation for category change and row removal
                tbody.addEventListener('change', function(e) {
                    if (e.target.classList.contains('js-cat-select')) {
                        const tr = e.target.closest('tr');
                        refreshInvestigations(e.target, tr.querySelector('.js-inv-select'));
                    }
                });
                tbody.addEventListener('click', function(e) {
                    const btn = e.target.closest('.js-remove-row');
                    if (btn) btn.closest('tr').remove();
                });

                addBtn.addEventListener('click', function() {
                    buildRow();
                });

                // Initialize immediately so the first row is visible when modal opens
                if (Array.isArray(oldRequests) && oldRequests.length) {
                    oldRequests.forEach(r => buildRow(r.lab_inv_category_id || '', r.lab_inv || ''));
                } else {
                    buildRow();
                }

                @if ($errors->any())
                    new bootstrap.Modal(modalEl).show();
                @endif
            })();

            // Client-side pagination + search
            const tbody = document.getElementById('pathologyTbody');
            const rowsSel = document.getElementById('rowsPerPage');
            const pagination = document.getElementById('pagination');
            const searchEl = document.getElementById('pathologySearch');
            let currentPage = 1;

            function allRows() {
                return Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.classList.contains('empty-row'));
            }

            function filteredRows() {
                const q = (searchEl.value || '').toLowerCase().trim();
                if (!q) return allRows();
                return allRows().filter(tr => tr.textContent.toLowerCase().includes(q));
            }

            function render() {
                const rows = allRows();
                const matched = filteredRows();
                const perPage = parseInt(rowsSel.value, 10) || 10;
                const totalPages = Math.max(1, Math.ceil(matched.length / perPage));
                if (currentPage > totalPages) currentPage = totalPages;

                const matchedSet = new Set(matched);
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                rows.forEach(tr => {
                    tr.style.display = 'none';
                });
                matched.slice(start, end).forEach(tr => {
                    tr.style.display = '';
                });

                // Build pagination with ellipsis
                pagination.innerHTML = '';
                const addItem = (label, page, opts = {}) => {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (opts.disabled ? ' disabled' : '') + (opts.active ? ' active' :
                        '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = 'javascript:void(0)';
                    a.textContent = label;
                    if (!opts.disabled && page != null) {
                        a.addEventListener('click', () => {
                            currentPage = page;
                            render();
                        });
                    }
                    li.appendChild(a);
                    pagination.appendChild(li);
                };

                addItem('«', currentPage - 1, {
                    disabled: currentPage === 1
                });

                const pages = [];
                const addPage = (p) => {
                    if (!pages.includes(p) && p >= 1 && p <= totalPages) pages.push(p);
                };
                addPage(1);
                for (let p = currentPage - 1; p <= currentPage + 1; p++) addPage(p);
                addPage(totalPages);
                pages.sort((a, b) => a - b);

                let prev = 0;
                pages.forEach(p => {
                    if (p - prev > 1) addItem('…', null, {
                        disabled: true
                    });
                    addItem(String(p), p, {
                        active: p === currentPage
                    });
                    prev = p;
                });

                addItem('»', currentPage + 1, {
                    disabled: currentPage === totalPages
                });
            }

            rowsSel.addEventListener('change', () => {
                currentPage = 1;
                render();
            });
            searchEl.addEventListener('input', () => {
                currentPage = 1;
                render();
            });
            render();
        });
    </script>
@endpush

@push('styles')
    <style>
        #createModal .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        #createModal #investigations-table tbody tr {
            background: #fff;
        }

        #createModal #investigations-table tbody tr td {
            padding: 8px;
            vertical-align: middle;
        }

        #createModal #investigations-table .form-select-sm {
            min-height: 34px;
        }
    </style>
@endpush
