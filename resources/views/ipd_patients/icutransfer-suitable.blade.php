<form method="POST" action="{{ route('ipd-patients.icu-transfer.store', $id) }}" enctype="multipart/form-data">
    @csrf

    <div class="alert alert-light border small mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Resource validation runs before transfer. Use <span class="fw-semibold text-danger">Emergency Override</span>
        only when no suitable bed / ventilator / monitor is available.
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Clinical & Resource Requirements --}}
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title">Clinical &amp; Resource Requirements</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">ICU Type <span class="text-danger">*</span></label>
                    <select name="icu_type" id="icu_type" class="form-select" required>
                        <option value="">-- Select --</option>
                        @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                            <option value="{{ $t }}" {{ old('icu_type', 'ICU') === $t ? 'selected' : '' }}>{{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Admission Type</label>
                    <select name="admission_type" class="form-select">
                        @foreach (['Emergency', 'Planned', 'Transfer'] as $t)
                            <option value="{{ $t }}" {{ old('admission_type', 'Transfer') === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Isolation Type</label>
                    <select name="isolation_type" id="isolation_type" class="form-select">
                        @foreach (['None', 'Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                            <option value="{{ $t }}" {{ old('isolation_type', 'None') === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">From <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="from"
                        value="{{ old('from', now()->format('Y-m-d\TH:i')) }}" class="form-control" required>
                    @error('from')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Admission Diagnosis <span class="text-danger">*</span></label>
                    <textarea name="admission_diagnosis" class="form-control" rows="2" required>{{ old('admission_diagnosis') }}</textarea>
                    @error('admission_diagnosis')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Referring Doctor <span class="text-danger">*</span></label>
                    <select name="referring_doctor_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        @foreach ($doctors as $d)
                            <option value="{{ $d->id }}"
                                {{ old('referring_doctor_id', $ipdPatient->doctor_id) == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input type="hidden" name="ventilator_required" value="0">
                        <input class="form-check-input" type="checkbox" id="vent_req" name="ventilator_required"
                            value="1" {{ old('ventilator_required') ? 'checked' : '' }}>
                        <label class="form-check-label" for="vent_req">Ventilator required</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input type="hidden" name="monitor_required" value="0">
                        <input class="form-check-input" type="checkbox" id="mon_req" name="monitor_required"
                            value="1" {{ old('monitor_required', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="mon_req">Monitor required</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bed Suggestion --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">ICU / CCU Bed</h6>
                <button type="button" id="check_avail" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-search"></i> Check Availability
                </button>
            </div>

            <label class="form-label">Available Bed</label>
            <select name="icu_bed_id" id="icu_bed_id" class="form-select">
                <option value="">-- Select an ICU bed (or use Override below) --</option>
                @foreach ($icuBeds as $b)
                    <option value="{{ $b->id }}"
                        data-icu-type="{{ optional($b->bedType)->icu_type }}"
                        data-vent="{{ optional($b->bedType)->has_ventilator_support ? 1 : 0 }}"
                        data-mon="{{ optional($b->bedType)->has_monitor_support ? 1 : 0 }}"
                        data-iso="{{ optional($b->bedType)->is_isolation_bed ? 1 : 0 }}"
                        {{ old('icu_bed_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                        @if (optional($b->bedType)->name)
                            [{{ $b->bedType->name }}]
                        @endif
                        (৳ {{ $b->rent }})
                    </option>
                @endforeach
            </select>
            @error('icu_bed_id')<div class="text-danger small">{{ $message }}</div>@enderror
            <div class="form-text" id="bed_help">List narrows after you click Check Availability.</div>
        </div>
    </div>

    {{-- Emergency Override --}}
    <div class="card border-danger-subtle mb-3">
        <div class="card-body">
            <div class="form-check form-switch">
                <input type="hidden" name="override" value="0">
                <input class="form-check-input" type="checkbox" id="override" name="override" value="1"
                    {{ old('override') ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-danger" for="override">
                    Use Emergency Override
                </label>
                <div class="form-text">Required only when bed / ventilator / monitor / isolation is unavailable
                    and patient cannot wait. Logged in audit trail.</div>
            </div>

            <div id="override_block" class="row g-3 mt-2" style="{{ old('override') ? '' : 'display:none;' }}">
                <div class="col-md-3">
                    <label class="form-label">Resource Issue</label>
                    <select name="override_resource_issue" class="form-select">
                        @foreach (['NoBed', 'NoVentilator', 'NoMonitor', 'NoIsolationBed', 'Other'] as $r)
                            <option value="{{ $r }}"
                                {{ old('override_resource_issue') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Approved By (User ID)</label>
                    <input type="number" name="override_approved_by" value="{{ old('override_approved_by') }}"
                        class="form-control" placeholder="ICU Consultant user ID">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Temporary Bed (optional)</label>
                    <input type="number" name="override_temporary_bed_id"
                        value="{{ old('override_temporary_bed_id') }}" class="form-control" placeholder="Bed ID">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Override Reason</label>
                    <textarea name="override_reason" class="form-control" rows="2"
                        placeholder="Why override is needed">{{ old('override_reason') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Remarks --}}
    <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" placeholder="Reason for ICU transfer / clinical note" rows="2">{{ old('remarks') }}</textarea>
        @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-heart-pulse"></i> Transfer to ICU / CCU
        </button>
    </div>
</form>

<script>
    (function () {
        var overrideToggle = document.getElementById('override');
        var overrideBlock  = document.getElementById('override_block');
        if (overrideToggle && overrideBlock) {
            overrideToggle.addEventListener('change', function () {
                overrideBlock.style.display = overrideToggle.checked ? '' : 'none';
            });
        }

        var btn = document.getElementById('check_avail');
        if (!btn) return;

        btn.addEventListener('click', async function () {
            var icuType  = document.getElementById('icu_type').value;
            var iso      = document.getElementById('isolation_type').value;
            var needVent = document.getElementById('vent_req').checked ? 1 : 0;
            if (!icuType) { alert('Pick an ICU type first.'); return; }

            var url = '{{ route('icu.beds.available') }}'
                    + '?icu_type=' + encodeURIComponent(icuType)
                    + '&isolation_type=' + encodeURIComponent(iso)
                    + '&ventilator_required=' + needVent;
            try {
                var res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                var json = await res.json();
                var sel  = document.getElementById('icu_bed_id');
                sel.innerHTML = '<option value="">-- Select --</option>';
                (json.beds || []).forEach(function (b) {
                    var opt = document.createElement('option');
                    opt.value = b.id;
                    opt.textContent = b.name + ' [' + (b.bed_type || '-') + '] (৳ ' + b.rent + ')';
                    opt.setAttribute('data-icu-type', b.icu_type || '');
                    opt.setAttribute('data-vent', b.has_vent ? 1 : 0);
                    opt.setAttribute('data-mon',  b.has_monitor ? 1 : 0);
                    opt.setAttribute('data-iso',  b.is_isolation ? 1 : 0);
                    sel.appendChild(opt);
                });
                document.getElementById('bed_help').textContent =
                    (json.beds ? json.beds.length : 0) + ' bed(s) match. If 0, use Emergency Override.';
            } catch (e) {
                alert('Could not fetch beds: ' + e.message);
            }
        });
    })();
</script>
