<form method="POST" action="{{ route('ipd-patients.icu-transfer.store', $id) }}" enctype="multipart/form-data">
    @csrf

    @php
        $badge = match ($type ?? '') {
            'NICU' => ['NICU', 'bg-warning text-dark', 'bi-emoji-smile'],
            'CCU'  => ['CCU', 'bg-danger', 'bi-heart'],
            'ICU'  => ['ICU', 'bg-info', 'bi-heart-pulse'],
            default => ['ICU / CCU / NICU', 'bg-secondary', 'bi-shuffle'],
        };
    @endphp

    <div class="alert alert-light border d-flex align-items-center gap-2 mb-3">
        <i class="bi {{ $badge[2] }} fs-4"></i>
        <div>
            <strong>{{ $title ?? 'Transfer to ICU / CCU / NICU' }}</strong>
            <small class="text-muted d-block">Pick an available {{ $badge[0] }} bed below. The current bed-day is auto-charged at the existing rate before the new allocation starts.</small>
        </div>
        <span class="badge {{ $badge[1] }} ms-auto">{{ $badge[0] }}</span>
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label">{{ $badge[0] }} Bed <span class="text-danger">*</span></label>
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
                    <option value="" disabled>No {{ $badge[0] }} beds available</option>
                @endforelse
            </select>
            @error('icu_bed_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">From <span class="text-danger">*</span></label>
            <input type="datetime-local" name="from" value="{{ old('from', now()->format('Y-m-d\TH:i')) }}"
                class="form-control" required>
            @error('from')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" placeholder="Reason for transfer / clinical note" rows="3">{{ old('remarks') }}</textarea>
            @error('remarks')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-danger">
                <i class="bi {{ $badge[2] }}"></i> Transfer to {{ $badge[0] }}
            </button>
        </div>

    </div>
</form>
