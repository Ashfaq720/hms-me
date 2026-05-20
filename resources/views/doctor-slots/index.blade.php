@extends('backend.layouts.master')

@section('title', 'Doctor Slots')

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-4 mb-3">
                @include('backend.layouts.appointment_setup')
            </div>

            <div class="col-lg-9 col-md-8">
                <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="clearfix">
                        <h1 class="app-page-title mb-0">Doctor Slots</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 mt-1">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Appointment</a>
                                </li>
                                <li class="breadcrumb-item active">Slots</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="card mt-3 overflow-hidden">
                    {{-- Card Header --}}
                    <div class="card-header bg-light border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fa-solid fa-table-cells text-primary me-1"></i> Doctor Slot Configuration
                        </h6>
                    </div>

                    <div class="card-body">
                        {{-- Doctor / Shift Selector --}}
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Doctor <span
                                        class="text-danger">*</span></label>
                                <select id="slot_doctor" class="form-select">
                                    <option value="">-- Select Doctor --</option>
                                    @foreach ($doctors as $d)
                                        <option value="{{ $d->id }}">
                                            {{ $d->name }}@if ($d->doctor_code)
                                                ({{ $d->doctor_code }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Shift <span
                                        class="text-danger">*</span></label>
                                <select id="slot_shift" class="form-select">
                                    <option value="">-- Select Shift --</option>
                                    @foreach ($shifts as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="slot_search_btn" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        {{-- Slot Settings Section (hidden until search) --}}
                        <div id="slot_config_wrap" style="display:none;">
                            <hr class="my-4">

                            <h6 class="fw-semibold text-muted text-uppercase small mb-3">
                                <i class="fa-solid fa-sliders me-1"></i> Slot Settings
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Duration (min) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" id="slot_duration" class="form-control" min="1"
                                        value="15">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Charge Category</label>
                                    <select id="slot_charge_category" class="form-select">
                                        <option value="">-- Select --</option>
                                        @foreach ($chargeCategories as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Charge <span
                                            class="text-danger">*</span></label>
                                    <select id="slot_charge" class="form-select">
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Amount ($)</label>
                                    <input type="number" step="0.01" id="slot_amount" class="form-control"
                                        readonly>
                                </div>
                            </div>

                            {{-- Weekly Schedule --}}
                            <hr class="my-4">

                            <h6 class="fw-semibold text-muted text-uppercase small mb-3">
                                <i class="fa-solid fa-calendar-days me-1"></i> Weekly Schedule
                            </h6>

                            <ul class="nav nav-tabs" role="tablist" id="slot_day_tabs">
                                @foreach ($days as $i => $day)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $i === 0 ? 'active' : '' }}" type="button"
                                            data-bs-toggle="tab" data-bs-target="#day_{{ $day }}">
                                            {{ $day }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                @foreach ($days as $i => $day)
                                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                                        id="day_{{ $day }}">

                                        <div
                                            class="d-flex fw-semibold border-bottom pb-2 mb-2 text-muted small text-uppercase">
                                            <div class="flex-grow-1"><i class="fa-regular fa-clock me-1"></i> Time From
                                            </div>
                                            <div class="flex-grow-1"><i class="fa-regular fa-clock me-1"></i> Time To
                                            </div>
                                            <div style="width:60px" class="text-end">Action</div>
                                        </div>

                                        <div class="slot-rows" data-day="{{ $day }}"></div>

                                        <button type="button"
                                            class="btn btn-outline-primary btn-sm mt-2 js-add-slot"
                                            data-day="{{ $day }}">
                                            <i class="fa-solid fa-plus me-1"></i> Add Time Slot
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-end mt-3">
                                <button type="button" id="slot_save_btn" class="btn btn-primary">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrf = "{{ csrf_token() }}";
            const urls = {
                fetch: "{{ route('doctor-slots.fetch') }}",
                save: "{{ route('doctor-slots.save') }}",
                chargesByCategory: "{{ url('/get-charges-by-category') }}",
                chargeDetails: "{{ url('/get-charge-details') }}",
            };

            const doctorEl = document.getElementById('slot_doctor');
            const shiftEl = document.getElementById('slot_shift');
            const durationEl = document.getElementById('slot_duration');
            const categoryEl = document.getElementById('slot_charge_category');
            const chargeEl = document.getElementById('slot_charge');
            const amountEl = document.getElementById('slot_amount');
            const configWrap = document.getElementById('slot_config_wrap');

            function rowHtml(from, to) {
                return `
                    <div class="d-flex gap-2 mb-2 align-items-center slot-row">
                        <input type="time" class="form-control slot-from" value="${from||''}">
                        <input type="time" class="form-control slot-to" value="${to||''}">
                        <button type="button" class="btn btn-outline-danger btn-sm js-remove-slot" style="width:60px">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>`;
            }

            function addRow(day, from, to) {
                const wrap = document.querySelector(`.slot-rows[data-day="${day}"]`);
                wrap.insertAdjacentHTML('beforeend', rowHtml(from, to));
            }

            function clearAllRows() {
                document.querySelectorAll('.slot-rows').forEach(w => w.innerHTML = '');
            }

            // Add / remove row handlers
            document.querySelectorAll('.js-add-slot').forEach(btn => {
                btn.addEventListener('click', () => addRow(btn.dataset.day, '', ''));
            });
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-remove-slot');
                if (!btn) return;
                btn.closest('.slot-row').remove();
            });

            // Charge Category -> charges
            categoryEl.addEventListener('change', function() {
                chargeEl.innerHTML = '<option value="">-- Select --</option>';
                amountEl.value = '';
                if (!this.value) return;

                fetch(`${urls.chargesByCategory}/${this.value}`)
                    .then(r => r.json())
                    .then(list => {
                        list.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.charge_name;
                            chargeEl.appendChild(opt);
                        });
                    });
            });

            // Charge -> amount
            chargeEl.addEventListener('change', function() {
                amountEl.value = '';
                if (!this.value) return;
                fetch(`${urls.chargeDetails}/${this.value}`)
                    .then(r => r.json())
                    .then(data => {
                        amountEl.value = data.standard_charge ?? '';
                    });
            });

            // Search: load existing config
            document.getElementById('slot_search_btn').addEventListener('click', function() {
                if (!doctorEl.value || !shiftEl.value) {
                    toastr.warning('Please select Doctor and Shift');
                    return;
                }

                const fd = new FormData();
                fd.append('doctor_id', doctorEl.value);
                fd.append('shift_id', shiftEl.value);

                fetch(urls.fetch, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: fd,
                    })
                    .then(r => r.json())
                    .then(async function(json) {
                        configWrap.style.display = '';

                        clearAllRows();

                        const s = json.setting;
                        durationEl.value = s?.consultation_minutes ?? 15;
                        categoryEl.value = s?.charge_category_id ?? '';

                        // load charges for selected category then set charge + amount
                        if (s?.charge_category_id) {
                            await fetch(`${urls.chargesByCategory}/${s.charge_category_id}`)
                                .then(r => r.json())
                                .then(list => {
                                    chargeEl.innerHTML =
                                        '<option value="">-- Select --</option>';
                                    list.forEach(c => {
                                        const opt = document.createElement('option');
                                        opt.value = c.id;
                                        opt.textContent = c.charge_name;
                                        chargeEl.appendChild(opt);
                                    });
                                });
                            chargeEl.value = s?.charge_id ?? '';
                            amountEl.value = s?.amount ?? '';
                        } else {
                            chargeEl.innerHTML =
                                '<option value="">-- Select --</option>';
                            amountEl.value = '';
                        }

                        // populate rows
                        Object.entries(json.times || {}).forEach(([day, rows]) => {
                            rows.forEach(r => addRow(day, r.time_from, r.time_to));
                        });
                    })
                    .catch(() => toastr.error('Failed to load slot data'));
            });

            // Save
            document.getElementById('slot_save_btn').addEventListener('click', function() {
                if (!doctorEl.value || !shiftEl.value) {
                    toastr.warning('Please select Doctor and Shift');
                    return;
                }

                const times = [];
                document.querySelectorAll('.slot-rows').forEach(wrap => {
                    const day = wrap.dataset.day;
                    wrap.querySelectorAll('.slot-row').forEach(row => {
                        const from = row.querySelector('.slot-from').value;
                        const to = row.querySelector('.slot-to').value;
                        if (from && to) times.push({
                            day,
                            time_from: from,
                            time_to: to
                        });
                    });
                });

                const payload = {
                    doctor_id: doctorEl.value,
                    shift_id: shiftEl.value,
                    consultation_minutes: durationEl.value || 15,
                    charge_category_id: categoryEl.value || null,
                    charge_id: chargeEl.value || null,
                    amount: amountEl.value || 0,
                    times: times,
                };

                const btn = this;
                btn.disabled = true;

                fetch(urls.save, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(r => r.json().then(j => ({
                        ok: r.ok,
                        j
                    })))
                    .then(({
                        ok,
                        j
                    }) => {
                        if (!ok) {
                            const msg = j.errors ? Object.values(j.errors).flat().join(
                                    '\n') :
                                (j.message || 'Failed');
                            toastr.error(msg);
                            return;
                        }
                        toastr.success('Schedule saved successfully.');
                    })
                    .catch(() => toastr.error('Failed to save schedule.'))
                    .finally(() => {
                        btn.disabled = false;
                    });
            });
        });
    </script>
@endpush
