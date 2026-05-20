<form method="POST" action="{{ route('ipd-patients.icu-transfer.store', $id) }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">

        {{-- ICU Bed --}}
        <div class="col-md-6">
            <label class="form-label">ICU Bed <span class="text-danger">*</span></label>
            <select name="icu_bed_id" class="form-select" required>
                <option value="">-- Select --</option>
                @forelse ($icuBeds as $b)
                    <option value="{{ $b->id }}" {{ old('icu_bed_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                        @if (optional($b->bedType)->name)
                            [{{ $b->bedType->name }}]
                        @endif
                        (৳ {{ $b->rent }})
                    </option>
                @empty
                    <option value="" disabled>No ICU beds available</option>
                @endforelse
            </select>
            @error('icu_bed_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- From --}}
        <div class="col-md-6">
            <label class="form-label">From <span class="text-danger">*</span></label>
            <input type="datetime-local" name="from" value="{{ old('from', now()->format('Y-m-d\TH:i')) }}"
                class="form-control" required>
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remarks --}}
        <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" placeholder="Reason for ICU transfer / clinical note" rows="3">{{ old('remarks') }}</textarea>
            @error('remarks')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-heart-pulse"></i> Transfer to ICU / CCU
            </button>
        </div>

    </div>
</form>
