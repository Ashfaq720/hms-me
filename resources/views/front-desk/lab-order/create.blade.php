@extends('backend.layouts.master')

@section('title', 'Create Lab Orders')

@section('content')
<div class="container-fluid">

    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <h1 class="app-page-title">Create Lab Orders</h1>
            <p class="text-muted mb-0 small">Patient registered. Now select the required lab tests below.</p>
        </div>
        <a href="{{ route('front_desk.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Front Desk
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Patient Info Card --}}
    <div class="card mt-4 border-primary">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;font-size:1.4rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-6">{{ $patient->patient_name }}</div>
                    <div class="text-muted small">
                        MRN: <span class="fw-semibold">{{ $patient->mrn }}</span>
                        @if($patient->mobileno) &nbsp;|&nbsp; {{ $patient->mobileno }} @endif
                        @if($patient->gender) &nbsp;|&nbsp; {{ ucfirst($patient->gender) }} @endif
                    </div>
                </div>
                <div class="col-auto text-end">
                    <span class="badge bg-info text-dark">Case #{{ $case->id }}</span>
                    <span class="badge bg-warning text-dark ms-1">LAB ONLY</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('front_desk.lab_order.store') }}" method="POST" id="labOrderForm">
        @csrf
        <input type="hidden" name="case_id"    value="{{ $case->id }}">
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

        {{-- These hidden inputs are toggled by JS based on lab type selection --}}
        <input type="hidden" name="pathology_enabled" id="pathologyEnabledInput" value="0">
        <input type="hidden" name="radiology_enabled" id="radiologyEnabledInput" value="0">

        {{-- ===== LAB TYPE SELECTOR ===== --}}
        @php
            $oldLabType = old('lab_type', 'pathology');
        @endphp
        <div class="card mt-4">
            <div class="card-body py-3">
                <label class="form-label fw-semibold mb-3">Select Lab Type</label>
                <div class="d-flex flex-wrap gap-3" id="labTypeSelector">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lab_type" id="ltPathology"
                               value="pathology" {{ $oldLabType === 'pathology' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="ltPathology">
                            <i class="bi bi-droplet-fill text-danger me-1"></i> Pathology Only
                            <span class="text-muted fw-normal small">(Blood tests, urine, culture, etc.)</span>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lab_type" id="ltRadiology"
                               value="radiology" {{ $oldLabType === 'radiology' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="ltRadiology">
                            <i class="bi bi-activity text-primary me-1"></i> Radiology Only
                            <span class="text-muted fw-normal small">(X-Ray, MRI, CT Scan, USG, etc.)</span>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="lab_type" id="ltBoth"
                               value="both" {{ $oldLabType === 'both' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="ltBoth">
                            <i class="bi bi-collection text-success me-1"></i> Both
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 g-4" id="labSectionsRow">

            {{-- ===== PATHOLOGY SECTION ===== --}}
            <div class="col-12" id="pathologySection" style="display:none;">
                <div class="card h-100" id="pathologyCard">
                    <div class="card-header d-flex align-items-center gap-2"
                         style="background:rgba(220,53,69,.08); border-bottom:2px solid #dc3545;">
                        <i class="bi bi-droplet-fill text-danger me-1"></i>
                        <span class="fw-bold fs-6">Pathology</span>
                        <span class="text-muted small">(Blood tests, urine, culture, etc.)</span>
                    </div>

                    <div class="card-body">
                        @if($pathologyType)
                            <input type="hidden" name="pathology_lab_inv_type_id" value="{{ $pathologyType->id }}">
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Priority</label>
                                <select name="pathology_priority" class="form-select form-select-sm">
                                    <option value="Regular" @selected(old('pathology_priority','Regular')==='Regular')>Regular</option>
                                    <option value="Urgent"  @selected(old('pathology_priority')==='Urgent')>Urgent</option>
                                    <option value="STAT"    @selected(old('pathology_priority')==='STAT')>STAT</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referring Doctor</label>
                                <select name="pathology_doctor_id" class="form-select form-select-sm">
                                    <option value="">-- Optional --</option>
                                    @foreach($doctors as $d)
                                        <option value="{{ $d->id }}" @selected(old('pathology_doctor_id')==$d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="pathology_remarks" rows="2" class="form-control form-control-sm"
                                          placeholder="Optional remarks">{{ old('pathology_remarks') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0 fw-semibold">Tests</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPathRow">
                                        <i class="bi bi-plus-lg"></i> Add Test
                                    </button>
                                </div>
                                <table class="table table-bordered table-sm align-middle" id="pathTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:45%">Category</th>
                                            <th style="width:45%">Test <span class="text-danger">*</span></th>
                                            <th style="width:10%" class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="pathTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== RADIOLOGY SECTION ===== --}}
            <div class="col-12" id="radiologySection" style="display:none;">
                <div class="card h-100" id="radiologyCard">
                    <div class="card-header d-flex align-items-center gap-2"
                         style="background:rgba(13,110,253,.08); border-bottom:2px solid #0d6efd;">
                        <i class="bi bi-activity text-primary me-1"></i>
                        <span class="fw-bold fs-6">Radiology</span>
                        <span class="text-muted small">(X-Ray, MRI, CT Scan, USG, etc.)</span>
                    </div>

                    <div class="card-body">
                        @if($radiologyType)
                            <input type="hidden" name="radiology_lab_inv_type_id" value="{{ $radiologyType->id }}">
                        @endif

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Referring Doctor</label>
                                <select name="radiology_doctor_id" class="form-select form-select-sm">
                                    <option value="">-- Optional --</option>
                                    @foreach($doctors as $d)
                                        <option value="{{ $d->id }}" @selected(old('radiology_doctor_id')==$d->id)>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="radiology_remarks" rows="2" class="form-control form-control-sm"
                                          placeholder="Optional remarks">{{ old('radiology_remarks') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0 fw-semibold">Tests</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRadRow">
                                        <i class="bi bi-plus-lg"></i> Add Test
                                    </button>
                                </div>
                                <table class="table table-bordered table-sm align-middle" id="radTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:45%">Category</th>
                                            <th style="width:45%">Test <span class="text-danger">*</span></th>
                                            <th style="width:10%" class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="radTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
            <a href="{{ route('front_desk.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-1"></i> Save Lab Orders &amp; Print Slip
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data from server ──────────────────────────────────────────
    const pathCategories     = @json($pathologyCategories->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->values());
    const pathInvestigations = @json($pathologyInvestigations->map(fn($i)=>['id'=>$i->id,'name'=>$i->name,'category_id'=>$i->category_id])->values());
    const radCategories      = @json($radiologyCategories->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->values());
    const radInvestigations  = @json($radiologyInvestigations->map(fn($i)=>['id'=>$i->id,'name'=>$i->name,'category_id'=>$i->category_id])->values());

    const pathSection        = document.getElementById('pathologySection');
    const radSection         = document.getElementById('radiologySection');
    const pathEnabledInput   = document.getElementById('pathologyEnabledInput');
    const radEnabledInput    = document.getElementById('radiologyEnabledInput');

    function setDisabled(section, disabled) {
        section.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = disabled;
        });
    }

    // Make pathology/radiology sections side-by-side when "Both" is selected
    function applyLayout(type) {
        const showPath = (type === 'pathology' || type === 'both');
        const showRad  = (type === 'radiology' || type === 'both');

        pathSection.style.display = showPath ? '' : 'none';
        radSection.style.display  = showRad  ? '' : 'none';

        // Disable hidden section inputs so they are not submitted
        setDisabled(pathSection, !showPath);
        setDisabled(radSection,  !showRad);

        pathEnabledInput.value = showPath ? '1' : '0';
        radEnabledInput.value  = showRad  ? '1' : '0';

        if (type === 'both') {
            pathSection.className = 'col-12 col-xl-6';
            radSection.className  = 'col-12 col-xl-6';
        } else {
            pathSection.className = 'col-12';
            radSection.className  = 'col-12';
        }
    }

    // ── Lab type radio change ─────────────────────────────────────
    document.querySelectorAll('input[name="lab_type"]').forEach(radio => {
        radio.addEventListener('change', () => applyLayout(radio.value));
    });

    // ── Generic row builder ───────────────────────────────────────
    function makeRowBuilder(tbodyId, addBtnId, prefix, categories, investigations) {
        const tbody  = document.getElementById(tbodyId);
        const addBtn = document.getElementById(addBtnId);
        let idx = 0;

        function options(items, placeholder, selectedVal) {
            return `<option value="">${placeholder}</option>` +
                items.map(i => `<option value="${i.id}" ${String(selectedVal)===String(i.id)?'selected':''}>${i.name}</option>`).join('');
        }

        function buildRow(catId='', invId='') {
            const i = idx++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="${prefix}_requests[${i}][lab_inv_category_id]"
                            class="form-select form-select-sm js-cat"></select>
                </td>
                <td>
                    <select name="${prefix}_requests[${i}][lab_inv]"
                            class="form-select form-select-sm js-inv"></select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger js-del" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
            tbody.appendChild(tr);

            const catSel = tr.querySelector('.js-cat');
            const invSel = tr.querySelector('.js-inv');
            catSel.innerHTML = options(categories, '-- All --', catId);
            refreshInv(catSel, invSel, invId);
        }

        function refreshInv(catSel, invSel, selected='') {
            const cid = catSel.value;
            const filtered = investigations.filter(i => !cid || String(i.category_id)===String(cid));
            invSel.innerHTML = options(filtered, '-- Select Test --', selected);
        }

        tbody.addEventListener('change', e => {
            if (e.target.classList.contains('js-cat')) {
                refreshInv(e.target, e.target.closest('tr').querySelector('.js-inv'));
            }
        });
        tbody.addEventListener('click', e => {
            const btn = e.target.closest('.js-del');
            if (btn) btn.closest('tr').remove();
        });

        addBtn.addEventListener('click', () => buildRow());

        function init(oldRequests) {
            if (oldRequests.length) {
                oldRequests.forEach(r => buildRow(r.lab_inv_category_id||'', r.lab_inv||''));
            } else {
                buildRow();
            }
        }
        return { init };
    }

    const pathBuilder = makeRowBuilder('pathTbody', 'addPathRow', 'pathology', pathCategories, pathInvestigations);
    pathBuilder.init(@json(old('pathology_requests', [])));

    const radBuilder  = makeRowBuilder('radTbody',  'addRadRow',  'radiology',  radCategories,  radInvestigations);
    radBuilder.init(@json(old('radiology_requests', [])));

    // Apply initial state AFTER row builders have inserted their first rows,
    // so setDisabled correctly covers those rows too.
    const checkedRadio = document.querySelector('input[name="lab_type"]:checked');
    applyLayout(checkedRadio ? checkedRadio.value : 'pathology');
});
</script>
@endpush
