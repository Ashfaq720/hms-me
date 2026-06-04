@extends('backend.layouts.master')

@section('title', 'Edit Procedure Order — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Edit Procedure Order</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('icu.admissions.procedure-orders.index', $admission->id) }}"
                    class="btn btn-outline-secondary btn-sm">Back to Queue</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="card mt-2">
            <div class="card-body">
                <form action="{{ route('icu.admissions.procedure-orders.update', [$admission->id, $order->id]) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category" id="po-category" class="form-select form-select-sm" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categoryTypes as $cat => $types)
                                    <option value="{{ $cat }}" @selected(old('category', $order->category) === $cat)>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Type <span class="text-danger">*</span></label>
                            <select name="type" id="po-type" class="form-select form-select-sm" required>
                                <option value="">-- Select Type --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Doctor</label>
                            <select name="doctor_id" class="form-select form-select-sm">
                                <option value="">-- Select Doctor --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        @selected(old('doctor_id', $order->doctor_id) == $doctor->id)>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Priority</label>
                            @php $p = old('priority', $order->priority); @endphp
                            <select name="priority" class="form-select form-select-sm">
                                <option value="Routine" @selected($p === 'Routine')>Routine</option>
                                <option value="Urgent" @selected($p === 'Urgent')>Urgent</option>
                                <option value="STAT" @selected($p === 'STAT')>STAT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Start Date/Time</label>
                            <input type="datetime-local" name="start_datetime"
                                class="form-control form-control-sm"
                                value="{{ old('start_datetime', $order->start_datetime?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Status</label>
                            @php $s = old('status', $order->status); @endphp
                            <select name="status" class="form-select form-select-sm">
                                <option value="Scheduled" @selected($s === 'Scheduled')>Scheduled</option>
                                <option value="InProgress" @selected($s === 'InProgress')>In Progress</option>
                                <option value="Completed" @selected($s === 'Completed')>Completed</option>
                                <option value="OnHold" @selected($s === 'OnHold')>On Hold</option>
                                <option value="Cancelled" @selected($s === 'Cancelled')>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-1">Details</label>
                            <textarea name="details" rows="2" class="form-control form-control-sm">{{ old('details', $order->details) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label mb-1">Remarks</label>
                            <textarea name="remarks" rows="2" class="form-control form-control-sm">{{ old('remarks', $order->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('icu.admissions.procedure-orders.index', $admission->id) }}"
                            class="btn btn-light btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const categoryTypes = @json($categoryTypes);
            const catEl  = document.getElementById('po-category');
            const typeEl = document.getElementById('po-type');
            const initialCategory = @json(old('category', $order->category));
            const initialType     = @json(old('type', $order->type));

            function fillTypes(cat, selected) {
                if (!typeEl) return;
                typeEl.innerHTML = '<option value="">-- Select Type --</option>';
                if (!cat || !categoryTypes[cat]) return;
                categoryTypes[cat].forEach(function (t) {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    if (selected && selected === t) opt.selected = true;
                    typeEl.appendChild(opt);
                });
            }

            if (catEl) {
                catEl.addEventListener('change', function () {
                    fillTypes(this.value, null);
                });
                fillTypes(initialCategory, initialType);
            }
        })();
    </script>
@endsection
