<form action="{{ route('admin.pharmacy.inventory.purchase.store') }}" method="POST" id="purchaseForm">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger py-2 small mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Purchase Header --}}
    <div class="row g-2 mb-3 pb-3 border-bottom">
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Supplier</label>
            <select name="supplier_id" class="form-select form-select-sm select2" data-placeholder="— optional —">
                <option value=""></option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->supplier_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Purchase Date</label>
            <input type="date" name="purchase_date" class="form-control form-control-sm"
                   value="{{ old('purchase_date', date('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Invoice / Ref. No.</label>
            <input type="text" name="invoice_no" class="form-control form-control-sm"
                   placeholder="INV-0001" value="{{ old('invoice_no') }}">
        </div>
    </div>

    {{-- Items Table --}}
    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
        <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                <tr>
                    <th style="min-width:190px;">Medicine <span class="text-danger">*</span></th>
                    <th style="min-width:105px;">Batch No <span class="text-danger">*</span></th>
                    <th style="min-width:115px;">Store <span class="text-danger">*</span></th>
                    <th style="min-width:65px;">Qty <span class="text-danger">*</span></th>
                    <th style="min-width:100px;">Unit Cost <span class="text-danger">*</span></th>
                    <th style="min-width:100px;">Sell Price <span class="text-danger">*</span></th>
                    <th style="min-width:120px;">Expiry Date</th>
                    <th style="min-width:90px;" class="text-end">Line Total</th>
                    <th style="width:36px;"></th>
                </tr>
            </thead>
            <tbody id="purchaseRows"></tbody>
        </table>
    </div>

    {{-- Add Row + Grand Total --}}
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <button type="button" id="addPurchaseRowBtn" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Row
        </button>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="small text-muted fw-medium">Grand Total (TK):</span>
            <input type="text" id="grandTotalDisplay"
                   class="form-control form-control-sm bg-light fw-semibold text-end"
                   style="width:120px;" readonly value="0.00">
        </div>
    </div>

    {{-- Note --}}
    <div class="mb-3">
        <label class="form-label small text-muted mb-1">Note / Remarks</label>
        <textarea name="note" class="form-control form-control-sm" rows="2"
                  placeholder="Delivery notes, invoice remarks...">{{ old('note') }}</textarea>
    </div>

    <div class="d-flex justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-box-arrow-in-down me-1"></i> Record Purchase
        </button>
    </div>
</form>

<script>
(function () {
    var medicines = @json($medicines);
    var stores    = @json($stores);
    var rowIndex  = 0;

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str == null ? '' : String(str);
        return d.innerHTML;
    }

    function buildMedOptions() {
        var html = '<option value="">Select Drug</option>';
        for (var i = 0; i < medicines.length; i++) {
            html += '<option value="' + medicines[i].id + '">' + esc(medicines[i].name) + '</option>';
        }
        return html;
    }

    function buildStoreList(idx) {
        var html = '';
        for (var i = 0; i < stores.length; i++) {
            html += '<option value="' + esc(stores[i]) + '">';
        }
        return '<datalist id="storeList_' + idx + '">' + html + '</datalist>';
    }

    function buildRow() {
        var idx = rowIndex++;
        var tmp = document.createElement('tbody');
        tmp.innerHTML =
            '<tr class="purchase-row">' +
            '<td><select name="items[' + idx + '][medicine_id]" class="form-select form-select-sm purchase-med-select" required style="width:100%;">' + buildMedOptions() + '</select></td>' +
            '<td><input type="text" name="items[' + idx + '][batch_no]" class="form-control form-control-sm" placeholder="BT-0001" required></td>' +
            '<td>' +
              '<input type="text" name="items[' + idx + '][store]" class="form-control form-control-sm" list="storeList_' + idx + '" placeholder="Store" required>' +
              buildStoreList(idx) +
            '</td>' +
            '<td><input type="number" name="items[' + idx + '][quantity]" class="form-control form-control-sm row-qty" min="1" placeholder="0" required></td>' +
            '<td><input type="number" name="items[' + idx + '][purchase_price]" class="form-control form-control-sm row-cost" min="0" step="0.01" placeholder="0.00" required></td>' +
            '<td><input type="number" name="items[' + idx + '][selling_price]" class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00" required></td>' +
            '<td><input type="date" name="items[' + idx + '][expiry_date]" class="form-control form-control-sm"></td>' +
            '<td class="text-end"><input type="text" class="form-control form-control-sm bg-light row-total text-end" readonly value="0.00"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn px-1 py-0" title="Remove" style="line-height:1.2;"><i class="bi bi-x-lg" style="font-size:0.7rem;"></i></button></td>' +
            '</tr>';
        return tmp.querySelector('tr');
    }

    function initSelect2(tr) {
        var sel = tr.querySelector('.purchase-med-select');
        if (sel && typeof $ !== 'undefined' && $.fn.select2) {
            $(sel).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#commonModal'),
                placeholder: 'Select Drug',
            });
        }
    }

    function calcLine(row) {
        var qty  = parseFloat(row.querySelector('.row-qty').value)  || 0;
        var cost = parseFloat(row.querySelector('.row-cost').value) || 0;
        row.querySelector('.row-total').value = (qty * cost).toFixed(2);
        calcGrand();
    }

    function calcGrand() {
        var total = 0;
        document.querySelectorAll('#purchaseRows .row-total').forEach(function (el) {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('grandTotalDisplay').value = total.toFixed(2);
    }

    function addRow() {
        var tr = buildRow();
        document.getElementById('purchaseRows').appendChild(tr);
        initSelect2(tr);
    }

    document.getElementById('purchaseRows').addEventListener('input', function (e) {
        if (e.target.classList.contains('row-qty') || e.target.classList.contains('row-cost')) {
            calcLine(e.target.closest('tr.purchase-row'));
        }
    });

    document.getElementById('purchaseRows').addEventListener('click', function (e) {
        if (!e.target.closest('.remove-row-btn')) return;
        if (document.querySelectorAll('#purchaseRows tr.purchase-row').length > 1) {
            e.target.closest('tr').remove();
            calcGrand();
        }
    });

    document.getElementById('addPurchaseRowBtn').onclick = addRow;

    addRow();
})();
</script>
