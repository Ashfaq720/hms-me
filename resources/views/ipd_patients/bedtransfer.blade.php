<form method="POST" action="{{ route('ipd-patients.bed-transfer.store', $id) }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">

        {{-- Bed --}}
        <div class="col-md-6">
            <label class="form-label">Bed <span class="text-danger">*</span></label>
            <select name="bed_id" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach ($beds as $b)
                    <option value="{{ $b->id }}" {{ old('bed_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }} (৳ {{ $b->rent }})
                    </option>
                @endforeach
            </select>
            @error('bed_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- From --}}
        <div class="col-md-6">
            <label class="form-label">From <span class="text-danger">*</span></label>
            <input type="datetime-local" name="from"
                value="{{ old('from', isset($ipdBed->from) ? \Carbon\Carbon::parse($ipdBed->from)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                class="form-control" required>
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Bed Remarks --}}
        <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="bed_remarks" value="{{ old('bed_remarks', $ipdBed->remarks ?? '') }}" class="form-control"
                placeholder="Shifting reason / bed note" cols="3"></textarea>
            @error('bed_remarks')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

            {{-- Submit --}}
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>

    </div>
</form>
