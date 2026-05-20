<form action="{{ route('opd-patients.medications.store', $opdPatient->id) }}" method="POST">
    @csrf

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle" id="medication-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 22%">Medicine <span class="text-danger">*</span></th>
                    <th style="width: 18%">Date & Time <span class="text-danger">*</span></th>
                    <th style="width: 12%">Dosage</th>
                    <th style="width: 15%">Medicated By</th>
                    <th style="width: 14%">Remarks</th>
                    <th style="width: 14%">Notes</th>
                    <th style="width: 5%"></th>
                </tr>
            </thead>
            <tbody id="medication-body">
                <tr>
                    <td>
                        <select name="medications[0][medicine_id]" class="form-select form-select-sm" required>
                            <option value="">-- Select Medicine --</option>
                            @foreach ($medicines as $medicine)
                                <option value="{{ $medicine->id }}">{{ $medicine->medicine_name }} ({{ $medicine->unit?->name ?? '-' }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="datetime-local" name="medications[0][datetime]" class="form-control form-control-sm" required>
                    </td>
                    <td>
                        <input type="text" name="medications[0][dosage]" class="form-control form-control-sm" placeholder="">
                    </td>
                    <td>
                        <input type="text" name="medications[0][medicated_by]" class="form-control form-control-sm" placeholder="Medicated By">
                    </td>
                    <td>
                        <input type="text" name="medications[0][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                    </td>
                    <td>
                        <input type="text" name="medications[0][notes]" class="form-control form-control-sm" placeholder="Notes">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-med-row" onclick="removeMedRow(this)" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <button type="button" class="btn btn-sm btn-success" onclick="addMedRow()">
            <i class="bi bi-plus-circle"></i> Add More
        </button>
        <div class="d-flex gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save Medications</button>
        </div>
    </div>
</form>

<script>
    let medRowIndex = 1;

    function addMedRow() {
        const tbody = document.getElementById('medication-body');
        const row = `
        <tr>
            <td>
                <select name="medications[${medRowIndex}][medicine_id]" class="form-select form-select-sm" required>
                    <option value="">-- Select Medicine --</option>
                    @foreach ($medicines as $medicine)
                        <option value="{{ $medicine->id }}">{{ $medicine->medicine_name }} ({{ $medicine->unit?->name ?? '-' }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="datetime-local" name="medications[${medRowIndex}][datetime]" class="form-control form-control-sm" required>
            </td>
            <td>
                <input type="text" name="medications[${medRowIndex}][dosage]" class="form-control form-control-sm" placeholder="">
            </td>
            <td>
                <input type="text" name="medications[${medRowIndex}][medicated_by]" class="form-control form-control-sm" placeholder="Medicated By">
            </td>
            <td>
                <input type="text" name="medications[${medRowIndex}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
            </td>
            <td>
                <input type="text" name="medications[${medRowIndex}][notes]" class="form-control form-control-sm" placeholder="Notes">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-med-row" onclick="removeMedRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
        medRowIndex++;
        toggleMedRemoveButtons();
    }

    function removeMedRow(btn) {
        btn.closest('tr').remove();
        toggleMedRemoveButtons();
    }

    function toggleMedRemoveButtons() {
        const rows = document.querySelectorAll('#medication-body tr');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-med-row');
            btn.disabled = rows.length <= 1;
        });
    }
</script>
