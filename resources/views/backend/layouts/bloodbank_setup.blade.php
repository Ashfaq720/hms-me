<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="list-group list-group-flush mb-0">

        <a href="{{ route('bb.blood-groups.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.blood-groups.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-vial me-2"></i> Blood Group </span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        <a href="{{ route('bb.components.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.components.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-layer-group me-2"></i> Component</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        {{-- <a href="{{ route('bb.temperature-rules.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.temperature-rules.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-temperature-low me-2"></i> Temperature Rules</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        <a href="{{ route('bb.storage-locations.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.storage-locations.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-warehouse me-2"></i> Storage Locations</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        <a href="{{ route('bb.blood-bags.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.blood-bags.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-bag-shopping me-2"></i> Blood Bag </span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

        <a href="{{ route('bb.deferral-reasons.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.deferral-reasons.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-ban me-2"></i> Deferral Reasons</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a> --}}

        <a href="{{ route('bb.blood-donors.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center justify-content-between {{ request()->routeIs('bb.blood-donors.*') ? 'active' : '' }}">
            <span><i class="fa-solid fa-hand-holding-droplet me-2"></i> Blood Donor</span>
            <i class="fa-solid fa-chevron-right small"></i>
        </a>

    </div>
</div>

@push('styles')
    <style>
        /* Make active item look nicer if your theme doesn't style it */
        .list-group-item.active {
            background: #316AFF !important;
            border-color: #316AFF !important;
            color: #fff !important;
            font-weight: 600;
        }
    </style>
@endpush
