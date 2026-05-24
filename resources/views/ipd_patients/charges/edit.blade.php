@extends('backend.layouts.master')
@section('title', 'Edit Charge - Ipd')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Edit Charge</h4>
            <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <span class="fw-semibold">Patient: {{ $ipdPatient->patient->patient_name ?? 'N/A' }}</span>
                <span class="text-muted ms-2">({{ $ipdPatient->ipd_no }})</span>
            </div>
            <div class="card-body">
                <form action="{{ route('ipd-patients.charges.update', [$ipdPatient->id, $patientCharge->id]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Charge Item --}}
                        <div class="col-md-6">
                            <label for="charge_id" class="form-label">Charge Item <span class="text-danger">*</span></label>
                            <select name="charge_id" id="charge_id" class="form-select @error('charge_id') is-invalid @enderror" required>
                                <option value="">-- Select Charge --</option>
                                @foreach ($charges as $charge)
                                    <option value="{{ $charge->id }}"
                                        data-price="{{ $charge->standard_charge }}"
                                        data-tax="{{ $charge->tax ?? 0 }}"
                                        @selected(old('charge_id', $patientCharge->charge_item == $charge->charge_name ? $charge->id : null) == $charge->id)>
                                        {{ $charge->charge_name }}
                                        ({{ optional($charge->chargeCategory)->name }})
                                        - &#2547; {{ number_format($charge->standard_charge, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('charge_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="col-md-6">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="date" id="date"
                                class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', $patientCharge->date?->format('Y-m-d\TH:i')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Doctor --}}
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                                <option value="">-- Select Doctor --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected(old('doctor_id', $patientCharge->doctor_id) == $doctor->id)>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Department --}}
                        <div class="col-md-6">
                            <label for="department_id" class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $patientCharge->department_id) == $dept->id)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity', $patientCharge->quantity) }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Unit Price (read-only) --}}
                        <div class="col-md-3">
                            <label class="form-label">Unit Price</label>
                            <input type="text" id="display_unit_price" class="form-control bg-light" readonly value="{{ number_format($patientCharge->unit_price, 2) }}">
                        </div>

                        {{-- Tax (read-only) --}}
                        <div class="col-md-3">
                            <label class="form-label">Tax (%)</label>
                            <input type="text" id="display_tax" class="form-control bg-light" readonly value="0.00">
                        </div>

                        {{-- Net Amount (read-only) --}}
                        <div class="col-md-3">
                            <label class="form-label">Net Amount</label>
                            <input type="text" id="display_net_amount" class="form-control bg-light fw-bold" readonly value="{{ number_format($patientCharge->net_amount, 2) }}">
                        </div>

                        {{-- Notes --}}
                        <div class="col-md-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Optional notes about the charge">{{ old('notes', $patientCharge->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('ipd-patients.ipd-patients.show', $ipdPatient->id) }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Charge</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chargeSelect = document.getElementById('charge_id');
        const quantityInput = document.getElementById('quantity');
        const unitPriceDisplay = document.getElementById('display_unit_price');
        const taxDisplay = document.getElementById('display_tax');
        const netAmountDisplay = document.getElementById('display_net_amount');

        function calculate() {
            const selected = chargeSelect.options[chargeSelect.selectedIndex];
            const unitPrice = parseFloat(selected?.dataset?.price) || 0;
            const taxRate = parseFloat(selected?.dataset?.tax) || 0;
            const qty = parseInt(quantityInput.value) || 1;

            const amount = unitPrice * qty;
            const tax = Math.round(amount * taxRate / 100 * 100) / 100;
            const netAmount = amount + tax;

            unitPriceDisplay.value = unitPrice.toFixed(2);
            taxDisplay.value = taxRate.toFixed(2);
            netAmountDisplay.value = netAmount.toFixed(2);
        }

        chargeSelect.addEventListener('change', calculate);
        quantityInput.addEventListener('input', calculate);
        calculate();
    });
</script>
@endpush
