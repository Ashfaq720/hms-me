@extends('backend.layouts.master')

@section('title', 'Patient Queue')

@section('content')
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Patient Queue</h5>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('patient-queue.index') }}" id="pqForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="pq_doctor" class="form-select" required>
                                <option value="">-- Select Doctor --</option>
                                @foreach ($doctors as $d)
                                    <option value="{{ $d->id }}"
                                        {{ (string) $filters['doctor_id'] === (string) $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}@if ($d->doctor_code)
                                            ({{ $d->doctor_code }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Shift <span class="text-danger">*</span></label>
                            <select name="shift_id" id="pq_shift" class="form-select" required>
                                <option value="">-- Select Shift --</option>
                                @foreach ($shifts as $s)
                                    <option value="{{ $s->id }}"
                                        {{ (string) $filters['shift_id'] === (string) $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="pq_date" class="form-control"
                                value="{{ $filters['date'] ?? now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Slot</label>
                            <select name="slot" id="pq_slot" class="form-select">
                                <option value="">-- All --</option>
                                @foreach ($slots as $s)
                                    @php
                                        $val = substr($s->time_from, 0, 5) . '|' . substr($s->time_to, 0, 5);
                                        $label =
                                            \Carbon\Carbon::parse($s->time_from)->format('h:i A') .
                                            ' - ' .
                                            \Carbon\Carbon::parse($s->time_to)->format('h:i A');
                                    @endphp
                                    <option value="{{ $val }}"
                                        {{ $filters['slot'] === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-4">
                    <table class="table display table-row-rounded">
                        <thead class="table-light">
                            <tr>
                                <th>Appointment S.No.</th>
                                <th>Patient Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $i => $a)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $a->patient->patient_name ?? '-' }}</td>
                                    <td>{{ $a->patient->mobileno ?? '-' }}</td>
                                    <td>{{ $a->patient->email ?? '-' }}</td>
                                    <td>{{ $a->date ? \Carbon\Carbon::parse($a->date)->format('d M Y') : '-' }}</td>
                                    <td>{{ $a->time ? \Carbon\Carbon::parse($a->time)->format('h:i A') : '-' }}</td>
                                    <td>{{ $a->source ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-danger py-3">No Record Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const slotsUrl = "{{ route('patient-queue.slots') }}";
                const csrf = "{{ csrf_token() }}";
                const doctorEl = document.getElementById('pq_doctor');
                const shiftEl = document.getElementById('pq_shift');
                const dateEl = document.getElementById('pq_date');
                const slotEl = document.getElementById('pq_slot');

                function refreshSlots() {
                    if (!doctorEl.value || !shiftEl.value || !dateEl.value) return;

                    const fd = new FormData();
                    fd.append('doctor_id', doctorEl.value);
                    fd.append('shift_id', shiftEl.value);
                    fd.append('date', dateEl.value);

                    fetch(slotsUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: fd,
                        })
                        .then(r => r.json())
                        .then(list => {
                            const current = slotEl.value;
                            slotEl.innerHTML = '<option value="">-- All --</option>';
                            list.forEach(s => {
                                const val = `${s.time_from}|${s.time_to}`;
                                const opt = document.createElement('option');
                                opt.value = val;
                                opt.textContent = fmt12(s.time_from) + ' - ' + fmt12(s.time_to);
                                if (val === current) opt.selected = true;
                                slotEl.appendChild(opt);
                            });
                        });
                }

                function fmt12(t) {
                    const [h, m] = t.split(':').map(Number);
                    const ap = h >= 12 ? 'PM' : 'AM';
                    const hh = ((h + 11) % 12 + 1);
                    return String(hh).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ' ' + ap;
                }

                [doctorEl, shiftEl, dateEl].forEach(el => el.addEventListener('change', refreshSlots));
            });
        </script>
    @endpush
