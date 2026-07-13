<form action="{{ route('ipd-patients.medicine-orders.update', [$ipdPatient->id, $medicineOrder->id]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">

        {{-- Medicine --}}
        <div class="col-md-6">
            <label for="medicine_id" class="form-label">Medicine <span class="text-danger">*</span></label>
            <select name="medicine_id" id="medicine_id"
                class="form-select @error('medicine_id') is-invalid @enderror" required>
                <option value="">-- Select Medicine --</option>
                @foreach ($medicines as $medicine)
                    <option value="{{ $medicine->id }}" @selected(old('medicine_id', $medicineOrder->medicine_id) == $medicine->id)>
                        {{ $medicine->medicine_name }}
                    </option>
                @endforeach
            </select>
            @error('medicine_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Qty --}}
        <div class="col-md-6">
            <label for="qty" class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="qty" id="qty"
                class="form-control @error('qty') is-invalid @enderror"
                value="{{ old('qty', $medicineOrder->qty) }}" min="1" required>
            @error('qty')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Prescribed By --}}
        <div class="col-md-6">
            <label for="prescribed_by" class="form-label">Prescribed By</label>
            <select name="prescribed_by" id="prescribed_by"
                class="form-select @error('prescribed_by') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('prescribed_by', $medicineOrder->prescribed_by) == $doctor->id)>
                        {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
            @error('prescribed_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Status --}}
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status"
                class="form-select @error('status') is-invalid @enderror">
                @foreach (['pending', 'approved', 'dispensed', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $medicineOrder->status) == $s)>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Order By --}}
        <div class="col-md-3">
            <label for="order_by" class="form-label">Order By</label>
            <input type="text" name="order_by" id="order_by"
                class="form-control @error('order_by') is-invalid @enderror"
                value="{{ old('order_by', $medicineOrder->order_by) }}" placeholder="Order by">
            @error('order_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Update Order</button>
        </div>

    </div>
</form>
