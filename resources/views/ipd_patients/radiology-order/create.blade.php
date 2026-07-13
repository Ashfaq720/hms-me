<form action="{{ route('ipd-patients.radiology-orders.store', $id) }}" method="POST">
    @csrf

    @php($radiology = $investigationTypes->first())
    <input type="hidden" name="lab_inv_type_id" value="{{ $radiology->id }}">

    <div class="row g-3">

        {{-- Investigation Type (locked to Radiology) --}}
        <div class="col-md-6">
            <label class="form-label">Investigation Type</label>
            <select class="form-select" disabled>
                <option selected>{{ $radiology->name }}</option>
            </select>
        </div>

        {{-- Doctor --}}
        <div class="col-md-6">
            <label for="doctor_id" class="form-label">Doctor</label>
            <select name="doctor_id" id="doctor_id"
                class="form-select @error('doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Datetime --}}
        <div class="col-md-6">
            <label for="datetime" class="form-label">Date/Time</label>
            <input type="datetime-local" name="datetime" id="datetime"
                class="form-control @error('datetime') is-invalid @enderror"
                value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}">
            @error('datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Lab Name --}}
        <div class="col-md-6">
            <label for="lab_name" class="form-label">Lab Name</label>
            <input type="text" name="lab_name" id="lab_name"
                class="form-control @error('lab_name') is-invalid @enderror"
                value="{{ old('lab_name') }}" placeholder="Enter lab name">
            @error('lab_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Collected By --}}
        <div class="col-md-6">
            <label for="collected_by" class="form-label">Collected By</label>
            <input type="text" name="collected_by" id="collected_by"
                class="form-control @error('collected_by') is-invalid @enderror"
                value="{{ old('collected_by') }}" placeholder="Enter collector name">
            @error('collected_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
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

        {{-- Remarks --}}
        <div class="col-12">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea name="remarks" id="remarks" rows="2"
                class="form-control @error('remarks') is-invalid @enderror"
                placeholder="Enter remarks">{{ old('remarks') }}</textarea>
            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Investigations (multiple) --}}
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Investigations</label>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-investigation-row">
                    + Add Investigation
                </button>
            </div>

            @error('requests')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror
            @error('requests.*.lab_inv_category_id')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror
            @error('requests.*.lab_inv')
                <div class="text-danger small mb-2">{{ $message }}</div>
            @enderror

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="investigations-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Category</th>
                            <th style="width: 45%;">Investigation</th>
                            <th style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="investigations-body">
                        {{-- rows injected by JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>

    </div>
</form>

<script>
(function () {
    const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());
    const investigations = @json($investigations->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'category_id' => $i->category_id])->values());
    const oldRequests = @json(old('requests', []));

    const tbody = document.getElementById('investigations-body');
    const addBtn = document.getElementById('add-investigation-row');
    let rowIndex = 0;

    function buildRow(catId = '', invId = '') {
        const idx = rowIndex++;
        const tr = document.createElement('tr');

        const catTd = document.createElement('td');
        const catSelect = document.createElement('select');
        catSelect.name = `requests[${idx}][lab_inv_category_id]`;
        catSelect.className = 'form-select form-select-sm';
        catSelect.innerHTML = '<option value="">-- Select Category --</option>' +
            categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        catSelect.value = catId;
        catTd.appendChild(catSelect);

        const invTd = document.createElement('td');
        const invSelect = document.createElement('select');
        invSelect.name = `requests[${idx}][lab_inv]`;
        invSelect.className = 'form-select form-select-sm';
        invTd.appendChild(invSelect);

        const actTd = document.createElement('td');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.textContent = 'Remove';
        removeBtn.addEventListener('click', () => tr.remove());
        actTd.appendChild(removeBtn);

        tr.appendChild(catTd);
        tr.appendChild(invTd);
        tr.appendChild(actTd);

        function refreshInvestigations(selectedInv = '') {
            const cid = catSelect.value;
            const filtered = investigations.filter(i => !cid || String(i.category_id) === String(cid));
            invSelect.innerHTML = '<option value="">-- Select Investigation --</option>' +
                filtered.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
            if (selectedInv) invSelect.value = selectedInv;
        }

        catSelect.addEventListener('change', () => refreshInvestigations());
        refreshInvestigations(invId);

        tbody.appendChild(tr);
    }

    addBtn.addEventListener('click', () => buildRow());

    if (Array.isArray(oldRequests) && oldRequests.length) {
        oldRequests.forEach(r => buildRow(r.lab_inv_category_id || '', r.lab_inv || ''));
    } else {
        buildRow();
    }
})();
</script>
