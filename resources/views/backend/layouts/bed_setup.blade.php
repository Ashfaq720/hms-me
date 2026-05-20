<div class="card sticky-top shadow-sm h-auto" style="top:30px; height: fit-content;">
    <div class="list-group list-group-flush mb-0">

        {{-- Floor --}}
        <a href="{{ route('floors.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('floors*') ? 'active' : '' }}">
            <i class="bi bi-layers me-2"></i>
            <span>Floor</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Bed Group --}}
        <a href="{{ route('bed-groups.index') }}"
            class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('bed-groups*') ? 'active' : '' }}">
            <i class="bi bi-grid me-2"></i>
            <span>Bed Group</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Bed Type --}}
        <a href="{{ route('bed-types.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('bed-types*') ? 'active' : '' }}">
            <i class="bi bi-ui-checks me-2"></i>
            <span>Bed Type</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

        {{-- Bed --}}
        <a href="{{ route('beds.index') }}"
           class="list-group-item list-group-item-action d-flex align-items-center
           {{ request()->is('beds*') ? 'active' : '' }}">
            <i class="bi bi-hospital me-2"></i>
            <span>Bed</span>
            <i class="bi bi-chevron-right ms-auto"></i>
        </a>

    </div>
</div>
