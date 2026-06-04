<form action="{{ route('ipd-patients.case-drs.store', $id) }}" method="POST">
    @csrf

    <div class="row g-3">

        {{-- Date & Time --}}
        <div class="col-md-6">
            <label for="datetime" class="form-label">Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="datetime" id="datetime"
                class="form-control @error('datetime') is-invalid @enderror"
                value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}" required>
            @error('datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Doctor --}}
        <div class="col-md-6">
            <label for="doctor_id" class="form-label">Doctor</label>
            <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                <option value="">-- Select Doctor --</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                        {{ $doctor->name }}
                    </option>
                @endforeach
            </select>
            @error('doctor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Shift --}}
        <div class="col-md-3">
            <label for="shift" class="form-label">Shift</label>
            <select name="shift" id="shift" class="form-select @error('shift') is-invalid @enderror">
                <option value="">-- Select Shift --</option>
                @foreach (['Morning', 'Evening', 'Night'] as $s)
                    <option value="{{ $s }}" @selected(old('shift') == $s)>{{ $s }}</option>
                @endforeach
            </select>
            @error('shift')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Priority --}}
        <div class="col-md-3">
            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
            <select name="priority" id="priority"
                class="form-select @error('priority') is-invalid @enderror" required>
                @foreach (['Normal', 'Urgent', 'Critical'] as $p)
                    <option value="{{ $p }}" @selected(old('priority', 'Normal') == $p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Order To --}}
        <div class="col-md-6">
            <label for="order_to" class="form-label">Order To</label>
            <select name="order_to" id="order_to" class="form-select @error('order_to') is-invalid @enderror">
                <option value="">-- Select --</option>
                @foreach (['Nurse', 'Round Dr'] as $o)
                    <option value="{{ $o }}" @selected(old('order_to') == $o)>{{ $o }}</option>
                @endforeach
            </select>
            @error('order_to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Diagnosis --}}
        <div class="col-md-12">
            <label for="diagnosis" class="form-label">Diagnosis</label>
            <textarea name="diagnosis" id="diagnosis" rows="2"
                class="form-control @error('diagnosis') is-invalid @enderror"
                placeholder="Enter diagnosis">{{ old('diagnosis') }}</textarea>
            @error('diagnosis')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Note --}}
        <div class="col-12">
            <label for="note" class="form-label">Note</label>
            <textarea name="note" id="note" rows="3"
                class="form-control @error('note') is-invalid @enderror"
                placeholder="Enter note">{{ old('note') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Observations --}}
        <div class="col-12">
            <label for="observations" class="form-label">Observations</label>
            <textarea name="observations" id="observations" rows="2"
                class="form-control @error('observations') is-invalid @enderror"
                placeholder="Enter observations">{{ old('observations') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Order --}}
        <div class="col-12">
            <label for="order" class="form-label">Order</label>
            <textarea name="order" id="order" rows="3"
                class="form-control @error('order') is-invalid @enderror"
                placeholder="Enter order">{{ old('order') }}</textarea>
            @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>

    </div>
</form>
