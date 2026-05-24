@extends('backend.layouts.master')

@section('title', 'New ICU Admission')

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">New ICU / CCU Admission</h1>
                <small class="text-muted">Resource validation runs before admission. Use Emergency Override only when no
                    suitable bed/ventilator/monitor is available.</small>
            </div>
            <a href="{{ route('icu.admissions.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('icu.admissions.store') }}" class="mt-2">
            @csrf

            <div class="row g-3">

                {{-- Patient + Source --}}
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Patient & Source</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select name="patient_id" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($patients as $p)
                                            <option value="{{ $p->id }}"
                                                {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                                {{ $p->patient_name }} ({{ $p->mrn ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Source <span class="text-danger">*</span></label>
                                    <select name="source_type" class="form-select" required>
                                        @foreach (['DIRECT', 'ER', 'OPD', 'Ipd'] as $s)
                                            <option value="{{ $s }}"
                                                {{ old('source_type', 'DIRECT') === $s ? 'selected' : '' }}>{{ $s }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Source Reference ID</label>
                                    <input type="number" name="source_id" value="{{ old('source_id') }}"
                                        class="form-control" placeholder="ER/OPD/Ipd record ID">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clinical --}}
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Clinical & Resource Requirements</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">ICU Type <span class="text-danger">*</span></label>
                                    <select name="icu_type" id="icu_type" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach (['ICU', 'CCU', 'NICU', 'PICU'] as $t)
                                            <option value="{{ $t }}"
                                                {{ old('icu_type', $icuType ?? '') === $t ? 'selected' : '' }}>{{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Admission Type</label>
                                    <select name="admission_type" class="form-select">
                                        @foreach (['Emergency', 'Planned', 'Transfer'] as $t)
                                            <option value="{{ $t }}"
                                                {{ old('admission_type', 'Emergency') === $t ? 'selected' : '' }}>{{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Isolation Type</label>
                                    <select name="isolation_type" id="isolation_type" class="form-select">
                                        @foreach (['None', 'Airborne', 'Contact', 'Droplet', 'Standard'] as $t)
                                            <option value="{{ $t }}"
                                                {{ old('isolation_type', 'None') === $t ? 'selected' : '' }}>{{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Admission Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="admission_time"
                                        value="{{ old('admission_time', now()->format('Y-m-d\TH:i')) }}"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Admission Diagnosis <span
                                            class="text-danger">*</span></label>
                                    <textarea name="admission_diagnosis" class="form-control" rows="2" required>{{ old('admission_diagnosis') }}</textarea>
                                    @error('admission_diagnosis')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Referring Doctor <span class="text-danger">*</span></label>
                                    <select name="referring_doctor_id" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($doctors as $d)
                                            <option value="{{ $d->id }}"
                                                {{ old('referring_doctor_id') == $d->id ? 'selected' : '' }}>
                                                {{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check form-switch mt-4">
                                        <input type="hidden" name="ventilator_required" value="0">
                                        <input class="form-check-input" type="checkbox" id="vent_req"
                                            name="ventilator_required" value="1"
                                            {{ old('ventilator_required') ? 'checked' : '' }}>
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
                </div>

                {{-- Bed Suggestion --}}
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="card-title mb-0">ICU Bed</h6>
                                <button type="button" id="check_avail" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-search"></i> Check Availability
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Available ICU Bed</label>
                                    <select name="bed_id" id="bed_id" class="form-select">
                                        <option value="">-- Select an ICU bed (or use Override below) --</option>
                                        @foreach ($beds as $b)
                                            <option value="{{ $b->id }}"
                                                data-icu-type="{{ optional($b->bedType)->icu_type }}"
                                                data-vent="{{ optional($b->bedType)->has_ventilator_support ? 1 : 0 }}"
                                                data-mon="{{ optional($b->bedType)->has_monitor_support ? 1 : 0 }}"
                                                data-iso="{{ optional($b->bedType)->is_isolation_bed ? 1 : 0 }}"
                                                {{ old('bed_id') == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }}
                                                @if (optional($b->bedType)->name)
                                                    [{{ $b->bedType->name }}]
                                                @endif
                                                (৳ {{ $b->rent }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bed_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    <div class="form-text" id="bed_help">List narrows after you click Check Availability.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Emergency Override --}}
                <div class="col-md-12">
                    <div class="card border-danger-subtle">
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input type="hidden" name="override" value="0">
                                <input class="form-check-input" type="checkbox" id="override" name="override" value="1"
                                    {{ old('override') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-danger" for="override">
                                    Use Emergency Override
                                </label>
                                <div class="form-text">Required only when bed/ventilator/monitor/isolation is unavailable
                                    and patient cannot wait. Logged in audit trail.</div>
                            </div>

                            <div id="override_block" class="row g-3 mt-2"
                                style="{{ old('override') ? '' : 'display:none;' }}">
                                <div class="col-md-3">
                                    <label class="form-label">Resource Issue</label>
                                    <select name="override_resource_issue" class="form-select">
                                        @foreach (['NoBed', 'NoVentilator', 'NoMonitor', 'NoIsolationBed', 'Other'] as $r)
                                            <option value="{{ $r }}"
                                                {{ old('override_resource_issue') === $r ? 'selected' : '' }}>{{ $r }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Approved By (User ID)</label>
                                    <input type="number" name="override_approved_by"
                                        value="{{ old('override_approved_by') }}" class="form-control"
                                        placeholder="ICU Consultant user ID">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Temporary Bed (optional)</label>
                                    <input type="number" name="override_temporary_bed_id"
                                        value="{{ old('override_temporary_bed_id') }}" class="form-control"
                                        placeholder="Bed ID">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Override Reason</label>
                                    <textarea name="override_reason" class="form-control" rows="2"
                                        placeholder="Why override is needed">{{ old('override_reason') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                </div>

                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-heart-pulse"></i> Confirm ICU Admission
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var overrideToggle = document.getElementById('override');
            var overrideBlock  = document.getElementById('override_block');
            overrideToggle.addEventListener('change', function () {
                overrideBlock.style.display = overrideToggle.checked ? '' : 'none';
            });

            var btn = document.getElementById('check_avail');
            btn.addEventListener('click', async function () {
                var icuType   = document.getElementById('icu_type').value;
                var iso       = document.getElementById('isolation_type').value;
                var needVent  = document.getElementById('vent_req').checked ? 1 : 0;
                if (!icuType) { alert('Pick an ICU type first.'); return; }

                var url  = '{{ route('icu.beds.available') }}'
                         + '?icu_type=' + encodeURIComponent(icuType)
                         + '&isolation_type=' + encodeURIComponent(iso)
                         + '&ventilator_required=' + needVent;
                try {
                    var res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    var json = await res.json();
                    var sel  = document.getElementById('bed_id');
                    sel.innerHTML = '<option value="">-- Select --</option>';
                    (json.beds || []).forEach(function (b) {
                        var opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.name + ' [' + (b.bed_type || '-') + '] (৳ ' + b.rent + ')';
                        sel.appendChild(opt);
                    });
                    document.getElementById('bed_help').textContent =
                        json.beds.length + ' bed(s) match. If 0, use Emergency Override.';
                } catch (e) {
                    alert('Could not fetch beds: ' + e.message);
                }
            });
        })();
    </script>
@endsection
