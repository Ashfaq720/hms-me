<style>
.amb-section {
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #fff;
}
.amb-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #6b7390;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.amb-section-title i { font-size: 13px; }
.req-type-btn {
    flex: 1;
    border-radius: 8px !important;
    font-size: 12px;
    padding: 6px 4px;
    transition: all .15s;
}
.req-type-btn.active { font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
</style>

<form method="POST" action="{{ route('amb.requests.store') }}" id="ambReqForm">
    @csrf

    {{-- ── Patient ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-person-vcard"></i> Patient</div>
        <div class="mb-2">
            <label class="form-label">Search Patient <span class="text-danger">*</span></label>
            <select name="patient_id" id="amb_patient_search" required
                class="form-select select2 @error('patient_id') is-invalid @enderror"
                style="width:100%">
                <option value="">— Search by name / contact —</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}"
                        data-contact="{{ $p->mobileno }}"
                        {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->patient_name }} | {{ $p->mobileno }}
                    </option>
                @endforeach
            </select>
            @error('patient_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="form-label">Contact No.</label>
            <input type="text" name="contact_no" id="amb_contact_no"
                class="form-control @error('contact_no') is-invalid @enderror"
                value="{{ old('contact_no') }}" placeholder="Auto-filled or enter manually">
            @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Request Details ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-truck"></i> Request Details</div>

        <div class="mb-2">
            <label class="form-label">Request Type <span class="text-danger">*</span></label>
            <input type="hidden" name="request_type" id="request_type_input" value="{{ old('request_type', 'NORMAL') }}">
            <div class="d-flex gap-2">
                @foreach(['EMERGENCY' => 'danger', 'NORMAL' => 'secondary', 'TRANSFER' => 'primary', 'SCHEDULED' => 'success'] as $type => $color)
                    <button type="button"
                        class="req-type-btn btn btn-outline-{{ $color }} @if(old('request_type','NORMAL') === $type) active btn-{{ $color }} text-white @endif"
                        data-type="{{ $type }}" data-color="{{ $color }}">
                        {{ ucfirst(strtolower($type)) }}
                    </button>
                @endforeach
            </div>
            @error('request_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Priority <span class="text-danger">*</span></label>
                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                    @foreach(['NORMAL' => 'Normal', 'LOW' => 'Low', 'HIGH' => 'High', 'CRITICAL' => 'Critical'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('priority', 'NORMAL') == $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" required
                    class="form-control @error('date') is-invalid @enderror"
                    value="{{ old('date', date('Y-m-d')) }}">
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Time <span class="text-danger">*</span></label>
                <input type="time" name="time" required
                    class="form-control @error('time') is-invalid @enderror"
                    value="{{ old('time', date('H:i')) }}">
                @error('time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Locations ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-geo-alt"></i> Locations</div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Pickup Location <span class="text-danger">*</span></label>
                <input type="text" name="pick_up_location" required
                    class="form-control @error('pick_up_location') is-invalid @enderror"
                    value="{{ old('pick_up_location') }}" placeholder="Ward / address / area">
                @error('pick_up_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Drop Location</label>
                <input type="text" name="drop_location"
                    class="form-control @error('drop_location') is-invalid @enderror"
                    value="{{ old('drop_location') }}" placeholder="Destination">
                @error('drop_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Dispatch ── --}}
    <div class="amb-section">
        <div class="amb-section-title"><i class="bi bi-person-gear"></i> Dispatch (optional)</div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Ambulance</label>
                <select name="ambulance_id"
                    class="form-select select2 @error('ambulance_id') is-invalid @enderror"
                    style="width:100%">
                    <option value="">— Assign later —</option>
                    @foreach ($ambulances as $a)
                        <option value="{{ $a->id }}" @selected(old('ambulance_id') == $a->id)>
                            {{ $a->reg_no }} — {{ $a->type }}
                        </option>
                    @endforeach
                </select>
                @error('ambulance_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Driver</label>
                <select name="driver_id"
                    class="form-select select2 @error('driver_id') is-invalid @enderror"
                    style="width:100%">
                    <option value="">— Assign later —</option>
                    @foreach ($drivers as $d)
                        <option value="{{ $d->id }}" @selected(old('driver_id') == $d->id)>
                            {{ $d->name }} | {{ $d->phone }}
                        </option>
                    @endforeach
                </select>
                @error('driver_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning btn-sm px-4">
            <i class="bi bi-truck me-1"></i>Save Request
        </button>
    </div>
</form>

<script>
(function () {
    // Request type toggle
    var reqTypeInput = document.getElementById('request_type_input');
    document.querySelectorAll('.req-type-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = this.dataset.type, col = this.dataset.color;
            document.querySelectorAll('.req-type-btn').forEach(function (b) {
                b.className = 'req-type-btn btn btn-outline-' + b.dataset.color;
            });
            this.classList.replace('btn-outline-' + col, 'btn-' + col);
            this.classList.add('active', 'text-white');
            reqTypeInput.value = t;
        });
    });

    // Auto-fill contact from patient selection
    var $modal = $('#ambReqForm').closest('.modal');

    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        $modal.find('select.select2').select2({
            width: '100%',
            dropdownParent: $modal.length ? $modal : $('body')
        });

        $('#amb_patient_search').on('select2:select', function (e) {
            var contact = e.params.data.element ? e.params.data.element.dataset.contact : '';
            if (contact) $('#amb_contact_no').val(contact);
        });

        $('#amb_patient_search').on('select2:unselect select2:clear', function () {
            $('#amb_contact_no').val('');
        });
    }
}());
</script>
