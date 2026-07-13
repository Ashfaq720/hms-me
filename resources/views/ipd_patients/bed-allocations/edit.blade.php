<form action="{{ route('ipd-patients.bed-allocations.update', [$ipdPatient->id, $allocation->id]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">

        {{-- Bed --}}
        <div class="col-md-6">
            <label for="bed_id" class="form-label">Bed <span class="text-danger">*</span></label>
            <select name="bed_id" id="bed_id" class="form-select @error('bed_id') is-invalid @enderror" required>
                <option value="">-- Select Bed --</option>
                @foreach ($beds as $bed)
                    <option value="{{ $bed->id }}" @selected(old('bed_id', $allocation->bed_id) == $bed->id)>
                        {{ $bed->name }} (&#2547; {{ $bed->rent }})
                    </option>
                @endforeach
            </select>
            @error('bed_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- From --}}
        <div class="col-md-6">
            <label for="from" class="form-label">From <span class="text-danger">*</span></label>
            <input type="datetime-local" name="from" id="from"
                class="form-control @error('from') is-invalid @enderror"
                value="{{ old('from', $allocation->from ? \Carbon\Carbon::parse($allocation->from)->format('Y-m-d\TH:i') : '') }}"
                required>
            @error('from')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- To --}}
        <div class="col-md-6">
            <label for="to" class="form-label">To</label>
            <input type="datetime-local" name="to" id="to"
                class="form-control @error('to') is-invalid @enderror"
                value="{{ old('to', $allocation->to ? \Carbon\Carbon::parse($allocation->to)->format('Y-m-d\TH:i') : '') }}">
            @error('to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remarks --}}
        <div class="col-md-6">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea name="remarks" id="remarks" rows="1"
                class="form-control @error('remarks') is-invalid @enderror"
                placeholder="Shifting reason / bed note">{{ old('remarks', $allocation->remarks) }}</textarea>
            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="col-12 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Allocation</button>
        </div>
    </div>
</form>
