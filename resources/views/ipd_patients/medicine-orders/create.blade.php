<form action="{{ route('ipd-patients.medicine-orders.store', $ipdPatient->id) }}" method="POST">
    @csrf

    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle" id="medicine-order-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 35%">Medicine <span class="text-danger">*</span></th>
                    <th style="width: 10%">Qty <span class="text-danger">*</span></th>
                    <th style="width: 25%">Prescribed By</th>
                    <th style="width: 15%">Status</th>
                    <th style="width: 10%">Order By</th>
                    <th style="width: 5%"></th>
                </tr>
            </thead>
            <tbody id="medicine-order-body">
                <tr>
                    <td>
                        <select name="orders[0][medicine_id]" class="form-select form-select-sm" required>
                            <option value="">-- Select Medicine --</option>
                            @foreach ($medicines as $medicine)
                                <option value="{{ $medicine->id }}">{{ $medicine->medicine_name }} - {{ $medicine->unit?->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="orders[0][qty]" class="form-control form-control-sm" value="1" min="1" required>
                    </td>
                    <td>
                        <select name="orders[0][prescribed_by]" class="form-select form-select-sm">
                            <option value="">-- Select Doctor --</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="orders[0][status]" class="form-select form-select-sm">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="dispensed">Dispensed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="orders[0][order_by]" class="form-control form-control-sm" placeholder="Order by">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)" disabled>
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <button type="button" class="btn btn-sm btn-success" onclick="addRow()">
            <i class="bi bi-plus-circle"></i> Add More
        </button>
        <div class="d-flex gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save Orders</button>
        </div>
    </div>
</form>

<script>
    let rowIndex = 1;

    function addRow() {
        const tbody = document.getElementById('medicine-order-body');
        const row = `
        <tr>
            <td>
                <select name="orders[${rowIndex}][medicine_id]" class="form-select form-select-sm" required>
                    <option value="">-- Select Medicine --</option>
                    @foreach ($medicines as $medicine)
                        <option value="{{ $medicine->id }}">{{ $medicine->medicine_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="orders[${rowIndex}][qty]" class="form-control form-control-sm" value="1" min="1" required>
            </td>
            <td>
                <select name="orders[${rowIndex}][prescribed_by]" class="form-select form-select-sm">
                    <option value="">-- Select Doctor --</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="orders[${rowIndex}][status]" class="form-select form-select-sm">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="dispensed">Dispensed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </td>
            <td>
                <input type="text" name="orders[${rowIndex}][order_by]" class="form-control form-control-sm" placeholder="Order by">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
        rowIndex++;
        toggleRemoveButtons();
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        toggleRemoveButtons();
    }

    function toggleRemoveButtons() {
        const rows = document.querySelectorAll('#medicine-order-body tr');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-row');
            btn.disabled = rows.length <= 1;
        });
    }
</script>
