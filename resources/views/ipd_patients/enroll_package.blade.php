<form method="POST" action="{{ route('ipd-patients.packages.enroll.store', $id) }}">
    @csrf

    <div class="alert alert-light border d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-box-seam fs-4 text-primary"></i>
        <div>
            <strong>Enrol Package for {{ $ipdPatient->patient->patient_name ?? 'patient' }}</strong>
            <small class="text-muted d-block">
                Pick a package. Included services are recorded as consumption entries linked to this IPD encounter,
                so package-covered items can be flagged on the bill automatically.
            </small>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-12">
            <label class="form-label">Package <span class="text-danger">*</span></label>
            <select name="package_id" id="enrollPackageSelect" class="form-select" required>
                <option value="">-- Select a package --</option>
                @foreach ($packages->groupBy('package_type') as $type => $group)
                    <optgroup label="{{ $type ?: 'OTHER' }} ({{ $group->count() }})">
                        @foreach ($group as $pkg)
                            <option value="{{ $pkg->id }}"
                                data-price="{{ $pkg->total_amount }}"
                                data-validity="{{ $pkg->validity_days ?? 30 }}"
                                data-type="{{ $pkg->package_type }}">
                                {{ $pkg->name }} — ৳ {{ number_format((float) $pkg->total_amount, 2) }}
                                @if ($pkg->validity_days) · {{ $pkg->validity_days }}d @endif
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <small class="text-muted">{{ $packages->count() }} active packages across {{ $packages->groupBy('package_type')->count() }} categories</small>
            @error('package_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
            @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" id="enrollEndDate" class="form-control" value="{{ old('end_date') }}">
            @error('end_date')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Agreed Price (৳)</label>
            <input type="number" step="0.01" name="agreed_price" id="enrollAgreedPrice" class="form-control" value="{{ old('agreed_price') }}" placeholder="defaults to package price">
            @error('agreed_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Advance / Paid (৳)</label>
            <input type="number" step="0.01" name="paid_amount" class="form-control" value="{{ old('paid_amount', 0) }}">
            @error('paid_amount')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Clinical / billing remarks">{{ old('notes') }}</textarea>
            @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Enrol Package
            </button>
        </div>
    </div>
</form>

<script>
(function () {
    const sel = document.getElementById('enrollPackageSelect');
    const price = document.getElementById('enrollAgreedPrice');
    const endDate = document.getElementById('enrollEndDate');
    if (!sel) return;
    sel.addEventListener('change', function () {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;
        if (price && !price.value) price.value = opt.dataset.price || '';
        if (endDate && !endDate.value) {
            const start = document.querySelector('input[name=start_date]')?.value || new Date().toISOString().slice(0,10);
            const days = parseInt(opt.dataset.validity || '30', 10);
            const d = new Date(start);
            d.setDate(d.getDate() + days);
            endDate.value = d.toISOString().slice(0,10);
        }
    });
})();
</script>
