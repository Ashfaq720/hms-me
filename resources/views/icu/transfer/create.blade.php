@extends('backend.layouts.master')

@section('title', 'ICU Transfer — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex justify-content-between">
            <div>
                <h1 class="app-page-title">Transfer Patient</h1>
                <div class="text-muted">
                    {{ $admission->icu_case_id }} — {{ $admission->patient?->patient_name }}
                    (Bed {{ $admission->bed?->name ?? '-' }})
                </div>
            </div>
            <a href="{{ route('icu.admissions.show', $admission->id) }}"
                class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        @if (session('error')) <div class="alert alert-danger mt-2">{{ session('error') }}</div> @endif

        @if (! empty($blockers))
            <div class="alert alert-warning mt-2">
                <strong>Blockers detected:</strong>
                <ul class="mb-0">
                    @foreach ($blockers as $b) <li>{{ $b }}</li> @endforeach
                </ul>
                <small>You can still proceed by checking <em>Force override</em> below — that gets logged.</small>
            </div>
        @endif

        <form method="POST" action="{{ route('icu.admissions.transfer.store', $admission->id) }}" class="mt-2">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Transfer Type <span class="text-danger">*</span></label>
                            <select name="transfer_type" id="transfer_type" class="form-select" required>
                                <option value="IcuToWard">ICU → IPD</option>
                                <option value="IcuToCcu">ICU → CCU</option>
                                <option value="IcuToIcu">ICU → ICU (different bed)</option>
                                {{-- <option value="IcuToOT">ICU → OT</option>
                                <option value="IcuToHigherCare">ICU → Higher Care (external)</option> --}}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Transfer Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="transfer_time"
                                value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Transfer To <span class="text-danger">*</span></label>
                            <select name="to_bed_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <optgroup label="ICU / CCU / NICU / PICU beds">
                                    @foreach ($icuBeds as $b)
                                        <option value="{{ $b->id }}"
                                            data-icu="{{ optional($b->bedType)->icu_type }}">
                                            {{ $b->name }} [{{ optional($b->bedType)->name }}] (৳ {{ $b->rent }})
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Ward / Cabin  / Regular beds">
                                    @foreach ($wardBeds as $b)
                                        <option value="{{ $b->id }}">
                                            {{ $b->name }} [{{ optional($b->bedType)->name ?? '-' }}] (৳ {{ $b->rent }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="transfer_reason" class="form-control" rows="2" required></textarea>
                        </div>

                        @if (! empty($blockers))
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="force" value="0">
                                    <input class="form-check-input" type="checkbox" id="force" name="force" value="1">
                                    <label class="form-check-label text-danger" for="force">
                                        Force override blockers (logged as administrative decision)
                                    </label>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary">Confirm Transfer</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
