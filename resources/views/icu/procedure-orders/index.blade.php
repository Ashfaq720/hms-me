@extends('backend.layouts.master')

@section('title', 'Procedure Orders — ' . $admission->icu_case_id)

@section('content')
    <div class="container">
        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h1 class="app-page-title">Procedure Orders</h1>
                <div class="text-muted">
                    <a href="{{ route('icu.admissions.show', $admission->id) }}"
                        class="fw-semibold">{{ $admission->icu_case_id }}</a>
                    — {{ $admission->patient?->patient_name }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('icu.admissions.show', $admission->id) }}"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success mt-2">{{ session('success') }}</div> @endif
        @if (session('error'))   <div class="alert alert-danger mt-2">{{ session('error') }}</div>   @endif

        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (in_array($admission->status, ['Approved', 'Admitted']))
            <div class="card mt-2">
                <div class="card-header py-2"><strong>Add Procedure Order</strong></div>
                <div class="card-body">
                    <form action="{{ route('icu.admissions.procedure-orders.store', $admission->id) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category <span class="text-danger">*</span></label>
                                <select name="category" id="po-category" class="form-select form-select-sm" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categoryTypes as $cat => $types)
                                        <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
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
                                            @selected(old('doctor_id', $admission->referring_doctor_id) == $doctor->id)>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Priority</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="Routine" @selected(old('priority') === 'Routine')>Routine</option>
                                    <option value="Urgent" @selected(old('priority') === 'Urgent')>Urgent</option>
                                    <option value="STAT" @selected(old('priority') === 'STAT')>STAT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Start Date/Time</label>
                                <input type="datetime-local" name="start_datetime"
                                    class="form-control form-control-sm" value="{{ old('start_datetime') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="Scheduled" @selected(old('status') === 'Scheduled')>Scheduled</option>
                                    <option value="InProgress" @selected(old('status') === 'InProgress')>In Progress</option>
                                    <option value="Completed" @selected(old('status') === 'Completed')>Completed</option>
                                    <option value="OnHold" @selected(old('status') === 'OnHold')>On Hold</option>
                                    <option value="Cancelled" @selected(old('status') === 'Cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1">Details</label>
                                <textarea name="details" rows="2" class="form-control form-control-sm">{{ old('details') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label mb-1">Remarks</label>
                                <textarea name="remarks" rows="2" class="form-control form-control-sm">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="reset" class="btn btn-light btn-sm">Reset</button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-header py-2"><strong>Procedure Queue</strong></div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">SN</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Doctor</th>
                                <th>Priority</th>
                                <th>Start</th>
                                <th>Status</th>
                                <th>Details</th>
                                <th>Created</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->category }}</td>
                                    <td>{{ $order->type }}</td>
                                    <td>{{ $order->doctor->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $pClass = [
                                                'Routine' => 'bg-secondary-subtle text-secondary',
                                                'Urgent'  => 'bg-warning-subtle text-warning',
                                                'STAT'    => 'bg-danger-subtle text-danger',
                                            ][$order->priority] ?? 'bg-secondary-subtle text-secondary';
                                        @endphp
                                        <span class="badge {{ $pClass }}">{{ $order->priority }}</span>
                                    </td>
                                    <td>{{ $order->start_datetime?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        @php
                                            $sClass = [
                                                'Scheduled'  => 'bg-info-subtle text-info',
                                                'InProgress' => 'bg-primary-subtle text-primary',
                                                'Completed'  => 'bg-success-subtle text-success',
                                                'OnHold'     => 'bg-warning-subtle text-warning',
                                                'Cancelled'  => 'bg-secondary-subtle text-secondary',
                                            ][$order->status] ?? 'bg-secondary-subtle text-secondary';
                                        @endphp
                                        <span class="badge {{ $sClass }}">{{ $order->status }}</span>
                                    </td>
                                    <td>{{ $order->details ?? '-' }}</td>
                                    <td>{{ $order->created_at?->format('d M Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('icu.admissions.procedure-orders.edit', [$admission->id, $order->id]) }}">
                                                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form
                                                        action="{{ route('icu.admissions.procedure-orders.destroy', [$admission->id, $order->id]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this procedure order?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-trash text-danger me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No procedure orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const categoryTypes = @json($categoryTypes);
            const catEl  = document.getElementById('po-category');
            const typeEl = document.getElementById('po-type');
            const oldCategory = @json(old('category'));
            const oldType     = @json(old('type'));

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
                if (oldCategory) fillTypes(oldCategory, oldType);
            }
        })();
    </script>
@endsection
