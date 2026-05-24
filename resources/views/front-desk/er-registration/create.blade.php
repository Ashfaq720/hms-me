<style>
.er-section {
    border: 1px solid #fee2e2;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #fff;
}
.er-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #dc3545;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.er-section-title i { font-size: 13px; }
.priority-btn {
    flex: 1;
    border-radius: 8px !important;
    font-size: 13px;
    padding: 7px 4px;
    transition: all .15s;
}
.priority-btn.active { font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
</style>

<form action="{{ route('front_desk.er_registration.store') }}" method="POST" id="erRegForm">
    @csrf

    {{-- ── Patient ── --}}
    <div class="er-section">
        <div class="er-section-title"><i class="bi bi-person-vcard"></i> Patient</div>

        <div class="mb-2">
            <label class="form-label">Search Existing Patient</label>
            <select name="patient_id" id="patient_search"
                class="form-select select2 @error('patient_id') is-invalid @enderror"
                style="width:100%">
                <option value="">— Search by name / contact —</option>
                @foreach ($patients as $p)
                    <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->patient_name }} | {{ $p->mobileno }}
                    </option>
                @endforeach
            </select>
            @error('patient_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="patient_name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" placeholder="Patient full name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact No. <span class="text-danger">*</span></label>
                <input type="text" name="contact_no" id="patient_contact"
                    class="form-control @error('contact_no') is-invalid @enderror"
                    value="{{ old('contact_no') }}" placeholder="01XXXXXXXXX" required>
                @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Clinical Info ── --}}
    <div class="er-section">
        <div class="er-section-title"><i class="bi bi-heart-pulse"></i> Clinical Info</div>

        <div class="row g-2 mb-2">
            <div class="col-4">
                <label class="form-label">Age</label>
                <input type="number" name="age" id="age"
                    class="form-control @error('age') is-invalid @enderror"
                    value="{{ old('age') }}" min="0" max="150" placeholder="Yrs">
                @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-4">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                    <option value="">—</option>
                    @foreach (['Male','Female','Other'] as $g)
                        <option value="{{ $g }}" @selected(old('gender') == $g)>{{ $g }}</option>
                    @endforeach
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-4">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                    <option value="">—</option>
                    @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group') == $bg)>{{ $bg }}</option>
                    @endforeach
                </select>
                @error('blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Arrival Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" name="arrival_time"
                    class="form-control @error('arrival_time') is-invalid @enderror"
                    value="{{ old('arrival_time', now()->format('Y-m-d\TH:i')) }}" required>
                @error('arrival_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Priority <span class="text-danger">*</span></label>
                <input type="hidden" name="priority" id="priority_input" value="{{ old('priority', 'NORMAL') }}">
                <div class="d-flex gap-2">
                    <button type="button" class="priority-btn btn btn-outline-success @if(old('priority','NORMAL')==='NORMAL') active btn-success text-white @endif"
                        data-priority="NORMAL">Normal</button>
                    <button type="button" class="priority-btn btn btn-outline-warning @if(old('priority')==='HIGH') active btn-warning text-white @endif"
                        data-priority="HIGH">High</button>
                    <button type="button" class="priority-btn btn btn-outline-danger @if(old('priority')==='CRITICAL') active btn-danger text-white @endif"
                        data-priority="CRITICAL"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</button>
                </div>
                @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Chief Complaint ── --}}
    <div class="er-section">
        <div class="er-section-title"><i class="bi bi-file-medical"></i> Chief Complaint</div>
        <textarea name="description" rows="2"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Describe the presenting complaint or reason for ER visit…">{{ old('description') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- ── Financial ── --}}
    <div class="er-section">
        <div class="er-section-title"><i class="bi bi-credit-card"></i> Financial</div>
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Discount Type</label>
                <select name="discount_type" id="discount_type"
                    class="form-select @error('discount_type') is-invalid @enderror">
                    <option value="">— Self / No discount —</option>
                    @foreach (['CORPORATE','INSURANCE','STUFF','SELF'] as $dt)
                        <option value="{{ $dt }}" @selected(old('discount_type') == $dt)>{{ $dt }}</option>
                    @endforeach
                </select>
                @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6" id="thirdPartyBox" style="{{ old('discount_type') && old('discount_type') !== 'SELF' ? '' : 'display:none' }}">
                <label class="form-label">Third Party Name</label>
                <input type="text" name="third_party_name"
                    class="form-control @error('third_party_name') is-invalid @enderror"
                    value="{{ old('third_party_name') }}" placeholder="Insurer / company name">
                @error('third_party_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm px-4">
            <i class="bi bi-activity me-1"></i>Register Emergency
        </button>
    </div>
</form>

<script>
(function () {
    // Priority toggle
    var priorityInput = document.getElementById('priority_input');
    var priorityColors = { NORMAL: 'success', HIGH: 'warning', CRITICAL: 'danger' };

    document.querySelectorAll('.priority-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var p = this.dataset.priority;
            document.querySelectorAll('.priority-btn').forEach(function (b) {
                var c = priorityColors[b.dataset.priority];
                b.className = 'priority-btn btn btn-outline-' + c;
            });
            var col = priorityColors[p];
            this.classList.replace('btn-outline-' + col, 'btn-' + col);
            this.classList.add('active', 'text-white');
            priorityInput.value = p;
        });
    });

    // Third party toggle based on discount type
    var discountSel  = document.getElementById('discount_type');
    var thirdPartyBox = document.getElementById('thirdPartyBox');

    function toggleThirdParty() {
        var v = discountSel.value;
        thirdPartyBox.style.display = (v && v !== 'SELF') ? '' : 'none';
    }
    discountSel.addEventListener('change', toggleThirdParty);

    // Init select2 inside modal
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        var $modal = $('#erRegForm').closest('.modal');
        $('#erRegForm select.select2').select2({
            width: '100%',
            dropdownParent: $modal.length ? $modal : $('body')
        });
    }
}());
</script>
