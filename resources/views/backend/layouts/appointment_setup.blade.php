<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="fi fi-rr-calendar-clock me-2"></i> Appointment Setup</h6>
    </div>
    <div class="list-group list-group-flush mb-0">

        {{-- Doctor Slots --}}
        <a href="{{ route('doctor-slots.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('doctor-slots.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-table-cells me-2"></i> Doctor Slots</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        {{-- Doctor Shift --}}
        <a href="{{ route('doctor-shifts.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('doctor-shifts.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-user-clock me-2"></i> Doctor Shift</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        {{-- Shift --}}
        <a href="{{ route('shifts.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-clock me-2"></i> Shift</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        {{-- Appointment Priority --}}
        <a href="{{ route('appointment-priorities.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('appointment-priorities.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-flag me-2"></i> Appointment Priority</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

    </div>
</div>

@push('styles')
    <style>
        .list-group-item.active {
            background: #316AFF !important;
            border-color: #316AFF !important;
            color: #fff !important;
            font-weight: 600;
        }
    </style>
@endpush
