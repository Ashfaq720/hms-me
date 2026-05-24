<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-journal-medical me-2 text-danger"></i>Patient History</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addHistoryForm">
            <i class="bi bi-plus-lg me-1"></i>Add Entry
        </button>
    </div>

    {{-- Add Entry Form --}}
    <div class="collapse" id="addHistoryForm">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('opd-patients.histories.store', $opdPatient->id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">History Type <span class="text-danger">*</span></label>
                        <select name="history_type" class="form-select @error('history_type') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            <option value="medical"  @selected(old('history_type') === 'medical')>Medical History</option>
                            <option value="surgical" @selected(old('history_type') === 'surgical')>Surgical History</option>
                            <option value="family"   @selected(old('history_type') === 'family')>Family History</option>
                            <option value="allergy"  @selected(old('history_type') === 'allergy')>Allergy</option>
                        </select>
                        @error('history_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="2" required
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Describe the history…">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-save me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- History Entries --}}
    <div class="card-body p-0">
        @forelse($patientHistories->groupBy('history_type') as $type => $entries)
            @php
                $labels = ['medical'=>'Medical History','surgical'=>'Surgical History','family'=>'Family History','allergy'=>'Allergy'];
                $badges = ['medical'=>'bg-primary','surgical'=>'bg-warning text-dark','family'=>'bg-info text-dark','allergy'=>'bg-danger'];
            @endphp
            <div class="border-bottom px-3 py-2">
                <span class="badge {{ $badges[$type] ?? 'bg-secondary' }} mb-2">{{ $labels[$type] ?? ucfirst($type) }}</span>
                <table class="table table-sm align-middle mb-0 custom-table">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th style="width:140px;">Recorded By</th>
                            <th style="width:110px;">Date</th>
                            <th style="width:80px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $h)
                        <tr>
                            <td class="small" style="white-space:pre-wrap;">{{ $h->description }}</td>
                            <td class="small">{{ $h->recordedBy?->name ?? '—' }}</td>
                            <td class="small">{{ $h->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <form action="{{ route('opd-patients.histories.destroy', [$opdPatient->id, $h->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Remove this history entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light" title="Delete">
                                        <i class="bi bi-trash text-danger small"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-medical fs-1 d-block mb-2 opacity-25"></i>
                No patient history recorded yet.
            </div>
        @endforelse
    </div>
</div>
