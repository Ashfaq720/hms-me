@php($r = $surgeryRequest ?? null)

{{-- ─── Patient & Encounter ─────────────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-person"></i> Patient &amp; Encounter
</h6>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">Patient *</label>
        <select name="patient_id" class="form-select" required>
            <option value="">— select —</option>
            @foreach($patients as $p)
                <option value="{{ $p->id }}" @selected(old('patient_id', $r->patient_id ?? '') == $p->id)>
                    {{ $p->patient_name }} ({{ $p->mrn }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Encounter Type *</label>
        <select name="encounter_type" class="form-select" required>
            @foreach(['IPD','OPD','ER'] as $t)
                <option value="{{ $t }}" @selected(old('encounter_type', $r->encounter_type ?? '') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Encounter ID</label>
        <input type="text" name="encounter_id" class="form-control" value="{{ old('encounter_id', $r->encounter_id ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">IPD Admission ID</label>
        <input type="text" name="ipd_admission_id" class="form-control" value="{{ old('ipd_admission_id', $r->ipd_admission_id ?? '') }}">
    </div>
</div>

{{-- ─── Surgery & Department ────────────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-clipboard-pulse"></i> Surgery / Procedure
</h6>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">Surgery Category</label>
        <select name="surgery_category_id" class="form-select">
            <option value="">—</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('surgery_category_id', $r->surgery_category_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Surgery / Procedure</label>
        <select name="surgery_type_id" class="form-select" id="surgery-type-select">
            <option value="">—</option>
            @foreach($surgeryTypes as $t)
                <option value="{{ $t->id }}"
                        data-duration="{{ $t->standard_duration_minutes }}"
                        @selected(old('surgery_type_id', $r->surgery_type_id ?? '') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
            <option value="">—</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(old('department_id', $r->department_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Required OT Type</label>
        <select name="required_ot_type" class="form-select">
            <option value="">—</option>
            @foreach(($otTypes ?? \App\Models\Ot\OtSurgeryRequest::OT_TYPES) as $t)
                <option value="{{ $t }}" @selected(old('required_ot_type', $r->required_ot_type ?? '') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Requesting Doctor</label>
        <select name="requested_by_doctor_id" class="form-select">
            <option value="">—</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(old('requested_by_doctor_id', $r->requested_by_doctor_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Primary Surgeon</label>
        <select name="primary_surgeon_id" class="form-select">
            <option value="">—</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(old('primary_surgeon_id', $r->primary_surgeon_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ─── Diagnosis (FR-03) ───────────────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-file-medical"></i> Diagnosis &amp; Clinical
</h6>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">Primary Diagnosis *</label>
        <textarea name="diagnosis" rows="2" class="form-control" required>{{ old('diagnosis', $r->diagnosis ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Secondary Diagnosis</label>
        <textarea name="secondary_diagnosis" rows="2" class="form-control">{{ old('secondary_diagnosis', $r->secondary_diagnosis ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">ICD-10 Code</label>
        <input type="text" name="icd_code" class="form-control" placeholder="e.g. K35.9" value="{{ old('icd_code', $r->icd_code ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">ASA Grade</label>
        <select name="asa_grade" class="form-select">
            <option value="">—</option>
            @foreach(['I','II','III','IV','V','VI'] as $g)
                <option value="{{ $g }}" @selected(old('asa_grade', $r->asa_grade ?? '') === $g)>{{ $g }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-7">
        <label class="form-label">Clinical Indication</label>
        <textarea name="clinical_indication" rows="2" class="form-control">{{ old('clinical_indication', $r->clinical_indication ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Procedure Notes</label>
        <textarea name="procedure_notes" rows="2" class="form-control">{{ old('procedure_notes', $r->procedure_notes ?? '') }}</textarea>
    </div>
</div>

{{-- ─── Priority + Emergency (FR-04) ────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-exclamation-triangle"></i> Priority &amp; Date
</h6>
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select" id="priority-select">
            @foreach(['Low','Normal','High','Emergency'] as $p)
                <option value="{{ $p }}" @selected(old('priority', $r->priority ?? 'Normal') === $p)>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Preferred Date</label>
        <input type="date" name="requested_surgery_date" class="form-control"
               value="{{ old('requested_surgery_date', optional($r?->requested_surgery_date)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Preferred Time</label>
        <input type="time" name="requested_surgery_time" class="form-control"
               value="{{ old('requested_surgery_time', $r->requested_surgery_time ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Duration (min)</label>
        <input type="number" name="estimated_duration_minutes" class="form-control"
               value="{{ old('estimated_duration_minutes', $r->estimated_duration_minutes ?? '') }}" min="5" max="1440">
    </div>
    <div class="col-md-2">
        <label class="form-label">Date Flexibility</label>
        <select name="date_flexibility" class="form-select">
            @foreach(['Flexible','Fixed'] as $f)
                <option value="{{ $f }}" @selected(old('date_flexibility', $r->date_flexibility ?? 'Flexible') === $f)>{{ $f }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Reason for Preferred Date / Flexibility Note</label>
        <input type="text" name="flexibility_reason" class="form-control" value="{{ old('flexibility_reason', $r->flexibility_reason ?? '') }}">
    </div>

    {{-- Emergency block — visible if priority Emergency OR is_emergency --}}
    <div class="col-12" id="emergency-block" style="display: {{ old('is_emergency', $r->is_emergency ?? false) || old('priority', $r->priority ?? '') === 'Emergency' ? 'block' : 'none' }};">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white py-2"><strong><i class="bi bi-exclamation-triangle"></i> Emergency Details</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_emergency" value="0">
                            <input class="form-check-input" type="checkbox" name="is_emergency" value="1" id="isEm"
                                   @checked(old('is_emergency', $r->is_emergency ?? false))>
                            <label class="form-check-label fw-semibold" for="isEm">Emergency Case</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_life_threatening" value="0">
                            <input class="form-check-input" type="checkbox" name="is_life_threatening" value="1" id="isLT"
                                   @checked(old('is_life_threatening', $r->is_life_threatening ?? false))>
                            <label class="form-check-label" for="isLT">Life-Threatening Condition</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_immediate_ot" value="0">
                            <input class="form-check-input" type="checkbox" name="is_immediate_ot" value="1" id="isIm"
                                   @checked(old('is_immediate_ot', $r->is_immediate_ot ?? false))>
                            <label class="form-check-label" for="isIm">Requested Immediate OT</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Emergency Reason *</label>
                        <textarea name="emergency_reason" rows="2" class="form-control">{{ old('emergency_reason', $r->emergency_reason ?? '') }}</textarea>
                        <div class="form-text text-danger">Required when priority is Emergency.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Blood Arrangement (FR-09) ───────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-droplet"></i> Blood Arrangement
</h6>
<div class="row g-3 mb-3">
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="blood_required" value="0">
            <input class="form-check-input" type="checkbox" name="blood_required" value="1" id="bloodReq"
                   @checked(old('blood_required', $r->blood_required ?? false))>
            <label class="form-check-label" for="bloodReq">Blood Required</label>
        </div>
    </div>
    <div class="col-md-2">
        <label class="form-label">Units</label>
        <input type="number" name="blood_units" class="form-control" value="{{ old('blood_units', $r->blood_units ?? '') }}" min="0" max="20">
    </div>
    <div class="col-md-2">
        <label class="form-label">Blood Group</label>
        <?php
            // Resolve the effective default blood_group_id:
            //   1. Existing value on the request, or
            //   2. Old() resubmit value, or
            //   3. Match the patient's stored blood_group string against the master.
            $effectiveBgId = old('blood_group_id', $r->blood_group_id ?? null);
            $autoMatched = false;
            if (! $effectiveBgId && isset($r) && $r && $r->patient && $r->patient->blood_group && isset($bloodGroups)) {
                $patientGroup = strtoupper(trim($r->patient->blood_group));
                $match = $bloodGroups->first(function ($bg) use ($patientGroup) {
                    return strtoupper($bg->combined ?? '') === $patientGroup
                        || strtoupper($bg->display_name ?? '') === $patientGroup;
                });
                if ($match) { $effectiveBgId = $match->id; $autoMatched = true; }
            }
        ?>
        <select name="blood_group_id" class="form-select">
            <option value="">—</option>
            @if(isset($bloodGroups) && $bloodGroups->count() > 0)
                @foreach($bloodGroups as $bg)
                    <option value="{{ $bg->id }}" @selected($effectiveBgId == $bg->id)>
                        {{ $bg->display_name ?: $bg->combined }}
                    </option>
                @endforeach
            @else
                <option value="" disabled>No blood groups configured</option>
            @endif
        </select>
        @if(isset($r) && $r && $r->patient && $r->patient->blood_group)
            <small class="form-text text-success">
                <i class="bi bi-info-circle"></i> Patient profile: <strong>{{ $r->patient->blood_group }}</strong>
                @if($autoMatched)— auto-matched @endif
            </small>
        @else
            <small class="form-text"><a href="{{ url('/blood-bank/blood-groups') }}" target="_blank" class="text-muted">Manage blood groups →</a></small>
        @endif
    </div>
    <div class="col-md-4">
        <label class="form-label">Components</label>
        @php($selectedComponents = old('blood_components', ($r?->blood_components ?? [])))
        @php($selectedComponents = is_array($selectedComponents) ? $selectedComponents : [])
        <select name="blood_components[]" class="form-select" multiple size="3">
            @foreach(($bloodComponents ?? \App\Models\Ot\OtSurgeryRequest::BLOOD_COMPONENTS) as $c)
                <option value="{{ $c }}" {{ in_array($c, $selectedComponents) ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="crossmatch_required" value="0">
            <input class="form-check-input" type="checkbox" name="crossmatch_required" value="1" id="xMatch"
                   @checked(old('crossmatch_required', $r->crossmatch_required ?? false))>
            <label class="form-check-label" for="xMatch">Crossmatch Required</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Blood Bank Instructions</label>
        <textarea name="blood_bank_instruction" rows="2" class="form-control">{{ old('blood_bank_instruction', $r->blood_bank_instruction ?? '') }}</textarea>
    </div>
</div>

{{-- ─── Required Equipment (FR-08) ──────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-tools"></i> Required Equipment
</h6>
<div class="mb-3">
    <table class="table table-sm" id="equipment-table">
        <thead class="table-light">
            <tr>
                <th style="width: 36%">Equipment</th>
                <th style="width: 12%">Qty</th>
                <th style="width: 12%">Mandatory</th>
                <th>Setup Instruction</th>
                <th style="width: 6%"></th>
            </tr>
        </thead>
        <tbody id="equipment-rows">
            <?php
                $existingEq = (isset($r) && $r) ? $r->equipments : null;
                $existingEq = $existingEq ? $existingEq->map(fn($e) => [
                    'ot_equipment_id' => $e->ot_equipment_id,
                    'equipment_name' => $e->equipment_name,
                    'quantity' => $e->quantity,
                    'is_mandatory' => $e->is_mandatory ? 1 : 0,
                    'setup_instruction' => $e->setup_instruction,
                ])->toArray() : [];
                $eqRows = old('equipments', $existingEq);
                $eqRows = is_array($eqRows) ? $eqRows : [];
            ?>
            @forelse($eqRows as $i => $row)
                <tr class="eq-row">
                    <td>
                        <input type="text" name="equipments[{{ $i }}][equipment_name]" class="form-control form-control-sm" value="{{ $row['equipment_name'] ?? '' }}" placeholder="Equipment name">
                        <input type="hidden" name="equipments[{{ $i }}][ot_equipment_id]" value="{{ $row['ot_equipment_id'] ?? '' }}">
                    </td>
                    <td><input type="number" name="equipments[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $row['quantity'] ?? 1 }}" min="1"></td>
                    <td class="text-center">
                        <input type="hidden" name="equipments[{{ $i }}][is_mandatory]" value="0">
                        <input type="checkbox" name="equipments[{{ $i }}][is_mandatory]" value="1" @checked(! empty($row['is_mandatory']))>
                    </td>
                    <td><input type="text" name="equipments[{{ $i }}][setup_instruction]" class="form-control form-control-sm" value="{{ $row['setup_instruction'] ?? '' }}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>
                </tr>
            @empty
                <tr class="eq-row text-muted"><td colspan="5" class="text-center small">No equipment added. Click "Add Equipment" below.</td></tr>
            @endforelse
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-equipment-btn">
        <i class="bi bi-plus-circle"></i> Add Equipment
    </button>
    <small class="text-muted ms-2">Mark mandatory equipment that will block scheduling if unavailable.</small>
</div>

{{-- ─── Approval & Instructions ─────────────────────────────────── --}}
<h6 class="text-muted text-uppercase small fw-semibold mb-2 border-bottom pb-2">
    <i class="bi bi-shield-check"></i> Approval &amp; Special Instructions
</h6>
<div class="row g-3 mb-2">
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="junior_approval_required" value="0">
            <input class="form-check-input" type="checkbox" name="junior_approval_required" value="1" id="jrAppr"
                   @checked(old('junior_approval_required', $r->junior_approval_required ?? false))>
            <label class="form-check-label" for="jrAppr">Junior approval required</label>
        </div>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="consultant_approval_required" value="0">
            <input class="form-check-input" type="checkbox" name="consultant_approval_required" value="1" id="cAppr"
                   @checked(old('consultant_approval_required', $r->consultant_approval_required ?? false))>
            <label class="form-check-label" for="cAppr">Consultant approval required</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Special Instructions</label>
        <textarea name="special_requirements" rows="2" class="form-control" placeholder="e.g. Keep patient fasting, arrange ICU bed, allergy to latex, need implant…">{{ old('special_requirements', $r->special_requirements ?? '') }}</textarea>
    </div>
</div>

<script>
(function () {
    // Show/hide emergency block when priority changes
    const prio = document.getElementById('priority-select');
    const block = document.getElementById('emergency-block');
    const isEm = document.getElementById('isEm');
    if (prio) {
        prio.addEventListener('change', () => {
            if (prio.value === 'Emergency') {
                block.style.display = 'block';
                if (isEm) isEm.checked = true;
            }
        });
    }
    // Auto-fill duration from surgery type
    const stSel = document.getElementById('surgery-type-select');
    if (stSel) {
        stSel.addEventListener('change', e => {
            const opt = stSel.options[stSel.selectedIndex];
            const dur = opt.dataset.duration;
            const durIn = document.querySelector('[name="estimated_duration_minutes"]');
            if (dur && durIn && !durIn.value) durIn.value = dur;
        });
    }
    // Add equipment row
    const addBtn = document.getElementById('add-equipment-btn');
    const rows = document.getElementById('equipment-rows');
    if (addBtn && rows) {
        addBtn.addEventListener('click', () => {
            // remove the "no equipment" placeholder row if present
            rows.querySelectorAll('tr.eq-row td[colspan]').forEach(td => td.closest('tr').remove());
            const idx = rows.querySelectorAll('tr.eq-row').length;
            const tr = document.createElement('tr');
            tr.className = 'eq-row';
            tr.innerHTML = `
                <td><input type="text" name="equipments[\${idx}][equipment_name]" class="form-control form-control-sm" placeholder="Equipment name">
                    <input type="hidden" name="equipments[\${idx}][ot_equipment_id]" value=""></td>
                <td><input type="number" name="equipments[\${idx}][quantity]" class="form-control form-control-sm" value="1" min="1"></td>
                <td class="text-center"><input type="hidden" name="equipments[\${idx}][is_mandatory]" value="0">
                    <input type="checkbox" name="equipments[\${idx}][is_mandatory]" value="1" checked></td>
                <td><input type="text" name="equipments[\${idx}][setup_instruction]" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">×</button></td>`;
            rows.appendChild(tr);
        });
    }
})();
</script>
