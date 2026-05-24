{{-- Per-IPD modal: create a NICU admission from this admission (vaginal delivery or post-delivery transfer). --}}
<div class="modal fade" id="nicuTransferModal-{{ $ipd->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" action="{{ route('nicu.admissions.create-from-ipd', $ipd->id) }}" method="POST">
            @csrf
            <div class="modal-header bg-info-subtle">
                <h5 class="modal-title"><i class="bi bi-emoji-smile"></i> Transfer Baby to NICU</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3 small">
                    Mother: <strong>{{ $ipd->patient?->patient_name ?? '—' }}</strong>
                    · IPD: <strong>{{ $ipd->ipd_no }}</strong>
                </div>

                <div class="row g-2">
                    <div class="col-md-7">
                        <label class="form-label small">Baby Name <span class="text-muted">(blank = "Baby of {mother}")</span></label>
                        <input type="text" name="baby_name" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Gender *</label>
                        <select name="gender" class="form-select form-select-sm" required>
                            <option value="">— pick —</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small">Birth At</label>
                        <input type="datetime-local" name="birth_at" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Delivery Type</label>
                        <select name="delivery_type" class="form-select form-select-sm">
                            <option value="">—</option>
                            <option value="Vaginal" selected>Vaginal</option>
                            <option value="C-Section">C-Section</option>
                            <option value="Assisted">Assisted</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Birth Weight (g)</label>
                        <input type="number" min="100" max="8000" step="1" name="birth_weight_grams"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Length (cm)</label>
                        <input type="number" min="20" max="80" step="0.1" name="birth_length_cm"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Head Circ. (cm)</label>
                        <input type="number" min="15" max="60" step="0.1" name="head_circumference_cm"
                               class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Gestational Age (wk)</label>
                        <input type="number" min="20" max="45" step="1" name="gestational_age_weeks"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">APGAR 1 min</label>
                        <input type="number" min="0" max="10" step="1" name="apgar_1min"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">APGAR 5 min</label>
                        <input type="number" min="0" max="10" step="1" name="apgar_5min"
                               class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6 form-check ms-2 mt-3">
                        <input type="hidden" name="is_multiple_birth" value="0">
                        <input class="form-check-input" type="checkbox" name="is_multiple_birth" value="1" id="ipd_rn_multi_{{ $ipd->id }}">
                        <label class="form-check-label" for="ipd_rn_multi_{{ $ipd->id }}">Multiple Birth</label>
                    </div>

                    <div class="col-md-12 mt-2">
                        <label class="form-label small">Clinical Notes</label>
                        <textarea name="clinical_notes" rows="2" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-info"><i class="bi bi-check2-circle"></i> Create NICU Admission</button>
            </div>
        </form>
    </div>
</div>
