@php
    // old values priority > edit values > defaults
    $pkgName = old('name', $package->name ?? '');
    $pkgDiscount = old('discount', $package->discount ?? 0);
    $pkgDesc = old('description', $package->description ?? '');

    // items: use old('items') when validation fails, else use package services for edit, else one empty row
    $items = old('items');

    if ($items === null) {
        if (!empty($package) && $package->services) {
            $items = $package->services->map(function($ps) {
                return [
                    'service_id' => $ps->service_id,
                    'quantity'   => $ps->quantity,
                    'rate'       => $ps->rate,
                ];
            })->toArray();
        } else {
            $items = [
                ['service_id' => '', 'quantity' => 1, 'rate' => 0],
            ];
        }
    }
@endphp

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Package <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ $pkgName }}" required>
        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Discount (%) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" max="100" name="discount" id="discount"
               class="form-control" value="{{ $pkgDiscount }}" required>
        @error('discount') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4">{{ $pkgDesc }}</textarea>
    @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Package Items</h6>
            <button type="button" class="btn btn-primary btn-sm" id="addRow">Add</button>
        </div>

        @error('items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Service <span class="text-danger">*</span></th>
                        <th style="width:170px;">Qty <span class="text-danger">*</span></th>
                        <th style="width:180px;">Rate <span class="text-danger">*</span></th>
                        <th style="width:180px;">Amount</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">

                    @foreach($items as $i => $row)
                        <tr class="itemRow">
                            <td class="sl">{{ $i+1 }}</td>

                            <td>
                                <select name="items[{{ $i }}][service_id]" class="form-select service" required>
                                    <option value="">Select Service</option>
                                    @foreach($services as $s)
                                        <option value="{{ $s->id }}"
                                            {{ (string)($row['service_id'] ?? '') === (string)$s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("items.$i.service_id") <div class="text-danger small">{{ $message }}</div> @enderror
                            </td>

                            <td>
                                <input type="number" step="0.01" min="1"
                                       name="items[{{ $i }}][quantity]"
                                       class="form-control qty"
                                       value="{{ $row['quantity'] ?? 1 }}" required>
                                @error("items.$i.quantity") <div class="text-danger small">{{ $message }}</div> @enderror
                            </td>

                            <td>
                                <input type="number" step="0.01" min="0"
                                       name="items[{{ $i }}][rate]"
                                       class="form-control rate"
                                       value="{{ $row['rate'] ?? 0 }}" required>
                                @error("items.$i.rate") <div class="text-danger small">{{ $message }}</div> @enderror
                            </td>

                            <td>
                                <input type="text" class="form-control amount" value="0" readonly>
                            </td>

                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow">&times;</button>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <div class="fw-bold">Subtotal: ৳ <span id="subtotal">0</span></div>
            <div class="fw-bold">Discount: ৳ <span id="discountAmount">0</span></div>
            <div class="fw-bold fs-5">Net Total: ৳ <span id="netTotal">0</span></div>
        </div>
    </div>
</div>

{{-- ===== JS (keep inside same file) ===== --}}
@push('scripts')
<script>
(function () {
    const itemsBody = document.getElementById('itemsBody');
    const addRowBtn = document.getElementById('addRow');
    const discountInput = document.getElementById('discount');

    function renumberRows() {
        const rows = itemsBody.querySelectorAll('tr');
        rows.forEach((row, i) => {
            row.querySelector('.sl').innerText = i + 1;
            row.querySelector('.service').name = `items[${i}][service_id]`;
            row.querySelector('.qty').name     = `items[${i}][quantity]`;
            row.querySelector('.rate').name    = `items[${i}][rate]`;
        });
    }

    function calcRow(row) {
        const qty = parseFloat(row.querySelector('.qty').value || 0);
        const rate = parseFloat(row.querySelector('.rate').value || 0);
        const amount = qty * rate;
        row.querySelector('.amount').value = amount.toFixed(2);
        return amount;
    }

    function calcTotal() {
        let subtotal = 0;
        itemsBody.querySelectorAll('tr').forEach(row => subtotal += calcRow(row));

        const d = parseFloat(discountInput.value || 0);
        const discountAmount = subtotal * (d / 100);
        const netTotal = subtotal - discountAmount;

        document.getElementById('subtotal').innerText = subtotal.toFixed(2);
        document.getElementById('discountAmount').innerText = discountAmount.toFixed(2);
        document.getElementById('netTotal').innerText = netTotal.toFixed(2);
    }

    addRowBtn.addEventListener('click', function () {
        const index = itemsBody.querySelectorAll('tr').length;

        const tr = document.createElement('tr');
        tr.classList.add('itemRow');
        tr.innerHTML = `
            <td class="sl">${index + 1}</td>
            <td>
                <select class="form-select service" name="items[${index}][service_id]" required>
                    <option value="">Select Service</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" step="0.01" min="1" name="items[${index}][quantity]" class="form-control qty" value="1" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${index}][rate]" class="form-control rate" value="0" required></td>
            <td><input type="text" class="form-control amount" value="0" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
        `;

        itemsBody.appendChild(tr);
        renumberRows();
        calcTotal();
    });

    itemsBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeRow')) {
            const rows = itemsBody.querySelectorAll('tr');
            if (rows.length === 1) return; // keep at least one row
            e.target.closest('tr').remove();
            renumberRows();
            calcTotal();
        }
    });

    itemsBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate')) {
            calcTotal();
        }
    });

    discountInput.addEventListener('input', calcTotal);

    calcTotal();
})();
</script>
@endpush
